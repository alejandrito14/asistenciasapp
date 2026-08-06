<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        /* Estilos del Sidebar para que coincidan con el layout */
        .sidebar { min-height: 100vh; background-color: #212529; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: 0.3s; }
        .sidebar a:hover { background-color: #343a40; color: white; }
        .sidebar a.active { background-color: #0d6efd; color: white; border-left-color: white; }
        .sidebar i { width: 25px; }
        .stat-card {
            border: 0;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            border-radius: 20px;
            overflow: hidden;
        }
        .stat-icon {
            width: 76px;
            min-height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>

<div class="d-flex">
    
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 bg-light" style="height: 100vh; overflow-y: auto;">
        <nav class="navbar navbar-light bg-white shadow-sm px-4 py-3">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1 fw-bold text-primary">Bienvenido, Administrador</span>
                <div class="d-flex align-items-center">
                    <i class="bi bi-person-circle fs-4 text-secondary"></i>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">
            
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card h-100">
                        <div class="card-body p-0 d-flex align-items-stretch">
                            <div class="stat-icon bg-primary text-white">
                                <i class="bi bi-diagram-3 fs-1"></i>
                            </div>
                            <div class="p-4 flex-grow-1">
                                <h6 class="text-muted text-uppercase fw-bold mb-1">Grupos</h6>
                                <h2 class="mb-0 fw-bold text-dark"><?php echo (int)($totalGroups ?? 0); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card h-100">
                        <div class="card-body p-0 d-flex align-items-stretch">
                            <div class="stat-icon bg-success text-white">
                                <i class="bi bi-journal-bookmark-fill fs-1"></i>
                            </div>
                            <div class="p-4 flex-grow-1">
                                <h6 class="text-muted text-uppercase fw-bold mb-1">Materias</h6>
                                <h2 class="mb-0 fw-bold text-dark"><?php echo (int)($totalSubjects ?? 0); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card h-100">
                        <div class="card-body p-0 d-flex align-items-stretch">
                            <div class="stat-icon bg-warning text-white">
                                <i class="bi bi-people-fill fs-1"></i>
                            </div>
                            <div class="p-4 flex-grow-1">
                                <h6 class="text-muted text-uppercase fw-bold mb-1">Alumnos</h6>
                                <h2 class="mb-0 fw-bold text-dark"><?php echo (int)($totalStudents ?? 0); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card h-100">
                        <div class="card-body p-0 d-flex align-items-stretch">
                            <div class="stat-icon bg-danger text-white">
                                <i class="bi bi-file-earmark-text fs-1"></i>
                            </div>
                            <div class="p-4 flex-grow-1">
                                <h6 class="text-muted text-uppercase fw-bold mb-1">Justificantes</h6>
                                <h2 class="mb-0 fw-bold text-dark"><?php echo isset($recentJustifications) ? count($recentJustifications) : 0; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card shadow border-0">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold text-secondary"><i class="bi bi-file-earmark-text"></i> Justificantes recientes</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Alumno</th>
                                            <th>Grupo</th>
                                            <th>Materia</th>
                                            <th>Motivo</th>
                                            <th>Estado</th>
                                            <th class="text-end pe-3">Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recentJustifications)): ?>
                                            <?php foreach ($recentJustifications as $item): ?>
                                                <tr>
                                                    <td class="ps-3 fw-semibold">
                                                        <?php echo htmlspecialchars(trim(($item['alumno_nombre'] ?? '') . ' ' . ($item['apellido_paterno'] ?? '') . ' ' . ($item['apellido_materno'] ?? ''))); ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($item['grupo_nombre'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($item['materia_nombre'] ?? '-'); ?></td>
                                                    <td style="max-width: 280px;">
                                                        <div class="text-truncate" title="<?php echo htmlspecialchars($item['motivo'] ?? ''); ?>">
                                                            <?php echo htmlspecialchars($item['motivo'] ?? '-'); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php
                                                            $status = strtoupper((string)($item['estatus'] ?? 'PENDIENTE'));
                                                            $badgeClass = $status === 'APROBADO' ? 'bg-success' : ($status === 'RECHAZADO' ? 'bg-danger' : 'bg-warning text-dark');
                                                        ?>
                                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($status); ?></span>
                                                    </td>
                                                    <td class="text-end pe-3">
                                                        <?php echo !empty($item['created_at']) ? date('d/m/Y H:i', strtotime($item['created_at'])) : '-'; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-5 text-muted">No hay justificantes registrados todavía.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
