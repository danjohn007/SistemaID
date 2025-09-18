<?php
/**
 * Sistema de Control de Servicios Digitales
 * Configuración principal del sistema
 */

// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'talentos_control');
define('DB_USER', 'talentos_control');
define('DB_PASS', 'Danjohn007!');
define('DB_CHARSET', 'utf8mb4');

// Configuración del sistema
define('SITE_NAME', 'Sistema ID - Control de Servicios Digitales');
define('SITE_VERSION', '1.0.0');

// Configuración de sesiones
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos

// Configuración de notificaciones
define('MAIL_FROM', 'noreply@sistemaid.com');
define('MAIL_FROM_NAME', 'Sistema ID');
define('WHATSAPP_API_TOKEN', ''); // Token de WhatsApp API

// Configuración de archivos de exportación
define('EXPORT_PATH', 'exports/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Configuración de alertas de vencimiento (días antes)
define('ALERT_DAYS', [30, 15, 7, 1]);

// Auto-detección de URL base
function getBaseUrl() {
    // Handle CLI mode
    if (php_sapi_name() === 'cli') {
        return 'http://localhost/';
    }
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . $host . $path . '/';
}

define('BASE_URL', getBaseUrl());

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de errores para desarrollo
$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
if ($httpHost === 'localhost' || strpos($httpHost, '127.0.0.1') !== false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    define('DEBUG_MODE', true);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    define('DEBUG_MODE', false);
}
