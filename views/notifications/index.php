<?php
$pageTitle = 'Notificaciones';
include 'views/layout/header.php';
?>

<div id="alert-container"></div>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-bell text-primary me-2"></i>
        Centro de Notificaciones
    </h1>
</div>

<!-- Desarrollo en Progreso -->
<div class="card shadow">
    <div class="card-body text-center py-5">
        <i class="fas fa-tools fa-3x text-warning mb-3"></i>
        <h4 class="text-muted">Funcionalidad en Desarrollo</h4>
        <p class="text-muted mb-4">
            El sistema de notificaciones está en desarrollo. Próximamente incluirá:
        </p>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="list-group">
                    <div class="list-group-item">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <strong>Notificaciones por Email</strong>
                        <p class="mb-0 text-muted">Alertas automáticas de vencimientos por correo electrónico</p>
                    </div>
                    <div class="list-group-item">
                        <i class="fab fa-whatsapp text-success me-2"></i>
                        <strong>Notificaciones por WhatsApp</strong>
                        <p class="mb-0 text-muted">Integración con WhatsApp Business API</p>
                    </div>
                    <div class="list-group-item">
                        <i class="fas fa-calendar-alt text-info me-2"></i>
                        <strong>Recordatorios Programados</strong>
                        <p class="mb-0 text-muted">Configuración de intervalos de recordatorio (30, 15, 7, 1 día)</p>
                    </div>
                    <div class="list-group-item">
                        <i class="fas fa-history text-secondary me-2"></i>
                        <strong>Historial de Notificaciones</strong>
                        <p class="mb-0 text-muted">Registro completo de notificaciones enviadas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>