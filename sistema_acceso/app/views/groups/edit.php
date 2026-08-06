<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Grupo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .app-page { min-height: 100vh; }
    </style>
</head>
<body class="bg-light">
<div class="d-flex app-page">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="mb-0 fw-bold text-secondary"><i class="bi bi-pencil-square"></i> Editar Grupo</h2>
                <small class="text-muted">Actualiza la configuración del grupo y su acceso.</small>
            </div>
            <a href="?c=Group" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-dark fw-bold">
                Datos del grupo
            </div>
            <div class="card-body p-4">
                <form method="POST" action="?c=Group&a=update">
                    <input type="hidden" name="id" value="<?php echo (int)$group['id']; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre del grupo</label>
                            <input type="text" name="nombre" class="form-control" required value="<?php echo htmlspecialchars($group['nombre']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Semestre</label>
                            <input type="text" name="semestre" class="form-control" required value="<?php echo htmlspecialchars($group['semestre']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Turno</label>
                            <select name="turno" class="form-select" required>
                                <option value="matutino" <?php echo ($group['turno'] === 'matutino') ? 'selected' : ''; ?>>Matutino</option>
                                <option value="vespertino" <?php echo ($group['turno'] === 'vespertino') ? 'selected' : ''; ?>>Vespertino</option>
                                <option value="nocturno" <?php echo ($group['turno'] === 'nocturno') ? 'selected' : ''; ?>>Nocturno</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Ciclo escolar</label>
                            <input type="text" name="ciclo_escolar" class="form-control" required value="<?php echo htmlspecialchars($group['ciclo_escolar']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Código de ingreso</label>
                            <input type="text" name="codigo_ingreso" class="form-control" maxlength="10" value="<?php echo htmlspecialchars($group['codigo_ingreso'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Requiere aprobación</label>
                            <select name="requiere_aprobacion" class="form-select">
                                <option value="1" <?php echo ((int)($group['requiere_aprobacion'] ?? 1) === 1) ? 'selected' : ''; ?>>Sí</option>
                                <option value="0" <?php echo ((int)($group['requiere_aprobacion'] ?? 1) === 0) ? 'selected' : ''; ?>>No</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Estado</label>
                            <select name="activo" class="form-select">
                                <option value="1" <?php echo ((int)($group['activo'] ?? 1) === 1) ? 'selected' : ''; ?>>Activo</option>
                                <option value="0" <?php echo ((int)($group['activo'] ?? 1) === 0) ? 'selected' : ''; ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="?c=Group" class="btn btn-secondary">Cancelar</a>
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
