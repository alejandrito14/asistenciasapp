<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Materia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="d-flex">
<?php require_once '../app/views/layouts/sidebar.php'; ?>
<div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
    <div class="d-flex align-items-center mb-4">
        <a href="?c=Subject" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Volver</a>
        <h2 class="mb-0 fw-bold text-secondary"><i class="bi bi-pencil-square"></i> Editar Materia</h2>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0">
                <div class="card-header bg-warning text-dark fw-bold">Actualizar Datos</div>
                <div class="card-body p-4">
                    <form method="POST" action="?c=Subject&a=update">
                        <input type="hidden" name="id" value="<?php echo (int)$subject['id']; ?>">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Clave</label>
                                <input type="text" name="clave" class="form-control" required value="<?php echo htmlspecialchars($subject['clave'] ?? ''); ?>">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" required value="<?php echo htmlspecialchars($subject['nombre'] ?? ''); ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="3"><?php echo htmlspecialchars($subject['descripcion'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado</label>
                                <select name="activo" class="form-select">
                                    <option value="1" <?php echo ((int)($subject['activo'] ?? 1) === 1) ? 'selected' : ''; ?>>Activo</option>
                                    <option value="0" <?php echo ((int)($subject['activo'] ?? 1) === 0) ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <a href="?c=Subject" class="btn btn-secondary">Cancelar</a>
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
