<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
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
                    <span class="navbar-brand mb-0 h1 fw-bold text-primary">Configuración</span>
                    <div class="text-muted">Actualiza los datos institucionales y del sistema.</div>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    Configuración guardada correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow border-0">
                <div class="card-body py-3">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                        <div>
                            <h5 class="fw-bold mb-1">Datos institucionales</h5>
                            <div class="text-muted">Mantén actualizada la información de la escuela.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow border-0 mt-3">
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
                    <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
                </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
