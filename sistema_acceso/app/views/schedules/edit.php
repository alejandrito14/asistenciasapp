<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Horario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>
    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        <div class="d-flex align-items-center mb-4">
            <a href="?c=Schedule" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Volver</a>
            <h2 class="mb-0 fw-bold text-secondary"><i class="bi bi-pencil-square"></i> Editar Horario</h2>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-warning text-dark fw-bold">Actualizar Datos</div>
                    <div class="card-body p-4">
                        <form method="POST" action="?c=Schedule&a=update">
                            <input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Asignación maestro-grupo-materia</label>
                                    <select name="grupo_materia_maestro_id" class="form-select" required>
                                        <?php foreach ($assignments as $assignment): ?>
                                            <option value="<?php echo (int)$assignment['id']; ?>" <?php echo ((int)$item['grupo_materia_maestro_id'] === (int)$assignment['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($assignment['grupo_nombre'] . ' / ' . $assignment['semestre'] . ' | ' . $assignment['materia_clave'] . ' - ' . $assignment['materia_nombre'] . ' | ' . $assignment['maestro_nombre'] . ' ' . $assignment['apellido_paterno']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Día de la semana</label>
                                    <select name="dia_semana" class="form-select" required>
                                        <option value="LUNES" <?php echo (($item['dia_semana'] ?? '') === 'LUNES') ? 'selected' : ''; ?>>Lunes</option>
                                        <option value="MARTES" <?php echo (($item['dia_semana'] ?? '') === 'MARTES') ? 'selected' : ''; ?>>Martes</option>
                                        <option value="MIERCOLES" <?php echo (($item['dia_semana'] ?? '') === 'MIERCOLES') ? 'selected' : ''; ?>>Miércoles</option>
                                        <option value="JUEVES" <?php echo (($item['dia_semana'] ?? '') === 'JUEVES') ? 'selected' : ''; ?>>Jueves</option>
                                        <option value="VIERNES" <?php echo (($item['dia_semana'] ?? '') === 'VIERNES') ? 'selected' : ''; ?>>Viernes</option>
                                        <option value="SABADO" <?php echo (($item['dia_semana'] ?? '') === 'SABADO') ? 'selected' : ''; ?>>Sábado</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Hora inicio</label>
                                    <input type="time" name="hora_inicio" class="form-control" required value="<?php echo htmlspecialchars(substr($item['hora_inicio'], 0, 5)); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Hora fin</label>
                                    <input type="time" name="hora_fin" class="form-control" required value="<?php echo htmlspecialchars(substr($item['hora_fin'], 0, 5)); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Aula</label>
                                    <input type="text" name="aula" class="form-control" value="<?php echo htmlspecialchars($item['aula'] ?? ''); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Minutos antes</label>
                                    <input type="number" name="minutos_antes" class="form-control" min="0" value="<?php echo htmlspecialchars((string)($item['minutos_antes'] ?? 15)); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Minutos después</label>
                                    <input type="number" name="minutos_despues" class="form-control" min="0" value="<?php echo htmlspecialchars((string)($item['minutos_despues'] ?? 15)); ?>">
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
                                <a href="?c=Schedule" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-warning fw-bold px-4">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
