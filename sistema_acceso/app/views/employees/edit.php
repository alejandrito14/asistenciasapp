<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Maestro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>
    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        <div class="d-flex align-items-center mb-4">
            <a href="?c=Employee" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Volver</a>
            <h2 class="mb-0 fw-bold text-secondary"><i class="bi bi-pencil-square"></i> Editar Maestro</h2>
        </div>
        <div class="card shadow border-0">
            <div class="card-header bg-warning text-dark fw-bold">Actualizar Datos del Maestro</div>
            <div class="card-body p-4">
                <form action="?c=Employee&a=update_data" method="POST">
                    <input type="hidden" name="id" value="<?php echo (int)$emp['id']; ?>">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="alert alert-info mb-0">
                                Este maestro está ligado a su cuenta de acceso. Si necesitas cambiar el correo o la contraseña, hazlo desde el registro de usuario correspondiente.
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required value="<?php echo htmlspecialchars($emp['nombre'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Apellido paterno</label>
                            <input type="text" name="apellido_paterno" class="form-control" value="<?php echo htmlspecialchars($emp['apellido_paterno'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Apellido materno</label>
                            <input type="text" name="apellido_materno" class="form-control" value="<?php echo htmlspecialchars($emp['apellido_materno'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($emp['telefono'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Correo</label>
                            <input type="email" name="correo" class="form-control" value="<?php echo htmlspecialchars($emp['correo'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Estado</label>
                            <select name="activo" class="form-select">
                                <option value="1" <?php echo ((int)($emp['activo'] ?? 1) === 1) ? 'selected' : ''; ?>>Activo</option>
                                <option value="0" <?php echo ((int)($emp['activo'] ?? 1) === 0) ? 'selected' : ''; ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="?c=Employee" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-warning fw-bold px-4">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
