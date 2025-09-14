/**
 * Sistema ID - Custom JavaScript
 */

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

// Función principal de inicialización
function initializeApp() {
    initAlerts();
    initTooltips();
    initConfirmDialogs();
    initFormValidation();
    initMobileMenu();
    initNumberFormatting();
}

// Inicializar alertas automáticas
function initAlerts() {
    // Auto-close alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
}

// Inicializar tooltips de Bootstrap
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Inicializar diálogos de confirmación
function initConfirmDialogs() {
    const confirmLinks = document.querySelectorAll('[data-confirm]');
    confirmLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || '¿Está seguro?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

// Validación de formularios
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

// Menú móvil
function initMobileMenu() {
    const sidebarToggle = document.createElement('button');
    sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
    sidebarToggle.className = 'btn btn-outline-primary d-md-none';
    sidebarToggle.setAttribute('type', 'button');
    
    // Agregar botón de menú en móvil
    const navbar = document.querySelector('.navbar .container-fluid');
    if (navbar && window.innerWidth <= 768) {
        navbar.insertBefore(sidebarToggle, navbar.firstChild);
        
        sidebarToggle.addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('show');
        });
        
        // Cerrar menú al hacer click fuera
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.sidebar');
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    }
}

// Formateo de números
function initNumberFormatting() {
    const numberInputs = document.querySelectorAll('input[type="number"], .format-currency');
    numberInputs.forEach(input => {
        if (input.classList.contains('format-currency')) {
            input.addEventListener('blur', function() {
                if (this.value) {
                    const number = parseFloat(this.value);
                    if (!isNaN(number)) {
                        this.value = number.toFixed(2);
                    }
                }
            });
        }
    });
}

// Utilidades globales
window.SistemaID = {
    // Mostrar alertas
    showAlert: function(type, message, timeout = 5000) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${this.getAlertIcon(type)} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        let container = document.getElementById('alert-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'alert-container';
            container.className = 'fixed-top p-3';
            container.style.zIndex = '9999';
            container.style.pointerEvents = 'none';
            document.body.appendChild(container);
        }
        
        const alertElement = document.createElement('div');
        alertElement.innerHTML = alertHtml;
        alertElement.style.pointerEvents = 'auto';
        container.appendChild(alertElement.firstElementChild);
        
        // Auto-close después del timeout
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, timeout);
    },
    
    // Obtener icono para alertas
    getAlertIcon: function(type) {
        const icons = {
            'success': 'check-circle',
            'danger': 'exclamation-triangle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle',
            'primary': 'info-circle'
        };
        return icons[type] || 'info-circle';
    },
    
    // Formatear fecha
    formatDate: function(dateString, locale = 'es-ES') {
        const date = new Date(dateString);
        return date.toLocaleDateString(locale, {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    },
    
    // Formatear moneda
    formatCurrency: function(amount, currency = 'MXN', locale = 'es-MX') {
        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currency
        }).format(amount);
    },
    
    // Calcular días entre fechas
    daysBetween: function(date1, date2) {
        const oneDay = 24 * 60 * 60 * 1000;
        const firstDate = new Date(date1);
        const secondDate = new Date(date2);
        
        return Math.round((secondDate - firstDate) / oneDay);
    },
    
    // Validar RFC
    validateRFC: function(rfc) {
        const rfcPattern = /^[A-Z&Ñ]{3,4}[0-9]{6}[A-Z0-9]{3}$/;
        return rfcPattern.test(rfc.toUpperCase());
    },
    
    // Validar email
    validateEmail: function(email) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailPattern.test(email);
    },
    
    // Confirmar acción
    confirm: function(message = '¿Está seguro?', callback = null) {
        if (confirm(message)) {
            if (callback && typeof callback === 'function') {
                callback();
            }
            return true;
        }
        return false;
    },
    
    // Copiar al portapapeles
    copyToClipboard: function(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                this.showAlert('success', 'Copiado al portapapeles');
            });
        } else {
            // Fallback para navegadores antiguos
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            this.showAlert('success', 'Copiado al portapapeles');
        }
    },
    
    // Loading overlay
    showLoading: function(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            element.style.position = 'relative';
            const loading = document.createElement('div');
            loading.className = 'loading-overlay';
            loading.innerHTML = `
                <div class="d-flex justify-content-center align-items-center h-100">
                    <div class="loading"></div>
                </div>
            `;
            loading.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255,255,255,0.8);
                z-index: 1000;
                display: flex;
                align-items: center;
                justify-content: center;
            `;
            element.appendChild(loading);
        }
    },
    
    hideLoading: function(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            const loading = element.querySelector('.loading-overlay');
            if (loading) {
                loading.remove();
            }
        }
    },
    
    // AJAX helper
    ajax: function(url, options = {}) {
        const defaults = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        const config = Object.assign(defaults, options);
        
        return fetch(url, config)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                this.showAlert('danger', 'Error en la comunicación con el servidor');
                throw error;
            });
    }
};

// Atajos de teclado
document.addEventListener('keydown', function(e) {
    // Ctrl + / para mostrar ayuda
    if (e.ctrlKey && e.key === '/') {
        e.preventDefault();
        // Mostrar modal de ayuda (implementar si es necesario)
    }
    
    // Escape para cerrar modales
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        });
    }
});

// Manejo de errores globales
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
    if (typeof SistemaID !== 'undefined') {
        SistemaID.showAlert('danger', 'Ha ocurrido un error inesperado');
    }
});

// Service Worker registration (si existe)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                console.log('SW registered: ', registration);
            })
            .catch(function(registrationError) {
                console.log('SW registration failed: ', registrationError);
            });
    });
}