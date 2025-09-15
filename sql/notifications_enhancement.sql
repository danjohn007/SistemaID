-- Mejoras a la tabla de notificaciones
-- Agregar campo para datos adicionales (JSON)

USE sistema_id;

-- Agregar columna para datos adicionales si no existe
ALTER TABLE notificaciones 
ADD COLUMN IF NOT EXISTS datos_adicionales JSON NULL COMMENT 'Datos adicionales como message_id, respuestas API, etc.';

-- Actualizar índices para optimización
CREATE INDEX IF NOT EXISTS idx_notificaciones_tipo_estado ON notificaciones(tipo, estado);
CREATE INDEX IF NOT EXISTS idx_notificaciones_servicio_tipo ON notificaciones(servicio_id, tipo);

-- Insertar configuraciones por defecto para notificaciones si no existen
INSERT IGNORE INTO configuraciones (clave, valor, descripcion) VALUES
('email_method', 'mail', 'Método de envío de email (mail o smtp)'),
('email_from_email', 'noreply@sistemaid.com', 'Email remitente por defecto'),
('email_from_name', 'Sistema ID', 'Nombre remitente por defecto'),
('email_reply_to', 'noreply@sistemaid.com', 'Email para respuestas'),
('whatsapp_timeout', '30', 'Timeout para peticiones WhatsApp API (segundos)'),
('notifications_enabled', '1', 'Habilitar sistema de notificaciones'),
('notifications_max_retries', '3', 'Máximo número de reintentos para notificaciones fallidas'),
('notifications_cleanup_days', '90', 'Días para mantener notificaciones enviadas antes de eliminar');

-- Crear tabla para plantillas de notificaciones (opcional)
CREATE TABLE IF NOT EXISTS plantillas_notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    tipo ENUM('email', 'whatsapp') NOT NULL,
    asunto VARCHAR(200) NULL,
    contenido TEXT NOT NULL,
    variables JSON NULL COMMENT 'Variables disponibles para la plantilla',
    activa TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar plantillas por defecto
INSERT IGNORE INTO plantillas_notificaciones (nombre, tipo, asunto, contenido, variables) VALUES
('vencimiento_email', 'email', 'Recordatorio de vencimiento - {{servicio_nombre}}', 
'Estimado/a {{cliente_nombre}},

Le recordamos que su servicio vence {{tiempo_vencimiento}}:

═══════════════════════════════════════
📋 Servicio: {{servicio_nombre}}
💰 Monto: ${{servicio_monto}} MXN
📅 Fecha de vencimiento: {{fecha_vencimiento}}
🔄 Período: {{servicio_periodo}}
═══════════════════════════════════════

Para renovar su servicio, contáctenos lo antes posible.

Atentamente,
{{site_name}}', 
'["cliente_nombre", "servicio_nombre", "servicio_monto", "fecha_vencimiento", "servicio_periodo", "tiempo_vencimiento", "site_name"]'),

('vencimiento_whatsapp', 'whatsapp', NULL,
'🔔 *RECORDATORIO DE VENCIMIENTO*

Hola {{cliente_nombre}},

Su servicio *{{servicio_nombre}}* vence {{tiempo_vencimiento}}.

💰 *Monto:* ${{servicio_monto}}
📅 *Vencimiento:* {{fecha_vencimiento}}

Contáctenos para renovar. ¡Gracias! 🙏',
'["cliente_nombre", "servicio_nombre", "servicio_monto", "fecha_vencimiento", "tiempo_vencimiento"]');

-- Crear vista para resumen de notificaciones
CREATE OR REPLACE VIEW vista_resumen_notificaciones AS
SELECT 
    DATE(n.fecha_creacion) as fecha,
    n.tipo,
    n.estado,
    COUNT(*) as cantidad,
    COUNT(CASE WHEN n.estado = 'enviado' THEN 1 END) as enviadas,
    COUNT(CASE WHEN n.estado = 'pendiente' THEN 1 END) as pendientes,
    COUNT(CASE WHEN n.estado = 'fallido' THEN 1 END) as fallidas
FROM notificaciones n
WHERE n.fecha_creacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(n.fecha_creacion), n.tipo, n.estado
ORDER BY fecha DESC, n.tipo;

-- Procedimiento almacenado para limpiar notificaciones antiguas
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS LimpiarNotificacionesAntiguas(IN dias_antiguedad INT)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE eliminadas_enviadas INT DEFAULT 0;
    DECLARE eliminadas_fallidas INT DEFAULT 0;
    
    -- Eliminar notificaciones enviadas antiguas
    DELETE FROM notificaciones 
    WHERE estado = 'enviado' 
    AND fecha_enviado < DATE_SUB(NOW(), INTERVAL dias_antiguedad DAY);
    
    SET eliminadas_enviadas = ROW_COUNT();
    
    -- Eliminar notificaciones fallidas con 3+ intentos y más de 7 días
    DELETE FROM notificaciones 
    WHERE estado = 'fallido' 
    AND intentos >= 3 
    AND fecha_creacion < DATE_SUB(NOW(), INTERVAL 7 DAY);
    
    SET eliminadas_fallidas = ROW_COUNT();
    
    -- Registrar la limpieza en logs
    INSERT INTO logs (usuario_id, accion, tabla_afectada, datos_nuevos, ip_address, fecha_creacion)
    VALUES (NULL, 'limpieza_notificaciones', 'notificaciones', 
            JSON_OBJECT('eliminadas_enviadas', eliminadas_enviadas, 'eliminadas_fallidas', eliminadas_fallidas, 'dias_antiguedad', dias_antiguedad),
            'system', NOW());
            
    SELECT eliminadas_enviadas, eliminadas_fallidas;
END //
DELIMITER ;