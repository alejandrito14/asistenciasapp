<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .table-inactive { opacity: 0.6; background-color: #f8f9fa; }
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
                    <span class="navbar-brand mb-0 h1 fw-bold text-primary">Usuarios del Sistema</span>
                    <div class="text-muted">Administra los accesos de usuarios y sus roles.</div>
                </div>
                <button class="btn btn-success shadow" data-bs-toggle="modal" data-bs-target="#newUserModal">
                    <i class="bi bi-plus-circle"></i> Nuevo Usuario
                </button>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <?php if(isset($_GET['msg']) && $_GET['msg']=='creado') echo "<div class='alert alert-success'>Usuario creado correctamente.</div>"; ?>
            <?php if(isset($_GET['msg']) && $_GET['msg']=='estado_cambiado') echo "<div class='alert alert-success'>Estado actualizado.</div>"; ?>
            <?php if(isset($_GET['err']) && $_GET['err']=='existe') echo "<div class='alert alert-danger'>El nombre de usuario ya existe.</div>"; ?>
            <?php if(isset($_GET['err']) && $_GET['err']=='self_disable') echo "<div class='alert alert-danger'>No puedes desactivar tu propia cuenta.</div>"; ?>

            <div class="card shadow border-0 mb-3">
                <div class="card-body py-3">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                        <div>
                            <h5 class="fw-bold mb-1">Usuarios registrados</h5>
                            <div class="text-muted">Controla quién entra al sistema y con qué rol lo hace.</div>
                        </div>
                        <div class="text-muted small">
                            <i class="bi bi-people-fill me-1"></i>
                            Total: <?php echo is_array($users) ? count($users) : 0; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow border-0">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0 bg-white">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">ID</th>
                                <th>Estado</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th class="text-end pe-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                            <?php $status = isset($u['status']) ? $u['status'] : 'activo'; ?>
                            <tr class="<?php echo ($status == 'inactivo') ? 'table-inactive' : ''; ?>">
                                <td class="ps-3 text-muted">#<?php echo $u['id']; ?></td>
                                <td>
                                    <?php if($status == 'activo'): ?>
                                        <span class="badge bg-success">ACTIVO</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">INACTIVO</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?php echo htmlspecialchars($u['username']); ?></td>
                                <td>
                                    <?php if($u['role'] == 'admin'): ?>
                                        <span class="badge bg-primary">ADMIN</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">GUARDIA</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex flex-wrap justify-content-end gap-1">
                                        <a href="?c=User&a=edit&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-warning shadow-sm">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                                            <?php if($status == 'activo'): ?>
                                                <a href="?c=User&a=toggle&id=<?php echo $u['id']; ?>&status=activo" class="btn btn-sm btn-outline-danger shadow-sm" onclick="confirmarAccion(event, this.href, '¿Desactivar Acceso?', 'Este usuario no podrá iniciar sesión.', 'warning')">
                                                    <i class="bi bi-power"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="?c=User&a=toggle&id=<?php echo $u['id']; ?>&status=inactivo" class="btn btn-sm btn-outline-success shadow-sm" onclick="confirmarAccion(event, this.href, '¿Reactivar Acceso?', 'El usuario podrá volver a entrar.', 'success')">
                                                    <i class="bi bi-power"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
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

<div class="modal fade" id="newUserModal" tabindex="-1" aria-labelledby="newUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="newUserModalLabel"><i class="bi bi-person-plus-fill"></i> Nuevo Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="?c=User&a=store" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">Usuario</label>
                            <input type="text" name="username" class="form-control" required placeholder="ej: guardia1">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">Contraseña</label>
                            <input type="password" name="password" class="form-control" required placeholder="******">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">Rol</label>
                            <select name="role" class="form-select">
                                <option value="guardia">Guardia</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">Crear Usuario</button>
                </div>
            </form>
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
