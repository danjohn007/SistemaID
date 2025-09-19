<?php
/**
 * Test específico para creación de servicios
 */

// Simular variables de sesión y server
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

// Incluir archivos necesarios
require_once 'config/config.php';
require_once 'config/database.php';

// Autoloader
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

echo "<h1>Test de Creación de Servicios</h1>";

try {
    // Verificar conexión a base de datos
    $db = Database::getInstance();
    echo "<p>✅ Conexión a base de datos OK</p>";
    
    // Verificar modelos
    $clienteModel = new Cliente();
    $tipoServicioModel = new TipoServicio();
    $servicioModel = new Servicio();
    $notificacionModel = new Notificacion();
    echo "<p>✅ Modelos cargados OK</p>";
    
    // Verificar datos básicos
    $clientes = $clienteModel->findAll();
    $tiposServicio = $tipoServicioModel->findAll();
    
    echo "<p>Clientes disponibles: " . count($clientes) . "</p>";
    echo "<p>Tipos de servicio disponibles: " . count($tiposServicio) . "</p>";
    
    if (count($clientes) > 0 && count($tiposServicio) > 0) {
        echo "<h2>🧪 Simulando creación de servicio...</h2>";
        
        // Datos de prueba
        $data = [
            'cliente_id' => $clientes[0]['id'],
            'tipo_servicio_id' => $tiposServicio[0]['id'],
            'nombre' => 'Servicio Test - ' . date('Y-m-d H:i:s'),
            'descripcion' => 'Servicio de prueba para debug',
            'dominio' => 'test.example.com',
            'monto' => 1000.00,
            'periodo_vencimiento' => 'anual',
            'fecha_inicio' => date('Y-m-d')
        ];
        
        echo "<p>Datos a insertar:</p>";
        echo "<pre>" . print_r($data, true) . "</pre>";
        
        // Crear servicio
        if ($servicioModel->create($data)) {
            $servicioId = Database::getInstance()->getConnection()->lastInsertId();
            echo "<p>✅ Servicio creado con ID: $servicioId</p>";
            
            // Verificar datos del servicio
            $servicioCreado = $servicioModel->findById($servicioId);
            echo "<p>📋 Datos del servicio creado:</p>";
            echo "<pre>" . print_r($servicioCreado, true) . "</pre>";
            
            // Probar programación de notificaciones
            echo "<h3>🔔 Probando programación de notificaciones...</h3>";
            
            if ($notificacionModel->programarNotificacionesVencimiento($servicioId)) {
                echo "<p>✅ Notificaciones programadas exitosamente</p>";
                
                // Verificar notificaciones creadas
                $connection = Database::getInstance()->getConnection();
                $stmt = $connection->prepare("SELECT * FROM notificaciones WHERE servicio_id = ?");
                $stmt->execute([$servicioId]);
                $notificaciones = $stmt->fetchAll();
                
                echo "<p>📨 Notificaciones creadas: " . count($notificaciones) . "</p>";
                foreach ($notificaciones as $notif) {
                    echo "<p>- Tipo: {$notif['tipo']}, Destinatario: {$notif['destinatario']}, Fecha: {$notif['fecha_programada']}</p>";
                }
            } else {
                echo "<p>❌ Error al programar notificaciones</p>";
            }
            
            // Limpiar: eliminar el servicio de prueba
            $servicioModel->delete($servicioId);
            echo "<p>🧹 Servicio de prueba eliminado</p>";
            
        } else {
            echo "<p>❌ Error al crear el servicio</p>";
        }
    } else {
        echo "<p>❌ No hay datos suficientes para la prueba (necesitas al menos 1 cliente y 1 tipo de servicio)</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error durante el test: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr><p><a href='" . BASE_URL . "'>← Volver al sistema</a></p>";
?>