<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .page-shell {
            min-width: 0;
            min-height: 100vh;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>
    <div class="app-main-content flex-grow-1 bg-light page-shell">
        <nav class="navbar navbar-light bg-white shadow-sm px-4 py-3">
            <div class="container-fluid">
                <div>
                    <span class="navbar-brand mb-0 h1 fw-bold text-primary">Calendario Escolar</span>
                    <div class="text-muted">Define vacaciones, suspensiones, eventos especiales e inhábiles.</div>
                </div>
                <button class="btn btn-success shadow" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="bi bi-plus-circle"></i> Nuevo registro
                </button>
            </div>
        </nav>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'creado'): ?>
            <div class="alert alert-success">Registro creado correctamente.</div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'actualizado'): ?>
            <div class="alert alert-success">Registro actualizado correctamente.</div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'eliminado'): ?>
            <div class="alert alert-success">Registro eliminado correctamente.</div>
        <?php elseif (isset($_GET['err']) && $_GET['err'] === 'datos_invalidos'): ?>
            <div class="alert alert-danger">Revisa los datos capturados.</div>
        <?php endif; ?>

        <div class="container-fluid p-4">
            <div class="card shadow border-0 mb-3">
                <div class="card-body py-3">
                    <form class="row g-2 align-items-center" method="GET" action="">
                        <input type="hidden" name="c" value="SchoolCalendar">
                        <div class="col-12 col-md-auto"><label class="col-form-label fw-bold">Buscar:</label></div>
                        <div class="col-12 col-md-6">
                            <input type="text" name="q" class="form-control" placeholder="Tipo o descripción" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                        </div>
                        <div class="col-12 col-md-auto d-grid">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow border-0">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0 bg-white">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Inicio</th>
                                <th>Fin</th>
                                <th>Tipo</th>
                                <th>Dirigido a</th>
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
                                    <td><?php echo !empty($item['maestro_id']) ? htmlspecialchars(trim(($item['maestro_nombre'] ?? '') . ' ' . ($item['maestro_apellido_paterno'] ?? '') . ' ' . ($item['maestro_apellido_materno'] ?? ''))) : 'Todos los maestros'; ?></td>
                                    <td><?php echo htmlspecialchars($item['descripcion'] ?? ''); ?></td>
                                    <td><?php echo ((int)($item['activo'] ?? 1) === 1) ? '<span class="badge bg-success">ACTIVO</span>' : '<span class="badge bg-danger">INACTIVO</span>'; ?></td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex flex-wrap justify-content-end gap-1">
                                            <a href="?c=SchoolCalendar&a=edit&id=<?php echo (int)$item['id']; ?>" class="btn btn-sm btn-warning shadow-sm"><i class="bi bi-pencil"></i></a>
                                            <a href="?c=SchoolCalendar&a=delete&id=<?php echo (int)$item['id']; ?>" class="btn btn-sm btn-outline-danger shadow-sm" onclick="return confirm('¿Eliminar este registro?');"><i class="bi bi-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center p-5 text-muted">No hay registros aún.</td>
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
</div>

<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
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
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="para_maestro" id="para_maestro">
                                <label class="form-check-label fw-semibold" for="para_maestro">Este registro es para un maestro en particular</label>
                            </div>
                        </div>
                        <div class="col-md-8" id="maestro_wrap" style="display:none;">
                            <label class="form-label">Maestro</label>
                            <select name="maestro_id" id="maestro_id" class="form-select">
                                <option value="">Selecciona un maestro</option>
                                <?php foreach (($teachers ?? []) as $teacher): ?>
                                    <?php $teacherName = trim(($teacher['nombre'] ?? '') . ' ' . ($teacher['apellido_paterno'] ?? '') . ' ' . ($teacher['apellido_materno'] ?? '')); ?>
                                    <option value="<?php echo (int)$teacher['id']; ?>"><?php echo htmlspecialchars($teacherName); ?></option>
                                <?php endforeach; ?>
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
<script>
    const modoUnico = document.getElementById('modo_unico');
    const modoRango = document.getElementById('modo_rango');
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    const fechaFinWrap = document.getElementById('fecha_fin_wrap');
    const paraMaestro = document.getElementById('para_maestro');
    const maestroWrap = document.getElementById('maestro_wrap');
    const maestroId = document.getElementById('maestro_id');

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

    paraMaestro.addEventListener('change', () => {
        maestroWrap.style.display = paraMaestro.checked ? 'block' : 'none';
        maestroId.required = paraMaestro.checked;
        if (!paraMaestro.checked) maestroId.value = '';
    });
</script>
</body>
</html>
