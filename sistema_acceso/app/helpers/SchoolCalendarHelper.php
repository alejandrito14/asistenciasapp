<?php

function isSchoolDay(PDO $db, string $date, ?int $maestroId = null): bool
{
    return schoolCalendarBlockReason($db, $date, $maestroId) === null;
}

function schoolCalendarBlockReason(PDO $db, string $date, ?int $maestroId = null): ?array
{
    $teacherFilter = $maestroId === null ? 'AND maestro_id IS NULL' : 'AND (maestro_id IS NULL OR maestro_id = :maestro_id)';
    $stmt = $db->prepare(
        "SELECT id, tipo, descripcion, fecha_inicio, fecha_fin
         FROM calendario_escolar
         WHERE activo = 1
           AND UPPER(tipo) IN ('INHABIL', 'VACACIONES', 'SUSPENSION')
           AND :date BETWEEN fecha_inicio AND fecha_fin
           {$teacherFilter}
         ORDER BY fecha_inicio DESC, id DESC
         LIMIT 1"
    );
    $stmt->bindValue(':date', $date);
    if ($maestroId !== null) {
        $stmt->bindValue(':maestro_id', $maestroId, PDO::PARAM_INT);
    }
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}
