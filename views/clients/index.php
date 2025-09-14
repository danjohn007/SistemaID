<?php
$pageTitle = 'Gestión de Clientes';
include 'views/layout/header.php';
?>

<div id="alert-container"></div>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-users text-primary me-2"></i>
        Gestión de Clientes
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>clientes/nuevo" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>
            Nuevo Cliente
        </a>
    </div>
</div>

<!-- Buscador y filtros -->
<div class="row mb-4">
    <div class="col-md-6">
        <form method="GET" class="d-flex">
            <input type="text" 
                   class="form-control me-2" 
                   name="search" 
                   placeholder="Buscar clientes..."
                   value="<?= htmlspecialchars($data['search'] ?? '') ?>">
            <button type="submit" class="btn btn-outline-secondary">
                <i class="fas fa-search"></i>
            </button>
            <?php if (!empty($data['search'])): ?>
            <a href="<?= BASE_URL ?>clientes" class="btn btn-outline-danger ms-2">
                <i class="fas fa-times"></i>
            </a>
            <?php endif; ?>
        </form>
    </div>
    <div class="col-md-6 text-end">
        <span class="text-muted">
            Total: <?= number_format($data['total_clientes'] ?? 0) ?> clientes
        </span>
    </div>
</div>

<!-- Tabla de Clientes -->
<div class="card shadow">
    <div class="card-body">
        <?php if (!empty($data['clientes'])): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Cliente</th>
                        <th>RFC</th>
                        <th>Contacto</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['clientes'] as $cliente): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <?= strtoupper(substr($cliente['nombre_razon_social'], 0, 1)) ?>
                                </div>
                                <div>
                                    <strong><?= htmlspecialchars($cliente['nombre_razon_social']) ?></strong>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?= htmlspecialchars($cliente['rfc'] ?? '-') ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($cliente['contacto'] ?? '-') ?>
                        </td>
                        <td>
                            <a href="mailto:<?= htmlspecialchars($cliente['email']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($cliente['email']) ?>
                            </a>
                        </td>
                        <td>
                            <?php if ($cliente['telefono']): ?>
                            <a href="tel:<?= htmlspecialchars($cliente['telefono']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($cliente['telefono']) ?>
                            </a>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= date('d/m/Y', strtotime($cliente['fecha_registro'])) ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= BASE_URL ?>clientes/ver/<?= $cliente['id'] ?>" 
                                   class="btn btn-outline-primary" 
                                   title="Ver cliente">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>clientes/editar/<?= $cliente['id'] ?>" 
                                   class="btn btn-outline-secondary" 
                                   title="Editar cliente">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= BASE_URL ?>clientes/eliminar/<?= $cliente['id'] ?>" 
                                   class="btn btn-outline-danger" 
                                   title="Eliminar cliente"
                                   onclick="return confirmDelete('¿Está seguro de eliminar este cliente?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <?php if (($data['total_pages'] ?? 1) > 1): ?>
        <nav aria-label="Paginación de clientes" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $data['total_pages']; $i++): ?>
                <li class="page-item <?= ($i == $data['current_page']) ? 'active' : '' ?>">
                    <a class="page-link" href="<?= BASE_URL ?>clientes?page=<?= $i ?><?= !empty($data['search']) ? '&search=' . urlencode($data['search']) : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No se encontraron clientes</h4>
            <p class="text-muted mb-4">
                <?php if (!empty($data['search'])): ?>
                No hay clientes que coincidan con "<?= htmlspecialchars($data['search']) ?>"
                <?php else: ?>
                Comience agregando su primer cliente
                <?php endif; ?>
            </p>
            <a href="<?= BASE_URL ?>clientes/nuevo" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Agregar Primer Cliente
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 16px;
    font-weight: 600;
}
</style>

<?php include 'views/layout/footer.php'; ?>