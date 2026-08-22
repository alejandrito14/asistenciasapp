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
$daysMap = [
    1 => 'LUNES',
    2 => 'MARTES',
    3 => 'MIERCOLES',
    4 => 'JUEVES',
    5 => 'VIERNES',
    6 => 'SABADO',
    7 => 'DOMINGO',
];
$todayDay = $daysMap[(int)$now->format('N')] ?? '';

if (!isSchoolDay($db, $today)) {
    $reason = schoolCalendarBlockReason($db, $today);
    echo "Dia inhabel. No se abren sesiones.\n";
    if ($reason) {
        echo "Bloqueo: {$reason['tipo']} - {$reason['descripcion']}\n";
    }
    exit(0);
}

$stmt = $db->prepare(
    "SELECT gmm.id AS grupo_materia_maestro_id,
            h.dia_semana,
            h.hora_inicio,
            COALESCE(h.minutos_antes, 15) AS minutos_antes
     FROM grupo_materia_maestro gmm
     INNER JOIN horarios h ON h.grupo_materia_maestro_id = gmm.id AND h.activo = 1
     INNER JOIN grupo_materias gm ON gm.id = gmm.grupo_materia_id
     INNER JOIN grupos g ON g.id = gm.grupo_id
     INNER JOIN materias m ON m.id = gm.materia_id
     WHERE gmm.activo = 1
       AND g.activo = 1
       AND m.activo = 1"
);
$stmt->execute();
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

$opened = 0;

foreach ($schedules as $schedule) {
    $gmmId = (int)($schedule['grupo_materia_maestro_id'] ?? 0);
    $scheduleDay = strtoupper((string)($schedule['dia_semana'] ?? ''));
    $startTime = (string)($schedule['hora_inicio'] ?? '');
    if ($gmmId <= 0 || $startTime === '' || $scheduleDay === '') {
        continue;
    }

    if ($scheduleDay !== $todayDay) {
        continue;
    }

    $scheduleStart = DateTime::createFromFormat('Y-m-d H:i:s', $today . ' ' . $startTime);
    if (!$scheduleStart) {
        continue;
    }

    if ($now < $scheduleStart) {
        continue;
    }

    $stmtSession = $db->prepare(
        "SELECT id, estatus
         FROM sesiones_clase
         WHERE grupo_materia_maestro_id = :gmm_id
           AND fecha = CURDATE()
         LIMIT 1"
    );
    $stmtSession->bindValue(':gmm_id', $gmmId, PDO::PARAM_INT);
    $stmtSession->execute();
    $session = $stmtSession->fetch(PDO::FETCH_ASSOC);

    if ($session) {
        if (($session['estatus'] ?? '') === 'ABIERTA') {
            continue;
        }

        $stmtUpdate = $db->prepare(
            "UPDATE sesiones_clase
             SET estatus = 'ABIERTA',
                 hora_inicio_real = COALESCE(hora_inicio_real, NOW()),
                 hora_fin_real = NULL
             WHERE id = :id"
        );
        $stmtUpdate->bindValue(':id', (int)$session['id'], PDO::PARAM_INT);
        if ($stmtUpdate->execute() && $stmtUpdate->rowCount() > 0) {
            $opened++;
        }
        continue;
    }

    $stmtInsert = $db->prepare(
        "INSERT INTO sesiones_clase (grupo_materia_maestro_id, fecha, hora_inicio_real, estatus, created_at)
         VALUES (:gmm_id, CURDATE(), NOW(), 'ABIERTA', NOW())"
    );
    $stmtInsert->bindValue(':gmm_id', $gmmId, PDO::PARAM_INT);
    if ($stmtInsert->execute()) {
        $opened++;
    }
}

echo "Sesiones abiertas: {$opened}\n";
