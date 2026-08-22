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

    public function findByUsuarioId(int $usuarioId): ?array {
        $query = "SELECT * FROM " . $this->table . " WHERE usuario_id = :usuario_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
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
        $usuarioId = (int)($data['usuario_id'] ?? 0);
        $apellidoPaterno = trim((string)($data['apellido_paterno'] ?? ''));
        $apellidoMaterno = trim((string)($data['apellido_materno'] ?? ''));
        $telefono = trim((string)($data['telefono'] ?? ''));
        $correo = trim((string)($data['correo'] ?? ''));
        $tutorNombre = trim((string)($data['tutor_nombre'] ?? ''));
        $tutorApellidoPaterno = trim((string)($data['tutor_apellido_paterno'] ?? ''));
        $tutorApellidoMaterno = trim((string)($data['tutor_apellido_materno'] ?? ''));
        $tutorTelefono = trim((string)($data['tutor_telefono'] ?? ''));
        $tutorCorreo = trim((string)($data['tutor_correo'] ?? ''));

        if ($matricula === '' || $nombre === '') {
            throw new InvalidArgumentException('Matrícula y nombre son obligatorios.');
        }

        if ($this->findByMatricula($matricula)) {
            throw new InvalidArgumentException('La matrícula ya existe.');
        }

        $activo = (int)($data['activo'] ?? 1);

        $query = "INSERT INTO " . $this->table . "
                  (usuario_id, matricula, nombre, apellido_paterno, apellido_materno, telefono, correo,
                   tutor_nombre, tutor_apellido_paterno, tutor_apellido_materno, tutor_telefono, tutor_correo, activo)
                  VALUES (:usuario_id, :matricula, :nombre, :apellido_paterno, :apellido_materno, :telefono, :correo,
                          :tutor_nombre, :tutor_apellido_paterno, :tutor_apellido_materno, :tutor_telefono, :tutor_correo, :activo)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindParam(':matricula', $matricula);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido_paterno', $apellidoPaterno);
        $stmt->bindParam(':apellido_materno', $apellidoMaterno);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':tutor_nombre', $tutorNombre);
        $stmt->bindParam(':tutor_apellido_paterno', $tutorApellidoPaterno);
        $stmt->bindParam(':tutor_apellido_materno', $tutorApellidoMaterno);
        $stmt->bindParam(':tutor_telefono', $tutorTelefono);
        $stmt->bindParam(':tutor_correo', $tutorCorreo);
        $stmt->bindValue(':activo', $activo, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            throw new RuntimeException('No se pudo registrar al alumno.');
        }

        return [
            'id' => (int)$this->conn->lastInsertId(),
            'matricula' => $matricula,
            'nombre' => $nombre,
            'usuario_id' => $usuarioId,
            'apellido_paterno' => $apellidoPaterno,
            'apellido_materno' => $apellidoMaterno,
            'telefono' => $telefono,
            'correo' => $correo,
            'tutor_nombre' => $tutorNombre,
            'tutor_apellido_paterno' => $tutorApellidoPaterno,
            'tutor_apellido_materno' => $tutorApellidoMaterno,
            'tutor_telefono' => $tutorTelefono,
            'tutor_correo' => $tutorCorreo,
            'activo' => $activo,
        ];
    }

    public function updatePhotoPathByUsuarioId(int $usuarioId, string $photoPath): bool {
        $query = "UPDATE " . $this->table . "
                  SET photo_path = :photo_path
                  WHERE usuario_id = :usuario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':photo_path', $photoPath);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
