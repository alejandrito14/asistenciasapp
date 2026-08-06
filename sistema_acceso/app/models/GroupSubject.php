<?php
class GroupSubjectModel {
    private $conn;
    private $table = 'grupo_materias';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function countAll($search = '') {
        $query = "SELECT COUNT(*) AS total
                  FROM " . $this->table . " gm
                  INNER JOIN grupos g ON gm.grupo_id = g.id
                  INNER JOIN materias m ON gm.materia_id = m.id";
        if ($search !== '') {
            $query .= " WHERE g.nombre LIKE :search
                        OR g.grado LIKE :search
                        OR m.clave LIKE :search
                        OR m.nombre LIKE :search";
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
        $query = "SELECT gm.id, gm.grupo_id, gm.materia_id,
                         g.nombre AS grupo_nombre, g.grado, g.semestre, g.turno,
                         m.clave AS materia_clave, m.nombre AS materia_nombre
                  FROM " . $this->table . " gm
                  INNER JOIN grupos g ON gm.grupo_id = g.id
                  INNER JOIN materias m ON gm.materia_id = m.id";
        if ($search !== '') {
            $query .= " WHERE g.nombre LIKE :search
                        OR g.grado LIKE :search
                        OR m.clave LIKE :search
                        OR m.nombre LIKE :search";
        }

        $query .= " ORDER BY gm.id DESC LIMIT :limit OFFSET :offset";
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

    public function existsByGroupAndSubject(int $grupoId, int $materiaId, int $excludeId = 0): bool {
        $query = "SELECT COUNT(*) AS total
                  FROM " . $this->table . "
                  WHERE grupo_id = :grupo_id
                    AND materia_id = :materia_id";
        if ($excludeId > 0) {
            $query .= " AND id <> :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':grupo_id', $grupoId, PDO::PARAM_INT);
        $stmt->bindValue(':materia_id', $materiaId, PDO::PARAM_INT);
        if ($excludeId > 0) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0) > 0;
    }

    public function create(array $data) {
        $query = "INSERT INTO " . $this->table . "
                  (grupo_id, materia_id)
                  VALUES (:grupo_id, :materia_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':grupo_id', $data['grupo_id'], PDO::PARAM_INT);
        $stmt->bindParam(':materia_id', $data['materia_id'], PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function update(array $data) {
        $query = "UPDATE " . $this->table . "
                  SET grupo_id = :grupo_id,
                      materia_id = :materia_id
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
        $stmt->bindParam(':grupo_id', $data['grupo_id'], PDO::PARAM_INT);
        $stmt->bindParam(':materia_id', $data['materia_id'], PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function hasRelations($id) {
        $tables = ['grupo_materia_maestro', 'horarios', 'sesiones_clase'];
        foreach ($tables as $table) {
            $query = "SELECT COUNT(*) AS total FROM " . $table . " WHERE grupo_materia_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ((int)($row['total'] ?? 0) > 0) {
                return true;
            }
        }
        return false;
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
