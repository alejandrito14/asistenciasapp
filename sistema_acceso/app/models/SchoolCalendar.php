<?php
class SchoolCalendar {
    private $conn;
    private $table = 'calendario_escolar';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function countAll($search = '') {
        $query = "SELECT COUNT(*) AS total FROM {$this->table}";
        if ($search !== '') {
            $query .= " WHERE tipo LIKE :search OR descripcion LIKE :search";
        }

        $stmt = $this->conn->prepare($query);
        if ($search !== '') {
            $term = '%' . $search . '%';
            $stmt->bindParam(':search', $term);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public function readPaginated($search = '', $page = 1, $perPage = 10) {
        $offset = max(0, ($page - 1) * $perPage);
        $query = "SELECT * FROM {$this->table}";
        if ($search !== '') {
            $query .= " WHERE tipo LIKE :search OR descripcion LIKE :search";
        }
        $query .= " ORDER BY fecha_inicio DESC, id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        if ($search !== '') {
            $term = '%' . $search . '%';
            $stmt->bindParam(':search', $term);
        }
        $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table}
             (fecha_inicio, fecha_fin, tipo, descripcion, activo)
             VALUES (:fecha_inicio, :fecha_fin, :tipo, :descripcion, :activo)"
        );
        $stmt->bindValue(':fecha_inicio', $data['fecha_inicio']);
        $stmt->bindValue(':fecha_fin', $data['fecha_fin']);
        $stmt->bindValue(':tipo', $data['tipo']);
        $stmt->bindValue(':descripcion', $data['descripcion']);
        $stmt->bindValue(':activo', (int)($data['activo'] ?? 1), PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function update(array $data) {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table}
             SET fecha_inicio = :fecha_inicio,
                 fecha_fin = :fecha_fin,
                 tipo = :tipo,
                 descripcion = :descripcion,
                 activo = :activo
             WHERE id = :id"
        );
        $stmt->bindValue(':id', (int)$data['id'], PDO::PARAM_INT);
        $stmt->bindValue(':fecha_inicio', $data['fecha_inicio']);
        $stmt->bindValue(':fecha_fin', $data['fecha_fin']);
        $stmt->bindValue(':tipo', $data['tipo']);
        $stmt->bindValue(':descripcion', $data['descripcion']);
        $stmt->bindValue(':activo', (int)($data['activo'] ?? 1), PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
