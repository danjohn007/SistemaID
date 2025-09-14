<?php
/**
 * Test de Conexión y Configuración
 * Sistema de Control de Servicios Digitales
 */

require_once 'config/config.php';
require_once 'config/database.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Conexión - Sistema ID</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-cogs"></i> Test de Conexión y Configuración</h4>
                    </div>
                    <div class="card-body">
                        
                        <!-- Información del Sistema -->
                        <h5><i class="fas fa-info-circle text-info"></i> Información del Sistema</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>Nombre:</strong> <?= SITE_NAME ?></p>
                                <p><strong>Versión:</strong> <?= SITE_VERSION ?></p>
                                <p><strong>URL Base:</strong> <code><?= BASE_URL ?></code></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>PHP:</strong> <?= phpversion() ?></p>
                                <p><strong>Zona Horaria:</strong> <?= date_default_timezone_get() ?></p>
                                <p><strong>Fecha/Hora:</strong> <?= date('d/m/Y H:i:s') ?></p>
                            </div>
                        </div>

                        <!-- Test de Base de Datos -->
                        <h5><i class="fas fa-database text-primary"></i> Conexión a Base de Datos</h5>
                        <div class="mb-4">
                            <?php
                            try {
                                $db = Database::getInstance();
                                if ($db->testConnection()) {
                                    echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Conexión exitosa a la base de datos</div>';
                                    
                                    // Verificar si existen las tablas principales
                                    $conn = $db->getConnection();
                                    $tables = ['usuarios', 'clientes', 'servicios', 'pagos', 'notificaciones'];
                                    $missing_tables = [];
                                    
                                    foreach ($tables as $table) {
                                        $stmt = $conn->prepare("SHOW TABLES LIKE ?");
                                        $stmt->execute([$table]);
                                        if (!$stmt->fetch()) {
                                            $missing_tables[] = $table;
                                        }
                                    }
                                    
                                    if (empty($missing_tables)) {
                                        echo '<div class="alert alert-success"><i class="fas fa-table"></i> Todas las tablas principales están presentes</div>';
                                        
                                        // Contar registros
                                        foreach ($tables as $table) {
                                            $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
                                            $count = $stmt->fetch()['count'];
                                            echo "<p><strong>$table:</strong> $count registros</p>";
                                        }
                                    } else {
                                        echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Faltan las siguientes tablas: ' . implode(', ', $missing_tables) . '</div>';
                                        echo '<p>Para crear las tablas, ejecute el archivo <code>sql/schema.sql</code> en su base de datos.</p>';
                                    }
                                    
                                } else {
                                    echo '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Error en la conexión a la base de datos</div>';
                                }
                            } catch (Exception $e) {
                                echo '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Error: ' . $e->getMessage() . '</div>';
                                echo '<div class="alert alert-info"><strong>Configuración actual:</strong><br>';
                                echo 'Host: ' . DB_HOST . '<br>';
                                echo 'Base de datos: ' . DB_NAME . '<br>';
                                echo 'Usuario: ' . DB_USER . '<br>';
                                echo 'Charset: ' . DB_CHARSET . '</div>';
                            }
                            ?>
                        </div>

                        <!-- Test de Extensiones PHP -->
                        <h5><i class="fas fa-code text-warning"></i> Extensiones PHP Requeridas</h5>
                        <div class="mb-4">
                            <?php
                            $required_extensions = [
                                'pdo' => 'PDO',
                                'pdo_mysql' => 'PDO MySQL',
                                'json' => 'JSON',
                                'session' => 'Sessions',
                                'curl' => 'cURL (para APIs)',
                                'openssl' => 'OpenSSL'
                            ];
                            
                            foreach ($required_extensions as $ext => $name) {
                                $loaded = extension_loaded($ext);
                                $class = $loaded ? 'success' : 'danger';
                                $icon = $loaded ? 'check' : 'times';
                                echo "<div class='alert alert-$class py-2'><i class='fas fa-$icon'></i> $name: " . ($loaded ? 'Cargada' : 'No disponible') . "</div>";
                            }
                            ?>
                        </div>

                        <!-- Test de Permisos -->
                        <h5><i class="fas fa-lock text-secondary"></i> Permisos de Escritura</h5>
                        <div class="mb-4">
                            <?php
                            $write_dirs = ['exports', 'assets/uploads'];
                            
                            foreach ($write_dirs as $dir) {
                                if (!file_exists($dir)) {
                                    mkdir($dir, 0755, true);
                                }
                                
                                $writable = is_writable($dir);
                                $class = $writable ? 'success' : 'warning';
                                $icon = $writable ? 'check' : 'exclamation-triangle';
                                echo "<div class='alert alert-$class py-2'><i class='fas fa-$icon'></i> Directorio $dir: " . ($writable ? 'Escribible' : 'Sin permisos de escritura') . "</div>";
                            }
                            ?>
                        </div>

                        <!-- URLs del Sistema -->
                        <h5><i class="fas fa-link text-info"></i> URLs del Sistema</h5>
                        <div class="mb-4">
                            <div class="list-group">
                                <a href="<?= BASE_URL ?>" class="list-group-item list-group-item-action">
                                    <i class="fas fa-home"></i> Inicio
                                </a>
                                <a href="<?= BASE_URL ?>login" class="list-group-item list-group-item-action">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </a>
                                <a href="<?= BASE_URL ?>dashboard" class="list-group-item list-group-item-action">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                                <a href="<?= BASE_URL ?>clientes" class="list-group-item list-group-item-action">
                                    <i class="fas fa-users"></i> Clientes
                                </a>
                                <a href="<?= BASE_URL ?>servicios" class="list-group-item list-group-item-action">
                                    <i class="fas fa-server"></i> Servicios
                                </a>
                            </div>
                        </div>

                        <div class="text-center">
                            <a href="<?= BASE_URL ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-right"></i> Ir al Sistema
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>