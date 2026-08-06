<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Grupo-Materia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>
    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        <div class="d-flex align-items-center mb-4">
            <a href="?c=GroupSubject" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Volver</a>
            <h2 class="mb-0 fw-bold text-secondary"><i class="bi bi-pencil-square"></i> Editar Relación</h2>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-warning text-dark fw-bold">Actualizar Datos</div>
                    <div class="card-body p-4">
                        <form method="POST" action="?c=GroupSubject&a=update">
                            <input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Grupo</label>
                                    <select name="grupo_id" class="form-select" required>
                                        <?php foreach ($groups as $group): ?>
                                            <option value="<?php echo (int)$group['id']; ?>" <?php echo ((int)$item['grupo_id'] === (int)$group['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($group['nombre'] . ' / ' . $group['semestre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Materia</label>
                                    <select name="materia_id" class="form-select" required>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo (int)$subject['id']; ?>" <?php echo ((int)$item['materia_id'] === (int)$subject['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($subject['clave'] . ' - ' . $subject['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4 d-flex justify-content-end gap-2">
                                <a href="?c=GroupSubject" class="btn btn-secondary">Cancelar</a>
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
