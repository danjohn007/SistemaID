<?php
$pageTitle = 'Configuración de Notificaciones';
include 'views/layout/header.php';
?>

<div id="alert-container"></div>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-cog text-primary me-2"></i>
        Configuración de Notificaciones
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>notificaciones" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Volver a Notificaciones
        </a>
    </div>
</div>

<form method="POST" id="configForm">
    <div class="row">
        <!-- Configuración de Email -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        Configuración de Email
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Método de Envío</label>
                        <select name="email_method" class="form-select" id="emailMethod">
                            <option value="mail" <?= ($config['email_method'] ?? 'mail') === 'mail' ? 'selected' : '' ?>>
                                PHP mail() - Básico
                            </option>
                            <option value="smtp" <?= ($config['email_method'] ?? '') === 'smtp' ? 'selected' : '' ?>>
                                SMTP - Recomendado
                            </option>
                        </select>
                        <div class="form-text">Se recomienda SMTP para mayor confiabilidad</div>
                    </div>
                    
                    <div id="smtpConfig" style="display: <?= ($config['email_method'] ?? 'mail') === 'smtp' ? 'block' : 'none' ?>">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Servidor SMTP</label>
                                    <input type="text" name="email_smtp_host" class="form-control" 
                                           value="<?= htmlspecialchars($config['email_smtp_host'] ?? '') ?>"
                                           placeholder="smtp.gmail.com">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Puerto</label>
                                    <input type="number" name="email_smtp_port" class="form-control" 
                                           value="<?= $config['email_smtp_port'] ?? 587 ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Usuario SMTP</label>
                            <input type="email" name="email_smtp_username" class="form-control" 
                                   value="<?= htmlspecialchars($config['email_smtp_username'] ?? '') ?>"
                                   placeholder="tu-email@gmail.com">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Contraseña SMTP</label>
                            <input type="password" name="email_smtp_password" class="form-control" 
                                   value="<?= htmlspecialchars($config['email_smtp_password'] ?? '') ?>"
                                   placeholder="Tu contraseña o app password">
                            <div class="form-text">Para Gmail, usa una contraseña de aplicación</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Seguridad</label>
                            <select name="email_smtp_secure" class="form-select">
                                <option value="tls" <?= ($config['email_smtp_secure'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>
                                    TLS (Recomendado)
                                </option>
                                <option value="ssl" <?= ($config['email_smtp_secure'] ?? '') === 'ssl' ? 'selected' : '' ?>>
                                    SSL
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label class="form-label">Email Remitente</label>
                        <input type="email" name="email_from_email" class="form-control" 
                               value="<?= htmlspecialchars($config['email_from_email'] ?? MAIL_FROM) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre Remitente</label>
                        <input type="text" name="email_from_name" class="form-control" 
                               value="<?= htmlspecialchars($config['email_from_name'] ?? MAIL_FROM_NAME) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email para Respuestas</label>
                        <input type="email" name="email_reply_to" class="form-control" 
                               value="<?= htmlspecialchars($config['email_reply_to'] ?? MAIL_FROM) ?>">
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" class="btn btn-outline-primary" onclick="probarEmail()">
                            <i class="fas fa-paper-plane me-1"></i>
                            Probar Email
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Configuración de WhatsApp -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fab fa-whatsapp text-success me-2"></i>
                        Configuración de WhatsApp Business API
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Nota:</strong> Requiere una cuenta de WhatsApp Business API activa.
                        <a href="https://developers.facebook.com/docs/whatsapp" target="_blank" class="alert-link">
                            Ver documentación
                        </a>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phone Number ID</label>
                        <input type="text" name="whatsapp_phone_number_id" class="form-control" 
                               value="<?= htmlspecialchars($config['whatsapp_phone_number_id'] ?? '') ?>"
                               placeholder="123456789012345">
                        <div class="form-text">ID del número de teléfono en Facebook Developer Console</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Access Token</label>
                        <input type="text" name="whatsapp_access_token" class="form-control" 
                               value="<?= htmlspecialchars($config['whatsapp_access_token'] ?? '') ?>"
                               placeholder="EAAxxxxxxxxxx">
                        <div class="form-text">Token de acceso permanente de WhatsApp Business API</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Verify Token</label>
                        <input type="text" name="whatsapp_verify_token" class="form-control" 
                               value="<?= htmlspecialchars($config['whatsapp_verify_token'] ?? '') ?>"
                               placeholder="mi_token_secreto_123">
                        <div class="form-text">Token para verificar el webhook</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Webhook URL</label>
                        <div class="input-group">
                            <input type="text" name="whatsapp_webhook_url" class="form-control" 
                                   value="<?= htmlspecialchars($config['whatsapp_webhook_url'] ?? BASE_URL . 'notificaciones/webhook') ?>"
                                   readonly>
                            <button type="button" class="btn btn-outline-secondary" onclick="copyWebhookUrl()">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <div class="form-text">URL para configurar en Facebook Developer Console</div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" class="btn btn-outline-success" onclick="probarWhatsApp()">
                            <i class="fab fa-whatsapp me-1"></i>
                            Probar WhatsApp
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Configuración de Intervalos -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-calendar-alt text-info me-2"></i>
                Intervalos de Recordatorio
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Los recordatorios se envían automáticamente en los siguientes intervalos antes del vencimiento:
            </div>
            
            <div class="row">
                <?php foreach (ALERT_DAYS as $dias): ?>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="card-title text-primary"><?= $dias ?></h4>
                            <p class="card-text">
                                <?= $dias === 1 ? 'día' : 'días' ?><br>
                                <small class="text-muted">antes del vencimiento</small>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-lightbulb me-1"></i>
                    <strong>Tip:</strong> Los intervalos se configuran en el archivo config/config.php en la constante ALERT_DAYS.
                </small>
            </div>
        </div>
    </div>
    
    <!-- Botones de Acción -->
    <div class="card">
        <div class="card-body">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="button" class="btn btn-outline-secondary me-md-2" onclick="window.history.back()">
                    <i class="fas fa-times me-1"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>
                    Guardar Configuración
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Modal para Pruebas -->
<div class="modal fade" id="testModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Probar Configuración</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="testForm">
                    <input type="hidden" id="testType" name="tipo">
                    <div class="mb-3">
                        <label class="form-label" id="testLabel">Destinatario</label>
                        <input type="text" id="testDestinatario" name="destinatario" class="form-control" required>
                        <div class="form-text" id="testHelp"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="enviarPrueba()">
                    <i class="fas fa-paper-plane me-1"></i>
                    Enviar Prueba
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Mostrar/ocultar configuración SMTP
document.getElementById('emailMethod').addEventListener('change', function() {
    const smtpConfig = document.getElementById('smtpConfig');
    smtpConfig.style.display = this.value === 'smtp' ? 'block' : 'none';
});

// Probar configuración de email
function probarEmail() {
    document.getElementById('testType').value = 'email';
    document.getElementById('testLabel').textContent = 'Email de destino';
    document.getElementById('testDestinatario').placeholder = 'ejemplo@correo.com';
    document.getElementById('testDestinatario').type = 'email';
    document.getElementById('testHelp').textContent = 'Ingrese un email válido para recibir el mensaje de prueba';
    
    const modal = new bootstrap.Modal(document.getElementById('testModal'));
    modal.show();
}

// Probar configuración de WhatsApp
function probarWhatsApp() {
    document.getElementById('testType').value = 'whatsapp';
    document.getElementById('testLabel').textContent = 'Número de teléfono';
    document.getElementById('testDestinatario').placeholder = '+5215512345678';
    document.getElementById('testDestinatario').type = 'tel';
    document.getElementById('testHelp').textContent = 'Ingrese un número con código de país (ej: +52 para México)';
    
    const modal = new bootstrap.Modal(document.getElementById('testModal'));
    modal.show();
}

// Enviar prueba
function enviarPrueba() {
    const form = document.getElementById('testForm');
    const formData = new FormData(form);
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Enviando...';
    
    fetch('<?= BASE_URL ?>notificaciones/probar?ajax=1', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('testModal'));
        modal.hide();
        
        showAlert(data.success ? 'success' : 'danger', data.message);
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Error de conexión');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Copiar URL del webhook
function copyWebhookUrl() {
    const input = document.querySelector('input[name="whatsapp_webhook_url"]');
    input.select();
    document.execCommand('copy');
    
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    
    setTimeout(() => {
        button.innerHTML = originalHTML;
    }, 2000);
    
    showAlert('info', 'URL copiada al portapapeles');
}

// Validar formulario antes de enviar
document.getElementById('configForm').addEventListener('submit', function(e) {
    const emailMethod = document.getElementById('emailMethod').value;
    
    if (emailMethod === 'smtp') {
        const requiredFields = ['email_smtp_host', 'email_smtp_username', 'email_smtp_password'];
        let hasErrors = false;
        
        requiredFields.forEach(field => {
            const input = document.querySelector(`input[name="${field}"]`);
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                hasErrors = true;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        if (hasErrors) {
            e.preventDefault();
            showAlert('warning', 'Por favor complete todos los campos requeridos para SMTP');
            return;
        }
    }
});

// Función para mostrar alertas
function showAlert(type, message) {
    const alertContainer = document.getElementById('alert-container');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    alertContainer.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}
</script>

<?php include 'views/layout/footer.php'; ?>