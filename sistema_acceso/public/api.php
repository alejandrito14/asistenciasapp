<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
date_default_timezone_set('America/Mexico_City');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once '../app/config/db.php';
require_once '../app/models/Usuario.php';
require_once '../app/models/Maestro.php';
require_once '../app/models/Alumno.php';
require_once '../app/helpers/SchoolCalendarHelper.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode([
        'success' => false,
        'message' => 'No se pudo conectar a la base de datos.'
    ]);
    exit;
}

$rawInput = file_get_contents('php://input');
$body = json_decode($rawInput, true);
if (!is_array($body)) {
    $body = $_POST;
}

$action = $_GET['action'] ?? ($body['action'] ?? '');

if ($action === 'login') {
    $correo = trim((string)($body['correo'] ?? ''));
    $password = (string)($body['password'] ?? '');

    if ($correo === '' || $password === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Correo y contraseña son obligatorios.'
        ]);
        exit;
    }

    $usuarioModel = new Usuario($db);
    $user = $usuarioModel->login($correo, $password);

    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Credenciales inválidas o usuario inactivo.'
        ]);
        exit;
    }

    $payloadUser = [
        'id' => (int)$user['id'],
        'nombre' => $user['nombre'],
        'correo' => $user['correo'],
        'rol' => $user['rol'],
    ];

    if ($user['rol'] === 'MAESTRO') {
        $stmt = $db->prepare("SELECT id, usuario_id, nombre, apellido_paterno, apellido_materno, telefono, correo, activo FROM maestros WHERE usuario_id = :usuario_id LIMIT 1");
        $stmt->bindValue(':usuario_id', (int)$user['id'], PDO::PARAM_INT);
        $stmt->execute();
        $payloadUser['maestro'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } elseif ($user['rol'] === 'ALUMNO') {
        $stmt = $db->prepare("SELECT id, matricula, nombre, apellido_paterno, apellido_materno, telefono, correo, activo FROM alumnos WHERE correo = :correo LIMIT 1");
        $stmt->bindValue(':correo', $correo);
        $stmt->execute();
        $payloadUser['alumno'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Login correcto.',
        'user' => $payloadUser,
    ]);
    exit;
}

if ($action === 'teacherDashboard') {
    $maestroId = (int)($body['maestro_id'] ?? 0);
    if ($maestroId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Maestro no válido.']);
        exit;
    }

    if (!isSchoolDay($db, date('Y-m-d'))) {
        echo json_encode(['success' => true, 'message' => 'Día inhábil.', 'data' => []]);
        exit;
    }

    $stmt = $db->prepare(
        "SELECT gmm.id,
                g.nombre AS grupo_nombre, g.semestre, g.turno, g.ciclo_escolar,
                m.clave AS materia_clave, m.nombre AS materia_nombre,
                h.dia_semana, h.hora_inicio, h.hora_fin, h.aula,
                sc.id AS sesion_id,
                sc.estatus AS sesion_estatus,
                sc.hora_inicio_real,
                sc.hora_fin_real
         FROM grupo_materia_maestro gmm
         INNER JOIN grupo_materias gm ON gmm.grupo_materia_id = gm.id
         INNER JOIN grupos g ON gm.grupo_id = g.id
         INNER JOIN materias m ON gm.materia_id = m.id
         LEFT JOIN horarios h ON h.grupo_materia_maestro_id = gmm.id AND h.activo = 1
         LEFT JOIN sesiones_clase sc ON sc.grupo_materia_maestro_id = gmm.id AND sc.fecha = CURDATE()
         WHERE gmm.maestro_id = :maestro_id AND gmm.activo = 1 AND g.activo = 1 AND m.activo = 1
         ORDER BY g.nombre ASC, m.nombre ASC, h.dia_semana ASC, h.hora_inicio ASC"
    );
    $stmt->bindValue(':maestro_id', $maestroId, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'message' => 'OK', 'data' => $items]);
    exit;
}

if ($action === 'teacherToggleSession') {
    $gmmId = (int)($body['grupo_materia_maestro_id'] ?? 0);
    $usuarioId = (int)($body['usuario_id'] ?? 0);
    $latitud = isset($body['latitud']) ? (float)$body['latitud'] : null;
    $longitud = isset($body['longitud']) ? (float)$body['longitud'] : null;
    if ($gmmId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Clase no válida.']);
        exit;
    }

    if (!isSchoolDay($db, date('Y-m-d'))) {
        echo json_encode(['success' => false, 'message' => 'No se pueden abrir sesiones en días inhábiles o vacaciones.']);
        exit;
    }

    $stmt = $db->prepare(
        "SELECT id, estatus, hora_inicio_real, hora_fin_real
         FROM sesiones_clase
         WHERE grupo_materia_maestro_id = :gmm_id
           AND fecha = CURDATE()
         LIMIT 1"
    );
    $stmt->bindValue(':gmm_id', $gmmId, PDO::PARAM_INT);
    $stmt->execute();
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($session) {
        if (($session['estatus'] ?? 'ABIERTA') === 'ABIERTA') {
            $stmt = $db->prepare(
                "UPDATE sesiones_clase
                 SET estatus = 'CERRADA', hora_fin_real = NOW()
                 WHERE id = :id"
            );
            $stmt->bindValue(':id', (int)$session['id'], PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Sesión cerrada correctamente.', 'data' => ['estatus' => 'CERRADA']]);
            exit;
        }

        $stmt = $db->prepare(
            "UPDATE sesiones_clase
             SET estatus = 'ABIERTA',
                 hora_inicio_real = COALESCE(hora_inicio_real, NOW()),
                 hora_fin_real = NULL,
                 latitud = COALESCE(:latitud, latitud),
                 longitud = COALESCE(:longitud, longitud),
                 usuario_id = COALESCE(:usuario_id, usuario_id)
             WHERE id = :id"
        );
        $stmt->bindValue(':id', (int)$session['id'], PDO::PARAM_INT);
        $stmt->bindValue(':usuario_id', $usuarioId > 0 ? $usuarioId : null, $usuarioId > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);
        if ($latitud !== null) {
            $stmt->bindValue(':latitud', $latitud);
        } else {
            $stmt->bindValue(':latitud', null, PDO::PARAM_NULL);
        }
        if ($longitud !== null) {
            $stmt->bindValue(':longitud', $longitud);
        } else {
            $stmt->bindValue(':longitud', null, PDO::PARAM_NULL);
        }
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Sesión abierta correctamente.', 'data' => ['estatus' => 'ABIERTA']]);
        exit;
    }

    $stmt = $db->prepare(
        "INSERT INTO sesiones_clase (grupo_materia_maestro_id, fecha, hora_inicio_real, latitud, longitud, estatus, usuario_id, created_at)
         VALUES (:gmm_id, CURDATE(), NOW(), :latitud, :longitud, 'ABIERTA', :usuario_id, NOW())"
    );
    $stmt->bindValue(':gmm_id', $gmmId, PDO::PARAM_INT);
    $stmt->bindValue(':usuario_id', $usuarioId > 0 ? $usuarioId : null, $usuarioId > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);
    if ($latitud !== null) {
        $stmt->bindValue(':latitud', $latitud);
    } else {
        $stmt->bindValue(':latitud', null, PDO::PARAM_NULL);
    }
    if ($longitud !== null) {
        $stmt->bindValue(':longitud', $longitud);
    } else {
        $stmt->bindValue(':longitud', null, PDO::PARAM_NULL);
    }
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Sesión abierta correctamente.', 'data' => ['estatus' => 'ABIERTA']]);
    exit;
}

if ($action === 'teacherClassStudents') {
    $gmmId = (int)($body['grupo_materia_maestro_id'] ?? 0);
    if ($gmmId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Clase no válida.']);
        exit;
    }

    if (!isSchoolDay($db, date('Y-m-d'))) {
        echo json_encode(['success' => false, 'message' => 'No hay clases activas en esta fecha.']);
        exit;
    }

    $stmt = $db->prepare(
        "SELECT gmm.id AS grupo_materia_maestro_id,
                g.id AS grupo_id,
                g.nombre AS grupo_nombre,
                g.semestre,
                g.turno,
                g.ciclo_escolar,
                m.id AS materia_id,
                m.clave AS materia_clave,
                m.nombre AS materia_nombre,
                ma.nombre AS maestro_nombre,
                ma.apellido_paterno,
                ma.apellido_materno,
                h.dia_semana,
                h.hora_inicio,
                h.hora_fin,
                h.aula
         FROM grupo_materia_maestro gmm
         INNER JOIN grupo_materias gm ON gmm.grupo_materia_id = gm.id
         INNER JOIN grupos g ON gm.grupo_id = g.id
         INNER JOIN materias m ON gm.materia_id = m.id
         LEFT JOIN maestros ma ON gmm.maestro_id = ma.id
         LEFT JOIN horarios h ON h.grupo_materia_maestro_id = gmm.id AND h.activo = 1
         WHERE gmm.id = :gmm_id
         LIMIT 1"
    );
    $stmt->bindValue(':gmm_id', $gmmId, PDO::PARAM_INT);
    $stmt->execute();
    $classInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$classInfo) {
        echo json_encode(['success' => false, 'message' => 'No se encontró la clase.']);
        exit;
    }

    $stmt = $db->prepare(
        "SELECT ag.alumno_id,
                a.nombre,
                a.apellido_paterno,
                a.apellido_materno,
                a.matricula,
                a.correo,
                COALESCE(ca.nombre, '') AS estado_actual,
                COALESCE(asis.observaciones, '') AS observaciones
         FROM alumnos_grupos ag
         INNER JOIN alumnos a ON ag.alumno_id = a.id
         LEFT JOIN sesiones_clase sc ON sc.grupo_materia_maestro_id = :gmm_id AND sc.fecha = CURDATE()
         LEFT JOIN asistencias asis ON asis.sesion_clase_id = sc.id AND asis.alumno_id = a.id
         LEFT JOIN catalogo_asistencia ca ON ca.id = asis.estado_id
         WHERE ag.grupo_id = :grupo_id
           AND a.activo = 1
           AND ag.fecha_baja IS NULL
         ORDER BY a.apellido_paterno ASC, a.apellido_materno ASC, a.nombre ASC"
    );
    $stmt->bindValue(':gmm_id', $gmmId, PDO::PARAM_INT);
    $stmt->bindValue(':grupo_id', (int)$classInfo['grupo_id'], PDO::PARAM_INT);
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $states = $db->query("SELECT id, nombre FROM catalogo_asistencia ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'OK',
        'data' => [
            'class' => $classInfo,
            'students' => $students,
            'states' => $states,
        ],
    ]);
    exit;
}

if ($action === 'teacherSaveManualAttendance') {
    $gmmId = (int)($body['grupo_materia_maestro_id'] ?? 0);
    $attendance = $body['attendance'] ?? [];
    if ($gmmId <= 0 || !is_array($attendance)) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
        exit;
    }

    if (!isSchoolDay($db, date('Y-m-d'))) {
        echo json_encode(['success' => false, 'message' => 'No se puede guardar asistencia en un día inhábil.']);
        exit;
    }

    $stmt = $db->prepare("SELECT id FROM sesiones_clase WHERE grupo_materia_maestro_id = :gmm_id AND fecha = CURDATE() LIMIT 1");
    $stmt->bindValue(':gmm_id', $gmmId, PDO::PARAM_INT);
    $stmt->execute();
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($session) {
        $sesionId = (int)$session['id'];
    } else {
        $stmt = $db->prepare("INSERT INTO sesiones_clase (grupo_materia_maestro_id, fecha, estatus, usuario_id, created_at) VALUES (:gmm_id, CURDATE(), 'ABIERTA', NULL, NOW())");
        $stmt->bindValue(':gmm_id', $gmmId, PDO::PARAM_INT);
        $stmt->execute();
        $sesionId = (int)$db->lastInsertId();
    }

    $db->beginTransaction();
    try {
        foreach ($attendance as $row) {
            $alumnoId = (int)($row['alumno_id'] ?? 0);
            $estadoId = (int)($row['estado_id'] ?? 0);
            $observaciones = trim((string)($row['observaciones'] ?? ''));
            if ($alumnoId <= 0 || $estadoId <= 0) {
                continue;
            }

            $stmt = $db->prepare(
                "INSERT INTO asistencias (sesion_clase_id, alumno_id, estado_id, observaciones)
                 VALUES (:sesion_id, :alumno_id, :estado_id, :observaciones)
                 ON DUPLICATE KEY UPDATE estado_id = VALUES(estado_id), observaciones = VALUES(observaciones), hora_registro = CURRENT_TIMESTAMP"
            );
            $stmt->bindValue(':sesion_id', $sesionId, PDO::PARAM_INT);
            $stmt->bindValue(':alumno_id', $alumnoId, PDO::PARAM_INT);
            $stmt->bindValue(':estado_id', $estadoId, PDO::PARAM_INT);
            $stmt->bindValue(':observaciones', $observaciones);
            $stmt->execute();
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Asistencia guardada correctamente.']);
        exit;
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'No se pudo guardar la asistencia.']);
        exit;
    }
}

if ($action === 'teacherSessionJustifications') {
    $sesionClaseId = (int)($body['sesion_clase_id'] ?? 0);
    if ($sesionClaseId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Sesión no válida.']);
        exit;
    }

    $stmt = $db->prepare(
        "SELECT j.id,
                j.alumno_id,
                j.sesion_clase_id,
                j.motivo,
                j.evidencia,
                j.estatus,
                j.fecha_revision,
                j.revisado_por,
                a.matricula,
                a.nombre AS alumno_nombre,
                a.apellido_paterno,
                a.apellido_materno
         FROM justificaciones j
         INNER JOIN alumnos a ON a.id = j.alumno_id
         WHERE j.sesion_clase_id = :sesion_clase_id
         ORDER BY j.id DESC"
    );
    $stmt->bindValue(':sesion_clase_id', $sesionClaseId, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'OK',
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
    ]);
    exit;
}

if ($action === 'teacherJustifications') {
    $maestroId = (int)($body['maestro_id'] ?? 0);
    if ($maestroId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Maestro no válido.']);
        exit;
    }

    $stmt = $db->prepare(
        "SELECT j.id,
                j.alumno_id,
                j.sesion_clase_id,
                j.motivo,
                j.evidencia,
                j.estatus,
                j.created_at,
                j.fecha_revision,
                sc.fecha AS sesion_fecha,
                CASE DAYOFWEEK(sc.fecha)
                   WHEN 1 THEN 'DOMINGO'
                   WHEN 2 THEN 'LUNES'
                   WHEN 3 THEN 'MARTES'
                   WHEN 4 THEN 'MIERCOLES'
                   WHEN 5 THEN 'JUEVES'
                   WHEN 6 THEN 'VIERNES'
                   WHEN 7 THEN 'SABADO'
                END AS dia_sesion,
                h.hora_inicio,
                h.hora_fin,
                g.nombre AS grupo_nombre,
                g.semestre,
                g.turno,
                m.clave AS materia_clave,
                m.nombre AS materia_nombre,
                a.nombre AS alumno_nombre,
                a.apellido_paterno,
                a.apellido_materno
         FROM justificaciones j
         INNER JOIN sesiones_clase sc ON sc.id = j.sesion_clase_id
         INNER JOIN grupo_materia_maestro gmm ON gmm.id = sc.grupo_materia_maestro_id
         INNER JOIN grupo_materias gm ON gm.id = gmm.grupo_materia_id
         INNER JOIN grupos g ON g.id = gm.grupo_id
         INNER JOIN materias m ON m.id = gm.materia_id
         INNER JOIN alumnos a ON a.id = j.alumno_id
         LEFT JOIN horarios h ON h.id = (
             SELECT hh.id
             FROM horarios hh
             WHERE hh.grupo_materia_maestro_id = gmm.id
               AND hh.activo = 1
               AND hh.dia_semana = CASE DAYOFWEEK(sc.fecha)
                   WHEN 1 THEN 'DOMINGO'
                   WHEN 2 THEN 'LUNES'
                   WHEN 3 THEN 'MARTES'
                   WHEN 4 THEN 'MIERCOLES'
                   WHEN 5 THEN 'JUEVES'
                   WHEN 6 THEN 'VIERNES'
                   WHEN 7 THEN 'SABADO'
               END
             ORDER BY hh.hora_inicio ASC
             LIMIT 1
         )
         WHERE gmm.maestro_id = :maestro_id
         ORDER BY g.nombre ASC, m.nombre ASC, sc.fecha DESC, j.id DESC"
    );
    $stmt->bindValue(':maestro_id', $maestroId, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'OK',
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
    ]);
    exit;
}

if ($action === 'teacherJustificationUpdate') {
    $justificationId = (int)($body['justification_id'] ?? 0);
    $status = strtoupper(trim((string)($body['estatus'] ?? '')));
    if ($justificationId <= 0 || !in_array($status, ['APROBADO', 'RECHAZADO', 'PENDIENTE'], true)) {
        echo json_encode(['success' => false, 'message' => 'Datos no válidos.']);
        exit;
    }

    $db->beginTransaction();
    try {
        $stmt = $db->prepare(
            "UPDATE justificaciones
             SET estatus = :estatus,
                 fecha_revision = NOW()
             WHERE id = :id"
        );
        $stmt->bindValue(':estatus', $status);
        $stmt->bindValue(':id', $justificationId, PDO::PARAM_INT);
        $stmt->execute();

        if ($status === 'APROBADO') {
            $stmt = $db->prepare(
                "SELECT sesion_clase_id, alumno_id
                 FROM justificaciones
                 WHERE id = :id
                 LIMIT 1"
            );
            $stmt->bindValue(':id', $justificationId, PDO::PARAM_INT);
            $stmt->execute();
            $justification = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($justification) {
                $stmt = $db->prepare(
                    "SELECT id
                     FROM catalogo_asistencia
                     WHERE UPPER(nombre) = 'JUSTIFICADA'
                     LIMIT 1"
                );
                $stmt->execute();
                $justifiedState = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($justifiedState) {
                    $stmt = $db->prepare(
                        "UPDATE asistencias
                         SET estado_id = :estado_id
                         WHERE sesion_clase_id = :sesion_clase_id
                           AND alumno_id = :alumno_id"
                    );
                    $stmt->bindValue(':estado_id', (int)$justifiedState['id'], PDO::PARAM_INT);
                    $stmt->bindValue(':sesion_clase_id', (int)$justification['sesion_clase_id'], PDO::PARAM_INT);
                    $stmt->bindValue(':alumno_id', (int)$justification['alumno_id'], PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Justificación actualizada correctamente.']);
        exit;
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la justificación.']);
        exit;
    }
}

if ($action === 'studentDashboard') {
    $alumnoId = (int)($body['alumno_id'] ?? 0);
    if ($alumnoId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Alumno no válido.']);
        exit;
    }

    $stmt = $db->prepare(
        "SELECT g.id, g.nombre, g.semestre, g.turno, g.ciclo_escolar, g.codigo_ingreso, g.requiere_aprobacion
         FROM grupos g
         WHERE g.activo = 1
         ORDER BY g.nombre ASC"
    );
    $stmt->execute();
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->prepare(
        "SELECT ag.grupo_id, ag.fecha_ingreso, ag.fecha_baja,
                g.nombre AS grupo_nombre, g.semestre, g.turno, g.ciclo_escolar
         FROM alumnos_grupos ag
         INNER JOIN grupos g ON ag.grupo_id = g.id
         WHERE ag.alumno_id = :alumno_id
         ORDER BY ag.id DESC"
    );
    $stmt->bindValue(':alumno_id', $alumnoId, PDO::PARAM_INT);
    $stmt->execute();
    $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'message' => 'OK', 'data' => ['available_groups' => $groups, 'inscriptions' => $inscriptions]]);
    exit;
}

if ($action === 'studentGroupSubjects') {
    $alumnoId = (int)($body['alumno_id'] ?? 0);
    $grupoId = (int)($body['grupo_id'] ?? 0);

    if ($alumnoId <= 0 || $grupoId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Alumno y grupo son obligatorios.']);
        exit;
    }

    $stmt = $db->prepare(
        "SELECT gm.id, m.clave, m.nombre, m.descripcion
         FROM alumnos_grupos ag
         INNER JOIN grupo_materias gm ON ag.grupo_id = gm.grupo_id
         INNER JOIN materias m ON gm.materia_id = m.id
         WHERE ag.alumno_id = :alumno_id
           AND ag.grupo_id = :grupo_id
           AND m.activo = 1
         ORDER BY m.nombre ASC"
    );
    $stmt->bindValue(':alumno_id', $alumnoId, PDO::PARAM_INT);
    $stmt->bindValue(':grupo_id', $grupoId, PDO::PARAM_INT);
    $stmt->execute();
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'message' => 'OK', 'data' => $subjects]);
    exit;
}

if ($action === 'studentGroupSchedule') {
    $alumnoId = (int)($body['alumno_id'] ?? 0);
    $grupoId = (int)($body['grupo_id'] ?? 0);

    if ($alumnoId <= 0 || $grupoId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Alumno y grupo son obligatorios.']);
        exit;
    }

    $stmt = $db->prepare(
        "SELECT m.id AS materia_id,
                m.clave AS materia_clave,
                m.nombre AS materia_nombre,
                m.descripcion,
                g.nombre AS grupo_nombre,
                g.semestre,
                g.turno,
                g.ciclo_escolar,
                h.dia_semana,
                h.hora_inicio,
                h.hora_fin,
                h.aula,
                ma.nombre AS maestro_nombre,
                ma.apellido_paterno,
                ma.apellido_materno
         FROM alumnos_grupos ag
         INNER JOIN grupo_materias gm ON ag.grupo_id = gm.grupo_id
         INNER JOIN materias m ON gm.materia_id = m.id AND m.activo = 1
         LEFT JOIN grupo_materia_maestro gmm ON gmm.grupo_materia_id = gm.id AND gmm.activo = 1
         LEFT JOIN horarios h ON h.grupo_materia_maestro_id = gmm.id AND h.activo = 1
         LEFT JOIN maestros ma ON gmm.maestro_id = ma.id
         INNER JOIN grupos g ON g.id = ag.grupo_id
         WHERE ag.alumno_id = :alumno_id
           AND ag.grupo_id = :grupo_id
           AND ag.fecha_baja IS NULL
         ORDER BY CASE h.dia_semana
             WHEN 'LUNES' THEN 1
             WHEN 'MARTES' THEN 2
             WHEN 'MIERCOLES' THEN 3
             WHEN 'JUEVES' THEN 4
             WHEN 'VIERNES' THEN 5
             WHEN 'SABADO' THEN 6
             WHEN 'DOMINGO' THEN 7
             ELSE 8 END ASC,
             h.hora_inicio ASC,
             m.nombre ASC"
    );
    $stmt->bindValue(':alumno_id', $alumnoId, PDO::PARAM_INT);
    $stmt->bindValue(':grupo_id', $grupoId, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'message' => 'OK', 'data' => $rows]);
    exit;
}

if ($action === 'studentSubjectSchedule') {
    $alumnoId = (int)($body['alumno_id'] ?? 0);
    $grupoId = (int)($body['grupo_id'] ?? 0);
    $materiaId = (int)($body['materia_id'] ?? 0);

    if ($alumnoId <= 0 || $grupoId <= 0 || $materiaId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Alumno, grupo y materia son obligatorios.']);
        exit;
    }

    if (!isSchoolDay($db, date('Y-m-d'))) {
        echo json_encode(['success' => true, 'message' => 'Día inhábil.', 'data' => null]);
        exit;
    }

    $stmt = $db->prepare(
        "SELECT h.id AS horario_id, h.dia_semana, h.hora_inicio, h.hora_fin, h.aula,
                gmm.id AS grupo_materia_maestro_id,
                g.nombre AS grupo_nombre, g.semestre, g.turno,
                m.clave AS materia_clave, m.nombre AS materia_nombre,
                ma.nombre AS maestro_nombre, ma.apellido_paterno, ma.apellido_materno,
                sc.id AS sesion_id,
                sc.estatus AS sesion_estatus
         FROM alumnos_grupos ag
         INNER JOIN grupo_materias gm ON ag.grupo_id = gm.grupo_id
         INNER JOIN materias m ON gm.materia_id = m.id
         LEFT JOIN grupo_materia_maestro gmm ON gmm.grupo_materia_id = gm.id AND gmm.activo = 1
         LEFT JOIN horarios h ON h.grupo_materia_maestro_id = gmm.id AND h.activo = 1
         LEFT JOIN maestros ma ON gmm.maestro_id = ma.id
         LEFT JOIN sesiones_clase sc ON sc.grupo_materia_maestro_id = gmm.id AND sc.fecha = CURDATE()
         INNER JOIN grupos g ON ag.grupo_id = g.id
         WHERE ag.alumno_id = :alumno_id
           AND ag.grupo_id = :grupo_id
           AND m.id = :materia_id
         ORDER BY gmm.id ASC, h.dia_semana ASC, h.hora_inicio ASC"
    );
    $stmt->bindValue(':alumno_id', $alumnoId, PDO::PARAM_INT);
    $stmt->bindValue(':grupo_id', $grupoId, PDO::PARAM_INT);
    $stmt->bindValue(':materia_id', $materiaId, PDO::PARAM_INT);
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $daysOrder = ['LUNES' => 1, 'MARTES' => 2, 'MIERCOLES' => 3, 'JUEVES' => 4, 'VIERNES' => 5, 'SABADO' => 6, 'DOMINGO' => 7];
    $todayIndex = (int)date('N');
    $currentTime = date('H:i:s');
    $todaySchedule = null;
    $nextSchedule = null;

    foreach ($schedules as $schedule) {
        if (empty($schedule['dia_semana']) || empty($schedule['hora_inicio']) || empty($schedule['hora_fin'])) {
            continue;
        }

        $scheduleDay = $daysOrder[$schedule['dia_semana']] ?? 8;
        $scheduleTime = (string)$schedule['hora_inicio'];
        $minutesBefore = (int)($schedule['minutos_antes'] ?? 15);
        $minutesAfter = (int)($schedule['minutos_despues'] ?? 15);
        $startAllowed = date('H:i:s', strtotime($scheduleTime . " -{$minutesBefore} minutes"));
        $endAllowed = date('H:i:s', strtotime((string)$schedule['hora_fin'] . " +{$minutesAfter} minutes"));
        $daysAhead = ($scheduleDay - $todayIndex + 7) % 7;

        if ($daysAhead === 0) {
            $todaySchedule = $schedule;
            $todaySchedule['sesion_estatus'] = $schedule['sesion_estatus'] ?? null;
            $todaySchedule['_can_register'] = ($currentTime >= $startAllowed && $currentTime <= $endAllowed);
            break;
        }

        $candidateKey = $daysAhead * 100000 + (int)str_replace(':', '', substr($scheduleTime, 0, 5));
        if ($nextSchedule === null || $candidateKey < ($nextSchedule['_key'] ?? PHP_INT_MAX)) {
            $schedule['_key'] = $candidateKey;
            $nextSchedule = $schedule;
        }
    }

    if ($todaySchedule) {
        $stmt = $db->prepare(
            "SELECT a.id
             FROM sesiones_clase sc
             INNER JOIN asistencias a ON a.sesion_clase_id = sc.id
             WHERE sc.grupo_materia_maestro_id = :gmm_id
               AND sc.fecha = CURDATE()
               AND a.alumno_id = :alumno_id
             LIMIT 1"
        );
        $stmt->bindValue(':gmm_id', (int)($todaySchedule['grupo_materia_maestro_id'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':alumno_id', $alumnoId, PDO::PARAM_INT);
        $stmt->execute();
        $todaySchedule['asistencia_registrada'] = (bool)$stmt->fetch(PDO::FETCH_ASSOC);
        unset($todaySchedule['_key']);
        echo json_encode(['success' => true, 'message' => 'OK', 'data' => $todaySchedule]);
        exit;
    }

    if ($nextSchedule) {
        unset($nextSchedule['_key']);
    }

    echo json_encode(['success' => true, 'message' => 'OK', 'data' => $nextSchedule ?: null]);
    exit;
}

if ($action === 'studentRegisterAttendance') {
    $alumnoId = (int)($body['alumno_id'] ?? 0);
    $grupoId = (int)($body['grupo_id'] ?? 0);
    $materiaId = (int)($body['materia_id'] ?? 0);

    if ($alumnoId <= 0 || $grupoId <= 0 || $materiaId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Alumno, grupo y materia son obligatorios.']);
        exit;
    }

    if (!isSchoolDay($db, date('Y-m-d'))) {
        echo json_encode(['success' => false, 'message' => 'No se puede registrar asistencia en un día inhábil o de vacaciones.']);
        exit;
    }

    $stmt = $db->prepare(
        "SELECT gmm.id AS grupo_materia_maestro_id, h.dia_semana, h.hora_inicio, h.hora_fin, h.minutos_antes, h.minutos_despues
         FROM grupo_materias gm
         INNER JOIN grupo_materia_maestro gmm ON gmm.grupo_materia_id = gm.id AND gmm.activo = 1
         INNER JOIN horarios h ON h.grupo_materia_maestro_id = gmm.id AND h.activo = 1
         WHERE gm.grupo_id = :grupo_id AND gm.materia_id = :materia_id
         ORDER BY CASE h.dia_semana
             WHEN 'LUNES' THEN 1
             WHEN 'MARTES' THEN 2
             WHEN 'MIERCOLES' THEN 3
             WHEN 'JUEVES' THEN 4
             WHEN 'VIERNES' THEN 5
             WHEN 'SABADO' THEN 6
             WHEN 'DOMINGO' THEN 7
             ELSE 8 END ASC, h.hora_inicio ASC"
    );
    $stmt->bindValue(':grupo_id', $grupoId, PDO::PARAM_INT);
    $stmt->bindValue(':materia_id', $materiaId, PDO::PARAM_INT);
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$schedules) {
        echo json_encode(['success' => false, 'message' => 'No hay horario disponible para esta materia.']);
        exit;
    }

    $daysMap = [
        1 => 'LUNES',
        2 => 'MARTES',
        3 => 'MIERCOLES',
        4 => 'JUEVES',
        5 => 'VIERNES',
        6 => 'SABADO',
        7 => 'DOMINGO',
    ];
    $today = $daysMap[(int)date('N')] ?? '';
    $currentTime = date('H:i:s');
    $selectedSchedule = null;
    $diagnostics = [];

    foreach ($schedules as $scheduleRow) {
        $startTime = (string)$scheduleRow['hora_inicio'];
        $endTime = (string)$scheduleRow['hora_fin'];
        $minutesBefore = (int)($scheduleRow['minutos_antes'] ?? 15);
        $minutesAfter = (int)($scheduleRow['minutos_despues'] ?? 15);
        $startAllowed = date('H:i:s', strtotime($startTime . " -{$minutesBefore} minutes"));
        $endAllowed = date('H:i:s', strtotime($endTime . " +{$minutesAfter} minutes"));
        $matchDay = ($today === $scheduleRow['dia_semana']);
        $inWindow = ($currentTime >= $startAllowed && $currentTime <= $endAllowed);

        $diagnostics[] = [
            'dia_programado' => $scheduleRow['dia_semana'],
            'dia_actual' => $today,
            'hora_actual' => $currentTime,
            'hora_inicio' => $startTime,
            'hora_fin' => $endTime,
            'minutos_antes' => $minutesBefore,
            'minutos_despues' => $minutesAfter,
            'inicio_permitido' => $startAllowed,
            'fin_permitido' => $endAllowed,
            'coincide_dia' => $matchDay,
            'esta_en_rango' => $inWindow,
        ];

        if ($matchDay && $inWindow) {
            $selectedSchedule = $scheduleRow;
            break;
        }
    }

    error_log('studentRegisterAttendance diagnostics: ' . json_encode([
        'alumno_id' => $alumnoId,
        'grupo_id' => $grupoId,
        'materia_id' => $materiaId,
        'dia_actual' => $today,
        'hora_actual' => $currentTime,
        'horarios' => $diagnostics,
        'seleccionado' => $selectedSchedule ? [
            'grupo_materia_maestro_id' => (int)$selectedSchedule['grupo_materia_maestro_id'],
            'dia_semana' => $selectedSchedule['dia_semana'],
            'hora_inicio' => $selectedSchedule['hora_inicio'],
            'hora_fin' => $selectedSchedule['hora_fin'],
        ] : null,
    ], JSON_UNESCAPED_UNICODE));

    if (!$selectedSchedule) {
        echo json_encode([
            'success' => false,
            'message' => 'No hay un horario válido en este momento.',
            'debug' => [
                'dia_actual' => $today,
                'hora_actual' => $currentTime,
                'horarios' => $diagnostics,
            ],
        ]);
        exit;
    }

    $stmt = $db->prepare(
        "SELECT id, estatus, latitud, longitud, usuario_id
         FROM sesiones_clase
         WHERE grupo_materia_maestro_id = :gmm_id
           AND fecha = CURDATE()
         ORDER BY id DESC
        LIMIT 1"
    );
    $stmt->bindValue(':gmm_id', (int)$selectedSchedule['grupo_materia_maestro_id'], PDO::PARAM_INT);
    $stmt->execute();
    $sesion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sesion || strtoupper((string)($sesion['estatus'] ?? '')) !== 'ABIERTA') {
        echo json_encode([
            'success' => false,
            'message' => 'La sesión aún no está abierta por el maestro.',
            'debug' => [
                'dia_actual' => $today,
                'hora_actual' => $currentTime,
                'horario' => [
                    'dia_semana' => $selectedSchedule['dia_semana'],
                    'hora_inicio' => $selectedSchedule['hora_inicio'],
                    'hora_fin' => $selectedSchedule['hora_fin'],
                    'minutos_antes' => (int)($selectedSchedule['minutos_antes'] ?? 15),
                    'minutos_despues' => (int)($selectedSchedule['minutos_despues'] ?? 15),
                ],
                'sesion_encontrada' => $sesion ?: null,
            ],
        ]);
        exit;
    }

    $sesionId = (int)$sesion['id'];

    $db->beginTransaction();

    try {

        $stmt = $db->prepare(
            "SELECT id FROM asistencias WHERE sesion_clase_id = :sesion_id AND alumno_id = :alumno_id LIMIT 1"
        );
        $stmt->bindValue(':sesion_id', $sesionId, PDO::PARAM_INT);
        $stmt->bindValue(':alumno_id', $alumnoId, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Ya registraste asistencia para esta clase.']);
            exit;
        }

        $stmt = $db->prepare("INSERT INTO asistencias (sesion_clase_id, alumno_id, estado_id, observaciones) VALUES (:sesion_id, :alumno_id, 1, 'Registrada desde app')");
        $stmt->bindValue(':sesion_id', $sesionId, PDO::PARAM_INT);
        $stmt->bindValue(':alumno_id', $alumnoId, PDO::PARAM_INT);
        $stmt->execute();

        $db->commit();

        echo json_encode(['success' => true, 'message' => 'Asistencia registrada correctamente.']);
        exit;
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'No se pudo registrar la asistencia.']);
        exit;
    }
}

if ($action === 'studentJustificationSessions') {
    $alumnoId = (int)($body['alumno_id'] ?? 0);
    if ($alumnoId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Alumno no válido.']);
        exit;
    }

    $stmt = $db->prepare(
        "SELECT sc.id AS sesion_clase_id,
                sc.fecha,
                sc.estatus,
                CASE DAYOFWEEK(sc.fecha)
                   WHEN 1 THEN 'DOMINGO'
                   WHEN 2 THEN 'LUNES'
                   WHEN 3 THEN 'MARTES'
                   WHEN 4 THEN 'MIERCOLES'
                   WHEN 5 THEN 'JUEVES'
                   WHEN 6 THEN 'VIERNES'
                   WHEN 7 THEN 'SABADO'
                END AS dia_sesion,
                a.id AS asistencia_id,
                g.id AS grupo_id,
                g.nombre AS grupo_nombre,
                g.semestre,
                g.turno,
                m.id AS materia_id,
                m.clave AS materia_clave,
                m.nombre AS materia_nombre,
                h.dia_semana,
                h.hora_inicio,
                h.hora_fin,
                ma.nombre AS maestro_nombre,
                ma.apellido_paterno,
                ma.apellido_materno
         FROM alumnos_grupos ag
         INNER JOIN grupo_materias gm ON ag.grupo_id = gm.grupo_id
         INNER JOIN grupo_materia_maestro gmm ON gmm.grupo_materia_id = gm.id AND gmm.activo = 1
         INNER JOIN sesiones_clase sc ON sc.grupo_materia_maestro_id = gmm.id
         INNER JOIN asistencias a ON a.sesion_clase_id = sc.id AND a.alumno_id = ag.alumno_id
         INNER JOIN catalogo_asistencia ca ON ca.id = a.estado_id AND UPPER(ca.nombre) = 'FALTA'
         INNER JOIN grupos g ON ag.grupo_id = g.id
         INNER JOIN materias m ON gm.materia_id = m.id
         LEFT JOIN maestros ma ON gmm.maestro_id = ma.id
         LEFT JOIN horarios h ON h.id = (
             SELECT hh.id
             FROM horarios hh
             WHERE hh.grupo_materia_maestro_id = gmm.id
               AND hh.activo = 1
               AND hh.dia_semana = CASE DAYOFWEEK(sc.fecha)
                   WHEN 1 THEN 'DOMINGO'
                   WHEN 2 THEN 'LUNES'
                   WHEN 3 THEN 'MARTES'
                   WHEN 4 THEN 'MIERCOLES'
                   WHEN 5 THEN 'JUEVES'
                   WHEN 6 THEN 'VIERNES'
                   WHEN 7 THEN 'SABADO'
               END
             ORDER BY hh.hora_inicio ASC
             LIMIT 1
         )
         WHERE ag.alumno_id = :alumno_id
           AND ag.fecha_baja IS NULL
           AND sc.fecha < CURDATE()
           AND NOT EXISTS (
               SELECT 1
               FROM justificaciones j
               WHERE j.sesion_clase_id = sc.id
                 AND j.alumno_id = ag.alumno_id
           )
         ORDER BY sc.fecha DESC, h.hora_inicio ASC
         LIMIT 100"
    );
    $stmt->bindValue(':alumno_id', $alumnoId, PDO::PARAM_INT);
    $stmt->execute();
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'message' => 'OK', 'data' => $sessions]);
    exit;
}

if ($action === 'studentJustifications') {
    $alumnoId = (int)($body['alumno_id'] ?? 0);
    if ($alumnoId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Alumno no válido.']);
        exit;
    }

    $stmt = $db->prepare(
        "SELECT j.id,
                j.alumno_id,
                j.sesion_clase_id,
                j.motivo,
                j.evidencia,
                j.estatus,
                j.created_at,
                j.fecha_revision,
                sc.fecha AS sesion_fecha,
                CASE DAYOFWEEK(sc.fecha)
                   WHEN 1 THEN 'DOMINGO'
                   WHEN 2 THEN 'LUNES'
                   WHEN 3 THEN 'MARTES'
                   WHEN 4 THEN 'MIERCOLES'
                   WHEN 5 THEN 'JUEVES'
                   WHEN 6 THEN 'VIERNES'
                   WHEN 7 THEN 'SABADO'
                END AS dia_sesion,
                h.hora_inicio,
                h.hora_fin,
                g.nombre AS grupo_nombre,
                g.semestre,
                g.turno,
                m.clave AS materia_clave,
                m.nombre AS materia_nombre,
                ma.nombre AS maestro_nombre,
                ma.apellido_paterno,
                ma.apellido_materno
         FROM justificaciones j
         INNER JOIN sesiones_clase sc ON sc.id = j.sesion_clase_id
         INNER JOIN grupo_materia_maestro gmm ON gmm.id = sc.grupo_materia_maestro_id
         INNER JOIN grupo_materias gm ON gm.id = gmm.grupo_materia_id
         INNER JOIN grupos g ON g.id = gm.grupo_id
         INNER JOIN materias m ON m.id = gm.materia_id
         LEFT JOIN maestros ma ON ma.id = gmm.maestro_id
         LEFT JOIN horarios h ON h.id = (
             SELECT hh.id
             FROM horarios hh
             WHERE hh.grupo_materia_maestro_id = gmm.id
               AND hh.activo = 1
               AND hh.dia_semana = CASE DAYOFWEEK(sc.fecha)
                   WHEN 1 THEN 'DOMINGO'
                   WHEN 2 THEN 'LUNES'
                   WHEN 3 THEN 'MARTES'
                   WHEN 4 THEN 'MIERCOLES'
                   WHEN 5 THEN 'JUEVES'
                   WHEN 6 THEN 'VIERNES'
                   WHEN 7 THEN 'SABADO'
               END
             ORDER BY hh.hora_inicio ASC
             LIMIT 1
         )
         WHERE j.alumno_id = :alumno_id
         ORDER BY j.id DESC"
    );
    $stmt->bindValue(':alumno_id', $alumnoId, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'message' => 'OK', 'data' => $items]);
    exit;
}

if ($action === 'studentSubmitJustification') {
    $alumnoId = (int)($body['alumno_id'] ?? 0);
    $sesionClaseId = (int)($body['sesion_clase_id'] ?? 0);
    $motivo = trim((string)($body['motivo'] ?? ''));

    if ($alumnoId <= 0 || $sesionClaseId <= 0 || $motivo === '') {
        echo json_encode(['success' => false, 'message' => 'Alumno, sesión y motivo son obligatorios.']);
        exit;
    }

    $stmt = $db->prepare(
        "SELECT sc.id, gm.grupo_id
         FROM sesiones_clase sc
         INNER JOIN grupo_materia_maestro gmm ON sc.grupo_materia_maestro_id = gmm.id
         INNER JOIN grupo_materias gm ON gmm.grupo_materia_id = gm.id
         INNER JOIN alumnos_grupos ag ON ag.grupo_id = gm.grupo_id
         WHERE sc.id = :sesion_clase_id
           AND ag.alumno_id = :alumno_id
           AND ag.fecha_baja IS NULL
         LIMIT 1"
    );
    $stmt->bindValue(':sesion_clase_id', $sesionClaseId, PDO::PARAM_INT);
    $stmt->bindValue(':alumno_id', $alumnoId, PDO::PARAM_INT);
    $stmt->execute();
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        echo json_encode(['success' => false, 'message' => 'La sesión no pertenece al alumno.']);
        exit;
    }

    $evidenciaPath = null;
    if (!empty($_FILES['evidencia']['tmp_name'])) {
        $uploadDir = __DIR__ . '/uploads/justificaciones';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $originalName = basename((string)($_FILES['evidencia']['name'] ?? 'evidencia'));
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $safeName = 'justificacion_' . $alumnoId . '_' . $sesionClaseId . '_' . time();
        if ($extension !== '') {
            $safeName .= '.' . $extension;
        }

        $destination = $uploadDir . '/' . $safeName;
        if (!move_uploaded_file($_FILES['evidencia']['tmp_name'], $destination)) {
            echo json_encode(['success' => false, 'message' => 'No se pudo guardar la evidencia.']);
            exit;
        }

        $evidenciaPath = 'uploads/justificaciones/' . $safeName;
    }

    $stmt = $db->prepare(
        "INSERT INTO justificaciones (alumno_id, sesion_clase_id, motivo, evidencia, estatus)
         VALUES (:alumno_id, :sesion_clase_id, :motivo, :evidencia, 'PENDIENTE')
         ON DUPLICATE KEY UPDATE motivo = VALUES(motivo),
                                 evidencia = VALUES(evidencia),
                                 estatus = 'PENDIENTE',
                                 fecha_revision = NULL,
                                 revisado_por = NULL"
    );
    $stmt->bindValue(':alumno_id', $alumnoId, PDO::PARAM_INT);
    $stmt->bindValue(':sesion_clase_id', $sesionClaseId, PDO::PARAM_INT);
    $stmt->bindValue(':motivo', $motivo);
    $stmt->bindValue(':evidencia', $evidenciaPath);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'No se pudo guardar la justificación.']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Justificación enviada correctamente.']);
    exit;
}

if ($action === 'enrollGroup') {
    $alumnoId = (int)($body['alumno_id'] ?? 0);
    $pin = trim((string)($body['pin'] ?? ''));
    if ($alumnoId <= 0 || $pin === '') {
        echo json_encode(['success' => false, 'message' => 'Alumno y PIN son obligatorios.']);
        exit;
    }

    $stmt = $db->prepare("SELECT id, nombre, activo, requiere_aprobacion FROM grupos WHERE codigo_ingreso = :pin LIMIT 1");
    $stmt->bindValue(':pin', $pin);
    $stmt->execute();
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$group || (int)$group['activo'] !== 1) {
        echo json_encode(['success' => false, 'message' => 'PIN no válido o grupo inactivo.']);
        exit;
    }

    $stmt = $db->prepare("SELECT id FROM alumnos_grupos WHERE alumno_id = :alumno_id AND grupo_id = :grupo_id LIMIT 1");
    $stmt->bindValue(':alumno_id', $alumnoId, PDO::PARAM_INT);
    $stmt->bindValue(':grupo_id', (int)$group['id'], PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode(['success' => false, 'message' => 'Ya estás inscrito en este grupo.']);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO alumnos_grupos (alumno_id, grupo_id, fecha_ingreso) VALUES (:alumno_id, :grupo_id, CURDATE())");
    $stmt->bindValue(':alumno_id', $alumnoId, PDO::PARAM_INT);
    $stmt->bindValue(':grupo_id', (int)$group['id'], PDO::PARAM_INT);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'No se pudo inscribir al alumno.']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Inscripción exitosa.', 'group' => $group]);
    exit;
}

if ($action === 'register' || $action === 'registerAlumno') {
    $nombre = trim((string)($body['nombre'] ?? ''));
    $correo = trim((string)($body['correo'] ?? ''));
    $password = (string)($body['password'] ?? '');
    $rol = strtoupper(trim((string)($body['rol'] ?? 'ALUMNO')));

    if ($nombre === '' || $correo === '' || $password === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Nombre, correo y contraseña son obligatorios.'
        ]);
        exit;
    }

    try {
        $db->beginTransaction();

        $usuarioModel = new Usuario($db);
        $maestroModel = new Maestro($db);
        $alumnoModel = new Alumno($db);

        $userData = $usuarioModel->register([
            'nombre' => $nombre,
            'correo' => $correo,
            'password' => $password,
            'rol' => $rol,
        ]);

        if ($rol === 'MAESTRO') {
            $maestroModel->createForUsuario((int)$userData['id'], [
                'nombre' => $nombre,
                'apellido_paterno' => $body['apellido_paterno'] ?? '',
                'apellido_materno' => $body['apellido_materno'] ?? '',
                'telefono' => $body['telefono'] ?? '',
                'correo' => $correo,
            ]);
        } elseif ($rol === 'ALUMNO') {
            $alumnoModel->create([
                'matricula' => $body['matricula'] ?? '',
                'nombre' => $nombre,
                'apellido_paterno' => $body['apellido_paterno'] ?? '',
                'apellido_materno' => $body['apellido_materno'] ?? '',
                'telefono' => $body['telefono'] ?? '',
                'correo' => $correo,
            ]);
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Registro correcto.',
            'user' => $userData,
        ]);
        exit;
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
        ]);
        exit;
    }
}

echo json_encode([
    'success' => false,
    'message' => 'Endpoint no válido.',
]);
