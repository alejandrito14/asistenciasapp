<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calendario Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>
    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0 fw-bold text-secondary"><i class="bi bi-calendar-event"></i> Calendario Escolar</h2>
                <small class="text-muted">Define vacaciones, suspensiones, eventos especiales e inhábiles.</small>
            </div>
            <button class="btn btn-success shadow" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-plus-circle"></i> Nuevo registro
            </button>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'creado'): ?>
            <div class="alert alert-success">Registro creado correctamente.</div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'actualizado'): ?>
            <div class="alert alert-success">Registro actualizado correctamente.</div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'eliminado'): ?>
            <div class="alert alert-success">Registro eliminado correctamente.</div>
        <?php elseif (isset($_GET['err']) && $_GET['err'] === 'datos_invalidos'): ?>
            <div class="alert alert-danger">Revisa los datos capturados.</div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <form class="row g-2 align-items-center" method="GET" action="">
                    <input type="hidden" name="c" value="SchoolCalendar">
                    <div class="col-auto"><label class="col-form-label fw-bold">Buscar:</label></div>
                    <div class="col-md-6">
                        <input type="text" name="q" class="form-control" placeholder="Tipo o descripción" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    </div>
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
                        <tr>
                            <th class="ps-4">Inicio</th>
                            <th>Fin</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="ps-4"><?php echo htmlspecialchars($item['fecha_inicio']); ?></td>
                                <td><?php echo htmlspecialchars($item['fecha_fin']); ?></td>
                                <td><?php echo htmlspecialchars($item['tipo']); ?></td>
                                <td><?php echo htmlspecialchars($item['descripcion'] ?? ''); ?></td>
                                <td><?php echo ((int)($item['activo'] ?? 1) === 1) ? 'Activo' : 'Inactivo'; ?></td>
                                <td class="text-end pe-4">
                                    <a href="?c=SchoolCalendar&a=edit&id=<?php echo (int)$item['id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                    <a href="?c=SchoolCalendar&a=delete&id=<?php echo (int)$item['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este registro?');"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center p-5 text-muted">No hay registros aún.</td>
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
                            <a class="page-link" href="?c=SchoolCalendar&q=<?php echo urlencode($search ?? ''); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nuevo registro</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="?c=SchoolCalendar&a=store">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Tipo de fecha</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="modo_fecha" id="modo_unico" value="unico" checked>
                                    <label class="form-check-label" for="modo_unico">Solo un día</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="modo_fecha" id="modo_rango" value="rango">
                                    <label class="form-check-label" for="modo_rango">Rango de fechas</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" id="fecha_inicio" required>
                        </div>
                        <div class="col-md-6" id="fecha_fin_wrap" style="display:none;">
                            <label class="form-label">Fecha fin</label>
                            <input type="date" name="fecha_fin" class="form-control" id="fecha_fin">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo</label>
                            <select name="tipo" class="form-select" required>
                                <option value="">Selecciona</option>
                                <option value="VACACIONES">Vacaciones</option>
                                <option value="INHABIL">Inhábil</option>
                                <option value="SUSPENSION">Suspensión</option>
                                <option value="EVENTO_ESPECIAL">Evento especial</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Descripción</label>
                            <input type="text" name="descripcion" class="form-control" maxlength="255" placeholder="Ej. Consejo técnico, puente, vacaciones...">
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
<script>
    const modoUnico = document.getElementById('modo_unico');
    const modoRango = document.getElementById('modo_rango');
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    const fechaFinWrap = document.getElementById('fecha_fin_wrap');

    function syncCalendarMode() {
        const isRange = modoRango.checked;
        fechaFinWrap.style.display = isRange ? 'block' : 'none';
        fechaFin.required = isRange;
        if (!isRange) {
            fechaFin.value = fechaInicio.value;
        }
    }

    modoUnico.addEventListener('change', syncCalendarMode);
    modoRango.addEventListener('change', syncCalendarMode);
    fechaInicio.addEventListener('change', () => {
        if (modoUnico.checked) {
            fechaFin.value = fechaInicio.value;
        }
    });
    syncCalendarMode();
</script>
</body>
</html>
