<?php
/**
 * Controlador Dashboard
 */
class DashboardController {
    private $clienteModel;
    private $servicioModel;
    
    public function __construct() {
        $this->clienteModel = new Cliente();
        $this->servicioModel = new Servicio();
    }
    
    public function index() {
        // Obtener filtros de fecha
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01', strtotime('-11 months'));
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        
        // Obtener estadísticas generales
        $totalClientes = $this->clienteModel->count();
        $estadisticasServicios = $this->servicioModel->getEstadisticas();
        
        // Obtener servicios por vencer (próximos 30 días)
        $serviciosPorVencer = $this->servicioModel->getServiciosPorVencer(30);
        
        // Obtener servicios vencidos
        $serviciosVencidos = $this->servicioModel->getServiciosVencidos();
        
        // Servicios próximos a vencer (7 días)
        $serviciosUrgentes = $this->servicioModel->getServiciosPorVencer(7);
        
        // Datos para gráficas con filtros
        $ventasPorMes = $this->getVentasPorMes($fechaInicio, $fechaFin);
        $serviciosPorTipo = $this->getServiciosPorTipo();
        $serviciosRenovados = $this->getServiciosRenovados($fechaInicio, $fechaFin);
        
        $data = [
            'total_clientes' => $totalClientes,
            'estadisticas_servicios' => $estadisticasServicios,
            'servicios_por_vencer' => $serviciosPorVencer,
            'servicios_vencidos' => $serviciosVencidos,
            'servicios_urgentes' => $serviciosUrgentes,
            'ventas_por_mes' => $ventasPorMes,
            'servicios_por_tipo' => $serviciosPorTipo,
            'servicios_renovados' => $serviciosRenovados,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ];
        
        include 'views/dashboard/index.php';
    }
    
    private function getVentasPorMes($fechaInicio = null, $fechaFin = null) {
        $db = Database::getInstance()->getConnection();
        
        // Si no se proporcionan fechas, usar últimos 12 meses
        if (!$fechaInicio || !$fechaFin) {
            $stmt = $db->query("
                SELECT 
                    DATE_FORMAT(fecha_pago, '%Y-%m') as mes,
                    SUM(monto) as total
                FROM pagos 
                WHERE estado = 'pagado' 
                AND fecha_pago >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(fecha_pago, '%Y-%m')
                ORDER BY mes ASC
            ");
            return $stmt->fetchAll();
        }
        
        $stmt = $db->prepare("
            SELECT 
                DATE_FORMAT(fecha_pago, '%Y-%m') as mes,
                SUM(monto) as total
            FROM pagos 
            WHERE estado = 'pagado' 
            AND fecha_pago >= ? 
            AND fecha_pago <= ?
            GROUP BY DATE_FORMAT(fecha_pago, '%Y-%m')
            ORDER BY mes ASC
        ");
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll();
    }
    
    private function getServiciosPorTipo() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("
            SELECT 
                ts.nombre,
                COUNT(s.id) as cantidad,
                SUM(s.monto) as total_monto
            FROM servicios s
            INNER JOIN tipos_servicios ts ON s.tipo_servicio_id = ts.id
            WHERE s.estado = 'activo'
            GROUP BY ts.id, ts.nombre
            ORDER BY cantidad DESC
        ");
        return $stmt->fetchAll();
    }
    
    private function getServiciosRenovados($fechaInicio = null, $fechaFin = null) {
        $db = Database::getInstance()->getConnection();
        
        // Si no se proporcionan fechas, usar últimos 12 meses
        if (!$fechaInicio || !$fechaFin) {
            $fechaInicio = date('Y-m-01', strtotime('-11 months'));
            $fechaFin = date('Y-m-t');
        }
        
        $stmt = $db->prepare("
            SELECT 
                DATE_FORMAT(p.fecha_pago, '%Y-%m') as mes,
                COUNT(CASE WHEN p.estado = 'pagado' THEN 1 END) as renovados,
                COUNT(CASE WHEN s.fecha_vencimiento < CURDATE() AND p.estado != 'pagado' THEN 1 END) as no_renovados
            FROM servicios s
            LEFT JOIN pagos p ON s.id = p.servicio_id 
                AND p.fecha_pago BETWEEN ? AND ?
            WHERE s.fecha_vencimiento BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(COALESCE(p.fecha_pago, s.fecha_vencimiento), '%Y-%m')
            ORDER BY mes ASC
        ");
        $stmt->execute([$fechaInicio, $fechaFin, $fechaInicio, $fechaFin]);
        return $stmt->fetchAll();
    }
}