<?php
/**
 * Cron Job para Procesamiento Automático de Notificaciones
 * 
 * Para configurar este cron job, agregue la siguiente línea a su crontab:
 * 
 * # Procesar notificaciones cada 5 minutos
 * 0,5,10,15,20,25,30,35,40,45,50,55 * * * * /usr/bin/php /ruta/completa/al/proyecto/cron_notifications.php
 * 
 * # Programar notificaciones diariamente a las 6:00 AM
 * 0 6 * * * /usr/bin/php /ruta/completa/al/proyecto/cron_notifications.php programar
 * 
 * # Limpiar notificaciones antiguas semanalmente (domingos a las 2:00 AM)
 * 0 2 * * 0 /usr/bin/php /ruta/completa/al/proyecto/cron_notifications.php limpiar
 */

// Solo ejecutar desde línea de comandos
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('Este script solo puede ejecutarse desde línea de comandos.');
}

// Incluir archivos necesarios
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Autoloader
spl_autoload_register(function($class) {
    $paths = [
        __DIR__ . '/models/' . $class . '.php',
        __DIR__ . '/controllers/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Función para log de cron
function logCron($mensaje) {
    $fecha = date('Y-m-d H:i:s');
    $logFile = __DIR__ . '/logs/cron_notifications.log';
    
    // Crear directorio logs si no existe
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, "[$fecha] $mensaje" . PHP_EOL, FILE_APPEND | LOCK_EX);
    echo "[$fecha] $mensaje" . PHP_EOL;
}

try {
    // Obtener acción desde argumentos de línea de comandos
    $accion = $argv[1] ?? 'procesar';
    
    logCron("Iniciando cron de notificaciones - Acción: $accion");
    
    $scheduler = new NotificationScheduler();
    
    switch ($accion) {
        case 'procesar':
            // Procesar notificaciones pendientes
            $resultado = $scheduler->procesarNotificacionesPendientes();
            logCron("Notificaciones procesadas: {$resultado['procesadas']}, Exitosas: {$resultado['exitosas']}, Fallidas: {$resultado['fallidas']}");
            
            // Si hay notificaciones fallidas, intentar reprocesar las que tengan menos de 3 intentos
            if ($resultado['fallidas'] > 0) {
                logCron("Reintentando notificaciones fallidas...");
                sleep(30); // Esperar 30 segundos antes de reintentar
                $reintento = $scheduler->procesarNotificacionesPendientes();
                logCron("Reintento - Procesadas: {$reintento['procesadas']}, Exitosas: {$reintento['exitosas']}, Fallidas: {$reintento['fallidas']}");
            }
            break;
            
        case 'programar':
            // Programar notificaciones automáticas
            $programadas = $scheduler->programarNotificacionesVencimiento();
            logCron("Notificaciones programadas automáticamente: $programadas");
            break;
            
        case 'limpiar':
            // Limpiar notificaciones antiguas
            $resultado = $scheduler->limpiarNotificacionesAntiguas();
            logCron("Limpieza completada - Enviadas eliminadas: {$resultado['enviadas_eliminadas']}, Fallidas eliminadas: {$resultado['fallidas_eliminadas']}");
            break;
            
        case 'resumen':
            // Mostrar resumen del día
            $resumen = $scheduler->getResumenDiario();
            logCron("=== RESUMEN DEL DÍA ===");
            foreach ($resumen as $item) {
                logCron("- {$item['tipo']} {$item['estado']}: {$item['cantidad']}");
            }
            
            // Mostrar próximas notificaciones
            $proximas = $scheduler->getProximasNotificaciones(10);
            logCron("=== PRÓXIMAS NOTIFICACIONES ===");
            foreach ($proximas as $proxima) {
                $fecha = date('d/m/Y H:i', strtotime($proxima['fecha_programada']));
                logCron("- $fecha: {$proxima['tipo']} para {$proxima['cliente_nombre']} ({$proxima['servicio_nombre']})");
            }
            break;
            
        case 'test':
            // Modo de prueba - solo mostrar qué se haría sin ejecutar
            logCron("=== MODO DE PRUEBA ===");
            
            $notificacionModel = new Notificacion();
            $pendientes = $notificacionModel->getPendientes();
            logCron("Notificaciones pendientes para procesar: " . count($pendientes));
            
            foreach ($pendientes as $notif) {
                $fecha = date('d/m/Y H:i', strtotime($notif['fecha_programada']));
                logCron("- ID {$notif['id']}: {$notif['tipo']} para {$notif['cliente_nombre']} programada para $fecha");
            }
            
            $servicioModel = new Servicio();
            $servicios = $servicioModel->findAll();
            $programables = 0;
            
            foreach ($servicios as $servicio) {
                if ($servicio['estado'] !== 'activo') continue;
                
                $fechaVencimiento = new DateTime($servicio['fecha_vencimiento']);
                $hoy = new DateTime();
                
                if ($fechaVencimiento <= $hoy) continue;
                
                $diasParaVencimiento = $hoy->diff($fechaVencimiento)->days;
                
                if (in_array($diasParaVencimiento, ALERT_DAYS)) {
                    $programables++;
                }
            }
            
            logCron("Servicios que requerirían notificaciones hoy: $programables");
            break;
            
        default:
            logCron("Acción no reconocida: $accion");
            logCron("Acciones disponibles: procesar, programar, limpiar, resumen, test");
            exit(1);
    }
    
    logCron("Cron de notificaciones completado exitosamente");
    
} catch (Exception $e) {
    $error = "Error en cron de notificaciones: " . $e->getMessage();
    logCron($error);
    
    // Enviar email de alerta al administrador si está configurado
    try {
        $emailService = new EmailService();
        $adminEmail = MAIL_FROM; // O configurar un email específico para alertas
        
        $asunto = "Error en Cron de Notificaciones - " . SITE_NAME;
        $mensaje = "Se produjo un error en el procesamiento automático de notificaciones:\n\n";
        $mensaje .= "Error: " . $e->getMessage() . "\n";
        $mensaje .= "Archivo: " . $e->getFile() . "\n";
        $mensaje .= "Línea: " . $e->getLine() . "\n";
        $mensaje .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
        $mensaje .= "Servidor: " . gethostname() . "\n\n";
        $mensaje .= "Por favor, revise los logs del servidor para más detalles.";
        
        $emailService->enviar($adminEmail, $asunto, $mensaje, false);
        logCron("Email de alerta enviado al administrador");
        
    } catch (Exception $emailError) {
        logCron("Error al enviar email de alerta: " . $emailError->getMessage());
    }
    
    exit(1);
}

// Función para verificar y crear directorios necesarios
function verificarDirectorios() {
    $directorios = [
        __DIR__ . '/logs',
        __DIR__ . '/exports'
    ];
    
    foreach ($directorios as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            logCron("Directorio creado: $dir");
        }
    }
}

// Verificar directorios al inicio
verificarDirectorios();

/**
 * Ejemplo de configuración de crontab completa:
 * 
 * # Editar crontab
 * crontab -e
 * 
 * # Agregar las siguientes líneas (ajustar la ruta):
 * 
 * # Procesar notificaciones cada 5 minutos
 * 0,5,10,15,20,25,30,35,40,45,50,55 * * * * /usr/bin/php /var/www/html/sistemaid/cron_notifications.php procesar >/dev/null 2>&1
 * 
 * # Programar notificaciones diariamente a las 6:00 AM
 * 0 6 * * * /usr/bin/php /var/www/html/sistemaid/cron_notifications.php programar >/dev/null 2>&1
 * 
 * # Limpiar notificaciones antiguas semanalmente (domingos a las 2:00 AM)
 * 0 2 * * 0 /usr/bin/php /var/www/html/sistemaid/cron_notifications.php limpiar >/dev/null 2>&1
 * 
 * # Generar resumen diario a las 23:30
 * 30 23 * * * /usr/bin/php /var/www/html/sistemaid/cron_notifications.php resumen >/dev/null 2>&1
 * 
 * # Para ver los logs:
 * tail -f /var/www/html/sistemaid/logs/cron_notifications.log
 */
?>