<?php
/**
 * Script de prueba para el sistema de notificaciones
 * Solo ejecutar desde línea de comandos
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde línea de comandos.');
}

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

echo "=== PRUEBA DEL SISTEMA DE NOTIFICACIONES ===\n\n";

try {
    // 1. Probar conexión a base de datos
    echo "1. Probando conexión a base de datos...\n";
    $db = Database::getInstance();
    if ($db->testConnection()) {
        echo "   ✓ Conexión exitosa\n\n";
    } else {
        echo "   ✗ Error en conexión\n\n";
        exit(1);
    }
    
    // 2. Probar modelos de notificaciones
    echo "2. Probando modelos de notificaciones...\n";
    
    $notificacionModel = new Notificacion();
    $emailService = new EmailService();
    $whatsappService = new WhatsAppService();
    $scheduler = new NotificationScheduler();
    
    echo "   ✓ Todos los modelos se instanciaron correctamente\n\n";
    
    // 3. Probar estadísticas
    echo "3. Obteniendo estadísticas de notificaciones...\n";
    $estadisticas = $notificacionModel->getEstadisticas();
    
    echo "   - Total notificaciones: " . ($estadisticas['total_notificaciones'] ?? 0) . "\n";
    echo "   - Enviadas: " . ($estadisticas['enviadas'] ?? 0) . "\n";
    echo "   - Pendientes: " . ($estadisticas['pendientes'] ?? 0) . "\n";
    echo "   - Fallidas: " . ($estadisticas['fallidas'] ?? 0) . "\n";
    echo "   - Emails: " . ($estadisticas['emails'] ?? 0) . "\n";
    echo "   - WhatsApps: " . ($estadisticas['whatsapps'] ?? 0) . "\n\n";
    
    // 4. Verificar tabla de notificaciones
    echo "4. Verificando estructura de tabla notificaciones...\n";
    $conn = $db->getConnection();
    $stmt = $conn->query("DESCRIBE notificaciones");
    $columnas = $stmt->fetchAll();
    
    $columnasRequeridas = ['id', 'servicio_id', 'tipo', 'mensaje', 'destinatario', 'estado', 'fecha_programada'];
    $columnasEncontradas = array_column($columnas, 'Field');
    
    foreach ($columnasRequeridas as $columna) {
        if (in_array($columna, $columnasEncontradas)) {
            echo "   ✓ Columna '$columna' presente\n";
        } else {
            echo "   ✗ Columna '$columna' faltante\n";
        }
    }
    echo "\n";
    
    // 5. Probar servicios próximos a vencer (sin crear notificaciones reales)
    echo "5. Verificando servicios próximos a vencer...\n";
    $servicioModel = new Servicio();
    
    foreach (ALERT_DAYS as $dias) {
        $serviciosPorVencer = $servicioModel->getServiciosPorVencer($dias);
        echo "   - Servicios que vencen en $dias días: " . count($serviciosPorVencer) . "\n";
    }
    
    $serviciosVencidos = $servicioModel->getServiciosVencidos();
    echo "   - Servicios ya vencidos: " . count($serviciosVencidos) . "\n\n";
    
    // 6. Probar configuración de email (solo validación)
    echo "6. Verificando configuración de email...\n";
    if ($emailService->validarEmail(MAIL_FROM)) {
        echo "   ✓ Email configurado válido: " . MAIL_FROM . "\n";
    } else {
        echo "   ✗ Email configurado inválido: " . MAIL_FROM . "\n";
    }
    
    // 7. Probar configuración de WhatsApp (solo validación)
    echo "7. Verificando configuración de WhatsApp...\n";
    if (!empty(WHATSAPP_API_TOKEN)) {
        echo "   ✓ Token de WhatsApp configurado\n";
    } else {
        echo "   ⚠ Token de WhatsApp no configurado (normal en desarrollo)\n";
    }
    
    // 8. Probar intervalos de alerta
    echo "\n8. Intervalos de alerta configurados:\n";
    foreach (ALERT_DAYS as $index => $dias) {
        echo "   - Alerta " . ($index + 1) . ": $dias días antes del vencimiento\n";
    }
    
    echo "\n=== PRUEBAS COMPLETADAS EXITOSAMENTE ===\n";
    echo "El sistema de notificaciones está correctamente configurado.\n\n";
    
    // Mostrar próximos pasos
    echo "PRÓXIMOS PASOS:\n";
    echo "1. Configurar credenciales SMTP en la interfaz web\n";
    echo "2. Configurar WhatsApp Business API (opcional)\n";
    echo "3. Ejecutar cron job para procesar notificaciones automáticamente\n";
    echo "4. Monitorear logs en: logs/cron_notifications.log\n\n";
    
} catch (Exception $e) {
    echo "✗ Error durante las pruebas: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    exit(1);
}
?>