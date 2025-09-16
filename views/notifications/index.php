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
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-success" onclick="procesarNotificaciones()">
                <i class="fas fa-play me-1"></i>
                Procesar Pendientes
            </button>
            <button type="button" class="btn btn-sm btn-info" onclick="programarNotificaciones()">
                <i class="fas fa-calendar-plus me-1"></i>
                Programar Automáticas
            </button>
        </div>
        <a href="<?= BASE_URL ?>notificaciones/configuracion" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-cog me-1"></i>
            Configuración
        </a>
    </div>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= number_format($estadisticas['total_notificaciones']) ?></h4>
                        <p class="card-text">Total Notificaciones</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-bell fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= number_format($estadisticas['enviadas']) ?></h4>
                        <p class="card-text">Enviadas</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= number_format($estadisticas['pendientes']) ?></h4>
                        <p class="card-text">Pendientes</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= number_format($estadisticas['fallidas']) ?></h4>
                        <p class="card-text">Fallidas</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Próximas Notificaciones -->
<?php if (!empty($proximas)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-alt text-info me-2"></i>
                    Próximas Notificaciones
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Fecha Programada</th>
                                <th>Tipo</th>
                                <th>Cliente</th>
                                <th>Servicio</th>
                                <th>Destinatario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proximas as $proxima): ?>
                            <tr>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($proxima['fecha_programada'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if ($proxima['tipo'] === 'email'): ?>
                                        <span class="badge bg-primary">
                                            <i class="fas fa-envelope me-1"></i>Email
                                        </span>
                                    <?php elseif ($proxima['tipo'] === 'whatsapp'): ?>
                                        <span class="badge bg-success">
                                            <i class="fab fa-whatsapp me-1"></i>WhatsApp
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-desktop me-1"></i>Sistema
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($proxima['cliente_nombre']) ?></small>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($proxima['servicio_nombre']) ?></small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($proxima['destinatario']) ?>
                                    </small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="email" <?= $filtros['tipo'] === 'email' ? 'selected' : '' ?>>Email</option>
                    <option value="whatsapp" <?= $filtros['tipo'] === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                    <option value="sistema" <?= $filtros['tipo'] === 'sistema' ? 'selected' : '' ?>>Sistema</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="pendiente" <?= $filtros['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="enviado" <?= $filtros['estado'] === 'enviado' ? 'selected' : '' ?>>Enviado</option>
                    <option value="fallido" <?= $filtros['estado'] === 'fallido' ? 'selected' : '' ?>>Fallido</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Desde</label>
                <input type="date" name="desde" class="form-control form-control-sm" value="<?= $filtros['desde'] ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Hasta</label>
                <input type="date" name="hasta" class="form-control form-control-sm" value="<?= $filtros['hasta'] ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-filter me-1"></i>Filtrar
                    </button>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <a href="<?= BASE_URL ?>notificaciones" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times me-1"></i>Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Notificaciones -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list text-secondary me-2"></i>
            Historial de Notificaciones
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($notificaciones)): ?>
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay notificaciones</h5>
                <p class="text-muted">No se encontraron notificaciones con los filtros aplicados.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Cliente</th>
                            <th>Servicio</th>
                            <th>Estado</th>
                            <th>Intentos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notificaciones as $notificacion): ?>
                        <tr>
                            <td>
                                <div>
                                    <small class="text-muted">Creado:</small><br>
                                    <strong><?= date('d/m/Y H:i', strtotime($notificacion['fecha_creacion'])) ?></strong>
                                </div>
                                <?php if ($notificacion['fecha_enviado']): ?>
                                <div class="mt-1">
                                    <small class="text-muted">Enviado:</small><br>
                                    <small><?= date('d/m/Y H:i', strtotime($notificacion['fecha_enviado'])) ?></small>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($notificacion['tipo'] === 'email'): ?>
                                    <span class="badge bg-primary">
                                        <i class="fas fa-envelope me-1"></i>Email
                                    </span>
                                <?php elseif ($notificacion['tipo'] === 'whatsapp'): ?>
                                    <span class="badge bg-success">
                                        <i class="fab fa-whatsapp me-1"></i>WhatsApp
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-desktop me-1"></i>Sistema
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($notificacion['cliente_nombre']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($notificacion['destinatario']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($notificacion['servicio_nombre']) ?>
                            </td>
                            <td>
                                <?php if ($notificacion['estado'] === 'enviado'): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>Enviado
                                    </span>
                                <?php elseif ($notificacion['estado'] === 'pendiente'): ?>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock me-1"></i>Pendiente
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times me-1"></i>Fallido
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $notificacion['intentos'] > 2 ? 'bg-danger' : 'bg-secondary' ?>">
                                    <?= $notificacion['intentos'] ?>/3
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>notificaciones/ver?id=<?= $notificacion['id'] ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($notificacion['estado'] === 'fallido' && $notificacion['intentos'] < 3): ?>
                                    <button type="button" class="btn btn-outline-warning btn-sm" 
                                            onclick="reenviarNotificacion(<?= $notificacion['id'] ?>)">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function procesarNotificaciones() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Procesando...';
    
    fetch('<?= BASE_URL ?>notificaciones/procesar?ajax=1', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', `Procesadas: ${data.data.procesadas}, Exitosas: ${data.data.exitosas}, Fallidas: ${data.data.fallidas}`);
            setTimeout(() => window.location.reload(), 2000);
        } else {
            showAlert('danger', 'Error al procesar notificaciones');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Error de conexión');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function programarNotificaciones() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Programando...';
    
    fetch('<?= BASE_URL ?>notificaciones/programar?ajax=1', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', `Se programaron ${data.programadas} notificaciones automáticas`);
            setTimeout(() => window.location.reload(), 2000);
        } else {
            showAlert('danger', 'Error al programar notificaciones');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Error de conexión');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

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