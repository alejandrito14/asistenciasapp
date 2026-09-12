<?php
class SchoolCalendar {
    private $conn;
    private $table = 'calendario_escolar';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getConnection(): PDO {
        return $this->conn;
    }

    public function countAll($search = '') {
        $query = "SELECT COUNT(*) AS total FROM {$this->table} c
                  LEFT JOIN maestros ma ON ma.id = c.maestro_id";
        if ($search !== '') {
            $query .= " WHERE c.tipo LIKE :search OR c.descripcion LIKE :search
                        OR ma.nombre LIKE :search OR ma.apellido_paterno LIKE :search OR ma.apellido_materno LIKE :search";
        }
        $stmt = $this->conn->prepare($query);
        if ($search !== '') {
            $stmt->bindValue(':search', '%' . $search . '%');
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public function readPaginated($search = '', $page = 1, $perPage = 10) {
        $offset = max(0, ($page - 1) * $perPage);
        $query = "SELECT c.*, ma.nombre AS maestro_nombre, ma.apellido_paterno AS maestro_apellido_paterno,
                         ma.apellido_materno AS maestro_apellido_materno
                  FROM {$this->table} c
                  LEFT JOIN maestros ma ON ma.id = c.maestro_id";
        if ($search !== '') {
            $query .= " WHERE c.tipo LIKE :search OR c.descripcion LIKE :search
                        OR ma.nombre LIKE :search OR ma.apellido_paterno LIKE :search OR ma.apellido_materno LIKE :search";
        }
        $query .= " ORDER BY c.fecha_inicio DESC, c.id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        if ($search !== '') {
            $stmt->bindValue(':search', '%' . $search . '%');
        }
        $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT c.*, ma.nombre AS maestro_nombre, ma.apellido_paterno AS maestro_apellido_paterno,
                                             ma.apellido_materno AS maestro_apellido_materno
                                      FROM {$this->table} c
                                      LEFT JOIN maestros ma ON ma.id = c.maestro_id
                                      WHERE c.id = :id LIMIT 1");
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table}
             (fecha_inicio, fecha_fin, tipo, descripcion, maestro_id, activo)
             VALUES (:fecha_inicio, :fecha_fin, :tipo, :descripcion, :maestro_id, :activo)"
        );
        $this->bindCommon($stmt, $data);
        return $stmt->execute();
    }

    public function update(array $data) {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table}
             SET fecha_inicio = :fecha_inicio,
                 fecha_fin = :fecha_fin,
                 tipo = :tipo,
                 descripcion = :descripcion,
                 maestro_id = :maestro_id,
                 activo = :activo
             WHERE id = :id"
        );
        $stmt->bindValue(':id', (int)$data['id'], PDO::PARAM_INT);
        $this->bindCommon($stmt, $data);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    private function bindCommon(PDOStatement $stmt, array $data): void {
        $stmt->bindValue(':fecha_inicio', $data['fecha_inicio']);
        $stmt->bindValue(':fecha_fin', $data['fecha_fin']);
        $stmt->bindValue(':tipo', $data['tipo']);
        $stmt->bindValue(':descripcion', $data['descripcion']);
        $teacherId = (int)($data['maestro_id'] ?? 0);
        $stmt->bindValue(':maestro_id', $teacherId > 0 ? $teacherId : null, $teacherId > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':activo', (int)($data['activo'] ?? 1), PDO::PARAM_INT);
    }
}
?>
