<?php
/**
 * Programador de Notificaciones
 * Maneja el envío automático y programado de notificaciones
 */
class NotificationScheduler {
    private $db;
    private $emailService;
    private $whatsappService;
    private $notificacionModel;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->emailService = new EmailService();
        $this->whatsappService = new WhatsAppService();
        $this->notificacionModel = new Notificacion();
    }
    
    /**
     * Procesar todas las notificaciones pendientes
     */
    public function procesarNotificacionesPendientes() {
        $notificaciones = $this->notificacionModel->getPendientes();
        $procesadas = 0;
        $exitosas = 0;
        $fallidas = 0;
        
        foreach ($notificaciones as $notificacion) {
            $procesadas++;
            
            if ($this->enviarNotificacion($notificacion)) {
                $exitosas++;
                $this->notificacionModel->marcarEnviada($notificacion['id']);
                $this->registrarLog($notificacion['id'], 'enviado', 'Notificación enviada exitosamente');
            } else {
                $fallidas++;
                $this->notificacionModel->marcarFallida($notificacion['id'], true);
                $this->registrarLog($notificacion['id'], 'fallido', 'Error al enviar notificación');
            }
            
            // Pequeña pausa para no sobrecargar las APIs
            usleep(500000); // 0.5 segundos
        }
        
        return [
            'procesadas' => $procesadas,
            'exitosas' => $exitosas,
            'fallidas' => $fallidas
        ];
    }
    
    /**
     * Enviar una notificación específica
     */
    private function enviarNotificacion($notificacion) {
        try {
            switch ($notificacion['tipo']) {
                case 'email':
                    return $this->emailService->enviar(
                        $notificacion['destinatario'],
                        $notificacion['asunto'],
                        $notificacion['mensaje'],
                        true
                    );
                    
                case 'whatsapp':
                    return $this->whatsappService->enviarMensaje(
                        $notificacion['destinatario'],
                        $notificacion['mensaje']
                    );
                    
                case 'sistema':
                    // Para notificaciones del sistema (dentro de la aplicación)
                    return $this->enviarNotificacionSistema($notificacion);
                    
                default:
                    error_log("Tipo de notificación no soportado: " . $notificacion['tipo']);
                    return false;
            }
        } catch (Exception $e) {
            error_log("Error enviando notificación {$notificacion['id']}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar notificación del sistema (interno)
     */
    private function enviarNotificacionSistema($notificacion) {
        // Las notificaciones del sistema se consideran "enviadas" automáticamente
        // ya que se muestran en la interfaz web
        return true;
    }
    
    /**
     * Programar notificaciones para servicios próximos a vencer
     */
    public function programarNotificacionesVencimiento() {
        $servicioModel = new Servicio();
        $programadas = 0;
        
        // Obtener servicios activos
        $servicios = $servicioModel->findAll();
        
        foreach ($servicios as $servicio) {
            if ($servicio['estado'] !== 'activo') continue;
            
            $fechaVencimiento = new DateTime($servicio['fecha_vencimiento']);
            $hoy = new DateTime();
            
            // Solo procesar servicios que vencen en el futuro
            if ($fechaVencimiento <= $hoy) continue;
            
            $diasParaVencimiento = $hoy->diff($fechaVencimiento)->days;
            
            // Verificar si necesitamos programar notificaciones para este servicio
            foreach (ALERT_DAYS as $diasAlert) {
                if ($diasParaVencimiento == $diasAlert) {
                    // Verificar si ya existe una notificación programada para este día
                    if (!$this->existeNotificacionProgramada($servicio['id'], $diasAlert)) {
                        $this->programarNotificacionServicio($servicio, $diasAlert);
                        $programadas++;
                    }
                }
            }
        }
        
        return $programadas;
    }
    
    /**
     * Verificar si existe notificación programada
     */
    private function existeNotificacionProgramada($servicioId, $diasAlert) {
        $fechaNotificacion = new DateTime();
        $fechaNotificacion->add(new DateInterval("P{$diasAlert}D"));
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM notificaciones 
            WHERE servicio_id = ? 
            AND DATE(fecha_programada) = DATE(?)
            AND estado IN ('pendiente', 'enviado')
        ");
        
        $stmt->execute([$servicioId, $fechaNotificacion->format('Y-m-d')]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Programar notificación para un servicio específico
     */
    private function programarNotificacionServicio($servicio, $diasAlert) {
        $fechaNotificacion = new DateTime();
        $fechaVencimiento = new DateTime($servicio['fecha_vencimiento']);
        $fechaNotificacion = clone $fechaVencimiento;
        $fechaNotificacion->sub(new DateInterval("P{$diasAlert}D"));
        
        // Solo programar si la fecha no ha pasado
        if ($fechaNotificacion <= new DateTime()) {
            return false;
        }
        
        // Obtener datos del cliente
        $clienteModel = new Cliente();
        $cliente = $clienteModel->findById($servicio['cliente_id']);
        
        if (!$cliente) return false;
        
        // Programar notificación por email
        if (!empty($cliente['email'])) {
            $this->notificacionModel->create([
                'servicio_id' => $servicio['id'],
                'tipo' => 'email',
                'asunto' => $this->generarAsunto($servicio, $diasAlert),
                'mensaje' => $this->generarMensajeEmail($servicio, $cliente, $diasAlert),
                'destinatario' => $cliente['email'],
                'fecha_programada' => $fechaNotificacion->format('Y-m-d 09:00:00')
            ]);
        }
        
        // Programar notificación por WhatsApp
        if (!empty($cliente['telefono'])) {
            $this->notificacionModel->create([
                'servicio_id' => $servicio['id'],
                'tipo' => 'whatsapp',
                'mensaje' => $this->generarMensajeWhatsApp($servicio, $cliente, $diasAlert),
                'destinatario' => $cliente['telefono'],
                'fecha_programada' => $fechaNotificacion->format('Y-m-d 10:00:00')
            ]);
        }
        
        // Programar notificación del sistema
        $this->notificacionModel->create([
            'servicio_id' => $servicio['id'],
            'tipo' => 'sistema',
            'asunto' => 'Servicio próximo a vencer',
            'mensaje' => "El servicio {$servicio['nombre']} del cliente {$cliente['nombre_razon_social']} vence en {$diasAlert} días",
            'destinatario' => 'admin',
            'fecha_programada' => $fechaNotificacion->format('Y-m-d 08:00:00')
        ]);
        
        return true;
    }
    
    /**
     * Generar asunto para email
     */
    private function generarAsunto($servicio, $dias) {
        $urgencia = $dias <= 3 ? "⚠️ URGENTE: " : "📅 ";
        $tiempo = $dias == 1 ? "mañana" : "en {$dias} días";
        
        return $urgencia . "Su servicio {$servicio['nombre']} vence {$tiempo}";
    }
    
    /**
     * Generar mensaje para email
     */
    private function generarMensajeEmail($servicio, $cliente, $dias) {
        $tiempo = $dias == 1 ? "MAÑANA" : "en {$dias} días";
        $urgencia = $dias <= 3 ? "URGENTE" : "";
        
        $mensaje = "Estimado/a {$cliente['nombre_razon_social']},\n\n";
        
        if ($urgencia) {
            $mensaje .= "⚠️ AVISO {$urgencia} ⚠️\n\n";
        }
        
        $mensaje .= "Le recordamos que su servicio vence {$tiempo}:\n\n";
        $mensaje .= "═══════════════════════════════════════\n";
        $mensaje .= "📋 Servicio: {$servicio['nombre']}\n";
        $mensaje .= "💰 Monto: $" . number_format($servicio['monto'], 2) . " MXN\n";
        $mensaje .= "📅 Fecha de vencimiento: " . date('d/m/Y', strtotime($servicio['fecha_vencimiento'])) . "\n";
        $mensaje .= "🔄 Período: " . ucfirst($servicio['periodo_vencimiento']) . "\n";
        $mensaje .= "═══════════════════════════════════════\n\n";
        
        if ($dias <= 3) {
            $mensaje .= "🚨 Para evitar la suspensión de su servicio, ";
        } else {
            $mensaje .= "📞 Para renovar su servicio, ";
        }
        
        $mensaje .= "contáctenos lo antes posible:\n\n";
        $mensaje .= "📧 Email: " . MAIL_FROM . "\n";
        $mensaje .= "🌐 Web: " . BASE_URL . "\n\n";
        
        $mensaje .= "Gracias por confiar en nuestros servicios.\n\n";
        $mensaje .= "Atentamente,\n";
        $mensaje .= SITE_NAME . "\n";
        $mensaje .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $mensaje .= "Este es un mensaje automático. No responda a este correo.";
        
        return $mensaje;
    }
    
    /**
     * Generar mensaje para WhatsApp
     */
    private function generarMensajeWhatsApp($servicio, $cliente, $dias) {
        $emoji = $dias <= 3 ? "🚨" : "📅";
        $tiempo = $dias == 1 ? "*mañana*" : "*en {$dias} días*";
        
        $mensaje = "{$emoji} *RECORDATORIO DE VENCIMIENTO*\n\n";
        $mensaje .= "Hola {$cliente['nombre_razon_social']},\n\n";
        $mensaje .= "Su servicio *{$servicio['nombre']}* vence {$tiempo}.\n\n";
        $mensaje .= "💰 *Monto:* $" . number_format($servicio['monto'], 2) . "\n";
        $mensaje .= "📅 *Vencimiento:* " . date('d/m/Y', strtotime($servicio['fecha_vencimiento'])) . "\n\n";
        
        if ($dias <= 3) {
            $mensaje .= "⚠️ *Acción requerida:* Renovar antes del vencimiento\n\n";
        }
        
        $mensaje .= "Contáctenos para renovar:\n";
        $mensaje .= "📧 " . MAIL_FROM . "\n\n";
        $mensaje .= "¡Gracias por su preferencia! 🙏";
        
        return $mensaje;
    }
    
    /**
     * Limpiar notificaciones antiguas y fallidas
     */
    public function limpiarNotificacionesAntiguas() {
        // Eliminar notificaciones enviadas hace más de 90 días
        $stmt = $this->db->prepare("
            DELETE FROM notificaciones 
            WHERE estado = 'enviado' 
            AND fecha_enviado < DATE_SUB(NOW(), INTERVAL 90 DAY)
        ");
        $eliminadasEnviadas = $stmt->execute() ? $stmt->rowCount() : 0;
        
        // Eliminar notificaciones fallidas después de 3 intentos y más de 7 días
        $stmt = $this->db->prepare("
            DELETE FROM notificaciones 
            WHERE estado = 'fallido' 
            AND intentos >= 3 
            AND fecha_creacion < DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $eliminadasFallidas = $stmt->execute() ? $stmt->rowCount() : 0;
        
        return [
            'enviadas_eliminadas' => $eliminadasEnviadas,
            'fallidas_eliminadas' => $eliminadasFallidas
        ];
    }
    
    /**
     * Registrar log de notificación
     */
    private function registrarLog($notificacionId, $estado, $mensaje) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO logs (usuario_id, accion, tabla_afectada, registro_id, datos_nuevos, ip_address, fecha_creacion)
                VALUES (NULL, ?, 'notificaciones', ?, ?, ?, NOW())
            ");
            
            $datos = json_encode([
                'estado' => $estado,
                'mensaje' => $mensaje,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
            $stmt->execute([
                'notificacion_' . $estado,
                $notificacionId,
                $datos,
                $_SERVER['REMOTE_ADDR'] ?? 'system'
            ]);
        } catch (Exception $e) {
            error_log("Error registrando log de notificación: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener resumen de notificaciones del día
     */
    public function getResumenDiario() {
        $stmt = $this->db->query("
            SELECT 
                tipo,
                estado,
                COUNT(*) as cantidad
            FROM notificaciones 
            WHERE DATE(fecha_creacion) = CURDATE()
            GROUP BY tipo, estado
            ORDER BY tipo, estado
        ");
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener próximas notificaciones a enviar
     */
    public function getProximasNotificaciones($limite = 10) {
        $stmt = $this->db->prepare("
            SELECT n.*, s.nombre as servicio_nombre, c.nombre_razon_social as cliente_nombre
            FROM notificaciones n
            INNER JOIN servicios s ON n.servicio_id = s.id
            INNER JOIN clientes c ON s.cliente_id = c.id
            WHERE n.estado = 'pendiente'
            AND n.fecha_programada > NOW()
            ORDER BY n.fecha_programada ASC
            LIMIT ?
        ");
        
        $stmt->execute([$limite]);
        return $stmt->fetchAll();
    }
}