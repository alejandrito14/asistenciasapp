<?php
class Usuario {
    private $conn;
    private $table = 'usuarios';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function findByCorreo($correo) {
        $query = "SELECT id, nombre, correo, password, rol, activo
                  FROM " . $this->table . "
                  WHERE correo = :correo
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function login($correo, $password) {
        $user = $this->findByCorreo($correo);

        if (!$user || !$user['activo']) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        return $user;
    }

    public function emailExists($correo) {
        $query = "SELECT id FROM " . $this->table . " WHERE correo = :correo LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function register(array $data) {
        $nombre = trim((string)($data['nombre'] ?? ''));
        $correo = trim((string)($data['correo'] ?? ''));
        $password = (string)($data['password'] ?? '');
        $rol = strtoupper(trim((string)($data['rol'] ?? 'MAESTRO')));

        if ($this->emailExists($correo)) {
            throw new InvalidArgumentException('El correo ya está registrado.');
        }

        if (!in_array($rol, ['ADMIN', 'MAESTRO', 'ALUMNO'], true)) {
            throw new InvalidArgumentException('Rol inválido. Usa ADMIN, MAESTRO o ALUMNO.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO " . $this->table . "
                  (nombre, correo, password, rol, activo, created_at)
                  VALUES (:nombre, :correo, :password, :rol, 1, NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':password', $hash);
        $stmt->bindParam(':rol', $rol);

        if (!$stmt->execute()) {
            throw new RuntimeException('No se pudo crear el usuario.');
        }

        $usuarioId = (int)$this->conn->lastInsertId();

        return [
            'id' => $usuarioId,
            'nombre' => $nombre,
            'correo' => $correo,
            'rol' => $rol,
        ];
    }
}
?>
