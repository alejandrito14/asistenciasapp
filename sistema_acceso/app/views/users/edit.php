<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        .sidebar { min-height: 100vh; background-color: #212529; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: 0.3s; }
        .sidebar a:hover { background-color: #343a40; color: white; }
        .sidebar a.active { background-color: #0d6efd; color: white; border-left-color: white; }
        .sidebar i { width: 25px; }
    </style>
</head>
<body class="bg-light">

<div class="d-flex">
    
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        
        <div class="d-flex align-items-center mb-4">
            <a href="?c=User" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Volver</a>
            <h2 class="mb-0 fw-bold text-secondary"><i class="bi bi-person-gear"></i> Editar Usuario</h2>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0">
                    <div class="card-header bg-warning text-dark fw-bold">
                        Datos de Acceso
                    </div>
                    <div class="card-body p-4">
                        <form action="?c=User&a=update" method="POST">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Usuario</label>
                                <input type="text" name="username" class="form-control" value="<?php echo $user['username']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Rol</label>
                                <select name="role" class="form-select">
                                    <option value="guardia" <?php echo ($user['role']=='guardia')?'selected':''; ?>>Guardia</option>
                                    <option value="admin" <?php echo ($user['role']=='admin')?'selected':''; ?>>Administrador</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted">Cambiar Contraseña (Opcional)</label>
                                <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para no cambiar">
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="?c=User" class="btn btn-secondary me-2">Cancelar</a>
                                <button type="submit" class="btn btn-warning fw-bold px-4">Actualizar</button>
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