<?php
$pageTitle = 'Gestión de Servicios';
include 'views/layout/header.php';
?>

<div id="alert-container"></div>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-server text-primary me-2"></i>
        Gestión de Servicios
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>servicios/nuevo" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>
            Nuevo Servicio
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="row mb-4">
    <div class="col-md-6">
        <form method="GET" class="d-flex">
            <select name="cliente_id" class="form-select me-2">
                <option value="">Todos los clientes</option>
                <?php foreach ($data['clientes'] as $cliente): ?>
                <option value="<?= $cliente['id'] ?>" <?= ($data['cliente_selected'] == $cliente['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cliente['nombre_razon_social']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-outline-secondary">
                <i class="fas fa-filter"></i>
            </button>
            <?php if (!empty($data['cliente_selected'])): ?>
            <a href="<?= BASE_URL ?>servicios" class="btn btn-outline-danger ms-2">
                <i class="fas fa-times"></i>
            </a>
            <?php endif; ?>
        </form>
    </div>
    <div class="col-md-6 text-end">
        <span class="text-muted">
            Total: <?= count($data['servicios']) ?> servicios
        </span>
    </div>
</div>

<!-- Tabla de Servicios -->
<div class="card shadow">
    <div class="card-body">
        <?php if (!empty($data['servicios'])): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Servicio</th>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Período</th>
                        <th>Vencimiento</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['servicios'] as $servicio): 
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
                    <tr class="<?= $diasRestantes <= 7 && $servicio['estado'] === 'activo' ? 'table-warning' : '' ?>">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="service-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <i class="fas fa-server"></i>
                                </div>
                                <div>
                                    <strong><?= htmlspecialchars($servicio['nombre']) ?></strong>
                                    <?php if ($servicio['descripcion']): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($servicio['descripcion']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>clientes/ver/<?= $servicio['cliente_id'] ?>" class="text-decoration-none">
                                <?= htmlspecialchars($servicio['nombre_razon_social']) ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                <?= htmlspecialchars($servicio['tipo_servicio_nombre']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <strong>$<?= number_format($servicio['monto'], 2) ?></strong>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                <?= ucfirst($servicio['periodo_vencimiento']) ?>
                            </span>
                        </td>
                        <td>
                            <div>
                                <?= date('d/m/Y', strtotime($servicio['fecha_vencimiento'])) ?>
                                <?php if ($servicio['estado'] === 'activo'): ?>
                                <br><small class="text-<?= $diasRestantes <= 7 ? 'danger' : ($diasRestantes <= 30 ? 'warning' : 'muted') ?>">
                                    <?php if ($diasRestantes > 0): ?>
                                        <i class="fas fa-clock me-1"></i><?= $diasRestantes ?> días
                                    <?php else: ?>
                                        <i class="fas fa-exclamation-triangle me-1"></i><?= abs($diasRestantes) ?> días vencido
                                    <?php endif; ?>
                                </small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $estadoClase ?>">
                                <?= $estadoTexto ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= BASE_URL ?>servicios/ver/<?= $servicio['id'] ?>" 
                                   class="btn btn-outline-primary" 
                                   title="Ver servicio">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>servicios/editar/<?= $servicio['id'] ?>" 
                                   class="btn btn-outline-secondary" 
                                   title="Editar servicio">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($servicio['estado'] === 'activo' && $diasRestantes <= 0): ?>
                                <a href="<?= BASE_URL ?>pagos/nuevo?servicio_id=<?= $servicio['id'] ?>" 
                                   class="btn btn-success btn-sm" 
                                   title="Registrar pago">
                                    <i class="fas fa-credit-card"></i>
                                </a>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>servicios/eliminar/<?= $servicio['id'] ?>" 
                                   class="btn btn-outline-danger" 
                                   title="Cancelar servicio"
                                   onclick="return confirmDelete('¿Está seguro de cancelar este servicio?')">
                                    <i class="fas fa-ban"></i>
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
            <i class="fas fa-server fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No se encontraron servicios</h4>
            <p class="text-muted mb-4">
                <?php if (!empty($data['cliente_selected'])): ?>
                No hay servicios para el cliente seleccionado
                <?php else: ?>
                Comience agregando su primer servicio
                <?php endif; ?>
            </p>
            <a href="<?= BASE_URL ?>servicios/nuevo" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Agregar Primer Servicio
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Estadísticas Rápidas -->
<?php if (!empty($data['servicios'])): ?>
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="fas fa-chart-pie me-2"></i>
                    Resumen de Servicios
                </h6>
                <div class="row text-center">
                    <?php
                    $serviciosActivos = array_filter($data['servicios'], function($s) { return $s['estado'] === 'activo'; });
                    $serviciosPorVencer = array_filter($serviciosActivos, function($s) { 
                        return (strtotime($s['fecha_vencimiento']) - time()) / (60 * 60 * 24) <= 30; 
                    });
                    $serviciosVencidos = array_filter($serviciosActivos, function($s) { 
                        return strtotime($s['fecha_vencimiento']) < time(); 
                    });
                    $ingresosTotales = array_sum(array_column($data['servicios'], 'monto'));
                    ?>
                    <div class="col-3">
                        <div class="h4 text-success"><?= count($serviciosActivos) ?></div>
                        <small class="text-muted">Servicios Activos</small>
                    </div>
                    <div class="col-3">
                        <div class="h4 text-warning"><?= count($serviciosPorVencer) ?></div>
                        <small class="text-muted">Por Vencer (30 días)</small>
                    </div>
                    <div class="col-3">
                        <div class="h4 text-danger"><?= count($serviciosVencidos) ?></div>
                        <small class="text-muted">Vencidos</small>
                    </div>
                    <div class="col-3">
                        <div class="h4 text-primary">$<?= number_format($ingresosTotales, 2) ?></div>
                        <small class="text-muted">Ingresos Totales</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.service-icon {
    width: 40px;
    height: 40px;
    font-size: 16px;
}
</style>

<?php include 'views/layout/footer.php'; ?>