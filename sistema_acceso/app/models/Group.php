<?php
class GroupModel {
    private $conn;
    private $table = 'grupos';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function countAll($search = '') {
        $query = "SELECT COUNT(*) AS total FROM " . $this->table;

        if ($search !== '') {
            $query .= " WHERE nombre LIKE :search
                        OR semestre LIKE :search
                        OR turno LIKE :search
                        OR ciclo_escolar LIKE :search
                        OR codigo_ingreso LIKE :search";
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
            $query .= " WHERE nombre LIKE :search
                        OR semestre LIKE :search
                        OR turno LIKE :search
                        OR ciclo_escolar LIKE :search
                        OR codigo_ingreso LIKE :search";
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
                  (nombre, semestre, turno, ciclo_escolar, codigo_ingreso, requiere_aprobacion, activo)
                  VALUES (:nombre, :semestre, :turno, :ciclo_escolar, :codigo_ingreso, :requiere_aprobacion, :activo)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':semestre', $data['semestre']);
        $stmt->bindParam(':turno', $data['turno']);
        $stmt->bindParam(':ciclo_escolar', $data['ciclo_escolar']);
        $stmt->bindParam(':codigo_ingreso', $data['codigo_ingreso']);
        $stmt->bindParam(':requiere_aprobacion', $data['requiere_aprobacion'], PDO::PARAM_INT);
        $stmt->bindParam(':activo', $data['activo'], PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function update(array $data) {
        $query = "UPDATE " . $this->table . "
                  SET nombre = :nombre,
                      semestre = :semestre,
                      turno = :turno,
                      ciclo_escolar = :ciclo_escolar,
                      codigo_ingreso = :codigo_ingreso,
                      requiere_aprobacion = :requiere_aprobacion,
                      activo = :activo
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':semestre', $data['semestre']);
        $stmt->bindParam(':turno', $data['turno']);
        $stmt->bindParam(':ciclo_escolar', $data['ciclo_escolar']);
        $stmt->bindParam(':codigo_ingreso', $data['codigo_ingreso']);
        $stmt->bindParam(':requiere_aprobacion', $data['requiere_aprobacion'], PDO::PARAM_INT);
        $stmt->bindParam(':activo', $data['activo'], PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function hasRelations($id) {
        $tables = ['alumnos_grupos', 'grupo_materias'];
        foreach ($tables as $table) {
            $query = "SELECT COUNT(*) AS total FROM " . $table . " WHERE grupo_id = :id";
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
