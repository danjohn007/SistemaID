<?php
$pageTitle = 'Gestión de Pagos';
include 'views/layout/header.php';
?>

<div id="alert-container"></div>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-credit-card text-primary me-2"></i>
        Gestión de Pagos
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>pagos/nuevo" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>
            Registrar Pago
        </a>
    </div>
</div>

<!-- Pagos Pendientes Alert -->
<?php if (!empty($data['pagos_pendientes'])): ?>
<div class="alert alert-warning">
    <h5 class="alert-heading">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Pagos Pendientes
    </h5>
    <p>Hay <strong><?= count($data['pagos_pendientes']) ?></strong> pagos pendientes que requieren atención.</p>
</div>
<?php endif; ?>

<!-- Historial de Pagos -->
<div class="card shadow">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-history me-2"></i>
            Historial de Pagos
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($data['pagos'])): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Servicio</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th>Referencia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['pagos'] as $pago): 
                        $estadoClase = '';
                        switch ($pago['estado']) {
                            case 'pagado':
                                $estadoClase = 'bg-success';
                                break;
                            case 'pendiente':
                                $estadoClase = 'bg-warning';
                                break;
                            case 'por_vencer':
                                $estadoClase = 'bg-info';
                                break;
                            case 'vencido':
                                $estadoClase = 'bg-danger';
                                break;
                        }
                    ?>
                    <tr>
                        <td>
                            <?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>clientes/ver/<?= $pago['cliente_id'] ?? '#' ?>" class="text-decoration-none">
                                <?= htmlspecialchars($pago['nombre_razon_social']) ?>
                            </a>
                        </td>
                        <td>
                            <?= htmlspecialchars($pago['servicio_nombre']) ?>
                        </td>
                        <td class="text-end">
                            <strong>$<?= number_format($pago['monto'], 2) ?></strong>
                        </td>
                        <td>
                            <?= htmlspecialchars($pago['metodo_pago'] ?? 'N/A') ?>
                        </td>
                        <td>
                            <span class="badge <?= $estadoClase ?>">
                                <?= ucfirst(str_replace('_', ' ', $pago['estado'])) ?>
                            </span>
                        </td>
                        <td>
                            <?= htmlspecialchars($pago['referencia'] ?? '-') ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= BASE_URL ?>pagos/editar/<?= $pago['id'] ?>" 
                                   class="btn btn-outline-secondary" 
                                   title="Editar pago">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">Sin Pagos Registrados</h4>
            <p class="text-muted mb-4">No hay pagos registrados en el sistema</p>
            <a href="<?= BASE_URL ?>pagos/nuevo" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Registrar Primer Pago
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>