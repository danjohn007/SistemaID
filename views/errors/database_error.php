<?php
$pageTitle = 'Error de Conexión';
// Use a minimal header in case database is down
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Sistema ID</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="fas fa-database text-danger" style="font-size: 4rem;"></i>
                        </div>
                        <h2 class="text-danger mb-3">Error de Conexión</h2>
                        <p class="text-muted mb-4">
                            No se pudo establecer conexión con la base de datos. 
                            Este error ha sido reportado automáticamente.
                        </p>
                        <div class="d-grid gap-2">
                            <a href="<?= BASE_URL ?>dashboard" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Volver al Dashboard
                            </a>
                            <button onclick="location.reload()" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Intentar Nuevamente
                            </button>
                        </div>
                        <?php if (DEBUG_MODE): ?>
                        <div class="mt-4 text-start">
                            <small class="text-muted">
                                <strong>Debug Info:</strong><br>
                                Verifique la configuración de la base de datos en config/config.php<br>
                                Revise que el servidor MySQL esté ejecutándose<br>
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>