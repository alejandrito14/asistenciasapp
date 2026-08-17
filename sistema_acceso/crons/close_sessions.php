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

if (!isSchoolDay($db, $today)) {
    $reason = schoolCalendarBlockReason($db, $today);
    echo "Dia inhabel. No se cierran sesiones.\n";
    if ($reason) {
        echo "Bloqueo: {$reason['tipo']} - {$reason['descripcion']}\n";
    }
    exit(0);
}

$stmt = $db->prepare(
    "SELECT sc.id AS sesion_clase_id,
            sc.fecha,
            sc.estatus,
            gmm.id AS grupo_materia_maestro_id,
            h.hora_fin,
            COALESCE(h.minutos_despues, 15) AS minutos_despues
     FROM sesiones_clase sc
     INNER JOIN grupo_materia_maestro gmm ON gmm.id = sc.grupo_materia_maestro_id
     INNER JOIN horarios h ON h.grupo_materia_maestro_id = gmm.id AND h.activo = 1
     WHERE sc.estatus = 'ABIERTA'
       AND sc.fecha <= CURDATE()"
);
$stmt->execute();
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$closed = 0;

foreach ($sessions as $session) {
    $sessionDate = (string)($session['fecha'] ?? $today);
    $endTime = (string)($session['hora_fin'] ?? '');
    if ($endTime === '') {
        continue;
    }

    $sessionEnd = DateTime::createFromFormat('Y-m-d H:i:s', $sessionDate . ' ' . $endTime);
    if (!$sessionEnd) {
        continue;
    }

    $closeLimit = clone $sessionEnd;

    if ($now < $closeLimit) {
        continue;
    }

    $stmtUpdate = $db->prepare(
        "UPDATE sesiones_clase
         SET estatus = 'CERRADA',
             hora_fin_real = COALESCE(hora_fin_real, NOW())
         WHERE id = :id
           AND estatus = 'ABIERTA'"
    );
    $stmtUpdate->bindValue(':id', (int)$session['sesion_clase_id'], PDO::PARAM_INT);
    if ($stmtUpdate->execute() && $stmtUpdate->rowCount() > 0) {
        $closed++;
    }
}

echo "Sesiones cerradas: {$closed}\n";
