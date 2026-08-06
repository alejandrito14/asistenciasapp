<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignación Maestro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>
    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0 fw-bold text-secondary"><i class="bi bi-person-badge-fill"></i> Asignación Maestro</h2>
                <small class="text-muted">Vincula un maestro con una materia de un grupo.</small>
            </div>
            <button class="btn btn-success shadow" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-plus-circle"></i> Nueva Asignación
            </button>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'creado'): ?><div class="alert alert-success">Asignación creada correctamente.</div><?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'actualizado'): ?><div class="alert alert-success">Asignación actualizada correctamente.</div><?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'eliminado'): ?><div class="alert alert-success">Asignación eliminada correctamente.</div><?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'estado_cambiado'): ?><div class="alert alert-success">Estado de la asignación actualizado.</div><?php elseif (isset($_GET['err']) && $_GET['err'] === 'tiene_relaciones'): ?><div class="alert alert-warning">No se puede eliminar porque ya tiene horarios o sesiones asociadas.</div><?php elseif (isset($_GET['err']) && $_GET['err'] === 'duplicado'): ?><div class="alert alert-warning">Ese grupo ya tiene asignado ese maestro.</div><?php elseif (isset($_GET['err']) && $_GET['err'] === 'datos_invalidos'): ?><div class="alert alert-danger">Revisa los datos capturados.</div><?php endif; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <form class="row g-2 align-items-center" method="GET">
                    <input type="hidden" name="c" value="GroupMateriaMaestro">
                    <div class="col-auto"><label class="col-form-label fw-bold">Buscar:</label></div>
                    <div class="col-md-6"><input type="text" name="q" class="form-control" placeholder="Grupo, materia o maestro" value="<?php echo htmlspecialchars($search ?? ''); ?>"></div>
                    <div class="col-auto"><button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Filtrar</button></div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th class="ps-4">Estado</th><th>Grupo</th><th>Materia</th><th>Maestro</th><th class="text-end pe-4">Acciones</th></tr></thead>
                    <tbody>
                    <?php if (!empty($items)): foreach ($items as $item): ?>
                        <tr>
                            <td class="ps-4"><?php echo ((int)($item['activo'] ?? 1) === 1) ? '<span class="badge bg-success">ACTIVO</span>' : '<span class="badge bg-danger">INACTIVO</span>'; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($item['grupo_nombre']); ?> <?php echo htmlspecialchars($item['semestre']); ?></td>
                            <td><?php echo htmlspecialchars($item['materia_clave']); ?> - <?php echo htmlspecialchars($item['materia_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($item['maestro_nombre']); ?> <?php echo htmlspecialchars($item['apellido_paterno']); ?> <?php echo htmlspecialchars($item['apellido_materno']); ?></td>
                            <td class="text-end pe-4">
                                <a href="?c=GroupMateriaMaestro&a=edit&id=<?php echo (int)$item['id']; ?>" class="btn btn-sm btn-warning shadow-sm"><i class="bi bi-pencil-fill"></i></a>
                                <?php if ((int)($item['activo'] ?? 1) === 1): ?>
                                    <a href="?c=GroupMateriaMaestro&a=toggle&id=<?php echo (int)$item['id']; ?>&status=1" class="btn btn-sm btn-outline-danger shadow-sm" onclick="return confirm('¿Desactivar esta asignación?');"><i class="bi bi-power"></i></a>
                                <?php else: ?>
                                    <a href="?c=GroupMateriaMaestro&a=toggle&id=<?php echo (int)$item['id']; ?>&status=0" class="btn btn-sm btn-outline-success shadow-sm" onclick="return confirm('¿Reactivar esta asignación?');"><i class="bi bi-power"></i></a>
                                <?php endif; ?>
                                <a href="?c=GroupMateriaMaestro&a=delete&id=<?php echo (int)$item['id']; ?>" class="btn btn-sm btn-outline-danger shadow-sm" onclick="return confirm('¿Eliminar esta asignación?');"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="5" class="text-center p-5 text-muted">No hay asignaciones registradas.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="mt-4"><ul class="pagination justify-content-center"><?php for ($i=1; $i<=$totalPages; $i++): ?><li class="page-item <?php echo ($i===$page)?'active':''; ?>"><a class="page-link" href="?c=GroupMateriaMaestro&q=<?php echo urlencode($search ?? ''); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li><?php endfor; ?></ul></nav>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-badge-fill"></i> Nueva Asignación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="?c=GroupMateriaMaestro&a=store">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Grupo - Materia</label>
                            <select name="grupo_materia_id" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($groupSubjects as $gm): ?>
                                    <option value="<?php echo (int)$gm['id']; ?>"><?php echo htmlspecialchars($gm['grupo_nombre'] . ' / ' . $gm['semestre'] . ' | ' . $gm['materia_clave'] . ' - ' . $gm['materia_nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Maestro</label>
                            <select name="maestro_id" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?php echo (int)$teacher['id']; ?>"><?php echo htmlspecialchars($teacher['nombre'] . ' ' . $teacher['apellido_paterno'] . ' ' . $teacher['apellido_materno']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Estado</label>
                            <select name="activo" class="form-select">
                                <option value="1" selected>Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">Guardar Asignación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
