<?php
$pageTitle = isset($data['servicio']) ? 'Editar Servicio' : 'Nuevo Servicio';
include 'views/layout/header.php';
$isEdit = isset($data['servicio']);
$servicio = $data['servicio'] ?? null;
?>

<div id="alert-container"></div>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-<?= $isEdit ? 'edit' : 'plus' ?> text-primary me-2"></i>
        <?= $pageTitle ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>servicios" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Volver
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-server me-2"></i>
                    Información del Servicio
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($data['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($data['error']) ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" id="servicioForm" novalidate>
                    <div class="row">
                        <!-- Cliente -->
                        <div class="col-md-6 mb-3">
                            <label for="cliente_id" class="form-label">
                                <i class="fas fa-user me-1 text-primary"></i>
                                Cliente <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="cliente_id" name="cliente_id" required>
                                <option value="">Seleccionar cliente...</option>
                                <?php foreach ($data['clientes'] as $cliente): ?>
                                <option value="<?= $cliente['id'] ?>" 
                                        <?= ($servicio && $servicio['cliente_id'] == $cliente['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cliente['nombre_razon_social']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Tipo de Servicio -->
                        <div class="col-md-6 mb-3">
                            <label for="tipo_servicio_id" class="form-label">
                                <i class="fas fa-tags me-1 text-primary"></i>
                                Tipo de Servicio <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="tipo_servicio_id" name="tipo_servicio_id" required>
                                <option value="">Seleccionar tipo...</option>
                                <?php foreach ($data['tipos_servicio'] as $tipo): ?>
                                <option value="<?= $tipo['id'] ?>" 
                                        <?= ($servicio && $servicio['tipo_servicio_id'] == $tipo['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tipo['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Nombre del Servicio -->
                        <div class="col-md-12 mb-3">
                            <label for="nombre" class="form-label">
                                <i class="fas fa-server me-1 text-primary"></i>
                                Nombre del Servicio <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nombre" 
                                   name="nombre" 
                                   value="<?= htmlspecialchars($servicio['nombre'] ?? '') ?>"
                                   placeholder="Ej: empresaabc.com, Hosting Premium, SSL Wildcard"
                                   required>
                        </div>
                        
                        <!-- Descripción -->
                        <div class="col-md-12 mb-3">
                            <label for="descripcion" class="form-label">
                                <i class="fas fa-align-left me-1 text-primary"></i>
                                Descripción
                            </label>
                            <textarea class="form-control" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="3"
                                      placeholder="Descripción detallada del servicio..."><?= htmlspecialchars($servicio['descripcion'] ?? '') ?></textarea>
                        </div>
                        
                        <!-- Monto y Período -->
                        <div class="col-md-6 mb-3">
                            <label for="monto" class="form-label">
                                <i class="fas fa-dollar-sign me-1 text-primary"></i>
                                Monto <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" 
                                       class="form-control format-currency" 
                                       id="monto" 
                                       name="monto" 
                                       value="<?= $servicio['monto'] ?? '' ?>"
                                       step="0.01" 
                                       min="0"
                                       placeholder="0.00"
                                       required>
                                <span class="input-group-text">MXN</span>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="periodo_vencimiento" class="form-label">
                                <i class="fas fa-calendar-alt me-1 text-primary"></i>
                                Período de Vencimiento <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="periodo_vencimiento" name="periodo_vencimiento" required>
                                <option value="mensual" <?= ($servicio && $servicio['periodo_vencimiento'] == 'mensual') ? 'selected' : '' ?>>
                                    Mensual (cada mes)
                                </option>
                                <option value="trimestral" <?= ($servicio && $servicio['periodo_vencimiento'] == 'trimestral') ? 'selected' : '' ?>>
                                    Trimestral (cada 3 meses)
                                </option>
                                <option value="semestral" <?= ($servicio && $servicio['periodo_vencimiento'] == 'semestral') ? 'selected' : '' ?>>
                                    Semestral (cada 6 meses)
                                </option>
                                <option value="anual" <?= ($servicio && $servicio['periodo_vencimiento'] == 'anual') ? 'selected' : 'selected' ?>>
                                    Anual (cada año) - Por defecto
                                </option>
                            </select>
                        </div>
                        
                        <!-- Fecha de Inicio -->
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label">
                                <i class="fas fa-calendar-plus me-1 text-primary"></i>
                                Fecha de Inicio <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_inicio" 
                                   name="fecha_inicio" 
                                   value="<?= $servicio['fecha_inicio'] ?? date('Y-m-d') ?>"
                                   required>
                        </div>
                        
                        <?php if ($isEdit): ?>
                        <!-- Estado (solo en edición) -->
                        <div class="col-md-6 mb-3">
                            <label for="estado" class="form-label">
                                <i class="fas fa-info-circle me-1 text-primary"></i>
                                Estado
                            </label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="activo" <?= ($servicio['estado'] == 'activo') ? 'selected' : '' ?>>
                                    Activo
                                </option>
                                <option value="suspendido" <?= ($servicio['estado'] == 'suspendido') ? 'selected' : '' ?>>
                                    Suspendido
                                </option>
                                <option value="cancelado" <?= ($servicio['estado'] == 'cancelado') ? 'selected' : '' ?>>
                                    Cancelado
                                </option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Información de Vencimiento (Preview) -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Vista Previa del Vencimiento
                                    </h6>
                                    <div id="vencimiento-preview">
                                        <p class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            Seleccione la fecha de inicio y período para ver el cálculo del vencimiento
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <hr>
                            <div class="d-flex justify-content-between">
                                <a href="<?= BASE_URL ?>servicios" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    <?= $isEdit ? 'Actualizar Servicio' : 'Crear Servicio' ?>
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
    const fechaInicio = document.getElementById('fecha_inicio');
    const periodo = document.getElementById('periodo_vencimiento');
    const preview = document.getElementById('vencimiento-preview');
    
    function updatePreview() {
        const fecha = fechaInicio.value;
        const per = periodo.value;
        
        if (fecha && per) {
            const fechaInicial = new Date(fecha);
            let fechaVencimiento = new Date(fechaInicial);
            
            switch (per) {
                case 'mensual':
                    fechaVencimiento.setMonth(fechaVencimiento.getMonth() + 1);
                    break;
                case 'trimestral':
                    fechaVencimiento.setMonth(fechaVencimiento.getMonth() + 3);
                    break;
                case 'semestral':
                    fechaVencimiento.setMonth(fechaVencimiento.getMonth() + 6);
                    break;
                case 'anual':
                    fechaVencimiento.setFullYear(fechaVencimiento.getFullYear() + 1);
                    break;
            }
            
            const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
            const fechaFormateada = fechaVencimiento.toLocaleDateString('es-ES', opciones);
            
            preview.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>Fecha de Inicio:</strong><br>
                        <span class="text-primary">${fechaInicial.toLocaleDateString('es-ES', opciones)}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Primera Renovación:</strong><br>
                        <span class="text-success">${fechaFormateada}</span>
                    </div>
                </div>
            `;
        }
    }
    
    fechaInicio.addEventListener('change', updatePreview);
    periodo.addEventListener('change', updatePreview);
    
    // Preview inicial
    updatePreview();
    
    // Validación del formulario
    const form = document.getElementById('servicioForm');
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>

<?php include 'views/layout/footer.php'; ?>