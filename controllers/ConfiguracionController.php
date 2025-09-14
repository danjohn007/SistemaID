<?php
/**
 * Controlador de Configuración
 */
class ConfiguracionController {
    public function index() {
        $data = [
            'configuraciones' => []
        ];
        
        include 'views/config/index.php';
    }
}