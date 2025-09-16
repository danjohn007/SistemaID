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
        $estadisticasIngresos = $this->getEstadisticasIngresos($fechaInicio, $fechaFin);
        
        $data = [
            'total_clientes' => $totalClientes,
            'estadisticas_servicios' => $estadisticasServicios,
            'servicios_por_vencer' => $serviciosPorVencer,
            'servicios_vencidos' => $serviciosVencidos,
            'servicios_urgentes' => $serviciosUrgentes,
            'ventas_por_mes' => $ventasPorMes,
            'servicios_por_tipo' => $serviciosPorTipo,
            'servicios_renovados' => $serviciosRenovados,
            'estadisticas_ingresos' => $estadisticasIngresos,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ];
        
        include 'views/dashboard/index.php';
    }
    
    private function getVentasPorMes($fechaInicio = null, $fechaFin = null) {
        $db = Database::getInstance()->getConnection();
        
        // Si no se proporcionan fechas, usar últimos 30 días para vista diaria
        if (!$fechaInicio || !$fechaFin) {
            $stmt = $db->query("
                SELECT 
                    DATE_FORMAT(fecha_pago, '%Y-%m-%d') as fecha,
                    SUM(monto) as total
                FROM pagos 
                WHERE estado = 'pagado' 
                AND fecha_pago >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY DATE_FORMAT(fecha_pago, '%Y-%m-%d')
                ORDER BY fecha ASC
            ");
            return $stmt->fetchAll();
        }
        
        $stmt = $db->prepare("
            SELECT 
                DATE_FORMAT(fecha_pago, '%Y-%m-%d') as fecha,
                SUM(monto) as total
            FROM pagos 
            WHERE estado = 'pagado' 
            AND fecha_pago >= ? 
            AND fecha_pago <= ?
            GROUP BY DATE_FORMAT(fecha_pago, '%Y-%m-%d')
            ORDER BY fecha ASC
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
        
        // Si no se proporcionan fechas, usar últimos 30 días
        if (!$fechaInicio || !$fechaFin) {
            $fechaInicio = date('Y-m-d', strtotime('-30 days'));
            $fechaFin = date('Y-m-d');
        }
        
        // Obtener servicios renovados (pagos realizados)
        $stmtRenovados = $db->prepare("
            SELECT 
                COUNT(*) as renovados
            FROM pagos 
            WHERE estado = 'pagado' 
            AND fecha_pago BETWEEN ? AND ?
        ");
        $stmtRenovados->execute([$fechaInicio, $fechaFin]);
        $renovados = $stmtRenovados->fetchColumn();
        
        // Obtener servicios nuevos (creados en el rango)
        $stmtNuevos = $db->prepare("
            SELECT 
                COUNT(*) as nuevos
            FROM servicios 
            WHERE fecha_creacion BETWEEN ? AND ?
        ");
        $stmtNuevos->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
        $nuevos = $stmtNuevos->fetchColumn();
        
        // Obtener servicios pendientes (por vencer en los próximos 30 días)
        $stmtPendientes = $db->prepare("
            SELECT 
                COUNT(*) as pendientes
            FROM servicios 
            WHERE estado = 'activo' 
            AND fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ");
        $stmtPendientes->execute();
        $pendientes = $stmtPendientes->fetchColumn();
        
        // Obtener servicios cancelados en el rango
        $stmtCancelados = $db->prepare("
            SELECT 
                COUNT(*) as cancelados
            FROM servicios 
            WHERE estado = 'cancelado' 
            AND fecha_actualizacion BETWEEN ? AND ?
        ");
        $stmtCancelados->execute([$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
        $cancelados = $stmtCancelados->fetchColumn();
        
        return [
            'renovados' => $renovados ?: 0,
            'nuevos' => $nuevos ?: 0,
            'pendientes' => $pendientes ?: 0,
            'cancelados' => $cancelados ?: 0,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ];
    }
    
    private function getEstadisticasIngresos($fechaInicio = null, $fechaFin = null) {
        $db = Database::getInstance()->getConnection();
        
        // Si no se proporcionan fechas, usar últimos 30 días
        if (!$fechaInicio || !$fechaFin) {
            $fechaInicio = date('Y-m-d', strtotime('-30 days'));
            $fechaFin = date('Y-m-d');
        }
        
        // Obtener ingresos por método de pago
        $stmt = $db->prepare("
            SELECT 
                metodo_pago,
                COUNT(*) as cantidad,
                SUM(monto) as total
            FROM pagos 
            WHERE estado = 'pagado' 
            AND fecha_pago BETWEEN ? AND ?
            AND metodo_pago IS NOT NULL
            GROUP BY metodo_pago
            ORDER BY total DESC
        ");
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll();
    }
}