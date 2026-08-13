<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración</title>
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

    <div class="flex-grow-1 p-4">
        <h2 class="mb-4 fw-bold text-secondary"><i class="bi bi-gear-fill"></i> Configuración de la Escuela</h2>

        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                Configuración guardada correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow border-0" style="max-width: 900px;">
            <div class="card-header bg-primary text-white">
                <h5 class="m-0">Datos institucionales</h5>
            </div>
            <div class="card-body p-4">
                <form action="?c=Setting&a=update" method="POST" enctype="multipart/form-data">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre de la escuela</label>
                            <input type="text" name="school_name" class="form-control" value="<?php echo htmlspecialchars($school_name ?? ''); ?>" placeholder="Nombre de la institución">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">RFC</label>
                            <input type="text" name="school_rfc" class="form-control" value="<?php echo htmlspecialchars($school_rfc ?? ''); ?>" placeholder="RFC">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Lugar</label>
                            <input type="text" name="school_place" class="form-control" value="<?php echo htmlspecialchars($school_place ?? ''); ?>" placeholder="Ciudad / Estado">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Dirección</label>
                            <textarea name="school_address" class="form-control" rows="3" placeholder="Dirección completa"><?php echo htmlspecialchars($school_address ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Logo de la escuela</label>
                            <input type="file" name="school_logo" class="form-control" accept="image/*">
                            <small class="text-muted">PNG, JPG o WEBP.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Logo actual</label>
                            <div class="border rounded p-3 bg-light d-flex align-items-center justify-content-center" style="min-height: 120px;">
                                <?php if (!empty($school_logo)): ?>
                                    <img src="../public/<?php echo htmlspecialchars($school_logo); ?>" alt="Logo" style="max-height: 100px; max-width: 100%;">
                                <?php else: ?>
                                    <span class="text-muted">Sin logo cargado</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hora de entrada oficial oculta temporalmente -->
                    <div class="mb-3 d-none">
                        <label class="form-label fw-bold">Hora de Entrada Oficial</label>
                        <p class="text-muted small">Las marcas posteriores a esta hora se considerarán "Tardanzas".</p>
                        <input type="time" name="entry_time" class="form-control form-control-lg text-center fw-bold" 
                               value="<?php echo $entry_time; ?>" required>
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-primary w-100 btn-lg">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
