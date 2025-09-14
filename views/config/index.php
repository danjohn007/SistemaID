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

<!-- Desarrollo en Progreso -->
<div class="card shadow">
    <div class="card-body text-center py-5">
        <i class="fas fa-cog fa-3x text-secondary mb-3"></i>
        <h4 class="text-muted">Panel de Configuración en Desarrollo</h4>
        <p class="text-muted mb-4">
            El panel de configuración está en desarrollo. Próximamente incluirá:
        </p>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6><i class="fas fa-envelope text-primary me-2"></i>Configuración de Email</h6>
                                <ul class="list-unstyled text-muted small">
                                    <li>• Servidor SMTP</li>
                                    <li>• Credenciales de email</li>
                                    <li>• Plantillas de notificación</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6><i class="fab fa-whatsapp text-success me-2"></i>Configuración WhatsApp</h6>
                                <ul class="list-unstyled text-muted small">
                                    <li>• WhatsApp Business API</li>
                                    <li>• Token de acceso</li>
                                    <li>• Mensajes personalizados</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6><i class="fas fa-bell text-warning me-2"></i>Alertas y Recordatorios</h6>
                                <ul class="list-unstyled text-muted small">
                                    <li>• Días de anticipación</li>
                                    <li>• Frecuencia de recordatorios</li>
                                    <li>• Horarios de envío</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6><i class="fas fa-credit-card text-info me-2"></i>Pasarelas de Pago</h6>
                                <ul class="list-unstyled text-muted small">
                                    <li>• Stripe</li>
                                    <li>• PayPal</li>
                                    <li>• MercadoPago</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h5>Configuración Actual</h5>
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
                                    <td><strong>Zona Horaria:</strong></td>
                                    <td><?= date_default_timezone_get() ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Versión del Sistema:</strong></td>
                                    <td><?= SITE_VERSION ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Modo Debug:</strong></td>
                                    <td><?= DEBUG_MODE ? 'Activado' : 'Desactivado' ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<?php include 'views/layout/footer.php'; ?>