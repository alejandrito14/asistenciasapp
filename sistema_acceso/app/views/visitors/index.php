<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Visitantes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        /* Estilos necesarios para el layout */
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
        <h2 class="mb-4 fw-bold text-secondary">📋 Historial de Visitas</h2>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body bg-white rounded">
                <form action="" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="c" value="VisitorAdmin">
                    <input type="hidden" name="a" value="index">

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Buscar</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Nombre, DNI o Empresa..." 
                                   value="<?php echo isset($search) ? $search : ''; ?>">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Desde</label>
                        <input type="date" name="start" class="form-control" 
                               value="<?php echo isset($start) ? $start : date('Y-m-01'); ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Hasta</label>
                        <input type="date" name="end" class="form-control" 
                               value="<?php echo isset($end) ? $end : date('Y-m-d'); ?>">
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold">
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Visitante</th>
                                <th>Empresa</th>
                                <th>Motivo</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(isset($visitors) && count($visitors) > 0): ?>
                                <?php foreach($visitors as $vis): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold"><?php echo $vis['full_name']; ?></div>
                                        <small class="text-muted">DNI: <?php echo $vis['dni']; ?></small>
                                    </td>
                                    <td><?php echo $vis['company']; ?></td>
                                    <td><?php echo $vis['reason']; ?></td>
                                    <td class="text-success fw-bold">
                                        <?php echo date('d/m H:i:s', strtotime($vis['check_in'])); ?>
                                    </td>
                                    <td class="text-danger fw-bold">
                                        <?php if($vis['check_out']): ?>
                                            <?php echo date('d/m H:i:s', strtotime($vis['check_out'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">--:--:--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($vis['check_out']): ?>
                                            <span class="badge bg-secondary">Finalizada</span>
                                        <?php else: ?>
                                            <span class="badge bg-success animate-pulse">En edificio</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center p-5 text-muted">No se encontraron visitas con esos criterios.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>