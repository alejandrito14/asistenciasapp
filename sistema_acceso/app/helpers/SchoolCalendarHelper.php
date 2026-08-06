<?php

function isSchoolDay(PDO $db, string $date): bool
{
    $stmt = $db->prepare(
        "SELECT id, tipo
         FROM calendario_escolar
         WHERE activo = 1
           AND :date BETWEEN fecha_inicio AND fecha_fin
         ORDER BY fecha_inicio DESC, id DESC
         LIMIT 1"
    );
    $stmt->bindValue(':date', $date);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return true;
    }

    $type = strtoupper((string)($row['tipo'] ?? ''));
    return !in_array($type, ['VACACIONES', 'INHABIL', 'SUSPENSION'], true);
}

function schoolCalendarBlockReason(PDO $db, string $date): ?array
{
    $stmt = $db->prepare(
        "SELECT id, tipo, descripcion, fecha_inicio, fecha_fin
         FROM calendario_escolar
         WHERE activo = 1
           AND :date BETWEEN fecha_inicio AND fecha_fin
         ORDER BY fecha_inicio DESC, id DESC
         LIMIT 1"
    );
    $stmt->bindValue(':date', $date);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return null;
    }

    $type = strtoupper((string)($row['tipo'] ?? ''));
    if (!in_array($type, ['VACACIONES', 'INHABIL', 'SUSPENSION'], true)) {
        return null;
    }

    return $row;
}
