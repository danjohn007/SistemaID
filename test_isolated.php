<?php
session_start();

// Set required server variables to avoid warnings
$_SERVER['HTTP_HOST'] = 'localhost:8000';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['HTTPS'] = '';

require_once 'config/config.php';

// Mock database class to isolate the view issue
class Database {
    private static $instance = null;
    private $connection;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        // Return a mock PDO connection for testing
        return new class {
            public function prepare($sql) {
                return new class {
                    public function execute($params = []) { return true; }
                    public function fetchAll() { return []; }
                    public function fetch() { return false; }
                };
            }
            public function lastInsertId() { return 1; }
        };
    }
}

// Mock models
class Cliente {
    public function findAll() { return []; }
}

class TipoServicio {
    public function findAll() { return []; }
}

class Servicio {
    public function create($data) { return true; }
}

class Notificacion {
    public function programarNotificacionesVencimiento($id) { return true; }
}

// Simulate authenticated user
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

require_once 'controllers/ServiciosController.php';

echo "Testing ServiciosController::nuevo() with mocked dependencies...\n";

try {
    $controller = new ServiciosController();
    
    // Test GET request (should show form)
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    ob_start();
    $controller->nuevo();
    $output = ob_get_contents();
    ob_end_clean();
    
    if (empty($output)) {
        echo "PROBLEM: Empty output from nuevo() method\n";
        echo "This indicates the issue is in the view file inclusion\n";
    } else {
        echo "SUCCESS: Output generated (" . strlen($output) . " characters)\n";
        if (strpos($output, '<form') !== false) {
            echo "Form found in output\n";
        } else {
            echo "No form found in output - checking what was included\n";
            echo "First 500 chars: " . substr($output, 0, 500) . "\n";
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
