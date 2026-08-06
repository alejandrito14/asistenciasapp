<?php
class GroupMateriaMaestroModel {
    private $conn;
    private $table = 'grupo_materia_maestro';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function countAll($search = '') {
        $query = "SELECT COUNT(*) AS total
                  FROM " . $this->table . " gmm
                  INNER JOIN grupo_materias gm ON gmm.grupo_materia_id = gm.id
                  INNER JOIN grupos g ON gm.grupo_id = g.id
                  INNER JOIN materias m ON gm.materia_id = m.id
                  INNER JOIN maestros ma ON gmm.maestro_id = ma.id";

        if ($search !== '') {
            $query .= " WHERE g.nombre LIKE :search
                        OR m.nombre LIKE :search
                        OR ma.nombre LIKE :search
                        OR ma.apellido_paterno LIKE :search";
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
        $query = "SELECT gmm.id, gmm.grupo_materia_id, gmm.maestro_id, gmm.activo,
                         g.nombre AS grupo_nombre, g.semestre,
                         m.clave AS materia_clave, m.nombre AS materia_nombre,
                         ma.nombre AS maestro_nombre, ma.apellido_paterno, ma.apellido_materno
                  FROM " . $this->table . " gmm
                  INNER JOIN grupo_materias gm ON gmm.grupo_materia_id = gm.id
                  INNER JOIN grupos g ON gm.grupo_id = g.id
                  INNER JOIN materias m ON gm.materia_id = m.id
                  INNER JOIN maestros ma ON gmm.maestro_id = ma.id";

        if ($search !== '') {
            $query .= " WHERE g.nombre LIKE :search
                        OR m.nombre LIKE :search
                        OR ma.nombre LIKE :search
                        OR ma.apellido_paterno LIKE :search";
        }

        $query .= " ORDER BY gmm.id DESC LIMIT :limit OFFSET :offset";
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

    public function existsByGroupSubjectAndTeacher(int $grupoMateriaId, int $maestroId, int $excludeId = 0): bool {
        $query = "SELECT COUNT(*) AS total
                  FROM " . $this->table . "
                  WHERE grupo_materia_id = :grupo_materia_id
                    AND maestro_id = :maestro_id";
        if ($excludeId > 0) {
            $query .= " AND id <> :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':grupo_materia_id', $grupoMateriaId, PDO::PARAM_INT);
        $stmt->bindValue(':maestro_id', $maestroId, PDO::PARAM_INT);
        if ($excludeId > 0) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0) > 0;
    }

    public function create(array $data) {
        $query = "INSERT INTO " . $this->table . "
                  (grupo_materia_id, maestro_id, activo)
                  VALUES (:grupo_materia_id, :maestro_id, :activo)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':grupo_materia_id', $data['grupo_materia_id'], PDO::PARAM_INT);
        $stmt->bindParam(':maestro_id', $data['maestro_id'], PDO::PARAM_INT);
        $stmt->bindValue(':activo', (int)($data['activo'] ?? 1), PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function update(array $data) {
        $query = "UPDATE " . $this->table . "
                  SET grupo_materia_id = :grupo_materia_id,
                      maestro_id = :maestro_id,
                      activo = :activo
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
        $stmt->bindParam(':grupo_materia_id', $data['grupo_materia_id'], PDO::PARAM_INT);
        $stmt->bindParam(':maestro_id', $data['maestro_id'], PDO::PARAM_INT);
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
        $tables = ['horarios', 'sesiones_clase'];
        foreach ($tables as $table) {
            $query = "SELECT COUNT(*) AS total FROM " . $table . " WHERE grupo_materia_maestro_id = :id";
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
