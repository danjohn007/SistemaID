<?php
/**
 * Demo page for notification system without database dependency
 */
$pageTitle = 'Demo: Sistema de Notificaciones';

// Mock data for demonstration
$estadisticas = [
    'total_notificaciones' => 156,
    'enviadas' => 134,
    'pendientes' => 15,
    'fallidas' => 7,
    'emails' => 89,
    'whatsapps' => 67
];

$proximas = [
    [
        'fecha_programada' => '2024-09-16 09:00:00',
        'tipo' => 'email',
        'cliente_nombre' => 'Empresa ABC S.A. de C.V.',
        'servicio_nombre' => 'Hosting Web Premium',
        'destinatario' => 'admin@empresaabc.com'
    ],
    [
        'fecha_programada' => '2024-09-16 10:00:00',
        'tipo' => 'whatsapp', 
        'cliente_nombre' => 'Juan Pérez Gómez',
        'servicio_nombre' => 'Dominio .com',
        'destinatario' => '+5215512345678'
    ],
    [
        'fecha_programada' => '2024-09-17 09:00:00',
        'tipo' => 'email',
        'cliente_nombre' => 'Servicios XYZ',
        'servicio_nombre' => 'Certificado SSL',
        'destinatario' => 'contacto@serviciosxyz.mx'
    ]
];

$notificaciones = [
    [
        'id' => 1,
        'fecha_creacion' => '2024-09-15 14:30:00',
        'fecha_enviado' => '2024-09-15 14:30:15',
        'tipo' => 'email',
        'cliente_nombre' => 'Tech Solutions SA',
        'servicio_nombre' => 'Sistema ERP Personalizado',
        'destinatario' => 'admin@techsolutions.com',
        'estado' => 'enviado',
        'intentos' => 1
    ],
    [
        'id' => 2,
        'fecha_creacion' => '2024-09-15 15:00:00',
        'fecha_enviado' => null,
        'tipo' => 'whatsapp',
        'cliente_nombre' => 'María González',
        'servicio_nombre' => 'Hosting WordPress',
        'destinatario' => '+5215587654321',
        'estado' => 'pendiente',
        'intentos' => 0
    ],
    [
        'id' => 3,
        'fecha_creacion' => '2024-09-15 12:15:00',
        'fecha_enviado' => null,
        'tipo' => 'email',
        'cliente_nombre' => 'Desarrollos Web MX',
        'servicio_nombre' => 'Servidor VPS',
        'destinatario' => 'info@desarrolloswebmx.com',
        'estado' => 'fallido',
        'intentos' => 3
    ]
];

$filtros = [
    'tipo' => '',
    'estado' => '',
    'desde' => '',
    'hasta' => ''
];

function BASE_URL() {
    return 'http://localhost:8000/';
}

define('BASE_URL', 'http://localhost:8000/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { padding-top: 20px; }
        .demo-header { 
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .card { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Demo Header -->
        <div class="demo-header text-center">
            <h1><i class="fas fa-bell me-2"></i>Sistema de Notificaciones - DEMO</h1>
            <p class="mb-0">Demostración completa del sistema de notificaciones implementado</p>
        </div>

        <div id="alert-container"></div>

        <!-- Header -->
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">
                <i class="fas fa-bell text-primary me-2"></i>
                Centro de Notificaciones
            </h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group me-2">
                    <button type="button" class="btn btn-sm btn-success" onclick="showDemo('procesando')">
                        <i class="fas fa-play me-1"></i>
                        Procesar Pendientes
                    </button>
                    <button type="button" class="btn btn-sm btn-info" onclick="showDemo('programando')">
                        <i class="fas fa-calendar-plus me-1"></i>
                        Programar Automáticas
                    </button>
                </div>
                <button class="btn btn-sm btn-outline-secondary" onclick="showDemo('config')">
                    <i class="fas fa-cog me-1"></i>
                    Configuración
                </button>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?= number_format($estadisticas['total_notificaciones']) ?></h4>
                                <p class="card-text">Total Notificaciones</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-bell fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?= number_format($estadisticas['enviadas']) ?></h4>
                                <p class="card-text">Enviadas</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?= number_format($estadisticas['pendientes']) ?></h4>
                                <p class="card-text">Pendientes</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?= number_format($estadisticas['fallidas']) ?></h4>
                                <p class="card-text">Fallidas</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Próximas Notificaciones -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt text-info me-2"></i>
                            Próximas Notificaciones
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Fecha Programada</th>
                                        <th>Tipo</th>
                                        <th>Cliente</th>
                                        <th>Servicio</th>
                                        <th>Destinatario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($proximas as $proxima): ?>
                                    <tr>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('d/m/Y H:i', strtotime($proxima['fecha_programada'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($proxima['tipo'] === 'email'): ?>
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-envelope me-1"></i>Email
                                                </span>
                                            <?php elseif ($proxima['tipo'] === 'whatsapp'): ?>
                                                <span class="badge bg-success">
                                                    <i class="fab fa-whatsapp me-1"></i>WhatsApp
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars($proxima['cliente_nombre']) ?></small>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars($proxima['servicio_nombre']) ?></small>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($proxima['destinatario']) ?>
                                            </small>
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

        <!-- Lista de Notificaciones -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list text-secondary me-2"></i>
                    Historial de Notificaciones
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Cliente</th>
                                <th>Servicio</th>
                                <th>Estado</th>
                                <th>Intentos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notificaciones as $notificacion): ?>
                            <tr>
                                <td>
                                    <div>
                                        <small class="text-muted">Creado:</small><br>
                                        <strong><?= date('d/m/Y H:i', strtotime($notificacion['fecha_creacion'])) ?></strong>
                                    </div>
                                    <?php if ($notificacion['fecha_enviado']): ?>
                                    <div class="mt-1">
                                        <small class="text-muted">Enviado:</small><br>
                                        <small><?= date('d/m/Y H:i', strtotime($notificacion['fecha_enviado'])) ?></small>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($notificacion['tipo'] === 'email'): ?>
                                        <span class="badge bg-primary">
                                            <i class="fas fa-envelope me-1"></i>Email
                                        </span>
                                    <?php elseif ($notificacion['tipo'] === 'whatsapp'): ?>
                                        <span class="badge bg-success">
                                            <i class="fab fa-whatsapp me-1"></i>WhatsApp
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($notificacion['cliente_nombre']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($notificacion['destinatario']) ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($notificacion['servicio_nombre']) ?>
                                </td>
                                <td>
                                    <?php if ($notificacion['estado'] === 'enviado'): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Enviado
                                        </span>
                                    <?php elseif ($notificacion['estado'] === 'pendiente'): ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>Pendiente
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times me-1"></i>Fallido
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $notificacion['intentos'] > 2 ? 'bg-danger' : 'bg-secondary' ?>">
                                        <?= $notificacion['intentos'] ?>/3
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary btn-sm" onclick="showDemo('ver')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($notificacion['estado'] === 'fallido' && $notificacion['intentos'] < 3): ?>
                                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="showDemo('reenviar')">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Features Demo -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-star text-warning me-2"></i>
                            Funcionalidades Implementadas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-envelope text-primary me-2"></i>Sistema de Email</h6>
                                <ul class="list-unstyled ms-4">
                                    <li>✅ Soporte SMTP y PHP mail()</li>
                                    <li>✅ Plantillas HTML personalizables</li>
                                    <li>✅ Configuración flexible</li>
                                    <li>✅ Reintentos automáticos</li>
                                </ul>

                                <h6><i class="fab fa-whatsapp text-success me-2"></i>Integración WhatsApp</h6>
                                <ul class="list-unstyled ms-4">
                                    <li>✅ WhatsApp Business API</li>
                                    <li>✅ Mensajes de texto enriquecidos</li>
                                    <li>✅ Webhook para confirmaciones</li>
                                    <li>✅ Validación de números</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-calendar-alt text-info me-2"></i>Recordatorios Automáticos</h6>
                                <ul class="list-unstyled ms-4">
                                    <li>✅ Intervalos: 30, 15, 7, 1 días</li>
                                    <li>✅ Programación automática</li>
                                    <li>✅ Cron jobs integrados</li>
                                    <li>✅ Notificaciones por servicio</li>
                                </ul>

                                <h6><i class="fas fa-history text-secondary me-2"></i>Historial Completo</h6>
                                <ul class="list-unstyled ms-4">
                                    <li>✅ Registro de todas las notificaciones</li>
                                    <li>✅ Estados y reintentos</li>
                                    <li>✅ Filtros avanzados</li>
                                    <li>✅ Estadísticas detalladas</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showDemo(action) {
            let message = '';
            let type = 'info';
            
            switch(action) {
                case 'procesando':
                    message = '🔄 Funcionalidad: Procesando notificaciones pendientes... (En producción enviaría emails y WhatsApps reales)';
                    type = 'success';
                    break;
                case 'programando':
                    message = '📅 Funcionalidad: Programando notificaciones automáticas para servicios próximos a vencer...';
                    type = 'info';
                    break;
                case 'config':
                    message = '⚙️ Funcionalidad: Configuración de SMTP, WhatsApp Business API, intervalos de recordatorio...';
                    type = 'warning';
                    break;
                case 'ver':
                    message = '👁️ Funcionalidad: Ver detalles completos de la notificación, contenido del mensaje, fechas...';
                    type = 'primary';
                    break;
                case 'reenviar':
                    message = '🔄 Funcionalidad: Reenviar notificación fallida (máximo 3 intentos)...';
                    type = 'warning';
                    break;
            }
            
            showAlert(type, message);
        }

        function showAlert(type, message) {
            const alertContainer = document.getElementById('alert-container');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 8000);
        }

        // Show welcome message
        setTimeout(() => {
            showAlert('info', '🎯 Esta es una demostración del sistema de notificaciones completo. Todas las funcionalidades están implementadas y listas para producción.');
        }, 1000);
    </script>
</body>
</html>