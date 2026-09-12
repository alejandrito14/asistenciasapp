<?php
$search = $search ?? '';
$status = $status ?? '';
$totalStudents = is_countable($students ?? null) ? count($students) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Alumnos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .page-shell { min-height: 100vh; overflow-y: auto; }
        .panel-card { border: 0; border-radius: 18px; box-shadow: 0 12px 32px rgba(15, 23, 42, .08); }
    </style>
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>
    <main class="app-main-content flex-grow-1 page-shell">
        <nav class="navbar navbar-light bg-white shadow-sm px-4 py-3">
            <div class="container-fluid">
                <div>
                    <span class="navbar-brand mb-0 h1 fw-bold text-primary">Listado de Alumnos</span>
                    <div class="text-muted">Consulta y descarga los datos de contacto.</div>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-dark"><?php echo (int)$totalStudents; ?> alumnos</div>
                    <div class="text-muted small"><?php echo (int)($activeStudents ?? 0); ?> activos</div>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <div class="panel-card bg-white p-4 mb-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <div>
                        <h5 class="fw-bold mb-1"><i class="bi bi-funnel text-primary me-2"></i>Filtros</h5>
                        <div class="text-muted small">Busca por nombre, teléfono, correo o tutor.</div>
                    </div>
                    <a class="btn btn-success" href="?c=StudentAccess&amp;a=export&amp;search=<?php echo urlencode($search); ?>&amp;status=<?php echo urlencode($status); ?>">
                        <i class="bi bi-file-earmark-excel me-2"></i>Exportar a Excel
                    </a>
                </div>
                <form action="?c=StudentAccess" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="c" value="StudentAccess">
                    <div class="col-md-7">
                        <label for="search" class="form-label fw-semibold">Buscar alumno</label>
                        <input type="search" id="search" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nombre, teléfono, correo o tutor">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label fw-semibold">Estado</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="activo" <?php echo $status === 'activo' ? 'selected' : ''; ?>>Activos</option>
                            <option value="inactivo" <?php echo $status === 'inactivo' ? 'selected' : ''; ?>>Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search me-2"></i>Buscar</button>
                    </div>
                </form>
            </div>

            <div class="panel-card bg-white p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-people text-success me-2"></i>Alumnos encontrados</h5>
                    <span class="text-muted small"><?php echo (int)$totalStudents; ?> registros</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th><th>Teléfono</th><th>Correo</th><th>Tutor</th><th>Teléfono tutor</th><th>Correo tutor</th><th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($totalStudents > 0): ?>
                            <?php foreach ($students as $student): ?>
                                <?php
                                    $name = trim(($student['nombre'] ?? '') . ' ' . ($student['apellido_paterno'] ?? '') . ' ' . ($student['apellido_materno'] ?? ''));
                                    $tutor = trim(($student['tutor_nombre'] ?? '') . ' ' . ($student['tutor_apellido_paterno'] ?? '') . ' ' . ($student['tutor_apellido_materno'] ?? ''));
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($name ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($student['telefono'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($student['correo'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($tutor ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($student['tutor_telefono'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($student['tutor_correo'] ?? '-'); ?></td>
                                    <td><span class="badge <?php echo (int)($student['activo'] ?? 0) === 1 ? 'bg-success' : 'bg-secondary'; ?>"><?php echo (int)($student['activo'] ?? 0) === 1 ? 'Activo' : 'Inactivo'; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted">No hay alumnos con los filtros seleccionados.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
