<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Asignación Maestro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>
    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        <div class="d-flex align-items-center mb-4">
            <a href="?c=GroupMateriaMaestro" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Volver</a>
            <h2 class="mb-0 fw-bold text-secondary"><i class="bi bi-pencil-square"></i> Editar Asignación</h2>
        </div>

        <?php if (isset($_GET['err']) && $_GET['err'] === 'duplicado'): ?><div class="alert alert-warning">Ese grupo ya tiene asignado ese maestro.</div><?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-warning text-dark fw-bold">Actualizar Datos</div>
                    <div class="card-body p-4">
                        <form method="POST" action="?c=GroupMateriaMaestro&a=update">
                            <input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Grupo - Materia</label>
                                    <select name="grupo_materia_id" class="form-select" required>
                                        <?php foreach ($groupSubjects as $gm): ?>
                                            <option value="<?php echo (int)$gm['id']; ?>" <?php echo ((int)$item['grupo_materia_id'] === (int)$gm['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($gm['grupo_nombre'] . ' / ' . $gm['semestre'] . ' | ' . $gm['materia_clave'] . ' - ' . $gm['materia_nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Maestro</label>
                                    <select name="maestro_id" class="form-select" required>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <option value="<?php echo (int)$teacher['id']; ?>" <?php echo ((int)$item['maestro_id'] === (int)$teacher['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($teacher['nombre'] . ' ' . $teacher['apellido_paterno'] . ' ' . $teacher['apellido_materno']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
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
                                <a href="?c=GroupMateriaMaestro" class="btn btn-secondary">Cancelar</a>
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
