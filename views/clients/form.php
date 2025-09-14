<?php
$pageTitle = isset($cliente) ? 'Editar Cliente' : 'Nuevo Cliente';
include 'views/layout/header.php';
$isEdit = isset($cliente);
?>

<div id="alert-container"></div>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-<?= $isEdit ? 'edit' : 'plus' ?> text-primary me-2"></i>
        <?= $pageTitle ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
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
        <li class="breadcrumb-item active"><?= $isEdit ? 'Editar' : 'Nuevo' ?></li>
    </ol>
</nav>

<?php if (!empty($error)): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<!-- Formulario -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-circle me-2"></i>
                    Información del Cliente
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" id="clienteForm" novalidate>
                    <div class="row">
                        <!-- Nombre/Razón Social -->
                        <div class="col-md-12 mb-3">
                            <label for="nombre_razon_social" class="form-label">
                                <i class="fas fa-building me-1 text-primary"></i>
                                Nombre / Razón Social <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nombre_razon_social" 
                                   name="nombre_razon_social" 
                                   value="<?= htmlspecialchars($cliente['nombre_razon_social'] ?? '') ?>"
                                   placeholder="Ej: Empresa ABC S.A. de C.V."
                                   required>
                            <div class="invalid-feedback">
                                Por favor, ingrese el nombre o razón social.
                            </div>
                        </div>
                        
                        <!-- RFC -->
                        <div class="col-md-6 mb-3">
                            <label for="rfc" class="form-label">
                                <i class="fas fa-id-card me-1 text-primary"></i>
                                RFC
                            </label>
                            <input type="text" 
                                   class="form-control text-uppercase" 
                                   id="rfc" 
                                   name="rfc" 
                                   value="<?= htmlspecialchars($cliente['rfc'] ?? '') ?>"
                                   placeholder="AAAA123456789"
                                   maxlength="13">
                            <div class="form-text">
                                Opcional - RFC de 12 o 13 caracteres
                            </div>
                        </div>
                        
                        <!-- Contacto -->
                        <div class="col-md-6 mb-3">
                            <label for="contacto" class="form-label">
                                <i class="fas fa-user me-1 text-primary"></i>
                                Persona de Contacto
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="contacto" 
                                   name="contacto" 
                                   value="<?= htmlspecialchars($cliente['contacto'] ?? '') ?>"
                                   placeholder="Ej: Juan Pérez">
                        </div>
                        
                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1 text-primary"></i>
                                Correo Electrónico <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($cliente['email'] ?? '') ?>"
                                   placeholder="contacto@empresa.com"
                                   required>
                            <div class="invalid-feedback">
                                Por favor, ingrese un correo electrónico válido.
                            </div>
                        </div>
                        
                        <!-- Teléfono -->
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">
                                <i class="fas fa-phone me-1 text-primary"></i>
                                Teléfono
                            </label>
                            <input type="tel" 
                                   class="form-control" 
                                   id="telefono" 
                                   name="telefono" 
                                   value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>"
                                   placeholder="555-123-4567">
                        </div>
                        
                        <!-- Dirección -->
                        <div class="col-md-12 mb-3">
                            <label for="direccion" class="form-label">
                                <i class="fas fa-map-marker-alt me-1 text-primary"></i>
                                Dirección
                            </label>
                            <textarea class="form-control" 
                                      id="direccion" 
                                      name="direccion" 
                                      rows="3"
                                      placeholder="Calle, número, colonia, ciudad, estado, CP"><?= htmlspecialchars($cliente['direccion'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Botones -->
                    <div class="row">
                        <div class="col-12">
                            <hr>
                            <div class="d-flex justify-content-between">
                                <a href="<?= BASE_URL ?>clientes" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    <?= $isEdit ? 'Actualizar Cliente' : 'Crear Cliente' ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('clienteForm');
    const rfcInput = document.getElementById('rfc');
    
    // Validación del RFC
    rfcInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
        
        // Validar formato RFC (opcional)
        if (this.value.length > 0) {
            const rfcPattern = /^[A-Z&Ñ]{3,4}[0-9]{6}[A-Z0-9]{3}$/;
            if (this.value.length === 12 || this.value.length === 13) {
                if (!rfcPattern.test(this.value)) {
                    this.setCustomValidity('Formato de RFC inválido');
                } else {
                    this.setCustomValidity('');
                }
            } else {
                this.setCustomValidity('');
            }
        }
    });
    
    // Validación del formulario
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        form.classList.add('was-validated');
    });
    
    // Limpiar validación al escribir
    const inputs = form.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });
});
</script>

<?php include 'views/layout/footer.php'; ?>