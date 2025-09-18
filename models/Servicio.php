<?php
/**
 * Modelo Servicio
 */
class Servicio {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        // Calcular fecha de vencimiento basada en el período
        $fechaVencimiento = $this->calcularFechaVencimiento($data['fecha_inicio'], $data['periodo_vencimiento']);
        
        $stmt = $this->db->prepare("
            INSERT INTO servicios (cliente_id, tipo_servicio_id, nombre, descripcion, dominio, monto, 
                                 periodo_vencimiento, fecha_inicio, fecha_vencimiento, fecha_proximo_vencimiento) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['cliente_id'],
            $data['tipo_servicio_id'],
            $data['nombre'],
            $data['descripcion'] ?? null,
            $data['dominio'] ?? null,
            $data['monto'],
            $data['periodo_vencimiento'],
            $data['fecha_inicio'],
            $fechaVencimiento,
            $fechaVencimiento
        ]);
    }
    
    public function findAll($clienteId = null, $limit = null, $offset = 0, $searchTerm = null) {
        $sql = "SELECT s.*, c.nombre_razon_social, c.email, c.telefono, ts.nombre as tipo_servicio_nombre 
                FROM servicios s 
                INNER JOIN clientes c ON s.cliente_id = c.id 
                INNER JOIN tipos_servicios ts ON s.tipo_servicio_id = ts.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($clienteId) {
            $sql .= " AND s.cliente_id = ?";
            $params[] = $clienteId;
        }
        
        if ($searchTerm) {
            $sql .= " AND (s.nombre LIKE ? OR s.descripcion LIKE ? OR s.dominio LIKE ? OR c.nombre_razon_social LIKE ?)";
            $searchParam = '%' . $searchTerm . '%';
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
        }
        
        $sql .= " ORDER BY s.fecha_vencimiento ASC";
        
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT s.*, c.nombre_razon_social, c.email, c.telefono, ts.nombre as tipo_servicio_nombre 
            FROM servicios s 
            INNER JOIN clientes c ON s.cliente_id = c.id 
            INNER JOIN tipos_servicios ts ON s.tipo_servicio_id = ts.id 
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function update($id, $data) {
        // Recalcular fecha de vencimiento si cambia el período o fecha de inicio
        $fechaVencimiento = $this->calcularFechaVencimiento($data['fecha_inicio'], $data['periodo_vencimiento']);
        
        $stmt = $this->db->prepare("
            UPDATE servicios 
            SET cliente_id = ?, tipo_servicio_id = ?, nombre = ?, descripcion = ?, dominio = ?,
                monto = ?, periodo_vencimiento = ?, fecha_inicio = ?, 
                fecha_vencimiento = ?, fecha_proximo_vencimiento = ?, estado = ? 
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['cliente_id'],
            $data['tipo_servicio_id'],
            $data['nombre'],
            $data['descripcion'] ?? null,
            $data['dominio'] ?? null,
            $data['monto'],
            $data['periodo_vencimiento'],
            $data['fecha_inicio'],
            $fechaVencimiento,
            $fechaVencimiento,
            $data['estado'] ?? 'activo',
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE servicios SET estado = 'cancelado' WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getServiciosPorVencer($dias = 30) {
        $stmt = $this->db->prepare("
            SELECT s.*, c.nombre_razon_social, c.email, ts.nombre as tipo_servicio_nombre 
            FROM servicios s 
            INNER JOIN clientes c ON s.cliente_id = c.id 
            INNER JOIN tipos_servicios ts ON s.tipo_servicio_id = ts.id 
            WHERE s.estado = 'activo' 
            AND s.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
            AND s.fecha_vencimiento >= CURDATE()
            ORDER BY s.fecha_vencimiento ASC
        ");
        $stmt->execute([$dias]);
        return $stmt->fetchAll();
    }
    
    public function getServiciosVencidos() {
        $stmt = $this->db->query("
            SELECT s.*, c.nombre_razon_social, c.email, ts.nombre as tipo_servicio_nombre 
            FROM servicios s 
            INNER JOIN clientes c ON s.cliente_id = c.id 
            INNER JOIN tipos_servicios ts ON s.tipo_servicio_id = ts.id 
            WHERE s.estado = 'activo' 
            AND s.fecha_vencimiento < CURDATE()
            ORDER BY s.fecha_vencimiento ASC
        ");
        return $stmt->fetchAll();
    }
    
    public function renovarServicio($id, $fechaPago) {
        // Obtener el servicio actual
        $servicio = $this->findById($id);
        if (!$servicio) return false;
        
        // Calcular nueva fecha de vencimiento
        $nuevaFechaVencimiento = $this->calcularFechaVencimiento($fechaPago, $servicio['periodo_vencimiento']);
        
        // Actualizar servicio
        $stmt = $this->db->prepare("
            UPDATE servicios 
            SET fecha_vencimiento = ?, fecha_proximo_vencimiento = ?, estado = 'activo' 
            WHERE id = ?
        ");
        return $stmt->execute([$nuevaFechaVencimiento, $nuevaFechaVencimiento, $id]);
    }
    
    private function calcularFechaVencimiento($fechaInicio, $periodo) {
        $fecha = new DateTime($fechaInicio);
        
        switch ($periodo) {
            case 'mensual':
                $fecha->add(new DateInterval('P1M'));
                break;
            case 'trimestral':
                $fecha->add(new DateInterval('P3M'));
                break;
            case 'semestral':
                $fecha->add(new DateInterval('P6M'));
                break;
            case 'anual':
            default:
                $fecha->add(new DateInterval('P1Y'));
                break;
        }
        
        return $fecha->format('Y-m-d');
    }
    
    public function getEstadisticas() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_servicios,
                SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as servicios_activos,
                SUM(CASE WHEN estado = 'vencido' THEN 1 ELSE 0 END) as servicios_vencidos,
                SUM(CASE WHEN estado = 'activo' THEN monto ELSE 0 END) as ingresos_proyectados
            FROM servicios
        ");
        return $stmt->fetch();
    }
}