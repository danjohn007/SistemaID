<?php
$pageTitle = 'Mi Perfil';
include 'views/layout/header.php';
?>

<div id="alert-container"></div>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user text-primary me-2"></i>
        Mi Perfil
    </h1>
</div>

<!-- Mensajes de éxito/error -->
<?php if (!empty($data['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>
        <?= htmlspecialchars($data['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($data['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= htmlspecialchars($data['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Información del perfil -->
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-edit me-2"></i>
                    Información Personal
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">
                                <i class="fas fa-user me-2"></i>
                                Nombre Completo
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?= htmlspecialchars($data['user']['nombre'] ?? '') ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>
                                Correo Electrónico
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($data['user']['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="fas fa-shield-alt me-2"></i>
                                Rol
                            </label>
                            <input type="text" class="form-control" 
                                   value="<?= ucfirst($data['user']['rol'] ?? '') ?>" readonly>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Fecha de Registro
                            </label>
                            <input type="text" class="form-control" 
                                   value="<?= date('d/m/Y H:i', strtotime($data['user']['fecha_creacion'] ?? '')) ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Actualizar Información
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Panel lateral -->
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Información de la Cuenta
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">ID de Usuario</small>
                    <div class="fw-bold"><?= $data['user']['id'] ?? '' ?></div>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted">Estado</small>
                    <div>
                        <?php if (($data['user']['activo'] ?? 0) == 1): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactivo</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($data['user']['ultima_conexion'])): ?>
                <div class="mb-3">
                    <small class="text-muted">Última Conexión</small>
                    <div class="fw-bold">
                        <?= date('d/m/Y H:i', strtotime($data['user']['ultima_conexion'])) ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="d-grid">
                    <a href="<?= BASE_URL ?>logout" class="btn btn-danger" 
                       onclick="return confirm('¿Está seguro que desea cerrar sesión?')">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cambio de contraseña -->
<div class="row mt-4">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">
                    <i class="fas fa-key me-2"></i>
                    Cambiar Contraseña
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" id="passwordForm">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="current_password" class="form-label">
                                <i class="fas fa-lock me-2"></i>
                                Contraseña Actual
                            </label>
                            <input type="password" class="form-control" id="current_password" 
                                   name="current_password" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_password" class="form-label">
                                <i class="fas fa-key me-2"></i>
                                Nueva Contraseña
                            </label>
                            <input type="password" class="form-control" id="new_password" 
                                   name="new_password" minlength="6" required>
                            <small class="form-text text-muted">Mínimo 6 caracteres</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-check me-2"></i>
                                Confirmar Nueva Contraseña
                            </label>
                            <input type="password" class="form-control" id="confirm_password" 
                                   name="confirm_password" minlength="6" required>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-sync me-2"></i>
                            Cambiar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Las contraseñas no coinciden.');
        return false;
    }
    
    if (newPassword.length < 6) {
        e.preventDefault();
        alert('La contraseña debe tener al menos 6 caracteres.');
        return false;
    }
});
</script>

<?php include 'views/layout/footer.php'; ?>