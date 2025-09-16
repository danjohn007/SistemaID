<?php
/**
 * Controlador de Reportes
 */
class ReportesController {
    private $servicioModel;
    private $pagoModel;
    private $clienteModel;
    
    public function __construct() {
        $this->servicioModel = new Servicio();
        $this->pagoModel = new Pago();
        $this->clienteModel = new Cliente();
    }
    
    public function index() {
        // Obtener datos para los reportes
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // Primer día del mes actual
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t'); // Último día del mes actual
        $tipoReporte = $_GET['tipo'] ?? 'general';
        
        $data = [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'tipo_reporte' => $tipoReporte,
            'reportes' => $this->generarReportes($fechaInicio, $fechaFin, $tipoReporte)
        ];
        
        include 'views/reports/index.php';
    }
    
    private function generarReportes($fechaInicio, $fechaFin, $tipo) {
        $reportes = [];
        
        switch ($tipo) {
            case 'ingresos':
                $reportes = $this->getReporteIngresos($fechaInicio, $fechaFin);
                break;
            case 'vencimientos':
                $reportes = $this->getReporteVencimientos();
                break;
            case 'clientes':
                $reportes = $this->getReporteClientes($fechaInicio, $fechaFin);
                break;
            case 'servicios':
                $reportes = $this->getReporteServicios($fechaInicio, $fechaFin);
                break;
            default:
                $reportes = $this->getReporteGeneral($fechaInicio, $fechaFin);
        }
        
        return $reportes;
    }
    
    private function getReporteIngresos($fechaInicio, $fechaFin) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT 
                DATE(p.fecha_pago) as fecha,
                SUM(p.monto) as total_ingresos,
                COUNT(p.id) as cantidad_pagos,
                AVG(p.monto) as promedio_pago
            FROM pagos p 
            WHERE p.fecha_pago BETWEEN ? AND ?
            AND p.estado = 'pagado'
            GROUP BY DATE(p.fecha_pago)
            ORDER BY fecha DESC
        ");
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll();
    }
    
    private function getReporteVencimientos() {
        return [
            'proximos_a_vencer' => $this->servicioModel->getServiciosPorVencer(30),
            'vencidos' => $this->servicioModel->getServiciosVencidos()
        ];
    }
    
    private function getReporteClientes($fechaInicio, $fechaFin) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT 
                c.nombre_razon_social,
                c.email,
                COUNT(s.id) as total_servicios,
                COUNT(CASE WHEN s.estado = 'activo' THEN 1 END) as servicios_activos,
                COALESCE(SUM(p.monto), 0) as total_pagado
            FROM clientes c
            LEFT JOIN servicios s ON c.id = s.cliente_id
            LEFT JOIN pagos p ON s.id = p.servicio_id AND p.fecha_pago BETWEEN ? AND ?
            WHERE c.activo = 1
            GROUP BY c.id
            ORDER BY total_pagado DESC
        ");
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll();
    }
    
    private function getReporteServicios($fechaInicio, $fechaFin) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT 
                ts.nombre as tipo_servicio,
                COUNT(s.id) as cantidad,
                COUNT(CASE WHEN s.estado = 'activo' THEN 1 END) as activos,
                COUNT(CASE WHEN s.estado = 'vencido' THEN 1 END) as vencidos,
                AVG(s.monto) as precio_promedio,
                SUM(COALESCE(p.monto, 0)) as total_ingresos
            FROM tipos_servicios ts
            LEFT JOIN servicios s ON ts.id = s.tipo_servicio_id
            LEFT JOIN pagos p ON s.id = p.servicio_id AND p.fecha_pago BETWEEN ? AND ?
            GROUP BY ts.id
            ORDER BY cantidad DESC
        ");
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll();
    }
    
    private function getReporteGeneral($fechaInicio, $fechaFin) {
        $db = Database::getInstance()->getConnection();
        // Resumen general
        $stmt = $db->prepare("
            SELECT 
                COUNT(DISTINCT c.id) as total_clientes,
                COUNT(s.id) as total_servicios,
                COUNT(CASE WHEN s.estado = 'activo' THEN 1 END) as servicios_activos,
                COUNT(CASE WHEN s.fecha_vencimiento < CURDATE() AND s.estado = 'activo' THEN 1 END) as servicios_vencidos,
                COALESCE(SUM(p.monto), 0) as total_ingresos,
                COUNT(p.id) as total_pagos
            FROM clientes c
            LEFT JOIN servicios s ON c.id = s.cliente_id
            LEFT JOIN pagos p ON s.id = p.servicio_id AND p.fecha_pago BETWEEN ? AND ?
            WHERE c.activo = 1
        ");
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetch();
    }
    
    public function export() {
        $tipo = $_GET['type'] ?? 'excel';
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        $tipoReporte = $_GET['reporte'] ?? 'general';
        
        $data = $this->generarReportes($fechaInicio, $fechaFin, $tipoReporte);
        
        switch ($tipo) {
            case 'csv':
                $this->exportCSV($data, $tipoReporte);
                break;
            case 'excel':
                $this->exportExcel($data, $tipoReporte);
                break;
            case 'pdf':
                $this->exportPDF($data, $tipoReporte);
                break;
            default:
                header('Location: ' . BASE_URL . 'reportes?error=Formato de exportación no válido');
        }
    }
    
    private function exportCSV($data, $tipoReporte) {
        $filename = "reporte_{$tipoReporte}_" . date('Y-m-d') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        switch ($tipoReporte) {
            case 'ingresos':
                fputcsv($output, ['Fecha', 'Total Ingresos', 'Cantidad Pagos', 'Promedio por Pago']);
                foreach ($data as $row) {
                    fputcsv($output, [
                        $row['fecha'],
                        '$' . number_format($row['total_ingresos'], 2),
                        $row['cantidad_pagos'],
                        '$' . number_format($row['promedio_pago'], 2)
                    ]);
                }
                break;
            case 'clientes':
                fputcsv($output, ['Cliente', 'Email', 'Total Servicios', 'Servicios Activos', 'Total Pagado']);
                foreach ($data as $row) {
                    fputcsv($output, [
                        $row['nombre_razon_social'],
                        $row['email'],
                        $row['total_servicios'],
                        $row['servicios_activos'],
                        '$' . number_format($row['total_pagado'], 2)
                    ]);
                }
                break;
            case 'servicios':
                fputcsv($output, ['Tipo de Servicio', 'Cantidad', 'Activos', 'Vencidos', 'Precio Promedio', 'Total Ingresos']);
                foreach ($data as $row) {
                    fputcsv($output, [
                        $row['tipo_servicio'],
                        $row['cantidad'],
                        $row['activos'],
                        $row['vencidos'],
                        '$' . number_format($row['precio_promedio'], 2),
                        '$' . number_format($row['total_ingresos'], 2)
                    ]);
                }
                break;
        }
        
        fclose($output);
        exit;
    }
    
    private function exportExcel($data, $tipoReporte) {
        // Para Excel, usaremos CSV con extensión .xls para compatibilidad simple
        $filename = "reporte_{$tipoReporte}_" . date('Y-m-d') . ".xls";
        
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        
        $this->exportCSV($data, $tipoReporte);
    }
    
    private function exportPDF($data, $tipoReporte) {
        // Para PDF simple, generaremos HTML que se puede imprimir como PDF
        $filename = "reporte_{$tipoReporte}_" . date('Y-m-d') . ".html";
        
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Reporte</title>";
        echo "<style>body{font-family: Arial;} table{border-collapse: collapse; width: 100%;} th,td{border: 1px solid #ddd; padding: 8px; text-align: left;} th{background-color: #f2f2f2;}</style>";
        echo "</head><body>";
        echo "<h1>Reporte " . ucfirst($tipoReporte) . "</h1>";
        echo "<p>Generado el: " . date('d/m/Y H:i') . "</p>";
        
        if (is_array($data) && !empty($data)) {
            echo "<table>";
            
            // Headers based on report type
            switch ($tipoReporte) {
                case 'ingresos':
                    echo "<tr><th>Fecha</th><th>Total Ingresos</th><th>Cantidad Pagos</th><th>Promedio</th></tr>";
                    foreach ($data as $row) {
                        echo "<tr>";
                        echo "<td>" . $row['fecha'] . "</td>";
                        echo "<td>$" . number_format($row['total_ingresos'], 2) . "</td>";
                        echo "<td>" . $row['cantidad_pagos'] . "</td>";
                        echo "<td>$" . number_format($row['promedio_pago'], 2) . "</td>";
                        echo "</tr>";
                    }
                    break;
            }
            
            echo "</table>";
        }
        
        echo "</body></html>";
        exit;
    }
}