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
        // Obtener estadísticas generales
        $totalClientes = $this->clienteModel->count();
        $estadisticasServicios = $this->servicioModel->getEstadisticas();
        
        // Obtener servicios por vencer (próximos 30 días)
        $serviciosPorVencer = $this->servicioModel->getServiciosPorVencer(30);
        
        // Obtener servicios vencidos
        $serviciosVencidos = $this->servicioModel->getServiciosVencidos();
        
        // Servicios próximos a vencer (7 días)
        $serviciosUrgentes = $this->servicioModel->getServiciosPorVencer(7);
        
        // Datos para gráficas
        $ventasPorMes = $this->getVentasPorMes();
        $serviciosPorTipo = $this->getServiciosPorTipo();
        
        $data = [
            'total_clientes' => $totalClientes,
            'estadisticas_servicios' => $estadisticasServicios,
            'servicios_por_vencer' => $serviciosPorVencer,
            'servicios_vencidos' => $serviciosVencidos,
            'servicios_urgentes' => $serviciosUrgentes,
            'ventas_por_mes' => $ventasPorMes,
            'servicios_por_tipo' => $serviciosPorTipo
        ];
        
        include 'views/dashboard/index.php';
    }
    
    private function getVentasPorMes() {
        $db = Database::getInstance()->getConnection();
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
}