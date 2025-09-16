<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - <?= SITE_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>assets/css/custom.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color), #1e40af);
            box-shadow: 2px 0 4px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 2px 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            transform: translateX(5px);
        }
        
        .main-content {
            background-color: #f8fafc;
            min-height: 100vh;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-bottom: 1px solid #e2e8f0;
            border-radius: 12px 12px 0 0 !important;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #1d4ed8);
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-1px);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .table {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }
        
        .status-activo { background-color: #dcfce7; color: #166534; }
        .status-vencido { background-color: #fef2f2; color: #991b1b; }
        .status-pendiente { background-color: #fef3c7; color: #92400e; }
        .status-pagado { background-color: #ecfdf5; color: #065f46; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>dashboard">
                <i class="fas fa-digital-tachograph me-2 text-primary"></i>
                <strong>Sistema ID</strong>
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i>
                        <?= $_SESSION['user_name'] ?? 'Usuario' ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>perfil"><i class="fas fa-user me-2"></i> Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>configuracion"><i class="fas fa-cog me-2"></i> Configuración</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>logout"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= (($_GET['action'] ?? 'dashboard') == 'dashboard') ? 'active' : '' ?>" href="<?= BASE_URL ?>dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= (($_GET['action'] ?? '') == 'clientes') ? 'active' : '' ?>" href="<?= BASE_URL ?>clientes">
                                <i class="fas fa-users me-2"></i>
                                Clientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= (($_GET['action'] ?? '') == 'servicios') ? 'active' : '' ?>" href="<?= BASE_URL ?>servicios">
                                <i class="fas fa-server me-2"></i>
                                Servicios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= (($_GET['action'] ?? '') == 'pagos') ? 'active' : '' ?>" href="<?= BASE_URL ?>pagos">
                                <i class="fas fa-credit-card me-2"></i>
                                Pagos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= (($_GET['action'] ?? '') == 'notificaciones') ? 'active' : '' ?>" href="<?= BASE_URL ?>notificaciones">
                                <i class="fas fa-bell me-2"></i>
                                Notificaciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= (($_GET['action'] ?? '') == 'reportes') ? 'active' : '' ?>" href="<?= BASE_URL ?>reportes">
                                <i class="fas fa-chart-bar me-2"></i>
                                Reportes
                            </a>
                        </li>
                        
                        <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                        <li class="nav-item mt-3">
                            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-light">
                                <span>Administración</span>
                            </h6>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= (($_GET['action'] ?? '') == 'configuracion') ? 'active' : '' ?>" href="<?= BASE_URL ?>configuracion">
                                <i class="fas fa-cogs me-2"></i>
                                Configuración
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="py-4">