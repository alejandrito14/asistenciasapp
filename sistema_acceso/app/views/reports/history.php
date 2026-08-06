<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Empleados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        /* Estilos necesarios para que el Sidebar Maestro se vea bien */
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
        <h2 class="mb-4 fw-bold text-secondary"><i class="bi bi-search"></i> Historial de Asistencia</h2>

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body bg-white rounded">
                <form action="" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="c" value="Report">
                    <input type="hidden" name="a" value="history">

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Filtrar por Empleado</label>
                        <select name="employee_id" class="form-select">
                            <option value="">-- Todos los Empleados --</option>
                            <?php foreach($employees as $emp): ?>
                                <option value="<?php echo $emp['id']; ?>" <?php echo ($employee_id == $emp['id']) ? 'selected' : ''; ?>>
                                    <?php echo $emp['first_name'] . ' ' . $emp['last_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Desde</label>
                        <input type="date" name="start" class="form-control" value="<?php echo $start_date; ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Hasta</label>
                        <input type="date" name="end" class="form-control" value="<?php echo $end_date; ?>">
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold">
                            <i class="bi bi-filter"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mb-2 text-end">
            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger me-2">Llegada Tarde (> <?php echo date('h:i A', strtotime($horaEntradaOficial)); ?>)</span>
            <span class="badge bg-success bg-opacity-10 text-success border border-success">Puntual</span>
        </div>

        <div class="card shadow border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Empleado</th>
                                <th>Departamento</th>
                                <th>Fecha</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Tiempo Trabajado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($logs) > 0): ?>
                                <?php foreach($logs as $log): ?>
                                
                                <?php 
                                    $esTarde = false;
                                    // Usamos la hora oficial pasada desde el controlador
                                    if(strtotime($log['check_in_time']) > strtotime($horaEntradaOficial)) {
                                        $esTarde = true;
                                    }
                                ?>

                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold"><?php echo $log['first_name'] . ' ' . $log['last_name']; ?></div>
                                        <small class="text-muted"><?php echo $log['employee_code']; ?></small>
                                    </td>
                                    <td><?php echo $log['department']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($log['date_log'])); ?></td>
                                    
                                    <td>
                                        <?php if($esTarde): ?>
                                            <div class="text-danger fw-bold">
                                                <?php echo date('H:i:s', strtotime($log['check_in_time'])); ?>
                                                <i class="bi bi-exclamation-circle-fill" title="Tarde"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-success fw-bold">
                                                <?php echo date('H:i:s', strtotime($log['check_in_time'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="text-secondary fw-bold">
                                        <?php if($log['check_out_time']): ?>
                                            <?php echo date('H:i:s', strtotime($log['check_out_time'])); ?>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">En Turno</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php 
                                            if($log['check_out_time']) {
                                                $in = new DateTime($log['check_in_time']);
                                                $out = new DateTime($log['check_out_time']);
                                                $diff = $in->diff($out);
                                                echo '<span class="badge bg-info text-dark fs-6">' . $diff->format('%H h %I m') . '</span>';
                                            } else {
                                                echo "--";
                                            }
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center p-5 text-muted">No se encontraron registros.</td></tr>
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