<?php
/**
 * Controlador de Notificaciones
 */
class NotificacionesController {
    public function index() {
        $data = [
            'notificaciones' => []
        ];
        
        include 'views/notifications/index.php';
    }
}