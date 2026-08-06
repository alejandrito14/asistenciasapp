<?php
class Employee {
    private $conn;
    private $table = 'maestros';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read($search = '') {
        $query = "SELECT * FROM " . $this->table;
        if ($search !== '') {
            $query .= " WHERE nombre LIKE :search
                        OR apellido_paterno LIKE :search
                        OR apellido_materno LIKE :search
                        OR telefono LIKE :search
                        OR correo LIKE :search";
        }
        $query .= " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        if ($search !== '') {
            $term = '%' . $search . '%';
            $stmt->bindParam(':search', $term);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . "
                  (usuario_id, nombre, apellido_paterno, apellido_materno, telefono, correo, activo)
                  VALUES (:usuario_id, :nombre, :apellido_paterno, :apellido_materno, :telefono, :correo, :activo)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':usuario_id', (int)($data['usuario_id'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':nombre', $data['nombre']);
        $stmt->bindValue(':apellido_paterno', $data['apellido_paterno']);
        $stmt->bindValue(':apellido_materno', $data['apellido_materno']);
        $stmt->bindValue(':telefono', $data['telefono']);
        $stmt->bindValue(':correo', $data['correo']);
        $stmt->bindValue(':activo', (int)($data['activo'] ?? 1), PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function update($data) {
        $query = "UPDATE " . $this->table . "
                  SET nombre = :nombre,
                      apellido_paterno = :apellido_paterno,
                      apellido_materno = :apellido_materno,
                      telefono = :telefono,
                      correo = :correo,
                      activo = :activo
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);
        $stmt->bindValue(':nombre', $data['nombre']);
        $stmt->bindValue(':apellido_paterno', $data['apellido_paterno']);
        $stmt->bindValue(':apellido_materno', $data['apellido_materno']);
        $stmt->bindValue(':telefono', $data['telefono']);
        $stmt->bindValue(':correo', $data['correo']);
        $stmt->bindValue(':activo', (int)($data['activo'] ?? 1), PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function toggleStatus($id, $newStatus) {
        $stmt = $this->conn->prepare("UPDATE " . $this->table . " SET activo = :activo WHERE id = :id");
        $stmt->bindValue(':activo', (int)$newStatus, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table . " WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
