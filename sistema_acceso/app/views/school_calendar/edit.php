<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Calendario Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>
    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="mb-0 fw-bold text-secondary"><i class="bi bi-pencil-square"></i> Editar Calendario Escolar</h2>
                <small class="text-muted">Actualiza el rango o el tipo de calendario.</small>
            </div>
            <a href="?c=SchoolCalendar" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form method="POST" action="?c=SchoolCalendar&a=update">
                    <input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" required value="<?php echo htmlspecialchars($item['fecha_inicio']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha fin</label>
                            <input type="date" name="fecha_fin" class="form-control" required value="<?php echo htmlspecialchars($item['fecha_fin']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tipo</label>
                            <select name="tipo" class="form-select" required>
                                <?php $types = ['VACACIONES' => 'Vacaciones', 'INHABIL' => 'Inhábil', 'SUSPENSION' => 'Suspensión', 'EVENTO_ESPECIAL' => 'Evento especial']; ?>
                                <?php foreach ($types as $value => $label): ?>
                                    <option value="<?php echo $value; ?>" <?php echo (($item['tipo'] ?? '') === $value) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Descripción</label>
                            <input type="text" name="descripcion" class="form-control" maxlength="255" value="<?php echo htmlspecialchars($item['descripcion'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Estado</label>
                            <select name="activo" class="form-select">
                                <option value="1" <?php echo ((int)($item['activo'] ?? 1) === 1) ? 'selected' : ''; ?>>Activo</option>
                                <option value="0" <?php echo ((int)($item['activo'] ?? 1) === 0) ? 'selected' : ''; ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="?c=SchoolCalendar" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-warning fw-bold px-4">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
