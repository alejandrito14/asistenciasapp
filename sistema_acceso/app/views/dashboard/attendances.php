<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>
    <div class="flex-grow-1 bg-light" style="height: 100vh; overflow-y: auto;">
        <nav class="navbar navbar-light bg-white shadow-sm px-4 py-3">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1 fw-bold text-primary">Asistencias</span>
                <div class="d-flex align-items-center">
                    <i class="bi bi-check2-square fs-4 text-secondary"></i>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <form class="card shadow border-0 mb-3" method="GET" action="">
                <input type="hidden" name="c" value="Dashboard">
                <input type="hidden" name="a" value="attendances">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Grupo</label>
                            <select name="grupo_id" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach (($attendanceGroups ?? []) as $group): ?>
                                    <option value="<?php echo (int)$group['id']; ?>" <?php echo (($attendanceFilters['grupo_id'] ?? '') == (string)$group['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($group['nombre'] ?? ''); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Materia</label>
                            <select name="materia_id" class="form-select">
                                <option value="">Todas</option>
                                <?php foreach (($attendanceSubjects ?? []) as $subject): ?>
                                    <option value="<?php echo (int)$subject['id']; ?>" <?php echo (($attendanceFilters['materia_id'] ?? '') == (string)$subject['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(($subject['clave'] ?? '') . ' - ' . ($subject['nombre'] ?? '')); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Alumno</label>
                            <input
                                type="text"
                                class="form-control"
                                id="alumno_search"
                                list="attendanceStudentsList"
                                placeholder="Escribe matrícula o nombre"
                                value="<?php echo htmlspecialchars($attendanceFilters['alumno_text'] ?? ''); ?>"
                            >
                            <input type="hidden" name="alumno_id" id="alumno_id" value="<?php echo htmlspecialchars($attendanceFilters['alumno_id'] ?? ''); ?>">
                            <datalist id="attendanceStudentsList">
                                <?php foreach (($attendanceStudents ?? []) as $student): ?>
                                    <?php $studentLabel = trim(($student['matricula'] ?? '') . ' - ' . ($student['nombre'] ?? '') . ' ' . ($student['apellido_paterno'] ?? '') . ' ' . ($student['apellido_materno'] ?? '')); ?>
                                    <option value="<?php echo htmlspecialchars($studentLabel); ?>" data-id="<?php echo (int)$student['id']; ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="d-flex justify-content-end mb-3">
                <form action="?c=Dashboard&a=exportAttendances" method="POST" class="m-0">
                    <input type="hidden" name="grupo_id" value="<?php echo htmlspecialchars($attendanceFilters['grupo_id'] ?? ''); ?>">
                    <input type="hidden" name="materia_id" value="<?php echo htmlspecialchars($attendanceFilters['materia_id'] ?? ''); ?>">
                    <input type="hidden" name="alumno_id" value="<?php echo htmlspecialchars($attendanceFilters['alumno_id'] ?? ''); ?>">
                    <button type="submit" class="btn btn-success shadow">
                        <i class="bi bi-download me-2"></i> Exportar Excel
                    </button>
                </form>
            </div>

            <div class="card shadow border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-secondary"><i class="bi bi-check2-square"></i> Listado completo</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 bg-white">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Grupo</th>
                                    <th>Materia</th>
                                    <th>Horario</th>
                                    <th>Alumno</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th class="text-center">Asistencia</th>
                                    <th class="text-center">Falta</th>
                                    <th class="text-center">Justificado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentAttendances)): ?>
                                    <?php foreach ($recentAttendances as $item): ?>
                                        <?php
                                            $estado = strtoupper((string)($item['estado_nombre'] ?? ''));
                                            $justificacionStatus = strtoupper((string)($item['justificacion_estatus'] ?? ''));
                                            $isJustificado = in_array($justificacionStatus, ['APROBADO'], true) || in_array($estado, ['JUSTIFICADO', 'JUSTIFICADA'], true);
                                            $isAsistencia = !$isJustificado && in_array($estado, ['ASISTENCIA', 'ASISTIÓ', 'ASISTENTE'], true);
                                            $isFalta = !$isJustificado && $estado === 'FALTA';
                                            $studentName = trim(($item['alumno_nombre'] ?? '') . ' ' . ($item['apellido_paterno'] ?? '') . ' ' . ($item['apellido_materno'] ?? ''));
                                            $sessionDate = !empty($item['sesion_fecha']) ? date('d/m/Y', strtotime($item['sesion_fecha'])) : '-';
                                            $attendanceTime = !empty($item['hora_registro']) ? date('H:i', strtotime($item['hora_registro'])) : '-';
                                            $schedule = trim((string)($item['hora_inicio'] ?? '')) !== ''
                                                ? date('H:i', strtotime((string)$item['hora_inicio'])) . ' - ' . date('H:i', strtotime((string)$item['hora_fin']))
                                                : '-';
                                        ?>
                                        <tr>
                                            <td class="ps-3 fw-semibold"><?php echo htmlspecialchars($item['grupo_nombre'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($item['materia_nombre'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($schedule); ?></td>
                                            <td><?php echo htmlspecialchars($studentName !== '' ? $studentName : '-'); ?></td>
                                            <td><?php echo htmlspecialchars($sessionDate); ?></td>
                                            <td><?php echo htmlspecialchars($attendanceTime); ?></td>
                                            <td class="text-center"><?php echo $isAsistencia ? '<span class="badge bg-success">Sí</span>' : '<span class="text-muted">-</span>'; ?></td>
                                            <td class="text-center"><?php echo $isFalta ? '<span class="badge bg-danger">Sí</span>' : '<span class="text-muted">-</span>'; ?></td>
                                            <td class="text-center"><?php echo $isJustificado ? '<span class="badge bg-primary">Sí</span>' : '<span class="text-muted">-</span>'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">No hay asistencias registradas todavía.</td>
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
</body>
</html>
<script>
    (function () {
        const input = document.getElementById('alumno_search');
        const hidden = document.getElementById('alumno_id');
        const form = input ? input.closest('form') : null;
        const options = input ? Array.from(document.querySelectorAll('#attendanceStudentsList option')) : [];

        function syncAlumnoId() {
            if (!input || !hidden) return;
            const value = input.value.trim();
            const match = options.find((option) => option.value === value);
            hidden.value = match ? (match.dataset.id || '') : '';
        }

        if (input) {
            input.addEventListener('input', syncAlumnoId);
            input.addEventListener('change', syncAlumnoId);
        }

        if (form) {
            form.addEventListener('submit', syncAlumnoId);
        }
    })();
</script>
