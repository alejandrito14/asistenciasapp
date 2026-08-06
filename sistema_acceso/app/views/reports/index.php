<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes de Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        /* Estilos del Sidebar */
        .sidebar { min-height: 100vh; background-color: #212529; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: 0.3s; }
        .sidebar a:hover { background-color: #343a40; color: white; }
        .sidebar a.active { background-color: #0d6efd; color: white; border-left-color: white; }
        .sidebar i { width: 25px; }
    </style>
</head>
<body class="bg-light">

<div class="d-flex">
    
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        <h2 class="mb-4 fw-bold text-secondary">📊 Generar Reportes</h2>

        <div class="row">
            <div class="col-md-7">
                <div class="card shadow border-0">
                    <div class="card-header bg-success text-white py-3">
                        <h5 class="m-0"><i class="bi bi-file-earmark-excel"></i> Exportar a Excel (.csv)</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted mb-4">Selecciona el rango de fechas para descargar el historial de asistencia completo.</p>
                        
                        <form action="?c=Report&a=export" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Fecha Inicio</label>
                                <input type="date" name="start_date" class="form-control form-control-lg" required value="<?php echo date('Y-m-01'); ?>">
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Fecha Fin</label>
                                <input type="date" name="end_date" class="form-control form-control-lg" required value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg shadow"><i class="bi bi-download me-2"></i> Descargar Reporte</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="alert alert-info shadow-sm border-0">
                    <h5 class="alert-heading"><i class="bi bi-info-circle-fill"></i> Información</h5>
                    <p>El archivo descargado es en formato <strong>CSV (Valores Separados por Comas)</strong>.</p>
                    <hr>
                    <p class="mb-0 small">Puedes abrirlo directamente con:</p>
                    <ul class="small mt-2">
                        <li>Microsoft Excel</li>
                        <li>Google Sheets</li>
                        <li>LibreOffice Calc</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>