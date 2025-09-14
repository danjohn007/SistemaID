<?php
$pageTitle = 'Ver Servicio';
include 'views/layout/header.php';
$servicio = $data['servicio'];
$pagos = $data['pagos'] ?? [];

// Calcular estado del servicio
$diasRestantes = floor((strtotime($servicio['fecha_vencimiento']) - time()) / (60 * 60 * 24));
$estadoClase = '';
$estadoTexto = '';

switch ($servicio['estado']) {
    case 'activo':
        if ($diasRestantes < 0) {
            $estadoClase = 'bg-danger';
            $estadoTexto = 'Vencido';
        } elseif ($diasRestantes <= 7) {
            $estadoClase = 'bg-warning';
            $estadoTexto = 'Por Vencer';
        } else {
            $estadoClase = 'bg-success';
            $estadoTexto = 'Activo';
        }
        break;
    case 'vencido':
        $estadoClase = 'bg-danger';
        $estadoTexto = 'Vencido';
        break;
    case 'cancelado':
        $estadoClase = 'bg-secondary';
        $estadoTexto = 'Cancelado';
        break;
    case 'suspendido':
        $estadoClase = 'bg-warning';
        $estadoTexto = 'Suspendido';
        break;
}
?>

<div id="alert-container"></div>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-eye text-primary me-2"></i>
        Detalle del Servicio
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?= BASE_URL ?>servicios/editar/<?= $servicio['id'] ?>" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i>
                Editar
            </a>
            <?php if ($servicio['estado'] === 'activo' && $diasRestantes <= 0): ?>
            <a href="<?= BASE_URL ?>pagos/nuevo?servicio_id=<?= $servicio['id'] ?>" class="btn btn-success">
                <i class="fas fa-credit-card me-1"></i>
                Registrar Pago
            </a>
            <?php endif; ?>
        </div>
        <a href="<?= BASE_URL ?>servicios" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Información del Servicio -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-server me-2"></i>
                    Información del Servicio
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Nombre del Servicio</h6>
                        <p class="h5 mb-3"><?= htmlspecialchars($servicio['nombre']) ?></p>
                        
                        <h6 class="text-muted">Cliente</h6>
                        <p class="mb-3">
                            <a href="<?= BASE_URL ?>clientes/ver/<?= $servicio['cliente_id'] ?>" class="text-decoration-none">
                                <i class="fas fa-user me-1"></i>
                                <?= htmlspecialchars($servicio['nombre_razon_social']) ?>
                            </a>
                        </p>
                        
                        <h6 class="text-muted">Tipo de Servicio</h6>
                        <p class="mb-3">
                            <span class="badge bg-secondary fs-6">
                                <?= htmlspecialchars($servicio['tipo_servicio_nombre']) ?>
                            </span>
                        </p>
                        
                        <h6 class="text-muted">Descripción</h6>
                        <p class="mb-3">
                            <?= $servicio['descripcion'] ? htmlspecialchars($servicio['descripcion']) : '<em class="text-muted">Sin descripción</em>' ?>
                        </p>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-muted">Estado</h6>
                        <p class="mb-3">
                            <span class="badge <?= $estadoClase ?> fs-6">
                                <?= $estadoTexto ?>
                            </span>
                        </p>
                        
                        <h6 class="text-muted">Monto</h6>
                        <p class="h4 text-primary mb-3">$<?= number_format($servicio['monto'], 2) ?></p>
                        
                        <h6 class="text-muted">Período de Facturación</h6>
                        <p class="mb-3">
                            <span class="badge bg-info fs-6">
                                <?= ucfirst($servicio['periodo_vencimiento']) ?>
                            </span>
                        </p>
                        
                        <h6 class="text-muted">Fecha de Inicio</h6>
                        <p class="mb-3">
                            <i class="fas fa-calendar-alt me-1"></i>
                            <?= date('d/m/Y', strtotime($servicio['fecha_inicio'])) ?>
                        </p>
                        
                        <h6 class="text-muted">Fecha de Vencimiento</h6>
                        <p class="mb-3">
                            <i class="fas fa-calendar-check me-1"></i>
                            <?= date('d/m/Y', strtotime($servicio['fecha_vencimiento'])) ?>
                            <?php if ($servicio['estado'] === 'activo'): ?>
                            <br><small class="text-<?= $diasRestantes <= 7 ? 'danger' : ($diasRestantes <= 30 ? 'warning' : 'muted') ?>">
                                <?php if ($diasRestantes > 0): ?>
                                    <i class="fas fa-clock me-1"></i><?= $diasRestantes ?> días restantes
                                <?php else: ?>
                                    <i class="fas fa-exclamation-triangle me-1"></i><?= abs($diasRestantes) ?> días vencido
                                <?php endif; ?>
                            </small>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Resumen Rápido -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Resumen de Pagos
                </h6>
            </div>
            <div class="card-body">
                <?php
                $totalPagos = count($pagos);
                $pagosPagados = array_filter($pagos, function($p) { return $p['estado'] === 'pagado'; });
                $totalPagado = array_sum(array_column($pagosPagados, 'monto'));
                $ultimoPago = !empty($pagosPagados) ? max($pagosPagados) : null;
                ?>
                
                <div class="text-center mb-3">
                    <div class="h3 text-primary"><?= $totalPagos ?></div>
                    <small class="text-muted">Total de Pagos</small>
                </div>
                
                <div class="text-center mb-3">
                    <div class="h4 text-success">$<?= number_format($totalPagado, 2) ?></div>
                    <small class="text-muted">Total Pagado</small>
                </div>
                
                <?php if ($ultimoPago): ?>
                <div class="text-center">
                    <div class="small text-muted">Último Pago</div>
                    <div class="fw-bold"><?= date('d/m/Y', strtotime($ultimoPago['fecha_pago'])) ?></div>
                    <div class="text-success">$<?= number_format($ultimoPago['monto'], 2) ?></div>
                </div>
                <?php else: ?>
                <div class="text-center text-muted">
                    <i class="fas fa-info-circle"></i>
                    <br>Sin pagos registrados
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Acciones Rápidas -->
        <div class="card shadow">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Acciones Rápidas
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if ($servicio['estado'] === 'activo'): ?>
                        <?php if ($diasRestantes <= 0): ?>
                        <a href="<?= BASE_URL ?>pagos/nuevo?servicio_id=<?= $servicio['id'] ?>" class="btn btn-success">
                            <i class="fas fa-credit-card me-1"></i>
                            Registrar Pago
                        </a>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <a href="<?= BASE_URL ?>servicios/editar/<?= $servicio['id'] ?>" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-1"></i>
                        Editar Servicio
                    </a>
                    
                    <?php if ($servicio['estado'] !== 'cancelado'): ?>
                    <a href="<?= BASE_URL ?>servicios/eliminar/<?= $servicio['id'] ?>" 
                       class="btn btn-outline-danger"
                       onclick="return confirmDelete('¿Está seguro de cancelar este servicio?')">
                        <i class="fas fa-ban me-1"></i>
                        Cancelar Servicio
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Historial de Pagos -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>
                    Historial de Pagos
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($pagos)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha de Pago</th>
                                <th>Fecha de Vencimiento</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Referencia</th>
                                <th>Estado</th>
                                <th>Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagos as $pago): 
                                $estadoPagoClase = '';
                                switch ($pago['estado']) {
                                    case 'pagado':
                                        $estadoPagoClase = 'bg-success';
                                        break;
                                    case 'pendiente':
                                        $estadoPagoClase = 'bg-warning';
                                        break;
                                    case 'por_vencer':
                                        $estadoPagoClase = 'bg-info';
                                        break;
                                    case 'vencido':
                                        $estadoPagoClase = 'bg-danger';
                                        break;
                                }
                            ?>
                            <tr>
                                <td>
                                    <i class="fas fa-calendar me-1"></i>
                                    <?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?>
                                </td>
                                <td>
                                    <i class="fas fa-calendar-check me-1"></i>
                                    <?= date('d/m/Y', strtotime($pago['fecha_vencimiento'])) ?>
                                </td>
                                <td class="text-end">
                                    <strong class="text-success">$<?= number_format($pago['monto'], 2) ?></strong>
                                </td>
                                <td>
                                    <?= htmlspecialchars($pago['metodo_pago'] ?? 'N/A') ?>
                                </td>
                                <td>
                                    <code><?= htmlspecialchars($pago['referencia'] ?? '-') ?></code>
                                </td>
                                <td>
                                    <span class="badge <?= $estadoPagoClase ?>">
                                        <?= ucfirst(str_replace('_', ' ', $pago['estado'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($pago['notas'] ?? '-') ?>
                                    </small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Sin Historial de Pagos</h5>
                    <p class="text-muted mb-4">Este servicio aún no tiene pagos registrados</p>
                    <?php if ($servicio['estado'] === 'activo'): ?>
                    <a href="<?= BASE_URL ?>pagos/nuevo?servicio_id=<?= $servicio['id'] ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Registrar Primer Pago
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>