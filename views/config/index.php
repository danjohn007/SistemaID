<?php
$pageTitle = 'Configuración del Sistema';
include 'views/layout/header.php';
?>

<div id="alert-container"></div>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-cogs text-primary me-2"></i>
        Configuración del Sistema
    </h1>
</div>

<!-- Solo Admin -->
<?php if (($_SESSION['user_role'] ?? '') !== 'admin'): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle me-2"></i>
    Acceso denegado. Solo los administradores pueden acceder a esta sección.
</div>
<?php else: ?>

<!-- Mensajes de éxito/error -->
<?php if (!empty($data['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>
        <?= htmlspecialchars($data['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($data['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= htmlspecialchars($data['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Configuraciones Tabs -->
<div class="row">
    <div class="col-12">
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-system-tab" data-bs-toggle="tab" data-bs-target="#nav-system" type="button" role="tab">
                    <i class="fas fa-cog me-2"></i>Sistema
                </button>
                <button class="nav-link" id="nav-email-tab" data-bs-toggle="tab" data-bs-target="#nav-email" type="button" role="tab">
                    <i class="fas fa-envelope me-2"></i>Email
                </button>
                <button class="nav-link" id="nav-notifications-tab" data-bs-toggle="tab" data-bs-target="#nav-notifications" type="button" role="tab">
                    <i class="fas fa-bell me-2"></i>Notificaciones
                </button>
                <button class="nav-link" id="nav-info-tab" data-bs-toggle="tab" data-bs-target="#nav-info" type="button" role="tab">
                    <i class="fas fa-info-circle me-2"></i>Información
                </button>
            </div>
        </nav>
        
        <div class="tab-content" id="nav-tabContent">
            <!-- Configuración del Sistema -->
            <div class="tab-pane fade show active" id="nav-system" role="tabpanel">
                <div class="card shadow mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-cog me-2"></i>
                            Configuración General del Sistema
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="system_config">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre_sistema" class="form-label">Nombre del Sistema</label>
                                    <input type="text" class="form-control" id="nombre_sistema" name="nombre_sistema" 
                                           value="<?= htmlspecialchars($data['configuraciones']['sistema']['nombre_sistema'] ?? 'Sistema ID') ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="timezone" class="form-label">Zona Horaria</label>
                                    <select class="form-control" id="timezone" name="timezone">
                                        <option value="America/Mexico_City" <?= ($data['configuraciones']['sistema']['timezone'] ?? '') == 'America/Mexico_City' ? 'selected' : '' ?>>México</option>
                                        <option value="America/New_York" <?= ($data['configuraciones']['sistema']['timezone'] ?? '') == 'America/New_York' ? 'selected' : '' ?>>Nueva York (EST)</option>
                                        <option value="America/Los_Angeles" <?= ($data['configuraciones']['sistema']['timezone'] ?? '') == 'America/Los_Angeles' ? 'selected' : '' ?>>Los Ángeles (PST)</option>
                                        <option value="Europe/Madrid" <?= ($data['configuraciones']['sistema']['timezone'] ?? '') == 'Europe/Madrid' ? 'selected' : '' ?>>Madrid</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="moneda" class="form-label">Moneda</label>
                                    <select class="form-control" id="moneda" name="moneda">
                                        <option value="MXN" <?= ($data['configuraciones']['sistema']['moneda'] ?? '') == 'MXN' ? 'selected' : '' ?>>Peso Mexicano (MXN)</option>
                                        <option value="USD" <?= ($data['configuraciones']['sistema']['moneda'] ?? '') == 'USD' ? 'selected' : '' ?>>Dólar (USD)</option>
                                        <option value="EUR" <?= ($data['configuraciones']['sistema']['moneda'] ?? '') == 'EUR' ? 'selected' : '' ?>>Euro (EUR)</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="idioma" class="form-label">Idioma</label>
                                    <select class="form-control" id="idioma" name="idioma">
                                        <option value="es" <?= ($data['configuraciones']['sistema']['idioma'] ?? '') == 'es' ? 'selected' : '' ?>>Español</option>
                                        <option value="en" <?= ($data['configuraciones']['sistema']['idioma'] ?? '') == 'en' ? 'selected' : '' ?>>English</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="backup_automatico" name="backup_automatico" 
                                               <?= ($data['configuraciones']['sistema']['backup_automatico'] ?? '') == '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="backup_automatico">
                                            Activar respaldo automático de la base de datos
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Configuración
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Configuración de Email -->
            <div class="tab-pane fade" id="nav-email" role="tabpanel">
                <div class="card shadow mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-envelope me-2"></i>
                            Configuración de Email SMTP
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="email_config">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_host" class="form-label">Servidor SMTP</label>
                                    <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                           value="<?= htmlspecialchars($data['configuraciones']['email']['smtp_host'] ?? '') ?>"
                                           placeholder="smtp.gmail.com">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_port" class="form-label">Puerto</label>
                                    <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                           value="<?= htmlspecialchars($data['configuraciones']['email']['smtp_port'] ?? '587') ?>"
                                           placeholder="587">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_username" class="form-label">Usuario SMTP</label>
                                    <input type="email" class="form-control" id="smtp_username" name="smtp_username" 
                                           value="<?= htmlspecialchars($data['configuraciones']['email']['smtp_username'] ?? '') ?>"
                                           placeholder="usuario@gmail.com">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_password" class="form-label">Contraseña SMTP</label>
                                    <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                           value="<?= htmlspecialchars($data['configuraciones']['email']['smtp_password'] ?? '') ?>"
                                           placeholder="••••••••">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="smtp_encryption" class="form-label">Encriptación</label>
                                    <select class="form-control" id="smtp_encryption" name="smtp_encryption">
                                        <option value="tls" <?= ($data['configuraciones']['email']['smtp_encryption'] ?? '') == 'tls' ? 'selected' : '' ?>>TLS</option>
                                        <option value="ssl" <?= ($data['configuraciones']['email']['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : '' ?>>SSL</option>
                                        <option value="" <?= empty($data['configuraciones']['email']['smtp_encryption'] ?? '') ? 'selected' : '' ?>>Ninguna</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="from_email" class="form-label">Email Remitente</label>
                                    <input type="email" class="form-control" id="from_email" name="from_email" 
                                           value="<?= htmlspecialchars($data['configuraciones']['email']['from_email'] ?? '') ?>"
                                           placeholder="noreply@empresa.com">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="from_name" class="form-label">Nombre Remitente</label>
                                    <input type="text" class="form-control" id="from_name" name="from_name" 
                                           value="<?= htmlspecialchars($data['configuraciones']['email']['from_name'] ?? 'Sistema ID') ?>"
                                           placeholder="Sistema ID">
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-primary" onclick="testEmail()">
                                    <i class="fas fa-paper-plane me-2"></i>Probar Configuración
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Configuración
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Configuración de Notificaciones -->
            <div class="tab-pane fade" id="nav-notifications" role="tabpanel">
                <div class="card shadow mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-bell me-2"></i>
                            Configuración de Notificaciones
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="notification_config">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="dias_anticipacion" class="form-label">Días de Anticipación</label>
                                    <input type="number" class="form-control" id="dias_anticipacion" name="dias_anticipacion" 
                                           value="<?= htmlspecialchars($data['configuraciones']['notificaciones']['dias_anticipacion'] ?? '7') ?>"
                                           min="1" max="90">
                                    <small class="form-text text-muted">Días antes del vencimiento para enviar notificaciones</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="hora_envio" class="form-label">Hora de Envío</label>
                                    <input type="time" class="form-control" id="hora_envio" name="hora_envio" 
                                           value="<?= htmlspecialchars($data['configuraciones']['notificaciones']['hora_envio'] ?? '09:00') ?>">
                                    <small class="form-text text-muted">Hora del día para enviar notificaciones</small>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notificaciones_activas" name="notificaciones_activas" 
                                               <?= ($data['configuraciones']['notificaciones']['notificaciones_activas'] ?? '') == '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="notificaciones_activas">
                                            Activar notificaciones automáticas
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="recordatorios_multiples" name="recordatorios_multiples" 
                                               <?= ($data['configuraciones']['notificaciones']['recordatorios_multiples'] ?? '') == '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="recordatorios_multiples">
                                            Enviar recordatorios múltiples (7, 3 y 1 día antes)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Configuración
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Información del Sistema -->
            <div class="tab-pane fade" id="nav-info" role="tabpanel">
                <div class="card shadow mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Información del Sistema
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <td><strong>URL Base:</strong></td>
                                        <td><code><?= BASE_URL ?></code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Base de Datos:</strong></td>
                                        <td><?= DB_NAME ?> @ <?= DB_HOST ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Zona Horaria Actual:</strong></td>
                                        <td><?= date_default_timezone_get() ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Versión del Sistema:</strong></td>
                                        <td><?= SITE_VERSION ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Modo Debug:</strong></td>
                                        <td><?= DEBUG_MODE ? '<span class="badge bg-warning">Activado</span>' : '<span class="badge bg-success">Desactivado</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Versión PHP:</strong></td>
                                        <td><?= PHP_VERSION ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Servidor Web:</strong></td>
                                        <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'No disponible' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Fecha/Hora del Servidor:</strong></td>
                                        <td><?= date('d/m/Y H:i:s') ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<!-- Modal para test de email -->
<div class="modal fade" id="testEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Probar Configuración de Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="test_email" class="form-label">Email de Prueba</label>
                    <input type="email" class="form-control" id="test_email" placeholder="test@ejemplo.com">
                </div>
                <div id="test_result"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="sendTestEmail()">Enviar Prueba</button>
            </div>
        </div>
    </div>
</div>

<script>
function testEmail() {
    document.getElementById('test_email').value = '';
    document.getElementById('test_result').innerHTML = '';
    new bootstrap.Modal(document.getElementById('testEmailModal')).show();
}

function sendTestEmail() {
    const testEmail = document.getElementById('test_email').value;
    if (!testEmail) {
        document.getElementById('test_result').innerHTML = '<div class="alert alert-danger">Por favor ingrese un email de prueba.</div>';
        return;
    }
    
    document.getElementById('test_result').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Enviando...</div>';
    
    const formData = new FormData();
    formData.append('test_email', testEmail);
    
    fetch('<?= BASE_URL ?>configuracion&subaction=test_email', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const alertClass = data.success ? 'alert-success' : 'alert-danger';
        const icon = data.success ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
        document.getElementById('test_result').innerHTML = 
            `<div class="alert ${alertClass}"><i class="${icon} me-2"></i>${data.message}</div>`;
    })
    .catch(error => {
        document.getElementById('test_result').innerHTML = 
            '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error de conexión.</div>';
    });
}
</script>

<?php include 'views/layout/footer.php'; ?>