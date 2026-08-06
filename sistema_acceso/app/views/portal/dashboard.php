<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        /* =================================================
           ESTILOS DE IMPRESIÓN (CORREGIDOS)
           ================================================= */
        @media print {
            /* 1. Configuración de la hoja limpia */
            @page { margin: 0; size: auto; }
            body { margin: 0; padding: 0; }

            /* 2. Ocultar todo lo que no sea el carnet */
            body * { visibility: hidden; }
            .navbar, .btn, .card-footer, .card-header, .col-md-8 { display: none !important; }

            /* 3. Hacer visible el carnet */
            #printableArea, #printableArea * { visibility: visible; }

            /* 4. POSICIONAMIENTO SEGURO (Sin recorte superior) */
            #printableArea {
                position: absolute;
                left: 50%;
                top: 50px; /* MARGEN SUPERIOR FIJO (Soluciona el recorte) */
                transform: translateX(-50%); /* Solo centrado horizontal */
                
                width: 350px;
                /* Altura automática para ajustarse al contenido */
                height: auto;
                min-height: 450px;
                
                border: 3px solid #000; /* Borde más grueso y visible */
                border-radius: 20px;
                padding: 40px 20px;
                
                background-color: white;
                text-align: center;
                z-index: 9999;
            }

            /* Ajuste del QR */
            #qrImage {
                width: 220px !important;
                height: 220px !important;
                margin-bottom: 20px;
                display: block;
                margin-left: auto;
                margin-right: auto;
            }
            
            /* Ajuste de textos */
            h2 { 
                font-size: 32px !important; 
                margin: 15px 0 !important; 
                color: #000 !important; 
                font-weight: bold !important;
            }
            
            /* Estilo del código (Badge) */
            .badge { 
                border: 2px solid #000; 
                color: #000 !important; 
                background: transparent !important; 
                font-size: 20px !important; 
                padding: 10px 30px;
                border-radius: 50px;
            }
            
            /* Ocultar texto pequeño de ayuda */
            p.small { display: none; } 
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <span class="navbar-brand fw-bold"><i class="bi bi-person-workspace"></i> Mi Portal</span>
    <div class="d-flex align-items-center gap-2">
        <span class="text-white me-2 d-none d-md-block">Hola, <?php echo $empName; ?></span>
        
        <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#passModal">
            <i class="bi bi-key-fill"></i>
        </button>
        
        <a href="?c=Portal&a=logout" class="btn btn-sm btn-light text-primary fw-bold">Salir</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <div class="row">
        
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 text-center h-100">
                <div class="card-header bg-white fw-bold text-muted">MI CREDENCIAL</div>
                
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    
                    <div id="printableArea">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?php echo $empCode; ?>" 
                             id="qrImage" class="img-fluid border p-2 rounded mb-3" alt="QR">
                        
                        <h2 class="fw-bold text-primary mb-2"><?php echo $empName; ?></h2>
                        
                        <span class="badge bg-secondary fs-5"><?php echo $empCode; ?></span>
                        
                        <p class="text-muted small mt-3 mb-0">Presenta este código en el kiosco.</p>
                    </div>

                </div>
                
                <div class="card-footer bg-white border-0">
                    <button class="btn btn-outline-primary w-100" onclick="window.print()">
                        <i class="bi bi-printer"></i> Imprimir / Descargar
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold text-muted d-flex justify-content-between">
                    <span>MIS ASISTENCIAS (Este Mes)</span>
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr><th class="ps-3">Fecha</th><th>Entrada</th><th>Salida</th><th>Horas</th></tr>
                            </thead>
                            <tbody>
                                <?php if(count($myLogs) > 0): ?>
                                    <?php foreach($myLogs as $log): ?>
                                    <tr>
                                        <td class="ps-3"><?php echo date('d/m/Y', strtotime($log['date_log'])); ?></td>
                                        <td class="text-success fw-bold"><?php echo date('H:i', strtotime($log['check_in_time'])); ?></td>
                                        <td class="text-danger"><?php echo ($log['check_out_time']) ? date('H:i', strtotime($log['check_out_time'])) : 'En curso'; ?></td>
                                        <td><?php if($log['check_out_time']) { $diff = (new DateTime($log['check_in_time']))->diff(new DateTime($log['check_out_time'])); echo $diff->format('%Hh %Im'); } else { echo '--'; } ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center p-4 text-muted">No tienes registros este mes.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="passModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock"></i> Cambiar Contraseña</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="?c=Portal&a=change_password" method="POST">
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Contraseña Actual</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            <hr>
            <div class="mb-3">
                <label class="form-label">Nueva Contraseña</label>
                <input type="password" name="new_password" class="form-control" required minlength="6">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirmar Nueva</label>
                <input type="password" name="confirm_password" class="form-control" required minlength="6">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-warning fw-bold">Actualizar</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const err = urlParams.get('err');

    if(msg === 'pass_actualizada') Swal.fire('¡Éxito!', 'Contraseña actualizada.', 'success');
    if(err === 'pass_incorrecta') Swal.fire('Error', 'Contraseña actual incorrecta.', 'error');
    if(err === 'no_coinciden') Swal.fire('Error', 'Las nuevas contraseñas no coinciden.', 'error');
</script>

</body>
</html>