<?php
$pageTitle = 'Detalle de Notificación';
include 'views/layout/header.php';
?>

<div id="alert-container"></div>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-bell text-primary me-2"></i>
        Detalle de Notificación #<?= $notificacion['id'] ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>notificaciones" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Volver a Notificaciones
        </a>
    </div>
</div>

<div class="row">
    <!-- Información Principal -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle text-info me-2"></i>
                    Información de la Notificación
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Tipo:</strong></p>
                        <?php if ($notificacion['tipo'] === 'email'): ?>
                            <span class="badge bg-primary fs-6">
                                <i class="fas fa-envelope me-1"></i>Email
                            </span>
                        <?php elseif ($notificacion['tipo'] === 'whatsapp'): ?>
                            <span class="badge bg-success fs-6">
                                <i class="fab fa-whatsapp me-1"></i>WhatsApp
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary fs-6">
                                <i class="fas fa-desktop me-1"></i>Sistema
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <p><strong>Estado:</strong></p>
                        <?php if ($notificacion['estado'] === 'enviado'): ?>
                            <span class="badge bg-success fs-6">
                                <i class="fas fa-check me-1"></i>Enviado
                            </span>
                        <?php elseif ($notificacion['estado'] === 'pendiente'): ?>
                            <span class="badge bg-warning fs-6">
                                <i class="fas fa-clock me-1"></i>Pendiente
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger fs-6">
                                <i class="fas fa-times me-1"></i>Fallido
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Cliente:</strong></p>
                        <p class="text-muted"><?= htmlspecialchars($notificacion['cliente_nombre']) ?></p>
                    </div>
                    
                    <div class="col-md-6">
                        <p><strong>Servicio:</strong></p>
                        <p class="text-muted"><?= htmlspecialchars($notificacion['servicio_nombre']) ?></p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Destinatario:</strong></p>
                        <p class="text-muted">
                            <?php if ($notificacion['tipo'] === 'email'): ?>
                                <i class="fas fa-envelope me-1"></i>
                            <?php elseif ($notificacion['tipo'] === 'whatsapp'): ?>
                                <i class="fab fa-whatsapp me-1"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($notificacion['destinatario']) ?>
                        </p>
                    </div>
                    
                    <div class="col-md-6">
                        <p><strong>Intentos:</strong></p>
                        <span class="badge <?= $notificacion['intentos'] > 2 ? 'bg-danger' : 'bg-secondary' ?> fs-6">
                            <?= $notificacion['intentos'] ?>/3
                        </span>
                    </div>
                </div>
                
                <?php if (!empty($notificacion['asunto'])): ?>
                <hr>
                <div>
                    <p><strong>Asunto:</strong></p>
                    <p class="text-muted"><?= htmlspecialchars($notificacion['asunto']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Mensaje -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-comment-alt text-secondary me-2"></i>
                    Contenido del Mensaje
                </h5>
            </div>
            <div class="card-body">
                <div class="bg-light p-3 rounded">
                    <?php if ($notificacion['tipo'] === 'email'): ?>
                        <pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;"><?= htmlspecialchars($notificacion['mensaje']) ?></pre>
                    <?php else: ?>
                        <p class="mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars($notificacion['mensaje']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Acciones -->
        <?php if ($notificacion['estado'] === 'fallido' && $notificacion['intentos'] < 3): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-tools text-warning me-2"></i>
                    Acciones Disponibles
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Esta notificación falló al ser enviada. Puede intentar reenviarla.
                </p>
                <button type="button" class="btn btn-warning" onclick="reenviarNotificacion(<?= $notificacion['id'] ?>)">
                    <i class="fas fa-redo me-1"></i>
                    Reenviar Notificación
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Panel Lateral -->
    <div class="col-md-4">
        <!-- Fechas -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-calendar text-info me-2"></i>
                    Fechas
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Fecha de Creación</small>
                    <strong><?= date('d/m/Y H:i:s', strtotime($notificacion['fecha_creacion'])) ?></strong>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted d-block">Fecha Programada</small>
                    <strong><?= date('d/m/Y H:i:s', strtotime($notificacion['fecha_programada'])) ?></strong>
                    
                    <?php 
                    $fechaProgramada = new DateTime($notificacion['fecha_programada']);
                    $ahora = new DateTime();
                    if ($fechaProgramada > $ahora): 
                        $diff = $ahora->diff($fechaProgramada);
                    ?>
                    <br><small class="text-info">
                        <i class="fas fa-clock me-1"></i>
                        <?php if ($diff->days > 0): ?>
                            En <?= $diff->days ?> días
                        <?php elseif ($diff->h > 0): ?>
                            En <?= $diff->h ?> horas
                        <?php else: ?>
                            En <?= $diff->i ?> minutos
                        <?php endif; ?>
                    </small>
                    <?php elseif ($notificacion['estado'] === 'pendiente'): ?>
                    <br><small class="text-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Fecha vencida
                    </small>
                    <?php endif; ?>
                </div>
                
                <?php if ($notificacion['fecha_enviado']): ?>
                <div class="mb-3">
                    <small class="text-muted d-block">Fecha de Envío</small>
                    <strong class="text-success">
                        <?= date('d/m/Y H:i:s', strtotime($notificacion['fecha_enviado'])) ?>
                    </strong>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Información del Servicio -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-server text-primary me-2"></i>
                    Servicio Relacionado
                </h6>
            </div>
            <div class="card-body">
                <p><strong><?= htmlspecialchars($notificacion['servicio_nombre']) ?></strong></p>
                <p class="text-muted mb-2">Cliente: <?= htmlspecialchars($notificacion['cliente_nombre']) ?></p>
                
                <a href="<?= BASE_URL ?>servicios/ver?id=<?= $notificacion['servicio_id'] ?>" 
                   class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye me-1"></i>
                    Ver Servicio
                </a>
            </div>
        </div>
        
        <!-- Estadísticas -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-bar text-secondary me-2"></i>
                    Información Técnica
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">ID de Notificación:</small><br>
                    <code>#<?= $notificacion['id'] ?></code>
                </div>
                
                <div class="mb-2">
                    <small class="text-muted">ID de Servicio:</small><br>
                    <code>#<?= $notificacion['servicio_id'] ?></code>
                </div>
                
                <?php if ($notificacion['estado'] === 'fallido'): ?>
                <div class="mt-3">
                    <small class="text-danger">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        La notificación falló después de <?= $notificacion['intentos'] ?> intento(s)
                    </small>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function reenviarNotificacion(id) {
    if (!confirm('¿Está seguro de que desea reenviar esta notificación?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('id', id);
    
    fetch('<?= BASE_URL ?>notificaciones/reenviar?ajax=1', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => window.location.reload(), 2000);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Error de conexión');
    });
}

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