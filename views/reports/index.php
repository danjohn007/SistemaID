<?php
$pageTitle = 'Centro de Reportes';
include 'views/layout/header.php';
?>

<div id="alert-container"></div>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-chart-bar text-primary me-2"></i>
        Centro de Reportes
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportReport('csv')">
                <i class="fas fa-file-csv me-1"></i> CSV
            </button>
            <button type="button" class="btn btn-sm btn-outline-success" onclick="exportReport('excel')">
                <i class="fas fa-file-excel me-1"></i> Excel
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportReport('pdf')">
                <i class="fas fa-file-pdf me-1"></i> PDF
            </button>
        </div>
    </div>
</div>

<!-- Filtros de Reporte -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>
            Filtros de Reporte
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" id="filtrosForm">
            <input type="hidden" name="action" value="reportes">
            <div class="row">
                <div class="col-md-3">
                    <label for="tipo" class="form-label">Tipo de Reporte</label>
                    <select class="form-control" name="tipo" id="tipo">
                        <option value="general" <?= ($data['tipo_reporte'] ?? '') == 'general' ? 'selected' : '' ?>>General</option>
                        <option value="ingresos" <?= ($data['tipo_reporte'] ?? '') == 'ingresos' ? 'selected' : '' ?>>Ingresos</option>
                        <option value="vencimientos" <?= ($data['tipo_reporte'] ?? '') == 'vencimientos' ? 'selected' : '' ?>>Vencimientos</option>
                        <option value="clientes" <?= ($data['tipo_reporte'] ?? '') == 'clientes' ? 'selected' : '' ?>>Clientes</option>
                        <option value="servicios" <?= ($data['tipo_reporte'] ?? '') == 'servicios' ? 'selected' : '' ?>>Servicios</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" 
                           value="<?= htmlspecialchars($data['fecha_inicio'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" name="fecha_fin" id="fecha_fin"
                           value="<?= htmlspecialchars($data['fecha_fin'] ?? '') ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>
                        Generar Reporte
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                        <i class="fas fa-undo me-1"></i>
                        Limpiar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Contenido del Reporte -->
<div class="card shadow">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-chart-line me-2"></i>
            Reporte: <?= ucfirst($data['tipo_reporte'] ?? 'General') ?>
        </h6>
    </div>
    <div class="card-body">
        <?php $reportes = $data['reportes'] ?? []; ?>
        
        <?php if (($data['tipo_reporte'] ?? '') == 'general'): ?>
            <!-- Reporte General -->
            <?php if (!empty($reportes)): ?>
                <div class="row text-center">
                    <div class="col-md-2">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h4><?= $reportes['total_clientes'] ?? 0 ?></h4>
                                <small>Clientes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h4><?= $reportes['total_servicios'] ?? 0 ?></h4>
                                <small>Servicios</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h4><?= $reportes['servicios_activos'] ?? 0 ?></h4>
                                <small>Activos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h4><?= $reportes['servicios_vencidos'] ?? 0 ?></h4>
                                <small>Vencidos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h4><?= $reportes['total_pagos'] ?? 0 ?></h4>
                                <small>Pagos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-secondary text-white">
                            <div class="card-body">
                                <h4>$<?= number_format($reportes['total_ingresos'] ?? 0, 2) ?></h4>
                                <small>Ingresos</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center text-muted">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <p>No hay datos disponibles para el período seleccionado.</p>
                </div>
            <?php endif; ?>
            
        <?php elseif (($data['tipo_reporte'] ?? '') == 'ingresos'): ?>
            <!-- Reporte de Ingresos -->
            <?php if (!empty($reportes)): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Total Ingresos</th>
                                <th>Cantidad Pagos</th>
                                <th>Promedio por Pago</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportes as $row): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($row['fecha'])) ?></td>
                                    <td class="text-success fw-bold">$<?= number_format($row['total_ingresos'], 2) ?></td>
                                    <td><?= $row['cantidad_pagos'] ?></td>
                                    <td>$<?= number_format($row['promedio_pago'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center text-muted">
                    <i class="fas fa-chart-line fa-2x mb-3"></i>
                    <p>No hay ingresos registrados para el período seleccionado.</p>
                </div>
            <?php endif; ?>
            
        <?php elseif (($data['tipo_reporte'] ?? '') == 'vencimientos'): ?>
            <!-- Reporte de Vencimientos -->
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-warning">Próximos a Vencer (30 días)</h6>
                    <?php if (!empty($reportes['proximos_a_vencer'])): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Servicio</th>
                                        <th>Vencimiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reportes['proximos_a_vencer'] as $servicio): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($servicio['nombre_razon_social']) ?></td>
                                            <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                                            <td class="text-warning"><?= date('d/m/Y', strtotime($servicio['fecha_vencimiento'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay servicios próximos a vencer.</p>
                    <?php endif; ?>
                </div>
                
                <div class="col-md-6">
                    <h6 class="text-danger">Servicios Vencidos</h6>
                    <?php if (!empty($reportes['vencidos'])): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Servicio</th>
                                        <th>Vencimiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reportes['vencidos'] as $servicio): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($servicio['nombre_razon_social']) ?></td>
                                            <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                                            <td class="text-danger"><?= date('d/m/Y', strtotime($servicio['fecha_vencimiento'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay servicios vencidos.</p>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php elseif (($data['tipo_reporte'] ?? '') == 'clientes'): ?>
            <!-- Reporte de Clientes -->
            <?php if (!empty($reportes)): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Email</th>
                                <th>Total Servicios</th>
                                <th>Servicios Activos</th>
                                <th>Total Pagado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportes as $cliente): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cliente['nombre_razon_social']) ?></td>
                                    <td><?= htmlspecialchars($cliente['email']) ?></td>
                                    <td><?= $cliente['total_servicios'] ?></td>
                                    <td><span class="badge bg-success"><?= $cliente['servicios_activos'] ?></span></td>
                                    <td class="text-success fw-bold">$<?= number_format($cliente['total_pagado'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center text-muted">
                    <i class="fas fa-users fa-2x mb-3"></i>
                    <p>No hay datos de clientes para mostrar.</p>
                </div>
            <?php endif; ?>
            
        <?php elseif (($data['tipo_reporte'] ?? '') == 'servicios'): ?>
            <!-- Reporte de Servicios -->
            <?php if (!empty($reportes)): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tipo de Servicio</th>
                                <th>Cantidad</th>
                                <th>Activos</th>
                                <th>Vencidos</th>
                                <th>Precio Promedio</th>
                                <th>Total Ingresos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportes as $servicio): ?>
                                <tr>
                                    <td><?= htmlspecialchars($servicio['tipo_servicio']) ?></td>
                                    <td><?= $servicio['cantidad'] ?></td>
                                    <td><span class="badge bg-success"><?= $servicio['activos'] ?></span></td>
                                    <td><span class="badge bg-danger"><?= $servicio['vencidos'] ?></span></td>
                                    <td>$<?= number_format($servicio['precio_promedio'], 2) ?></td>
                                    <td class="text-success fw-bold">$<?= number_format($servicio['total_ingresos'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center text-muted">
                    <i class="fas fa-server fa-2x mb-3"></i>
                    <p>No hay datos de servicios para mostrar.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function exportReport(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('subaction', 'export');
    params.set('type', format);
    params.set('reporte', document.getElementById('tipo').value);
    
    window.open(`<?= BASE_URL ?>reportes?${params.toString()}`, '_blank');
}

function resetFilters() {
    document.getElementById('tipo').value = 'general';
    document.getElementById('fecha_inicio').value = '<?= date('Y-m-01') ?>';
    document.getElementById('fecha_fin').value = '<?= date('Y-m-t') ?>';
    document.getElementById('filtrosForm').submit();
}
</script>

<?php include 'views/layout/footer.php'; ?>