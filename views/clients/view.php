<?php
$pageTitle = 'Ver Cliente - ' . $data['cliente']['nombre_razon_social'];
include 'views/layout/header.php';
$cliente = $data['cliente'];
$servicios = $data['servicios'];
?>

<div id="alert-container"></div>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user text-primary me-2"></i>
        <?= htmlspecialchars($cliente['nombre_razon_social']) ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?= BASE_URL ?>clientes/editar/<?= $cliente['id'] ?>" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i>
                Editar
            </a>
            <a href="<?= BASE_URL ?>servicios/nuevo?cliente_id=<?= $cliente['id'] ?>" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Nuevo Servicio
            </a>
        </div>
        <a href="<?= BASE_URL ?>clientes" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Volver
        </a>
    </div>
</div>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>clientes">Clientes</a></li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($cliente['nombre_razon_social']) ?></li>
    </ol>
</nav>

<div class="row">
    <!-- Información del Cliente -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Información del Cliente
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="avatar-lg bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                        <?= strtoupper(substr($cliente['nombre_razon_social'], 0, 2)) ?>
                    </div>
                    <h5 class="card-title"><?= htmlspecialchars($cliente['nombre_razon_social']) ?></h5>
                </div>
                
                <div class="info-group">
                    <?php if ($cliente['rfc']): ?>
                    <div class="info-item mb-3">
                        <label class="text-muted small">RFC:</label>
                        <div class="fw-semibold"><?= htmlspecialchars($cliente['rfc']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($cliente['contacto']): ?>
                    <div class="info-item mb-3">
                        <label class="text-muted small">Contacto:</label>
                        <div class="fw-semibold"><?= htmlspecialchars($cliente['contacto']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="info-item mb-3">
                        <label class="text-muted small">Email:</label>
                        <div class="fw-semibold">
                            <a href="mailto:<?= htmlspecialchars($cliente['email']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($cliente['email']) ?>
                            </a>
                        </div>
                    </div>
                    
                    <?php if ($cliente['telefono']): ?>
                    <div class="info-item mb-3">
                        <label class="text-muted small">Teléfono:</label>
                        <div class="fw-semibold">
                            <a href="tel:<?= htmlspecialchars($cliente['telefono']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($cliente['telefono']) ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($cliente['direccion']): ?>
                    <div class="info-item mb-3">
                        <label class="text-muted small">Dirección:</label>
                        <div class="fw-semibold"><?= nl2br(htmlspecialchars($cliente['direccion'])) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="info-item mb-3">
                        <label class="text-muted small">Fecha de Registro:</label>
                        <div class="fw-semibold"><?= date('d/m/Y', strtotime($cliente['fecha_registro'])) ?></div>
                    </div>
                </div>
                
                <!-- Acciones Rápidas -->
                <div class="d-grid gap-2 mt-4">
                    <a href="mailto:<?= htmlspecialchars($cliente['email']) ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-envelope me-1"></i>
                        Enviar Email
                    </a>
                    <?php if ($cliente['telefono']): ?>
                    <a href="tel:<?= htmlspecialchars($cliente['telefono']) ?>" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-phone me-1"></i>
                        Llamar
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Servicios del Cliente -->
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-server me-2"></i>
                    Servicios Contratados
                    <span class="badge bg-primary ms-2"><?= count($servicios) ?></span>
                </h5>
                <a href="<?= BASE_URL ?>servicios/nuevo?cliente_id=<?= $cliente['id'] ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>
                    Agregar Servicio
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($servicios)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Servicio</th>
                                <th>Tipo</th>
                                <th>Monto</th>
                                <th>Período</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($servicios as $servicio): 
                                $diasRestantes = floor((strtotime($servicio['fecha_vencimiento']) - time()) / (60 * 60 * 24));
                                $estadoClase = '';
                                switch ($servicio['estado']) {
                                    case 'activo':
                                        $estadoClase = $diasRestantes <= 7 ? 'bg-warning' : 'bg-success';
                                        break;
                                    case 'vencido':
                                        $estadoClase = 'bg-danger';
                                        break;
                                    case 'cancelado':
                                        $estadoClase = 'bg-secondary';
                                        break;
                                    case 'suspendido':
                                        $estadoClase = 'bg-warning';
                                        break;
                                }
                            ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($servicio['nombre']) ?></strong>
                                        <?php if ($servicio['descripcion']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($servicio['descripcion']) ?></small>
                                        <?php endif; ?>
                                    </div>
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
                                    <?= date('d/m/Y', strtotime($servicio['fecha_vencimiento'])) ?>
                                    <?php if ($servicio['estado'] === 'activo'): ?>
                                    <br><small class="text-<?= $diasRestantes <= 7 ? 'danger' : ($diasRestantes <= 30 ? 'warning' : 'muted') ?>">
                                        <?= $diasRestantes > 0 ? "$diasRestantes días" : abs($diasRestantes) . " días vencido" ?>
                                    </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $estadoClase ?>">
                                        <?= ucfirst($servicio['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>servicios/ver/<?= $servicio['id'] ?>" 
                                           class="btn btn-outline-primary btn-sm" 
                                           title="Ver servicio">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>servicios/editar/<?= $servicio['id'] ?>" 
                                           class="btn btn-outline-secondary btn-sm" 
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
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Resumen de Servicios -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Resumen de Servicios</h6>
                                <div class="row text-center">
                                    <div class="col-3">
                                        <div class="h4 text-success"><?= array_count_values(array_column($servicios, 'estado'))['activo'] ?? 0 ?></div>
                                        <small class="text-muted">Activos</small>
                                    </div>
                                    <div class="col-3">
                                        <div class="h4 text-warning"><?= count(array_filter($servicios, function($s) { return $s['estado'] === 'activo' && (strtotime($s['fecha_vencimiento']) - time()) / (60 * 60 * 24) <= 30; })) ?></div>
                                        <small class="text-muted">Por Vencer</small>
                                    </div>
                                    <div class="col-3">
                                        <div class="h4 text-danger"><?= array_count_values(array_column($servicios, 'estado'))['vencido'] ?? 0 ?></div>
                                        <small class="text-muted">Vencidos</small>
                                    </div>
                                    <div class="col-3">
                                        <div class="h4 text-primary">$<?= number_format(array_sum(array_column($servicios, 'monto')), 2) ?></div>
                                        <small class="text-muted">Total Mensual</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-server fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Sin Servicios</h4>
                    <p class="text-muted mb-4">Este cliente aún no tiene servicios contratados</p>
                    <a href="<?= BASE_URL ?>servicios/nuevo?cliente_id=<?= $cliente['id'] ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Agregar Primer Servicio
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-lg {
    width: 80px;
    height: 80px;
    font-size: 32px;
    font-weight: 600;
}

.info-item {
    border-bottom: 1px solid #f8f9fa;
    padding-bottom: 8px;
}

.info-item:last-child {
    border-bottom: none;
}
</style>

<?php include 'views/layout/footer.php'; ?>