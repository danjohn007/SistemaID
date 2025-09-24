<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';

// Simulate authenticated user
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

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

echo "Testing ServiciosController::nuevo()...\n";

try {
    $controller = new ServiciosController();
    ob_start();
    $controller->nuevo();
    $output = ob_get_contents();
    ob_end_clean();
    
    if (empty($output)) {
        echo "PROBLEM: Empty output from nuevo() method\n";
    } else {
        echo "SUCCESS: Output generated (" . strlen($output) . " characters)\n";
        // Check if it contains expected form elements
        if (strpos($output, '<form') !== false) {
            echo "Form found in output\n";
        } else {
            echo "No form found in output\n";
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
