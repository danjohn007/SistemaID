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
            case 'general':
                if (isset($data['total_clientes'])) {
                    // Summary data
                    fputcsv($output, ['Métrica', 'Valor']);
                    fputcsv($output, ['Total Clientes', $data['total_clientes']]);
                    fputcsv($output, ['Total Servicios', $data['total_servicios']]);
                    fputcsv($output, ['Servicios Activos', $data['servicios_activos']]);
                    fputcsv($output, ['Servicios Vencidos', $data['servicios_vencidos']]);
                    fputcsv($output, ['Total Ingresos', '$' . number_format($data['total_ingresos'], 2)]);
                    fputcsv($output, ['Total Pagos', $data['total_pagos']]);
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
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        switch ($tipoReporte) {
            case 'ingresos':
                fputcsv($output, ['Fecha', 'Total Ingresos', 'Cantidad Pagos', 'Promedio por Pago'], "\t");
                foreach ($data as $row) {
                    fputcsv($output, [
                        $row['fecha'],
                        '$' . number_format($row['total_ingresos'], 2),
                        $row['cantidad_pagos'],
                        '$' . number_format($row['promedio_pago'], 2)
                    ], "\t");
                }
                break;
            case 'clientes':
                fputcsv($output, ['Cliente', 'Email', 'Total Servicios', 'Servicios Activos', 'Total Pagado'], "\t");
                foreach ($data as $row) {
                    fputcsv($output, [
                        $row['nombre_razon_social'],
                        $row['email'],
                        $row['total_servicios'],
                        $row['servicios_activos'],
                        '$' . number_format($row['total_pagado'], 2)
                    ], "\t");
                }
                break;
            case 'servicios':
                fputcsv($output, ['Tipo de Servicio', 'Cantidad', 'Activos', 'Vencidos', 'Precio Promedio', 'Total Ingresos'], "\t");
                foreach ($data as $row) {
                    fputcsv($output, [
                        $row['tipo_servicio'],
                        $row['cantidad'],
                        $row['activos'],
                        $row['vencidos'],
                        '$' . number_format($row['precio_promedio'], 2),
                        '$' . number_format($row['total_ingresos'], 2)
                    ], "\t");
                }
                break;
            case 'general':
                if (isset($data['total_clientes'])) {
                    // Summary data
                    fputcsv($output, ['Métrica', 'Valor'], "\t");
                    fputcsv($output, ['Total Clientes', $data['total_clientes']], "\t");
                    fputcsv($output, ['Total Servicios', $data['total_servicios']], "\t");
                    fputcsv($output, ['Servicios Activos', $data['servicios_activos']], "\t");
                    fputcsv($output, ['Servicios Vencidos', $data['servicios_vencidos']], "\t");
                    fputcsv($output, ['Total Ingresos', '$' . number_format($data['total_ingresos'], 2)], "\t");
                    fputcsv($output, ['Total Pagos', $data['total_pagos']], "\t");
                }
                break;
        }
        
        fclose($output);
        exit;
    }
    
    private function exportPDF($data, $tipoReporte) {
        // Para PDF simple, generaremos HTML que se puede imprimir como PDF
        $filename = "reporte_{$tipoReporte}_" . date('Y-m-d') . ".html";
        
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Reporte " . ucfirst($tipoReporte) . "</title>";
        echo "<style>body{font-family: Arial, sans-serif; margin: 20px;} table{border-collapse: collapse; width: 100%; margin-top: 20px;} th,td{border: 1px solid #ddd; padding: 12px; text-align: left;} th{background-color: #f2f2f2; font-weight: bold;} .header{text-align: center; margin-bottom: 30px;} .summary{background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;} @media print{body{margin: 0;} .no-print{display: none;}}</style>";
        echo "</head><body>";
        
        echo "<div class='header'>";
        echo "<h1>Sistema ID - Reporte " . ucfirst($tipoReporte) . "</h1>";
        echo "<p>Generado el: " . date('d/m/Y H:i') . "</p>";
        echo "</div>";
        
        if (is_array($data) && !empty($data)) {
            echo "<table>";
            
            // Headers based on report type
            switch ($tipoReporte) {
                case 'ingresos':
                    echo "<thead><tr><th>Fecha</th><th>Total Ingresos</th><th>Cantidad Pagos</th><th>Promedio</th></tr></thead><tbody>";
                    foreach ($data as $row) {
                        echo "<tr>";
                        echo "<td>" . date('d/m/Y', strtotime($row['fecha'])) . "</td>";
                        echo "<td>$" . number_format($row['total_ingresos'], 2) . "</td>";
                        echo "<td>" . $row['cantidad_pagos'] . "</td>";
                        echo "<td>$" . number_format($row['promedio_pago'], 2) . "</td>";
                        echo "</tr>";
                    }
                    break;
                case 'clientes':
                    echo "<thead><tr><th>Cliente</th><th>Email</th><th>Total Servicios</th><th>Servicios Activos</th><th>Total Pagado</th></tr></thead><tbody>";
                    foreach ($data as $row) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['nombre_razon_social']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . $row['total_servicios'] . "</td>";
                        echo "<td>" . $row['servicios_activos'] . "</td>";
                        echo "<td>$" . number_format($row['total_pagado'], 2) . "</td>";
                        echo "</tr>";
                    }
                    break;
                case 'servicios':
                    echo "<thead><tr><th>Tipo de Servicio</th><th>Cantidad</th><th>Activos</th><th>Vencidos</th><th>Precio Promedio</th><th>Total Ingresos</th></tr></thead><tbody>";
                    foreach ($data as $row) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['tipo_servicio']) . "</td>";
                        echo "<td>" . $row['cantidad'] . "</td>";
                        echo "<td>" . $row['activos'] . "</td>";
                        echo "<td>" . $row['vencidos'] . "</td>";
                        echo "<td>$" . number_format($row['precio_promedio'], 2) . "</td>";
                        echo "<td>$" . number_format($row['total_ingresos'], 2) . "</td>";
                        echo "</tr>";
                    }
                    break;
                case 'general':
                    if (isset($data['total_clientes'])) {
                        echo "<thead><tr><th>Métrica</th><th>Valor</th></tr></thead><tbody>";
                        echo "<tr><td>Total Clientes</td><td>" . $data['total_clientes'] . "</td></tr>";
                        echo "<tr><td>Total Servicios</td><td>" . $data['total_servicios'] . "</td></tr>";
                        echo "<tr><td>Servicios Activos</td><td>" . $data['servicios_activos'] . "</td></tr>";
                        echo "<tr><td>Servicios Vencidos</td><td>" . $data['servicios_vencidos'] . "</td></tr>";
                        echo "<tr><td>Total Ingresos</td><td>$" . number_format($data['total_ingresos'], 2) . "</td></tr>";
                        echo "<tr><td>Total Pagos</td><td>" . $data['total_pagos'] . "</td></tr>";
                    }
                    break;
            }
            
            echo "</tbody></table>";
        } else {
            echo "<div class='summary'><p>No hay datos disponibles para el período seleccionado.</p></div>";
        }
        
        echo "<div class='no-print' style='margin-top: 30px;'>";
        echo "<button onclick='window.print()' style='background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;'>Imprimir / Guardar como PDF</button>";
        echo "</div>";
        
        echo "</body></html>";
        exit;
    }
}