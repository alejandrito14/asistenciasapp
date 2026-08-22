<?php
class Maestro {
    private $conn;
    private $table = 'maestros';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createForUsuario(int $usuarioId, array $data) {
        $nombre = trim((string)($data['nombre'] ?? ''));
        $apellidoPaterno = trim((string)($data['apellido_paterno'] ?? ''));
        $apellidoMaterno = trim((string)($data['apellido_materno'] ?? ''));
        $telefono = trim((string)($data['telefono'] ?? ''));
        $correo = trim((string)($data['correo'] ?? ''));

        $query = "INSERT INTO " . $this->table . "
                  (usuario_id, nombre, apellido_paterno, apellido_materno, telefono, correo, activo)
                  VALUES (:usuario_id, :nombre, :apellido_paterno, :apellido_materno, :telefono, :correo, 1)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido_paterno', $apellidoPaterno);
        $stmt->bindParam(':apellido_materno', $apellidoMaterno);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':correo', $correo);

        if (!$stmt->execute()) {
            throw new RuntimeException('No se pudo crear el perfil de maestro.');
        }

        return (int)$this->conn->lastInsertId();
    }

    public function findByUsuarioId(int $usuarioId): ?array {
        $query = "SELECT * FROM " . $this->table . " WHERE usuario_id = :usuario_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
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
