<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        /* Estilos del Sidebar para que coincidan con el layout */
        .sidebar { min-height: 100vh; background-color: #212529; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: 0.3s; }
        .sidebar a:hover { background-color: #343a40; color: white; }
        .sidebar a.active { background-color: #0d6efd; color: white; border-left-color: white; }
        .sidebar i { width: 25px; }
        .stat-card {
            border: 0;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            border-radius: 20px;
            overflow: hidden;
        }
        .stat-icon {
            width: 76px;
            min-height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .justification-evidence {
            max-width: 100%;
            max-height: 320px;
            object-fit: contain;
            border-radius: 16px;
            background: #f8f9fa;
        }
    </style>
</head>
<body>

<div class="d-flex">
    
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 bg-light" style="height: 100vh; overflow-y: auto;">
        <nav class="navbar navbar-light bg-white shadow-sm px-4 py-3">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1 fw-bold text-primary">Bienvenido, Administrador</span>
                <div class="d-flex align-items-center">
                    <i class="bi bi-person-circle fs-4 text-secondary"></i>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">
            
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card h-100">
                        <div class="card-body p-0 d-flex align-items-stretch">
                            <div class="stat-icon bg-primary text-white">
                                <i class="bi bi-diagram-3 fs-1"></i>
                            </div>
                            <div class="p-4 flex-grow-1">
                                <h6 class="text-muted text-uppercase fw-bold mb-1">Grupos</h6>
                                <h2 class="mb-0 fw-bold text-dark"><?php echo (int)($totalGroups ?? 0); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card h-100">
                        <div class="card-body p-0 d-flex align-items-stretch">
                            <div class="stat-icon bg-success text-white">
                                <i class="bi bi-journal-bookmark-fill fs-1"></i>
                            </div>
                            <div class="p-4 flex-grow-1">
                                <h6 class="text-muted text-uppercase fw-bold mb-1">Materias</h6>
                                <h2 class="mb-0 fw-bold text-dark"><?php echo (int)($totalSubjects ?? 0); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card h-100">
                        <div class="card-body p-0 d-flex align-items-stretch">
                            <div class="stat-icon bg-warning text-white">
                                <i class="bi bi-people-fill fs-1"></i>
                            </div>
                            <div class="p-4 flex-grow-1">
                                <h6 class="text-muted text-uppercase fw-bold mb-1">Alumnos</h6>
                                <h2 class="mb-0 fw-bold text-dark"><?php echo (int)($totalStudents ?? 0); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card h-100">
                        <div class="card-body p-0 d-flex align-items-stretch">
                            <div class="stat-icon bg-danger text-white">
                                <i class="bi bi-file-earmark-text fs-1"></i>
                            </div>
                            <div class="p-4 flex-grow-1">
                                <h6 class="text-muted text-uppercase fw-bold mb-1">Justificantes</h6>
                                <h2 class="mb-0 fw-bold text-dark"><?php echo isset($recentJustifications) ? count($recentJustifications) : 0; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card shadow border-0">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold text-secondary"><i class="bi bi-file-earmark-text"></i> Justificantes recientes</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Alumno</th>
                                            <th>Grupo</th>
                                            <th>Materia</th>
                                            <th>Fecha de sesión</th>
                                            <th>Día y hora</th>
                                            <th>Motivo</th>
                                            <th>Estado</th>
                                            <th class="text-end pe-3">Fecha envío</th>
                                            <th class="text-end pe-3">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recentJustifications)): ?>
                                            <?php foreach ($recentJustifications as $item): ?>
                                                <?php
                                                    $status = strtoupper((string)($item['estatus'] ?? 'PENDIENTE'));
                                                    $badgeClass = $status === 'APROBADO' ? 'bg-success' : ($status === 'RECHAZADO' ? 'bg-danger' : 'bg-warning text-dark');
                                                    $evidencePath = trim((string)($item['evidencia'] ?? ''));
                                                    $evidenceUrl = $evidencePath !== '' ? '../public/' . ltrim($evidencePath, '/') : '';
                                                    $isImage = $evidenceUrl !== '' && preg_match('/\.(jpg|jpeg|png|gif|webp|bmp)$/i', $evidenceUrl);
                                                    $sessionDate = !empty($item['sesion_fecha']) ? date('d/m/Y', strtotime($item['sesion_fecha'])) : '-';
                                                    $sessionDay = strtoupper((string)($item['dia_sesion'] ?? $item['dia_semana'] ?? ''));
                                                    $sessionTime = trim((string)($item['hora_inicio'] ?? '')) !== '' ? date('H:i', strtotime((string)$item['hora_inicio'])) : '-';
                                                    $sessionEndTime = trim((string)($item['hora_fin'] ?? '')) !== '' ? date('H:i', strtotime((string)$item['hora_fin'])) : '-';
                                                ?>
                                                <tr>
                                                    <td class="ps-3 fw-semibold">
                                                        <?php echo htmlspecialchars(trim(($item['alumno_nombre'] ?? '') . ' ' . ($item['apellido_paterno'] ?? '') . ' ' . ($item['apellido_materno'] ?? ''))); ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($item['grupo_nombre'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($item['materia_nombre'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($sessionDate); ?></td>
                                                    <td><?php echo htmlspecialchars(trim($sessionDay . ' ' . $sessionTime . ' - ' . $sessionEndTime)); ?></td>
                                                    <td style="max-width: 280px;">
                                                        <div class="text-truncate" title="<?php echo htmlspecialchars($item['motivo'] ?? ''); ?>">
                                                            <?php echo htmlspecialchars($item['motivo'] ?? '-'); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($status); ?></span>
                                                    </td>
                                                    <td class="text-end pe-3">
                                                        <?php echo !empty($item['created_at']) ? date('d/m/Y H:i', strtotime($item['created_at'])) : '-'; ?>
                                                    </td>
                                                    <td class="text-end pe-3">
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-primary justification-action-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#justificationModal"
                                                            data-justification-id="<?php echo (int)($item['id'] ?? 0); ?>"
                                                            data-justification-student="<?php echo htmlspecialchars(trim(($item['alumno_nombre'] ?? '') . ' ' . ($item['apellido_paterno'] ?? '') . ' ' . ($item['apellido_materno'] ?? ''))); ?>"
                                                            data-justification-group="<?php echo htmlspecialchars($item['grupo_nombre'] ?? '-'); ?>"
                                                            data-justification-subject="<?php echo htmlspecialchars($item['materia_nombre'] ?? '-'); ?>"
                                                            data-justification-motive="<?php echo htmlspecialchars($item['motivo'] ?? '-'); ?>"
                                                            data-justification-status="<?php echo htmlspecialchars($status); ?>"
                                                            data-justification-session-date="<?php echo htmlspecialchars($sessionDate); ?>"
                                                            data-justification-session-day="<?php echo htmlspecialchars($sessionDay ?: '-'); ?>"
                                                            data-justification-session-time="<?php echo htmlspecialchars($sessionTime); ?>"
                                                            data-justification-session-end-time="<?php echo htmlspecialchars($sessionEndTime); ?>"
                                                            data-justification-evidence="<?php echo htmlspecialchars($evidenceUrl); ?>"
                                                            data-justification-evidence-type="<?php echo $isImage ? 'image' : 'file'; ?>"
                                                        >
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center py-5 text-muted">No hay justificantes registrados todavía.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="justificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Revisar justificante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="?c=Dashboard&a=updateJustification">
                <div class="modal-body">
                    <input type="hidden" name="justification_id" id="justification_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="small text-muted">Alumno</div>
                            <div id="justification_student" class="fw-semibold"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Estado actual</div>
                            <div id="justification_status" class="fw-semibold"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Grupo</div>
                            <div id="justification_group" class="fw-semibold"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Materia</div>
                            <div id="justification_subject" class="fw-semibold"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Fecha de sesión</div>
                            <div id="justification_session_date" class="fw-semibold"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Día y hora</div>
                            <div id="justification_session_daytime" class="fw-semibold"></div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted">Motivo</div>
                            <div id="justification_motive" class="fw-semibold"></div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted mb-2">Evidencia</div>
                            <div id="justification_evidence_container" class="border rounded-4 p-3 text-center">
                                <div id="justification_evidence_empty" class="text-muted">No hay evidencia adjunta.</div>
                                <img id="justification_evidence_image" class="justification-evidence d-none" alt="Evidencia del justificante">
                                <a id="justification_evidence_file" class="btn btn-outline-secondary d-none" href="#" target="_blank" rel="noopener">Abrir archivo</a>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="estatus" class="form-label fw-semibold">Cambiar estatus</label>
                            <select class="form-select" name="estatus" id="estatus" required>
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="APROBADO">APROBADO</option>
                                <option value="RECHAZADO">RECHAZADO</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const justificationModal = document.getElementById('justificationModal');
    if (justificationModal) {
        justificationModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            const setText = (id, value) => {
                const element = document.getElementById(id);
                if (element) element.textContent = value || '-';
            };

            const idField = document.getElementById('justification_id');
            const statusField = document.getElementById('estatus');
            const evidenceImage = document.getElementById('justification_evidence_image');
            const evidenceFile = document.getElementById('justification_evidence_file');
            const evidenceEmpty = document.getElementById('justification_evidence_empty');

            if (idField) idField.value = button.getAttribute('data-justification-id') || '';
            if (statusField) statusField.value = button.getAttribute('data-justification-status') || 'PENDIENTE';

            setText('justification_student', button.getAttribute('data-justification-student'));
            setText('justification_group', button.getAttribute('data-justification-group'));
            setText('justification_subject', button.getAttribute('data-justification-subject'));
            setText('justification_session_date', button.getAttribute('data-justification-session-date'));
            const sessionDay = button.getAttribute('data-justification-session-day') || '-';
            const sessionTime = button.getAttribute('data-justification-session-time') || '-';
            const sessionEndTime = button.getAttribute('data-justification-session-end-time') || '-';
            setText('justification_session_daytime', `${sessionDay} ${sessionTime} - ${sessionEndTime}`);
            setText('justification_motive', button.getAttribute('data-justification-motive'));
            setText('justification_status', button.getAttribute('data-justification-status'));

            const evidenceUrl = button.getAttribute('data-justification-evidence') || '';
            const evidenceType = button.getAttribute('data-justification-evidence-type') || 'file';

            if (evidenceImage) {
                evidenceImage.classList.add('d-none');
                evidenceImage.removeAttribute('src');
            }
            if (evidenceFile) {
                evidenceFile.classList.add('d-none');
                evidenceFile.removeAttribute('href');
            }
            if (evidenceEmpty) {
                evidenceEmpty.classList.add('d-none');
            }

            if (evidenceUrl) {
                if (evidenceType === 'image' && evidenceImage) {
                    evidenceImage.src = evidenceUrl;
                    evidenceImage.classList.remove('d-none');
                } else if (evidenceFile) {
                    evidenceFile.href = evidenceUrl;
                    evidenceFile.classList.remove('d-none');
                }
            } else if (evidenceEmpty) {
                evidenceEmpty.classList.remove('d-none');
            }
        });
    }
</script>
</body>
</html>
