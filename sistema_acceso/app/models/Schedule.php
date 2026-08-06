<?php
class ScheduleModel {
    private $conn;
    private $table = 'horarios';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function countAll($search = '') {
        $query = "SELECT COUNT(*) AS total
                  FROM " . $this->table . " h
                  INNER JOIN grupo_materia_maestro gmm ON h.grupo_materia_maestro_id = gmm.id
                  INNER JOIN grupo_materias gm ON gmm.grupo_materia_id = gm.id
                  INNER JOIN grupos g ON gm.grupo_id = g.id
                  INNER JOIN materias m ON gm.materia_id = m.id
                  INNER JOIN maestros ma ON gmm.maestro_id = ma.id";
        if ($search !== '') {
            $query .= " WHERE g.nombre LIKE :search
                        OR m.nombre LIKE :search
                        OR ma.nombre LIKE :search
                        OR h.dia_semana LIKE :search
                        OR h.aula LIKE :search";
        }

        $stmt = $this->conn->prepare($query);
        if ($search !== '') {
            $searchTerm = '%' . $search . '%';
            $stmt->bindParam(':search', $searchTerm);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public function readPaginated($search = '', $page = 1, $perPage = 10) {
        $offset = max(0, ($page - 1) * $perPage);
        $query = "SELECT h.id, h.grupo_materia_maestro_id, h.dia_semana, h.hora_inicio, h.hora_fin, h.aula, h.minutos_antes, h.minutos_despues, h.activo,
                         g.nombre AS grupo_nombre, g.grado, g.semestre,
                         m.clave AS materia_clave, m.nombre AS materia_nombre,
                         ma.nombre AS maestro_nombre, ma.apellido_paterno, ma.apellido_materno
                  FROM " . $this->table . " h
                  INNER JOIN grupo_materia_maestro gmm ON h.grupo_materia_maestro_id = gmm.id
                  INNER JOIN grupo_materias gm ON gmm.grupo_materia_id = gm.id
                  INNER JOIN grupos g ON gm.grupo_id = g.id
                  INNER JOIN materias m ON gm.materia_id = m.id
                  INNER JOIN maestros ma ON gmm.maestro_id = ma.id";
        if ($search !== '') {
            $query .= " WHERE g.nombre LIKE :search
                        OR m.nombre LIKE :search
                        OR ma.nombre LIKE :search
                        OR h.dia_semana LIKE :search
                        OR h.aula LIKE :search";
        }

        $query .= " ORDER BY h.id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        if ($search !== '') {
            $searchTerm = '%' . $search . '%';
            $stmt->bindParam(':search', $searchTerm);
        }
        $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $query = "INSERT INTO " . $this->table . "
                  (grupo_materia_maestro_id, dia_semana, hora_inicio, hora_fin, aula, minutos_antes, minutos_despues, activo)
                  VALUES (:grupo_materia_maestro_id, :dia_semana, :hora_inicio, :hora_fin, :aula, :minutos_antes, :minutos_despues, :activo)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':grupo_materia_maestro_id', $data['grupo_materia_maestro_id'], PDO::PARAM_INT);
        $stmt->bindParam(':dia_semana', $data['dia_semana']);
        $stmt->bindParam(':hora_inicio', $data['hora_inicio']);
        $stmt->bindParam(':hora_fin', $data['hora_fin']);
        $stmt->bindParam(':aula', $data['aula']);
        $stmt->bindValue(':minutos_antes', (int)($data['minutos_antes'] ?? 15), PDO::PARAM_INT);
        $stmt->bindValue(':minutos_despues', (int)($data['minutos_despues'] ?? 15), PDO::PARAM_INT);
        $stmt->bindValue(':activo', (int)($data['activo'] ?? 1), PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function update(array $data) {
        $query = "UPDATE " . $this->table . "
                  SET grupo_materia_maestro_id = :grupo_materia_maestro_id,
                      dia_semana = :dia_semana,
                      hora_inicio = :hora_inicio,
                      hora_fin = :hora_fin,
                      aula = :aula,
                      minutos_antes = :minutos_antes,
                      minutos_despues = :minutos_despues,
                      activo = :activo
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
        $stmt->bindParam(':grupo_materia_maestro_id', $data['grupo_materia_maestro_id'], PDO::PARAM_INT);
        $stmt->bindParam(':dia_semana', $data['dia_semana']);
        $stmt->bindParam(':hora_inicio', $data['hora_inicio']);
        $stmt->bindParam(':hora_fin', $data['hora_fin']);
        $stmt->bindParam(':aula', $data['aula']);
        $stmt->bindValue(':minutos_antes', (int)($data['minutos_antes'] ?? 15), PDO::PARAM_INT);
        $stmt->bindValue(':minutos_despues', (int)($data['minutos_despues'] ?? 15), PDO::PARAM_INT);
        $stmt->bindValue(':activo', (int)($data['activo'] ?? 1), PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function toggleStatus($id, $newStatus) {
        $stmt = $this->conn->prepare("UPDATE " . $this->table . " SET activo = :activo WHERE id = :id");
        $stmt->bindValue(':activo', (int)$newStatus, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function hasRelations($id) {
        $query = "SELECT COUNT(*) AS total FROM asistencias WHERE sesion_clase_id IN (SELECT id FROM sesiones_clase WHERE grupo_materia_maestro_id = :id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0) > 0;
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
