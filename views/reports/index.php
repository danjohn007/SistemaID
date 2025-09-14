<?php
$pageTitle = 'Reportes';
include 'views/layout/header.php';
?>

<div id="alert-container"></div>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-chart-bar text-primary me-2"></i>
        Centro de Reportes
    </h1>
</div>

<!-- Desarrollo en Progreso -->
<div class="card shadow">
    <div class="card-body text-center py-5">
        <i class="fas fa-chart-line fa-3x text-info mb-3"></i>
        <h4 class="text-muted">Sistema de Reportes en Desarrollo</h4>
        <p class="text-muted mb-4">
            El sistema de reportes está en desarrollo. Próximamente incluirá:
        </p>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-file-excel fa-2x text-success mb-2"></i>
                                <h6>Exportación Excel</h6>
                                <p class="text-muted small mb-0">Reportes detallados en formato Excel</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-file-pdf fa-2x text-danger mb-2"></i>
                                <h6>Exportación PDF</h6>
                                <p class="text-muted small mb-0">Reportes profesionales en PDF</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-file-csv fa-2x text-warning mb-2"></i>
                                <h6>Exportación CSV</h6>
                                <p class="text-muted small mb-0">Datos estructurados en CSV</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6 mb-3">
                        <div class="list-group">
                            <div class="list-group-item">
                                <i class="fas fa-dollar-sign text-success me-2"></i>
                                <strong>Reportes de Ingresos</strong>
                                <p class="mb-0 text-muted">Análisis de ingresos por período</p>
                            </div>
                            <div class="list-group-item">
                                <i class="fas fa-calendar-check text-primary me-2"></i>
                                <strong>Reportes de Vencimientos</strong>
                                <p class="mb-0 text-muted">Servicios próximos a vencer</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="list-group">
                            <div class="list-group-item">
                                <i class="fas fa-users text-info me-2"></i>
                                <strong>Reportes de Clientes</strong>
                                <p class="mb-0 text-muted">Análisis detallado por cliente</p>
                            </div>
                            <div class="list-group-item">
                                <i class="fas fa-server text-secondary me-2"></i>
                                <strong>Reportes de Servicios</strong>
                                <p class="mb-0 text-muted">Estadísticas por tipo de servicio</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>