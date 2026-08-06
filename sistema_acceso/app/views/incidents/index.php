<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bitácora de Incidencias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        .sidebar { min-height: 100vh; background-color: #212529; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: 0.3s; }
        .sidebar a:hover { background-color: #343a40; color: white; }
        .sidebar a.active { background-color: #0d6efd; color: white; border-left-color: white; }
        .sidebar i { width: 25px; }

        @media print {
            body * { visibility: hidden; }
            #reportSection, #reportSection * { visibility: visible; }
            #reportSection { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
            .badge { border: 1px solid #000; color: #000 !important; }
            a { text-decoration: none; color: black; } /* Links negros al imprimir */
        }
    </style>
</head>
<body class="bg-light">

<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-secondary"><i class="bi bi-journal-text"></i> Bitácora de Seguridad</h2>
            
            <div>
                <button class="btn btn-secondary me-2 shadow-sm" onclick="window.print()">
                    <i class="bi bi-printer"></i> Imprimir Reporte
                </button>
                <button class="btn btn-danger shadow" data-bs-toggle="modal" data-bs-target="#incidentModal">
                    <i class="bi bi-plus-lg"></i> Registrar Incidencia
                </button>
            </div>
        </div>

        <div class="card shadow-sm mb-4 border-0 no-print">
            <div class="card-body bg-white rounded">
                <form action="" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="c" value="Incident">
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Buscar</label>
                        <input type="text" name="search" class="form-control" placeholder="Descripción o usuario..." value="<?php echo isset($search)?$search:''; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Desde</label>
                        <input type="date" name="start" class="form-control" value="<?php echo isset($start)?$start:date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Hasta</label>
                        <input type="date" name="end" class="form-control" value="<?php echo isset($end)?$end:date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="reportSection">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Severidad</th>
                                <th>Incidencia</th>
                                <th>Evidencia</th> <th>Reportado Por</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($incidents) > 0): ?>
                                <?php foreach($incidents as $inc): ?>
                                <tr>
                                    <td style="white-space: nowrap;">
                                        <?php echo date('d/m/Y H:i', strtotime($inc['created_at'])); ?>
                                    </td>
                                    <td>
                                        <?php 
                                            if($inc['severity'] == 'alta') echo '<span class="badge bg-danger">ALTA</span>';
                                            elseif($inc['severity'] == 'media') echo '<span class="badge bg-warning text-dark">MEDIA</span>';
                                            else echo '<span class="badge bg-success">BAJA</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo $inc['title']; ?></div>
                                        <small class="text-muted"><?php echo $inc['description']; ?></small>
                                    </td>
                                    
                                    <td>
                                        <?php if(!empty($inc['attachment'])): ?>
                                            <a href="uploads/<?php echo $inc['attachment']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-paperclip"></i> Ver Adjunto
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Sin archivo</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <i class="bi bi-person-circle me-1"></i> <?php echo $inc['reporter']; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center p-4 text-muted">Sin incidencias registradas.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="incidentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Nueva Incidencia</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      
      <form action="?c=Incident&a=store" method="POST" enctype="multipart/form-data">
          <div class="modal-body">
            
            <div class="mb-3">
                <label class="form-label fw-bold">Título</label>
                <input type="text" name="title" class="form-control" required placeholder="Ej: Portón dañado">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Severidad</label>
                <select name="severity" class="form-select">
                    <option value="baja">🟢 Baja</option>
                    <option value="media">🟡 Media</option>
                    <option value="alta">🔴 Alta</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Descripción</label>
                <textarea name="description" class="form-control" rows="3" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Adjuntar Evidencia (Opcional)</label>
                <input type="file" name="attachment" class="form-control" accept="image/*,.pdf">
                <small class="text-muted">Fotos (JPG, PNG) o PDF</small>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger fw-bold">Registrar</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.get('msg') === 'guardado') Swal.fire('Registrado', 'La incidencia y el archivo han sido guardados.', 'success');
</script>

</body>
</html>