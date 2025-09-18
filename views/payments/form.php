<?php
$pageTitle = 'Registrar Pago';
include 'views/layout/header.php';
$servicioSeleccionado = $data['servicio_seleccionado'] ?? null;
$servicios = $data['servicios'] ?? [];
?>

<div id="alert-container"></div>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-credit-card text-primary me-2"></i>
        Registrar Pago
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>pagos" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Volver
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-plus me-2"></i>
                    Información del Pago
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($data['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($data['error']) ?>
                </div>
                <?php endif; ?>
                
                <?php if ($servicioSeleccionado): ?>
                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle me-2"></i>
                        Servicio Seleccionado
                    </h6>
                    <p class="mb-0">
                        <strong><?= htmlspecialchars($servicioSeleccionado['nombre']) ?></strong> - 
                        <?= htmlspecialchars($servicioSeleccionado['nombre_razon_social']) ?>
                        <br><small>Monto: $<?= number_format($servicioSeleccionado['monto'], 2) ?></small>
                    </p>
                </div>
                <?php endif; ?>
                
                <form method="POST" id="pagoForm" novalidate>
                    <div class="row">
                        <!-- Servicio -->
                        <div class="col-md-12 mb-3">
                            <label for="servicio_id" class="form-label">
                                <i class="fas fa-server me-1 text-primary"></i>
                                Servicio <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="servicio_id" name="servicio_id" required <?= $servicioSeleccionado ? 'readonly' : '' ?>>
                                <option value="">Seleccionar servicio...</option>
                                <?php foreach ($servicios as $servicio): ?>
                                <option value="<?= $servicio['id'] ?>" 
                                        data-monto="<?= $servicio['monto'] ?>"
                                        data-cliente="<?= htmlspecialchars($servicio['nombre_razon_social']) ?>"
                                        <?= ($servicioSeleccionado && $servicioSeleccionado['id'] == $servicio['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($servicio['nombre']) ?> - <?= htmlspecialchars($servicio['nombre_razon_social']) ?>
                                    (Estado: <?= ucfirst($servicio['estado']) ?> - $<?= number_format($servicio['monto'], 2) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Por favor seleccione un servicio.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Monto -->
                        <div class="col-md-6 mb-3">
                            <label for="monto" class="form-label">
                                <i class="fas fa-dollar-sign me-1 text-success"></i>
                                Monto (Subtotal) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="monto" name="monto" 
                                       step="0.01" min="0" required
                                       value="<?= $servicioSeleccionado ? $servicioSeleccionado['monto'] : '' ?>"
                                       placeholder="0.00">
                                <div class="invalid-feedback">
                                    Por favor ingrese un monto válido.
                                </div>
                            </div>
                        </div>
                        
                        <!-- Requiere Factura -->
                        <div class="col-md-6 mb-3 d-flex align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="requiere_factura" name="requiere_factura">
                                <label class="form-check-label" for="requiere_factura">
                                    <i class="fas fa-receipt me-1 text-warning"></i>
                                    Requiere Factura (+ 16% IVA)
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información de Total -->
                    <div class="row" id="total-info" style="display: none;">
                        <div class="col-12 mb-3">
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="col-4">
                                        <strong>Subtotal:</strong> $<span id="subtotal-display">0.00</span>
                                    </div>
                                    <div class="col-4">
                                        <strong>IVA (16%):</strong> $<span id="iva-display">0.00</span>
                                    </div>
                                    <div class="col-4">
                                        <strong>Total:</strong> $<span id="total-display">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Fecha de Pago -->
                        <div class="col-md-6 mb-3">
                            <label for="fecha_pago" class="form-label">
                                <i class="fas fa-calendar me-1 text-info"></i>
                                Fecha de Pago
                            </label>
                            <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" 
                                   value="<?= date('Y-m-d') ?>">
                        </div>
                    
                    <div class="row">
                        <!-- Método de Pago -->
                        <div class="col-md-6 mb-3">
                            <label for="metodo_pago" class="form-label">
                                <i class="fas fa-credit-card me-1 text-primary"></i>
                                Método de Pago
                            </label>
                            <select class="form-select" id="metodo_pago" name="metodo_pago">
                                <option value="">Seleccionar método...</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia Bancaria</option>
                                <option value="tarjeta_credito">Tarjeta de Crédito</option>
                                <option value="tarjeta_debito">Tarjeta de Débito</option>
                                <option value="cheque">Cheque</option>
                                <option value="deposito">Depósito Bancario</option>
                                <option value="paypal">PayPal</option>
                                <option value="otros">Otros</option>
                            </select>
                        </div>
                        
                        <!-- Referencia -->
                        <div class="col-md-6 mb-3">
                            <label for="referencia" class="form-label">
                                <i class="fas fa-hashtag me-1 text-secondary"></i>
                                Referencia/Número de Transacción
                            </label>
                            <input type="text" class="form-control" id="referencia" name="referencia" 
                                   placeholder="Ej: REF123456, #TXN789, etc.">
                        </div>
                    </div>
                    
                    <!-- Notas -->
                    <div class="mb-3">
                        <label for="notas" class="form-label">
                            <i class="fas fa-sticky-note me-1 text-warning"></i>
                            Notas Adicionales
                        </label>
                        <textarea class="form-control" id="notas" name="notas" rows="3" 
                                  placeholder="Observaciones adicionales sobre el pago..."></textarea>
                    </div>
                    
                    <!-- Información del Servicio Seleccionado -->
                    <div id="servicio-info" class="alert alert-light d-none">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Información del Servicio
                        </h6>
                        <div id="servicio-details"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                Al registrar el pago, el estado del servicio se actualizará automáticamente.
                            </small>
                        </div>
                        <div>
                            <a href="<?= BASE_URL ?>pagos" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Registrar Pago
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const servicioSelect = document.getElementById('servicio_id');
    const montoInput = document.getElementById('monto');
    const servicioInfo = document.getElementById('servicio-info');
    const servicioDetails = document.getElementById('servicio-details');
    const requiereFacturaCheckbox = document.getElementById('requiere_factura');
    const totalInfo = document.getElementById('total-info');
    const subtotalDisplay = document.getElementById('subtotal-display');
    const ivaDisplay = document.getElementById('iva-display');
    const totalDisplay = document.getElementById('total-display');
    
    // Función para calcular y mostrar totales
    function calcularTotales() {
        const subtotal = parseFloat(montoInput.value) || 0;
        const requiereFactura = requiereFacturaCheckbox.checked;
        
        if (requiereFactura && subtotal > 0) {
            const iva = subtotal * 0.16;
            const total = subtotal + iva;
            
            subtotalDisplay.textContent = subtotal.toFixed(2);
            ivaDisplay.textContent = iva.toFixed(2);
            totalDisplay.textContent = total.toFixed(2);
            totalInfo.style.display = 'block';
        } else {
            totalInfo.style.display = 'none';
        }
    }
    
    // Event listeners para recalcular totales
    montoInput.addEventListener('input', calcularTotales);
    requiereFacturaCheckbox.addEventListener('change', calcularTotales);
    
    // Actualizar información cuando se selecciona un servicio
    servicioSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const monto = selectedOption.dataset.monto;
            const cliente = selectedOption.dataset.cliente;
            
            // Actualizar el monto sugerido
            montoInput.value = monto;
            calcularTotales(); // Recalcular después de cambiar el monto
            
            // Mostrar información del servicio
            servicioDetails.innerHTML = `
                <p class="mb-1"><strong>Cliente:</strong> ${cliente}</p>
                <p class="mb-1"><strong>Monto del Servicio:</strong> $${parseFloat(monto).toFixed(2)}</p>
                <p class="mb-0"><strong>Servicio:</strong> ${selectedOption.text.split(' - ')[0]}</p>
            `;
            servicioInfo.classList.remove('d-none');
        } else {
            // Limpiar información
            montoInput.value = '';
            servicioInfo.classList.add('d-none');
        }
    });
    
    // Validación del formulario
    const form = document.getElementById('pagoForm');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
    
    // Auto-completar monto si ya hay un servicio seleccionado
    if (servicioSelect.value) {
        servicioSelect.dispatchEvent(new Event('change'));
    }
});

// Función para confirmar eliminación (si se necesita en otras partes)
function confirmDelete(message) {
    return confirm(message || '¿Está seguro de que desea eliminar este elemento?');
}
</script>

<?php include 'views/layout/footer.php'; ?>