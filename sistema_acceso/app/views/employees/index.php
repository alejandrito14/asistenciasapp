<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Maestros</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .table-inactive { opacity: 0.65; background-color: #f8f9fa; }
        @media print {
            body * { visibility: hidden; }
            .modal-backdrop, .sidebar { display: none !important; }
            #printableArea, #printableArea * { visibility: visible; }
            #printableArea { position: absolute; left: 50%; top: 50px; transform: translateX(-50%); width: 350px; border: 2px solid #333; padding: 30px; border-radius: 15px; text-align: center; }
        }
    </style>
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>
    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0 fw-bold text-secondary"><i class="bi bi-person-badge-fill"></i> Lista de Maestros</h2>
                <small class="text-muted">Administra los perfiles de maestros registrados en la base de datos.</small>
            </div>
            <button class="btn btn-success shadow" data-bs-toggle="modal" data-bs-target="#newEmployeeModal">
                <i class="bi bi-plus-circle"></i> Nuevo Maestro
            </button>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'guardado'): ?>
            <div class="alert alert-success">Maestro guardado correctamente.</div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'actualizado'): ?>
            <div class="alert alert-success">Maestro actualizado correctamente.</div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'estado_cambiado'): ?>
            <div class="alert alert-success">Estado del maestro actualizado.</div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <form action="" method="GET" class="row g-2 align-items-center">
                    <input type="hidden" name="c" value="Employee">
                    <div class="col-auto"><label class="col-form-label fw-bold">Buscar:</label></div>
                    <div class="col-md-5">
                        <input type="text" name="q" class="form-control" placeholder="Nombre, apellidos, correo o teléfono..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
                        <?php if (!empty($_GET['q'])): ?>
                            <a href="?c=Employee" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Estado</th>
                            <th>Nombre Completo</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($employees)): ?>
                            <?php foreach ($employees as $emp): ?>
                                <tr class="<?php echo ((int)$emp['activo'] === 0) ? 'table-inactive' : ''; ?>">
                                    <td class="ps-4">
                                        <?php echo ((int)$emp['activo'] === 1) ? '<span class="badge bg-success">ACTIVO</span>' : '<span class="badge bg-danger">INACTIVO</span>'; ?>
                                    </td>
                                    <td class="fw-bold">
                                        <?php echo htmlspecialchars(trim($emp['nombre'] . ' ' . $emp['apellido_paterno'] . ' ' . $emp['apellido_materno'])); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($emp['correo'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($emp['telefono'] ?? ''); ?></td>
                                    <td class="text-end pe-4">
                                        <a href="?c=Employee&a=edit&id=<?php echo (int)$emp['id']; ?>" class="btn btn-sm btn-warning shadow-sm"><i class="bi bi-pencil-fill"></i></a>
                                        <?php if ((int)$emp['activo'] === 1): ?>
                                            <a href="?c=Employee&a=toggle&id=<?php echo (int)$emp['id']; ?>&status=1" class="btn btn-sm btn-outline-danger shadow-sm" onclick="return confirm('¿Desactivar este maestro?');"><i class="bi bi-power"></i></a>
                                        <?php else: ?>
                                            <a href="?c=Employee&a=toggle&id=<?php echo (int)$emp['id']; ?>&status=0" class="btn btn-sm btn-outline-success shadow-sm" onclick="return confirm('¿Reactivar este maestro?');"><i class="bi bi-power"></i></a>
                                        <?php endif; ?>
                                        <a href="?c=Employee&a=delete&id=<?php echo (int)$emp['id']; ?>" class="btn btn-sm btn-outline-danger shadow-sm" onclick="return confirm('¿Eliminar este maestro?');"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center p-5 text-muted">No hay maestros registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="newEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill"></i> Nuevo Maestro</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="?c=Employee&a=store" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Apellido paterno</label>
                            <input type="text" name="apellido_paterno" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Apellido materno</label>
                            <input type="text" name="apellido_materno" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Teléfono</label>
                            <input type="text" name="telefono" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Correo</label>
                            <input type="email" name="correo" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Contraseña de acceso</label>
                            <input type="password" name="password" class="form-control" required placeholder="Crea la clave inicial">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">Guardar Maestro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
