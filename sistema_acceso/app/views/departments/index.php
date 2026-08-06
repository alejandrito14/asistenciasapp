<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Departamentos</title>
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
        <h2 class="mb-4 fw-bold text-secondary">🏢 Departamentos y Áreas</h2>

        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>Operación exitosa.</div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-primary text-white"><h5 class="m-0"><i class="bi bi-plus-lg"></i> Nuevo Departamento</h5></div>
                    <div class="card-body">
                        <form action="?c=Department&a=store" method="POST">
                            <div class="mb-3"><label class="form-label">Nombre</label><input type="text" name="name" class="form-control" required></div>
                            <div class="d-grid"><button type="submit" class="btn btn-primary">Guardar</button></div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th class="ps-4">Estado</th><th>Nombre</th><th class="text-end pe-4">Acciones</th></tr></thead>
                            <tbody>
                                <?php if(isset($departments) && count($departments) > 0): ?>
                                    <?php foreach($departments as $dept): ?>
                                    <tr class="<?php echo (isset($dept['status']) && $dept['status'] == 'inactivo') ? 'table-inactive' : ''; ?>">
                                        <td class="ps-4"><?php echo (isset($dept['status']) && $dept['status'] == 'inactivo') ? '<span class="badge bg-danger">INACTIVO</span>' : '<span class="badge bg-success">ACTIVO</span>'; ?></td>
                                        <td class="fw-bold"><?php echo $dept['name']; ?></td>
                                        <td class="text-end pe-4">
                                            <a href="?c=Department&a=edit&id=<?php echo $dept['id']; ?>" class="btn btn-sm btn-outline-warning me-1"><i class="bi bi-pencil-fill"></i></a>
                                            <?php if(isset($dept['status']) && $dept['status'] == 'inactivo'): ?>
                                                <a href="?c=Department&a=toggle&id=<?php echo $dept['id']; ?>&status=inactivo" class="btn btn-sm btn-outline-success me-1" onclick="confirmarAccion(event, this.href, '¿Reactivar?', 'Visible.', 'success')"><i class="bi bi-power"></i></a>
                                            <?php else: ?>
                                                <a href="?c=Department&a=toggle&id=<?php echo $dept['id']; ?>&status=activo" class="btn btn-sm btn-outline-danger me-1" onclick="confirmarAccion(event, this.href, '¿Desactivar?', 'No visible.', 'warning')"><i class="bi bi-power"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?><tr><td colspan="3" class="text-center p-4">Sin datos.</td></tr><?php endif; ?>
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
        Swal.fire({ title: titulo, text: mensaje, icon: icono, showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33', confirmButtonText: 'Si', cancelButtonText: 'No' }).then((r) => { if (r.isConfirmed) window.location.href = url; })
    }
</script>
</body>
</html>