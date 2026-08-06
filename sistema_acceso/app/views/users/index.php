<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #212529; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: 0.3s; }
        .sidebar a:hover { background-color: #343a40; color: white; }
        .sidebar a.active { background-color: #0d6efd; color: white; border-left-color: white; }
        .sidebar i { width: 25px; }
        .table-inactive { opacity: 0.6; background-color: #f8f9fa; }
    </style>
</head>
<body class="bg-light">

<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        <h2 class="mb-4 fw-bold text-secondary"><i class="bi bi-person-gear"></i> Usuarios del Sistema</h2>

        <?php if(isset($_GET['msg']) && $_GET['msg']=='creado') echo "<div class='alert alert-success'>Usuario creado correctamente.</div>"; ?>
        <?php if(isset($_GET['msg']) && $_GET['msg']=='estado_cambiado') echo "<div class='alert alert-success'>Estado actualizado.</div>"; ?>
        <?php if(isset($_GET['err']) && $_GET['err']=='existe') echo "<div class='alert alert-danger'>El nombre de usuario ya existe.</div>"; ?>
        <?php if(isset($_GET['err']) && $_GET['err']=='self_disable') echo "<div class='alert alert-danger'>No puedes desactivar tu propia cuenta.</div>"; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-dark text-white"><h5 class="m-0">Nuevo Usuario</h5></div>
                    <div class="card-body">
                        <form action="?c=User&a=store" method="POST">
                            <div class="mb-3">
                                <label>Usuario</label>
                                <input type="text" name="username" class="form-control" required placeholder="ej: guardia1">
                            </div>
                            <div class="mb-3">
                                <label>Contraseña</label>
                                <input type="password" name="password" class="form-control" required placeholder="******">
                            </div>
                            <div class="mb-3">
                                <label>Rol</label>
                                <select name="role" class="form-select">
                                    <option value="guardia">Guardia</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-dark w-100">Crear Usuario</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>ID</th><th>Estado</th><th>Usuario</th><th>Rol</th><th>Acciones</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $u): ?>
                                
                                <?php 
                                    // Si el status es NULL (usuarios viejos), asumimos activo
                                    $status = isset($u['status']) ? $u['status'] : 'activo'; 
                                ?>

                                <tr class="<?php echo ($status == 'inactivo') ? 'table-inactive' : ''; ?>">
                                    <td class="ps-4 text-muted">#<?php echo $u['id']; ?></td>
                                    
                                    <td>
                                        <?php if($status == 'activo'): ?>
                                            <span class="badge bg-success">ACTIVO</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">INACTIVO</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="fw-bold"><?php echo $u['username']; ?></td>
                                    <td>
                                        <?php if($u['role'] == 'admin'): ?>
                                            <span class="badge bg-primary">ADMIN</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">GUARDIA</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td>
                                        <a href="?c=User&a=edit&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-warning me-1">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        
                                        <?php if($u['id'] != $_SESSION['user_id']): // No mostrar en mi propia fila ?>
                                            
                                            <?php if($status == 'activo'): ?>
                                                <a href="?c=User&a=toggle&id=<?php echo $u['id']; ?>&status=activo" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="confirmarAccion(event, this.href, '¿Desactivar Acceso?', 'Este usuario no podrá iniciar sesión.', 'warning')">
                                                    <i class="bi bi-power"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="?c=User&a=toggle&id=<?php echo $u['id']; ?>&status=inactivo" 
                                                   class="btn btn-sm btn-outline-success"
                                                   onclick="confirmarAccion(event, this.href, '¿Reactivar Acceso?', 'El usuario podrá volver a entrar.', 'success')">
                                                    <i class="bi bi-power"></i>
                                                </a>
                                            <?php endif; ?>

                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function confirmarAccion(event, url, titulo, mensaje, icono) {
        event.preventDefault();
        Swal.fire({
            title: titulo,
            text: mensaje,
            icon: icono,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '¡Sí, continuar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        })
    }
</script>
</body>
</html>