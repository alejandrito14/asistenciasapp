<?php
class Alumno {
    private $conn;
    private $table = 'alumnos';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function findByMatricula($matricula) {
        $query = "SELECT * FROM " . $this->table . " WHERE matricula = :matricula LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':matricula', $matricula);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readPaginated($search = '', $page = 1, $perPage = 10) {
        $offset = max(0, ($page - 1) * $perPage);
        $query = "SELECT * FROM " . $this->table;

        if ($search !== '') {
            $query .= " WHERE matricula LIKE :search
                        OR nombre LIKE :search
                        OR apellido_paterno LIKE :search
                        OR apellido_materno LIKE :search";
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

    public function create(array $data) {
        $matricula = trim((string)($data['matricula'] ?? ''));
        $nombre = trim((string)($data['nombre'] ?? ''));
        $apellidoPaterno = trim((string)($data['apellido_paterno'] ?? ''));
        $apellidoMaterno = trim((string)($data['apellido_materno'] ?? ''));
        $telefono = trim((string)($data['telefono'] ?? ''));
        $correo = trim((string)($data['correo'] ?? ''));

        if ($matricula === '' || $nombre === '') {
            throw new InvalidArgumentException('Matrícula y nombre son obligatorios.');
        }

        if ($this->findByMatricula($matricula)) {
            throw new InvalidArgumentException('La matrícula ya existe.');
        }

        $query = "INSERT INTO " . $this->table . "
                  (matricula, nombre, apellido_paterno, apellido_materno, telefono, correo, activo)
                  VALUES (:matricula, :nombre, :apellido_paterno, :apellido_materno, :telefono, :correo, 1)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':matricula', $matricula);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido_paterno', $apellidoPaterno);
        $stmt->bindParam(':apellido_materno', $apellidoMaterno);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':correo', $correo);

        if (!$stmt->execute()) {
            throw new RuntimeException('No se pudo registrar al alumno.');
        }

        return [
            'id' => (int)$this->conn->lastInsertId(),
            'matricula' => $matricula,
            'nombre' => $nombre,
            'apellido_paterno' => $apellidoPaterno,
            'apellido_materno' => $apellidoMaterno,
            'telefono' => $telefono,
            'correo' => $correo,
        ];
    }
}
?>
