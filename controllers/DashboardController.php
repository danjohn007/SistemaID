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
        
        // Obtener servicios renovados (pagos realizados)
        $stmtRenovados = $db->prepare("
            SELECT 
                DATE_FORMAT(fecha_pago, '%Y-%m') as mes,
                COUNT(*) as renovados
            FROM pagos 
            WHERE estado = 'pagado' 
            AND fecha_pago BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(fecha_pago, '%Y-%m')
            ORDER BY mes ASC
        ");
        $stmtRenovados->execute([$fechaInicio, $fechaFin]);
        $renovados = $stmtRenovados->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Obtener servicios no renovados (vencidos sin pago)
        $stmtNoRenovados = $db->prepare("
            SELECT 
                DATE_FORMAT(fecha_vencimiento, '%Y-%m') as mes,
                COUNT(*) as no_renovados
            FROM servicios 
            WHERE estado = 'vencido' 
            AND fecha_vencimiento BETWEEN ? AND ?
            AND id NOT IN (
                SELECT DISTINCT servicio_id 
                FROM pagos 
                WHERE estado = 'pagado' 
                AND fecha_pago >= fecha_vencimiento
            )
            GROUP BY DATE_FORMAT(fecha_vencimiento, '%Y-%m')
            ORDER BY mes ASC
        ");
        $stmtNoRenovados->execute([$fechaInicio, $fechaFin]);
        $noRenovados = $stmtNoRenovados->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Combinar resultados
        $mesesCompletos = [];
        $todosLosMeses = array_unique(array_merge(array_keys($renovados), array_keys($noRenovados)));
        
        foreach ($todosLosMeses as $mes) {
            $mesesCompletos[] = [
                'mes' => $mes,
                'renovados' => $renovados[$mes] ?? 0,
                'no_renovados' => $noRenovados[$mes] ?? 0
            ];
        }
        
        // Ordenar por mes
        usort($mesesCompletos, function($a, $b) {
            return strcmp($a['mes'], $b['mes']);
        });
        
        return $mesesCompletos;
    }
}