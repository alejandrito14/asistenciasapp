<?php
date_default_timezone_set('America/Mexico_City');

require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/helpers/SchoolCalendarHelper.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    fwrite(STDERR, "No se pudo conectar a la base de datos.\n");
    exit(1);
}

$now = new DateTime('now');
$today = $now->format('Y-m-d');
$currentTime = $now->format('H:i:s');

if (!isSchoolDay($db, $today)) {
    $reason = schoolCalendarBlockReason($db, $today);
    echo "Dia inhabel. No se generan faltas.\n";
    if ($reason) {
        echo "Bloqueo: {$reason['tipo']} - {$reason['descripcion']}\n";
    }
    exit(0);
}

$daysMap = [
    'LUNES' => 1,
    'MARTES' => 2,
    'MIERCOLES' => 3,
    'JUEVES' => 4,
    'VIERNES' => 5,
    'SABADO' => 6,
    'DOMINGO' => 7,
];

$stmt = $db->prepare(
    "SELECT sc.id AS sesion_clase_id,
            sc.fecha,
            sc.estatus,
            gmm.id AS grupo_materia_maestro_id,
            gm.grupo_id,
            gm.materia_id,
            h.dia_semana,
            h.hora_inicio,
            h.hora_fin,
            COALESCE(h.minutos_antes, 15) AS minutos_antes,
            COALESCE(h.minutos_despues, 15) AS minutos_despues
     FROM sesiones_clase sc
     INNER JOIN grupo_materia_maestro gmm ON gmm.id = sc.grupo_materia_maestro_id
     INNER JOIN grupo_materias gm ON gm.id = gmm.grupo_materia_id
     INNER JOIN horarios h ON h.grupo_materia_maestro_id = gmm.id AND h.activo = 1
     WHERE sc.fecha <= CURDATE()
       AND sc.estatus = 'CERRADA'"
);
$stmt->execute();
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$inserted = 0;

foreach ($sessions as $session) {
    $dayName = strtoupper((string)($session['dia_semana'] ?? ''));
    $dayIndex = $daysMap[$dayName] ?? 0;
    if ($dayIndex === 0) {
        continue;
    }

    $sessionDate = $session['fecha'] ?? $today;
    $sessionEnd = DateTime::createFromFormat('Y-m-d H:i:s', $sessionDate . ' ' . (string)$session['hora_fin']);
    if (!$sessionEnd) {
        continue;
    }

    $windowEnd = clone $sessionEnd;
    if ($now <= $windowEnd) {
        continue;
    }

    $stmtStudents = $db->prepare(
        "SELECT ag.alumno_id
         FROM alumnos_grupos ag
         WHERE ag.grupo_id = :grupo_id
           AND ag.fecha_baja IS NULL"
    );
    $stmtStudents->bindValue(':grupo_id', (int)$session['grupo_id'], PDO::PARAM_INT);
    $stmtStudents->execute();
    $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

    foreach ($students as $student) {
        $alumnoId = (int)($student['alumno_id'] ?? 0);
        if ($alumnoId <= 0) {
            continue;
        }

        $stmtAttendance = $db->prepare(
            "SELECT id FROM asistencias WHERE sesion_clase_id = :sesion_clase_id AND alumno_id = :alumno_id LIMIT 1"
        );
        $stmtAttendance->bindValue(':sesion_clase_id', (int)$session['sesion_clase_id'], PDO::PARAM_INT);
        $stmtAttendance->bindValue(':alumno_id', $alumnoId, PDO::PARAM_INT);
        $stmtAttendance->execute();

        if ($stmtAttendance->fetch(PDO::FETCH_ASSOC)) {
            continue;
        }

        $stmtInsert = $db->prepare(
            "INSERT INTO asistencias (sesion_clase_id, alumno_id, estado_id, observaciones)
             VALUES (:sesion_clase_id, :alumno_id, 2, 'Falta automática generada por cron')"
        );
        $stmtInsert->bindValue(':sesion_clase_id', (int)$session['sesion_clase_id'], PDO::PARAM_INT);
        $stmtInsert->bindValue(':alumno_id', $alumnoId, PDO::PARAM_INT);
        if ($stmtInsert->execute()) {
            $inserted++;
        }
    }
}

echo "Faltas generadas: {$inserted}\n";
