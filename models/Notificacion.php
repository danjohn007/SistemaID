<?php
/**
 * Modelo Notificacion
 * Manejo completo del sistema de notificaciones
 */
class Notificacion {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crear nueva notificación
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO notificaciones (servicio_id, tipo, asunto, mensaje, destinatario, 
                                       fecha_programada, estado) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['servicio_id'],
            $data['tipo'],
            $data['asunto'] ?? null,
            $data['mensaje'],
            $data['destinatario'],
            $data['fecha_programada'],
            $data['estado'] ?? 'pendiente'
        ]);
    }
    
    /**
     * Obtener todas las notificaciones con filtros
     */
    public function findAll($filtros = []) {
        $sql = "SELECT n.*, s.nombre as servicio_nombre, c.nombre_razon_social as cliente_nombre
                FROM notificaciones n 
                INNER JOIN servicios s ON n.servicio_id = s.id 
                INNER JOIN clientes c ON s.cliente_id = c.id 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['tipo'])) {
            $sql .= " AND n.tipo = ?";
            $params[] = $filtros['tipo'];
        }
        
        if (!empty($filtros['estado'])) {
            $sql .= " AND n.estado = ?";
            $params[] = $filtros['estado'];
        }
        
        if (!empty($filtros['desde'])) {
            $sql .= " AND n.fecha_creacion >= ?";
            $params[] = $filtros['desde'];
        }
        
        if (!empty($filtros['hasta'])) {
            $sql .= " AND n.fecha_creacion <= ?";
            $params[] = $filtros['hasta'] . ' 23:59:59';
        }
        
        $sql .= " ORDER BY n.fecha_creacion DESC";
        
        if (!empty($filtros['limit'])) {
            $sql .= " LIMIT " . intval($filtros['limit']);
            if (!empty($filtros['offset'])) {
                $sql .= " OFFSET " . intval($filtros['offset']);
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener notificación por ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT n.*, s.nombre as servicio_nombre, c.nombre_razon_social as cliente_nombre
            FROM notificaciones n 
            INNER JOIN servicios s ON n.servicio_id = s.id 
            INNER JOIN clientes c ON s.cliente_id = c.id 
            WHERE n.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtener notificaciones pendientes para envío
     */
    public function getPendientes() {
        $stmt = $this->db->prepare("
            SELECT n.*, s.nombre as servicio_nombre, s.monto, s.fecha_vencimiento,
                   c.nombre_razon_social as cliente_nombre, c.email as cliente_email, 
                   c.telefono as cliente_telefono
            FROM notificaciones n 
            INNER JOIN servicios s ON n.servicio_id = s.id 
            INNER JOIN clientes c ON s.cliente_id = c.id 
            WHERE n.estado = 'pendiente' 
            AND n.fecha_programada <= NOW()
            AND n.intentos < 3
            ORDER BY n.fecha_programada ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Marcar notificación como enviada
     */
    public function marcarEnviada($id) {
        $stmt = $this->db->prepare("
            UPDATE notificaciones 
            SET estado = 'enviado', fecha_enviado = NOW() 
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }
    
    /**
     * Marcar notificación como fallida
     */
    public function marcarFallida($id, $incrementarIntento = true) {
        $sql = "UPDATE notificaciones SET estado = 'fallido'";
        if ($incrementarIntento) {
            $sql .= ", intentos = intentos + 1";
        }
        $sql .= " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Programar notificaciones automáticas para un servicio
     */
    public function programarNotificacionesVencimiento($servicioId) {
        try {
            $servicio = new Servicio();
            $servicioData = $servicio->findById($servicioId);
            
            if (!$servicioData) {
                return false;
            }
            
            // Validar que tenemos al menos un medio de contacto
            if (empty($servicioData['email']) && empty($servicioData['telefono'])) {
                error_log("Advertencia: Servicio ID $servicioId sin email ni teléfono para notificaciones");
                return true; // No es un error crítico
            }
            
            $diasAlerta = ALERT_DAYS; // [30, 15, 7, 1]
            $fechaVencimiento = new DateTime($servicioData['fecha_vencimiento']);
            
            foreach ($diasAlerta as $dias) {
                $fechaNotificacion = clone $fechaVencimiento;
                $fechaNotificacion->sub(new DateInterval("P{$dias}D"));
                
                // Solo programar si la fecha no ha pasado
                if ($fechaNotificacion > new DateTime()) {
                    // Notificación por email (solo si hay email)
                    if (!empty($servicioData['email'])) {
                        $this->create([
                            'servicio_id' => $servicioId,
                            'tipo' => 'email',
                            'asunto' => $this->generarAsunto($servicioData, $dias),
                            'mensaje' => $this->generarMensajeEmail($servicioData, $dias),
                            'destinatario' => $servicioData['email'],
                            'fecha_programada' => $fechaNotificacion->format('Y-m-d H:i:s')
                        ]);
                    }
                    
                    // Notificación por WhatsApp (si hay teléfono)
                    if (!empty($servicioData['telefono'])) {
                        $this->create([
                            'servicio_id' => $servicioId,
                            'tipo' => 'whatsapp',
                            'mensaje' => $this->generarMensajeWhatsApp($servicioData, $dias),
                            'destinatario' => $this->limpiarTelefono($servicioData['telefono']),
                            'fecha_programada' => $fechaNotificacion->format('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error en programarNotificacionesVencimiento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generar asunto para email
     */
    private function generarAsunto($servicio, $dias) {
        if ($dias == 1) {
            return "⚠️ Su servicio {$servicio['nombre']} vence mañana";
        } else {
            return "📅 Su servicio {$servicio['nombre']} vence en {$dias} días";
        }
    }
    
    /**
     * Generar mensaje para email
     */
    private function generarMensajeEmail($servicio, $dias) {
        $mensaje = "Estimado/a {$servicio['nombre_razon_social']},\n\n";
        
        if ($dias == 1) {
            $mensaje .= "Le recordamos que su servicio vence MAÑANA:\n\n";
        } else {
            $mensaje .= "Le recordamos que su servicio vence en {$dias} días:\n\n";
        }
        
        $mensaje .= "📋 Servicio: {$servicio['nombre']}\n";
        $mensaje .= "💰 Monto: $" . number_format($servicio['monto'], 2) . "\n";
        $mensaje .= "📅 Fecha de vencimiento: " . date('d/m/Y', strtotime($servicio['fecha_vencimiento'])) . "\n\n";
        
        $mensaje .= "Para renovar su servicio o consultar más información, ";
        $mensaje .= "por favor contáctenos a la brevedad.\n\n";
        $mensaje .= "Saludos cordiales,\n";
        $mensaje .= SITE_NAME;
        
        return $mensaje;
    }
    
    /**
     * Generar mensaje para WhatsApp
     */
    private function generarMensajeWhatsApp($servicio, $dias) {
        $emoji = $dias == 1 ? "⚠️" : "📅";
        $tiempo = $dias == 1 ? "mañana" : "en {$dias} días";
        
        $mensaje = "{$emoji} *Recordatorio de Vencimiento*\n\n";
        $mensaje .= "Hola {$servicio['nombre_razon_social']},\n\n";
        $mensaje .= "Su servicio *{$servicio['nombre']}* vence {$tiempo}.\n\n";
        $mensaje .= "💰 Monto: $" . number_format($servicio['monto'], 2) . "\n";
        $mensaje .= "📅 Vencimiento: " . date('d/m/Y', strtotime($servicio['fecha_vencimiento'])) . "\n\n";
        $mensaje .= "Contáctenos para renovar. ¡Gracias!";
        
        return $mensaje;
    }
    
    /**
     * Limpiar número de teléfono para WhatsApp
     */
    private function limpiarTelefono($telefono) {
        // Remover espacios, guiones y otros caracteres
        $telefono = preg_replace('/[^0-9+]/', '', $telefono);
        
        // Si no empieza con +, agregar código de país (México por defecto)
        if (substr($telefono, 0, 1) !== '+') {
            $telefono = '+52' . $telefono;
        }
        
        return $telefono;
    }
    
    /**
     * Obtener estadísticas de notificaciones
     */
    public function getEstadisticas() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_notificaciones,
                SUM(CASE WHEN estado = 'enviado' THEN 1 ELSE 0 END) as enviadas,
                SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = 'fallido' THEN 1 ELSE 0 END) as fallidas,
                SUM(CASE WHEN tipo = 'email' THEN 1 ELSE 0 END) as emails,
                SUM(CASE WHEN tipo = 'whatsapp' THEN 1 ELSE 0 END) as whatsapps
            FROM notificaciones
            WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        return $stmt->fetch();
    }
    
    /**
     * Eliminar notificaciones antiguas
     */
    public function limpiarNotificacionesAntiguas($diasAntiguedad = 90) {
        $stmt = $this->db->prepare("
            DELETE FROM notificaciones 
            WHERE fecha_creacion < DATE_SUB(NOW(), INTERVAL ? DAY)
            AND estado = 'enviado'
        ");
        return $stmt->execute([$diasAntiguedad]);
    }
}