<?php
class SubjectModel {
    private $conn;
    private $table = 'materias';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function countAll($search = '') {
        $query = "SELECT COUNT(*) AS total FROM " . $this->table;
        if ($search !== '') {
            $query .= " WHERE clave LIKE :search
                        OR nombre LIKE :search
                        OR descripcion LIKE :search";
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
        $query = "SELECT * FROM " . $this->table;
        if ($search !== '') {
            $query .= " WHERE clave LIKE :search
                        OR nombre LIKE :search
                        OR descripcion LIKE :search";
        }

        $query .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
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
                  (clave, nombre, descripcion, activo)
                  VALUES (:clave, :nombre, :descripcion, :activo)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':clave', $data['clave']);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindValue(':activo', (int)($data['activo'] ?? 1), PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function update(array $data) {
        $query = "UPDATE " . $this->table . "
                  SET clave = :clave,
                      nombre = :nombre,
                      descripcion = :descripcion,
                      activo = :activo
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
        $stmt->bindParam(':clave', $data['clave']);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
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
        $tables = ['grupo_materias'];
        foreach ($tables as $table) {
            $query = "SELECT COUNT(*) AS total FROM " . $table . " WHERE materia_id = :id";
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
