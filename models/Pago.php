<?php
/**
 * Modelo Pago
 */
class Pago {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO pagos (servicio_id, monto, fecha_pago, fecha_vencimiento, estado, metodo_pago, referencia, notas) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['servicio_id'],
            $data['monto'],
            $data['fecha_pago'],
            $data['fecha_vencimiento'],
            $data['estado'] ?? 'pendiente',
            $data['metodo_pago'] ?? null,
            $data['referencia'] ?? null,
            $data['notas'] ?? null
        ]);
    }
    
    public function findAll($limit = null, $offset = 0) {
        $sql = "SELECT p.*, s.nombre as servicio_nombre, c.nombre_razon_social 
                FROM pagos p 
                INNER JOIN servicios s ON p.servicio_id = s.id 
                INNER JOIN clientes c ON s.cliente_id = c.id 
                ORDER BY p.fecha_creacion DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*, s.nombre as servicio_nombre, c.nombre_razon_social 
            FROM pagos p 
            INNER JOIN servicios s ON p.servicio_id = s.id 
            INNER JOIN clientes c ON s.cliente_id = c.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findByServicio($servicioId) {
        $stmt = $this->db->prepare("
            SELECT * FROM pagos 
            WHERE servicio_id = ? 
            ORDER BY fecha_vencimiento DESC
        ");
        $stmt->execute([$servicioId]);
        return $stmt->fetchAll();
    }
    
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE pagos 
            SET monto = ?, fecha_pago = ?, fecha_vencimiento = ?, estado = ?, 
                metodo_pago = ?, referencia = ?, notas = ? 
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['monto'],
            $data['fecha_pago'],
            $data['fecha_vencimiento'],
            $data['estado'],
            $data['metodo_pago'] ?? null,
            $data['referencia'] ?? null,
            $data['notas'] ?? null,
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM pagos WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getPagosPendientes() {
        $stmt = $this->db->query("
            SELECT p.*, s.nombre as servicio_nombre, c.nombre_razon_social, c.email 
            FROM pagos p 
            INNER JOIN servicios s ON p.servicio_id = s.id 
            INNER JOIN clientes c ON s.cliente_id = c.id 
            WHERE p.estado = 'pendiente' 
            ORDER BY p.fecha_vencimiento ASC
        ");
        return $stmt->fetchAll();
    }
    
    public function registrarPago($servicioId, $monto, $metodoPago, $referencia = null) {
        $this->db->beginTransaction();
        
        try {
            // Crear el pago
            $stmt = $this->db->prepare("
                INSERT INTO pagos (servicio_id, monto, fecha_pago, fecha_vencimiento, estado, metodo_pago, referencia) 
                VALUES (?, ?, CURDATE(), CURDATE(), 'pagado', ?, ?)
            ");
            $stmt->execute([$servicioId, $monto, $metodoPago, $referencia]);
            
            // Renovar el servicio
            $servicioModel = new Servicio();
            $servicioModel->renovarServicio($servicioId, date('Y-m-d'));
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
}