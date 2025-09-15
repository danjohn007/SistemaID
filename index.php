<?php
/**
 * Sistema de Control de Servicios Digitales
 * Controlador principal - Router MVC
 */

session_start();

// Incluir archivos de configuración
require_once 'config/config.php';
require_once 'config/database.php';

// Autoloader simple para modelos y controladores
spl_autoload_register(function($class) {
    $paths = [
        'models/' . $class . '.php',
        'controllers/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Obtener acción desde URL
$action = $_GET['action'] ?? 'dashboard';
$subaction = $_GET['subaction'] ?? 'index';

// Sistema de autenticación simple
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'login');
        exit();
    }
}

function requireAdmin() {
    requireAuth();
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: ' . BASE_URL . 'dashboard');
        exit();
    }
}

// Router principal
try {
    switch ($action) {
        case 'login':
            if (isset($_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . 'dashboard');
                exit();
            }
            $controller = new AuthController();
            $controller->login();
            break;
            
        case 'logout':
            $controller = new AuthController();
            $controller->logout();
            break;
            
        case 'dashboard':
            requireAuth();
            $controller = new DashboardController();
            $controller->index();
            break;
            
        case 'clientes':
            requireAuth();
            $controller = new ClientesController();
            switch ($subaction) {
                case 'nuevo':
                    $controller->nuevo();
                    break;
                case 'editar':
                    $controller->editar();
                    break;
                case 'ver':
                    $controller->ver();
                    break;
                case 'eliminar':
                    $controller->eliminar();
                    break;
                default:
                    $controller->index();
            }
            break;
            
        case 'servicios':
            requireAuth();
            $controller = new ServiciosController();
            switch ($subaction) {
                case 'nuevo':
                    $controller->nuevo();
                    break;
                case 'editar':
                    $controller->editar();
                    break;
                case 'ver':
                    $controller->ver();
                    break;
                case 'eliminar':
                    $controller->eliminar();
                    break;
                default:
                    $controller->index();
            }
            break;
            
        case 'pagos':
            requireAuth();
            $controller = new PagosController();
            switch ($subaction) {
                case 'nuevo':
                    $controller->nuevo();
                    break;
                case 'editar':
                    $controller->editar();
                    break;
                default:
                    $controller->index();
            }
            break;
            
        case 'notificaciones':
            requireAuth();
            $controller = new NotificacionesController();
            switch ($subaction) {
                case 'configuracion':
                    $controller->configuracion();
                    break;
                case 'procesar':
                    $controller->procesar();
                    break;
                case 'programar':
                    $controller->programar();
                    break;
                case 'probar':
                    $controller->probar();
                    break;
                case 'ver':
                    $controller->ver();
                    break;
                case 'reenviar':
                    $controller->reenviar();
                    break;
                case 'estadisticas':
                    $controller->estadisticas();
                    break;
                case 'webhook':
                    $controller->webhook();
                    break;
                default:
                    $controller->index();
            }
            break;
            
        case 'reportes':
            requireAuth();
            $controller = new ReportesController();
            switch ($subaction) {
                case 'export':
                    $controller->export();
                    break;
                default:
                    $controller->index();
            }
            break;
            
        case 'configuracion':
            requireAdmin();
            $controller = new ConfiguracionController();
            $controller->index();
            break;
            
        default:
            if (isset($_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . 'dashboard');
            } else {
                header('Location: ' . BASE_URL . 'login');
            }
            exit();
    }
    
} catch (Exception $e) {
    if (DEBUG_MODE) {
        die("Error: " . $e->getMessage());
    } else {
        // Log del error y mostrar página de error genérica
        error_log("Sistema ID Error: " . $e->getMessage());
        include 'views/errors/500.php';
    }
}