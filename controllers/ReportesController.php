<?php
/**
 * Controlador de Reportes
 */
class ReportesController {
    public function index() {
        $data = [
            'reportes' => []
        ];
        
        include 'views/reports/index.php';
    }
    
    public function export() {
        $type = $_GET['type'] ?? 'excel';
        // Implementar exportación
        echo "Exportando en formato: $type";
    }
}