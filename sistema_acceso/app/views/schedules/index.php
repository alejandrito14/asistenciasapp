<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>
    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0 fw-bold text-secondary"><i class="bi bi-calendar-week-fill"></i> Horarios</h2>
                <small class="text-muted">Define los días y horas de cada asignación maestro-grupo-materia.</small>
            </div>
            <button class="btn btn-success shadow" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-plus-circle"></i> Nuevo Horario
            </button>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'creado'): ?><div class="alert alert-success">Horario creado correctamente.</div><?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'actualizado'): ?><div class="alert alert-success">Horario actualizado correctamente.</div><?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'eliminado'): ?><div class="alert alert-success">Horario eliminado correctamente.</div><?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'estado_cambiado'): ?><div class="alert alert-success">Estado del horario actualizado.</div><?php elseif (isset($_GET['err']) && $_GET['err'] === 'tiene_relaciones'): ?><div class="alert alert-warning">No se puede eliminar porque ya tiene asistencias asociadas.</div><?php elseif (isset($_GET['err']) && $_GET['err'] === 'datos_invalidos'): ?><div class="alert alert-danger">Revisa los datos capturados.</div><?php endif; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <form class="row g-2 align-items-center" method="GET">
                    <input type="hidden" name="c" value="Schedule">
                    <div class="col-auto"><label class="col-form-label fw-bold">Buscar:</label></div>
                    <div class="col-md-6"><input type="text" name="q" class="form-control" placeholder="Grupo, materia, maestro, día o aula" value="<?php echo htmlspecialchars($search ?? ''); ?>"></div>
                    <div class="col-auto"><button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Filtrar</button></div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th class="ps-4">Estado</th><th>Grupo</th><th>Materia</th><th>Maestro</th><th>Día</th><th>Horario</th><th>Ventana</th><th>Aula</th><th class="text-end pe-4">Acciones</th></tr></thead>
                    <tbody>
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $item): ?>
                                <tr class="<?php echo ((int)($item['activo'] ?? 1) === 0) ? 'table-inactive' : ''; ?>">
                                    <td class="ps-4"><?php echo ((int)($item['activo'] ?? 1) === 1) ? '<span class="badge bg-success">ACTIVO</span>' : '<span class="badge bg-danger">INACTIVO</span>'; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($item['grupo_nombre']); ?> <?php echo htmlspecialchars($item['semestre']); ?></td>
                                    <td><?php echo htmlspecialchars($item['materia_clave']); ?> - <?php echo htmlspecialchars($item['materia_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($item['maestro_nombre']); ?> <?php echo htmlspecialchars($item['apellido_paterno']); ?> <?php echo htmlspecialchars($item['apellido_materno']); ?></td>
                                    <td><?php echo htmlspecialchars($item['dia_semana']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($item['hora_inicio'], 0, 5) . ' - ' . substr($item['hora_fin'], 0, 5)); ?></td>
                                    <td><?php echo (int)($item['minutos_antes'] ?? 15); ?> min antes / <?php echo (int)($item['minutos_despues'] ?? 15); ?> min después</td>
                                    <td><?php echo htmlspecialchars($item['aula'] ?? ''); ?></td>
                                    <td class="text-end pe-4">
                                        <a href="?c=Schedule&a=edit&id=<?php echo (int)$item['id']; ?>" class="btn btn-sm btn-warning shadow-sm"><i class="bi bi-pencil-fill"></i></a>
                                        <?php if ((int)($item['activo'] ?? 1) === 1): ?>
                                            <a href="?c=Schedule&a=toggle&id=<?php echo (int)$item['id']; ?>&status=1" class="btn btn-sm btn-outline-danger shadow-sm" onclick="return confirm('¿Desactivar este horario?');"><i class="bi bi-power"></i></a>
                                        <?php else: ?>
                                            <a href="?c=Schedule&a=toggle&id=<?php echo (int)$item['id']; ?>&status=0" class="btn btn-sm btn-outline-success shadow-sm" onclick="return confirm('¿Reactivar este horario?');"><i class="bi bi-power"></i></a>
                                        <?php endif; ?>
                                        <a href="?c=Schedule&a=delete&id=<?php echo (int)$item['id']; ?>" class="btn btn-sm btn-outline-danger shadow-sm" onclick="return confirm('¿Eliminar este horario?');"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center p-5 text-muted">No hay horarios registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="mt-4"><ul class="pagination justify-content-center"><?php for ($i=1; $i<=$totalPages; $i++): ?><li class="page-item <?php echo ($i===$page)?'active':''; ?>"><a class="page-link" href="?c=Schedule&q=<?php echo urlencode($search ?? ''); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li><?php endfor; ?></ul></nav>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-calendar-week-fill"></i> Nuevo Horario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="?c=Schedule&a=store">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Asignación maestro-grupo-materia</label>
                            <select name="grupo_materia_maestro_id" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($assignments as $assignment): ?>
                                    <option value="<?php echo (int)$assignment['id']; ?>">
                                        <?php echo htmlspecialchars($assignment['grupo_nombre'] . ' / ' . $assignment['semestre'] . ' | ' . $assignment['materia_clave'] . ' - ' . $assignment['materia_nombre'] . ' | ' . $assignment['maestro_nombre'] . ' ' . $assignment['apellido_paterno']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Día de la semana</label>
                            <select name="dia_semana" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="LUNES">Lunes</option>
                                <option value="MARTES">Martes</option>
                                <option value="MIERCOLES">Miércoles</option>
                                <option value="JUEVES">Jueves</option>
                                <option value="VIERNES">Viernes</option>
                                <option value="SABADO">Sábado</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Hora inicio</label>
                            <input type="time" name="hora_inicio" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Hora fin</label>
                            <input type="time" name="hora_fin" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Aula</label>
                            <input type="text" name="aula" class="form-control" placeholder="A-101">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Minutos antes</label>
                            <input type="number" name="minutos_antes" class="form-control" min="0" value="15">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Minutos después</label>
                            <input type="number" name="minutos_despues" class="form-control" min="0" value="15">
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
                    <button type="submit" class="btn btn-success fw-bold">Guardar Horario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
