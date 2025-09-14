<?php
$pageTitle = 'Dashboard';
include 'views/layout/header.php';
?>

<div id="alert-container"></div>

<!-- Header del Dashboard -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-tachometer-alt text-primary me-2"></i>
        Dashboard
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-calendar-day me-1"></i>
                <?= date('d/m/Y') ?>
            </button>
        </div>
    </div>
</div>

<!-- Tarjetas de Estadísticas -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Clientes</div>
                        <div class="h5 mb-0 font-weight-bold"><?= number_format($data['total_clientes'] ?? 0) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-primary bg-opacity-75">
                <a href="<?= BASE_URL ?>clientes" class="text-white text-decoration-none">
                    <i class="fas fa-arrow-right me-1"></i> Ver todos
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Servicios Activos</div>
                        <div class="h5 mb-0 font-weight-bold"><?= number_format($data['estadisticas_servicios']['servicios_activos'] ?? 0) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-server fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-success bg-opacity-75">
                <a href="<?= BASE_URL ?>servicios" class="text-white text-decoration-none">
                    <i class="fas fa-arrow-right me-1"></i> Gestionar
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Por Vencer (30 días)</div>
                        <div class="h5 mb-0 font-weight-bold"><?= count($data['servicios_por_vencer'] ?? []) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-warning bg-opacity-75">
                <a href="#serviciosPorVencer" class="text-white text-decoration-none">
                    <i class="fas fa-arrow-down me-1"></i> Ver detalles
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Ingresos Proyectados</div>
                        <div class="h5 mb-0 font-weight-bold">$<?= number_format($data['estadisticas_servicios']['ingresos_proyectados'] ?? 0, 2) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-info bg-opacity-75">
                <a href="<?= BASE_URL ?>reportes" class="text-white text-decoration-none">
                    <i class="fas fa-chart-line me-1"></i> Reportes
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Gráficas y Servicios por Vencer -->
<div class="row">
    <!-- Gráfica de Ventas -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-line me-2"></i>
                    Ingresos por Mes
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="ventasChart" width="100" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Servicios por Tipo -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-pie me-2"></i>
                    Servicios por Tipo
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="tiposChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alertas y Servicios por Vencer -->
<?php if (!empty($data['servicios_vencidos'])): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-danger border-left-danger">
            <h5 class="alert-heading">
                <i class="fas fa-exclamation-circle me-2"></i>
                Servicios Vencidos
            </h5>
            <p>Hay <strong><?= count($data['servicios_vencidos']) ?></strong> servicios que ya han vencido y requieren atención inmediata.</p>
            <hr>
            <a href="#serviciosVencidos" class="btn btn-danger btn-sm">
                <i class="fas fa-eye me-1"></i> Ver Servicios Vencidos
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Servicios por Vencer -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4" id="serviciosPorVencer">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-clock me-2"></i>
                    Servicios por Vencer (Próximos 30 días)
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($data['servicios_por_vencer'])): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Cliente</th>
                                <th>Servicio</th>
                                <th>Tipo</th>
                                <th>Monto</th>
                                <th>Fecha Vencimiento</th>
                                <th>Días Restantes</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['servicios_por_vencer'] as $servicio): 
                                $diasRestantes = floor((strtotime($servicio['fecha_vencimiento']) - time()) / (60 * 60 * 24));
                                $urgente = $diasRestantes <= 7;
                            ?>
                            <tr class="<?= $urgente ? 'table-warning' : '' ?>">
                                <td>
                                    <strong><?= htmlspecialchars($servicio['nombre_razon_social']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars($servicio['tipo_servicio_nombre']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <strong>$<?= number_format($servicio['monto'], 2) ?></strong>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($servicio['fecha_vencimiento'])) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $urgente ? 'danger' : 'warning' ?>">
                                        <?= $diasRestantes ?> días
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>servicios/ver/<?= $servicio['id'] ?>" 
                                           class="btn btn-outline-primary btn-sm" 
                                           title="Ver servicio">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>pagos/nuevo?servicio_id=<?= $servicio['id'] ?>" 
                                           class="btn btn-outline-success btn-sm" 
                                           title="Registrar pago">
                                            <i class="fas fa-credit-card"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5 class="text-muted">¡Excelente!</h5>
                    <p class="text-muted">No hay servicios próximos a vencer en los próximos 30 días.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Servicios Vencidos (si los hay) -->
<?php if (!empty($data['servicios_vencidos'])): ?>
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4 border-left-danger" id="serviciosVencidos">
            <div class="card-header py-3 bg-danger text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Servicios Vencidos - Atención Inmediata
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-danger">
                            <tr>
                                <th>Cliente</th>
                                <th>Servicio</th>
                                <th>Fecha Vencimiento</th>
                                <th>Días Vencido</th>
                                <th>Monto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['servicios_vencidos'] as $servicio): 
                                $diasVencido = floor((time() - strtotime($servicio['fecha_vencimiento'])) / (60 * 60 * 24));
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($servicio['nombre_razon_social']) ?></td>
                                <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                                <td><?= date('d/m/Y', strtotime($servicio['fecha_vencimiento'])) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-danger"><?= $diasVencido ?> días</span>
                                </td>
                                <td class="text-end">$<?= number_format($servicio['monto'], 2) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>servicios/ver/<?= $servicio['id'] ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>pagos/nuevo?servicio_id=<?= $servicio['id'] ?>" 
                                           class="btn btn-success btn-sm">
                                            <i class="fas fa-credit-card me-1"></i> Pagar
                                        </a>
                                    </div>
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

<script>
// Datos para las gráficas
const ventasData = <?= json_encode($data['ventas_por_mes'] ?? []) ?>;
const tiposData = <?= json_encode($data['servicios_por_tipo'] ?? []) ?>;

// Gráfica de ventas por mes
const ctxVentas = document.getElementById('ventasChart').getContext('2d');
const ventasChart = new Chart(ctxVentas, {
    type: 'line',
    data: {
        labels: ventasData.map(item => {
            const fecha = new Date(item.mes + '-01');
            return fecha.toLocaleDateString('es-ES', { month: 'short', year: '2-digit' });
        }),
        datasets: [{
            label: 'Ingresos',
            data: ventasData.map(item => item.total),
            borderColor: 'rgb(37, 99, 235)',
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Gráfica de servicios por tipo
const ctxTipos = document.getElementById('tiposChart').getContext('2d');
const tiposChart = new Chart(ctxTipos, {
    type: 'doughnut',
    data: {
        labels: tiposData.map(item => item.nombre),
        datasets: [{
            data: tiposData.map(item => item.cantidad),
            backgroundColor: [
                '#2563eb',
                '#059669',
                '#d97706',
                '#dc2626',
                '#7c3aed'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php include 'views/layout/footer.php'; ?>