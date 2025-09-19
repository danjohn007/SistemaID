<?php
/**
 * Test de conexión y estructura de base de datos
 */
require_once 'config/config.php';
require_once 'config/database.php';

echo "<h1>Test de Base de Datos - Sistema ID</h1>";

try {
    $db = Database::getInstance();
    echo "<p>✅ Conexión a la base de datos establecida correctamente</p>";
    
    // Test de conexión
    if ($db->testConnection()) {
        echo "<p>✅ Test de conexión exitoso</p>";
    } else {
        echo "<p>❌ Test de conexión falló</p>";
    }
    
    // Verificar tablas necesarias
    $requiredTables = ['clientes', 'tipos_servicios', 'servicios', 'notificaciones'];
    $connection = $db->getConnection();
    
    echo "<h2>Verificación de Tablas:</h2>";
    foreach ($requiredTables as $table) {
        try {
            $stmt = $connection->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p>✅ Tabla '$table' existe con $count registros</p>";
        } catch (PDOException $e) {
            echo "<p>❌ Error con tabla '$table': " . $e->getMessage() . "</p>";
        }
    }
    
    // Verificar datos básicos
    echo "<h2>Datos de Prueba:</h2>";
    
    // Clientes
    $stmt = $connection->query("SELECT COUNT(*) FROM clientes WHERE activo = 1");
    $clientCount = $stmt->fetchColumn();
    echo "<p>Clientes activos: $clientCount</p>";
    
    // Tipos de servicio
    $stmt = $connection->query("SELECT COUNT(*) FROM tipos_servicios WHERE activo = 1");
    $tipoCount = $stmt->fetchColumn();
    echo "<p>Tipos de servicio activos: $tipoCount</p>";
    
    if ($clientCount == 0 || $tipoCount == 0) {
        echo "<p>⚠️ <strong>ADVERTENCIA:</strong> No hay suficientes datos para crear servicios. Necesitas al menos 1 cliente y 1 tipo de servicio.</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr><p><a href='" . BASE_URL . "'>← Volver al sistema</a></p>";
?>