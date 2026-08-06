<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Grupos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0 fw-bold text-secondary"><i class="bi bi-diagram-3-fill"></i> Grupos</h2>
                <small class="text-muted">Administra la estructura académica antes de capturar asistencias.</small>
            </div>
            <button class="btn btn-success shadow" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                <i class="bi bi-plus-circle"></i> Nuevo Grupo
            </button>
        </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'creado'): ?>
        <div class="alert alert-success">Grupo creado correctamente.</div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'actualizado'): ?>
        <div class="alert alert-success">Grupo actualizado correctamente.</div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'eliminado'): ?>
        <div class="alert alert-success">Grupo eliminado correctamente.</div>
    <?php elseif (isset($_GET['err']) && $_GET['err'] === 'tiene_relaciones'): ?>
        <div class="alert alert-warning">No se puede eliminar porque el grupo tiene relaciones asociadas.</div>
    <?php elseif (isset($_GET['err']) && $_GET['err'] === 'datos_invalidos'): ?>
        <div class="alert alert-danger">Revisa los datos capturados.</div>
    <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <form class="row g-2 align-items-center" method="GET" action="">
                    <input type="hidden" name="c" value="Group">
                    <div class="col-auto"><label class="col-form-label fw-bold">Buscar:</label></div>
                    <div class="col-md-6"><input type="text" name="q" class="form-control" placeholder="Nombre, semestre o ciclo escolar" value="<?php echo htmlspecialchars($search ?? ''); ?>"></div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th class="ps-4">Grupo</th><th>Semestre</th><th>Código</th><th>Aprobación</th><th>Estado</th><th class="text-end pe-4">Acciones</th></tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($groups)): ?>
                        <?php foreach ($groups as $group): ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($group['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($group['semestre']); ?></td>
                                <td><?php echo htmlspecialchars($group['codigo_ingreso'] ?? ''); ?></td>
                                <td><?php echo !empty($group['requiere_aprobacion']) ? 'Sí' : 'No'; ?></td>
                                <td><?php echo ((int)($group['activo'] ?? 1) === 1) ? 'Activo' : 'Inactivo'; ?></td>
                                <td class="text-end">
                                    <a href="?c=Group&a=edit&id=<?php echo (int)$group['id']; ?>" class="btn btn-sm btn-warning shadow-sm"><i class="bi bi-pencil-fill"></i></a>
                                    <a href="?c=Group&a=delete&id=<?php echo (int)$group['id']; ?>" class="btn btn-sm btn-outline-danger shadow-sm" onclick="return confirm('¿Eliminar este grupo? Solo se permitirá si no tiene relaciones.');"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center p-5 text-muted">No hay grupos registrados.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?c=Group&q=<?php echo urlencode($search ?? ''); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="createGroupModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nuevo grupo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="?c=Group&a=store">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del grupo</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Semestre</label>
                            <input type="text" name="semestre" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Turno</label>
                            <select name="turno" class="form-select" required>
                                <option value="">Selecciona</option>
                                <option value="matutino">Matutino</option>
                                <option value="vespertino">Vespertino</option>
                                <option value="nocturno">Nocturno</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ciclo escolar</label>
                            <input type="text" name="ciclo_escolar" class="form-control" placeholder="2025-2026" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Código de ingreso</label>
                            <input type="text" name="codigo_ingreso" class="form-control" maxlength="10" placeholder="ABC123">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Requiere aprobación</label>
                            <select name="requiere_aprobacion" class="form-select">
                                <option value="1" selected>Sí</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="activo" class="form-select">
                                <option value="1" selected>Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
