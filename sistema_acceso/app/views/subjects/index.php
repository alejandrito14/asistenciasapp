<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Materias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>
    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0 fw-bold text-secondary"><i class="bi bi-journal-bookmark-fill"></i> Materias</h2>
                <small class="text-muted">Administra el catálogo académico de asignaturas.</small>
            </div>
            <button class="btn btn-success shadow" data-bs-toggle="modal" data-bs-target="#createSubjectModal">
                <i class="bi bi-plus-circle"></i> Nueva Materia
            </button>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'creado'): ?><div class="alert alert-success">Materia creada correctamente.</div><?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'actualizado'): ?><div class="alert alert-success">Materia actualizada correctamente.</div><?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'eliminado'): ?><div class="alert alert-success">Materia eliminada correctamente.</div><?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'estado_cambiado'): ?><div class="alert alert-success">Estado de la materia actualizado.</div><?php elseif (isset($_GET['err']) && $_GET['err'] === 'tiene_relaciones'): ?><div class="alert alert-warning">No se puede eliminar porque la materia tiene relaciones asociadas.</div><?php elseif (isset($_GET['err']) && $_GET['err'] === 'datos_invalidos'): ?><div class="alert alert-danger">Revisa los datos capturados.</div><?php endif; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <form class="row g-2 align-items-center" method="GET" action="">
                    <input type="hidden" name="c" value="Subject">
                    <div class="col-auto"><label class="col-form-label fw-bold">Buscar:</label></div>
                    <div class="col-md-6"><input type="text" name="q" class="form-control" placeholder="Clave, nombre o descripción" value="<?php echo htmlspecialchars($search ?? ''); ?>"></div>
                    <div class="col-auto"><button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button></div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th class="ps-4">Estado</th><th>Clave</th><th>Nombre</th><th class="text-end pe-4">Acciones</th></tr></thead>
                    <tbody>
                        <?php if (!empty($subjects)): ?>
                            <?php foreach ($subjects as $subject): ?>
                                <tr class="<?php echo ((int)($subject['activo'] ?? 1) === 0) ? 'table-secondary' : ''; ?>">
                                    <td class="ps-4"><?php echo ((int)($subject['activo'] ?? 1) === 1) ? '<span class="badge bg-success">ACTIVO</span>' : '<span class="badge bg-danger">INACTIVO</span>'; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($subject['clave'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($subject['nombre'] ?? ''); ?></td>
                                    <td class="text-end pe-4">
                                        <a href="?c=Subject&a=edit&id=<?php echo (int)$subject['id']; ?>" class="btn btn-sm btn-warning shadow-sm"><i class="bi bi-pencil-fill"></i></a>
                                        <?php if ((int)($subject['activo'] ?? 1) === 1): ?>
                                            <a href="?c=Subject&a=toggle&id=<?php echo (int)$subject['id']; ?>&status=1" class="btn btn-sm btn-outline-danger shadow-sm" onclick="return confirm('¿Desactivar esta materia?');"><i class="bi bi-power"></i></a>
                                        <?php else: ?>
                                            <a href="?c=Subject&a=toggle&id=<?php echo (int)$subject['id']; ?>&status=0" class="btn btn-sm btn-outline-success shadow-sm" onclick="return confirm('¿Reactivar esta materia?');"><i class="bi bi-power"></i></a>
                                        <?php endif; ?>
                                        <a href="?c=Subject&a=delete&id=<?php echo (int)$subject['id']; ?>" class="btn btn-sm btn-outline-danger shadow-sm" onclick="return confirm('¿Eliminar esta materia? Solo se permitirá si no tiene relaciones.');"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center p-5 text-muted">No hay materias registradas.</td></tr>
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
                            <a class="page-link" href="?c=Subject&q=<?php echo urlencode($search ?? ''); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="createSubjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nueva materia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="?c=Subject&a=store">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Clave</label>
                            <input type="text" name="clave" class="form-control" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3"></textarea>
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
