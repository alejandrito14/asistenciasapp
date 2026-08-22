<?php
class Usuario {
    private $conn;
    private $table = 'usuarios';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function findByCorreo($correo) {
        $query = "SELECT id, nombre, correo, password, rol, activo,
                         estado_cuenta,
                         verification_code_hash, verification_expires_at, email_verified_at
                  FROM " . $this->table . "
                  WHERE correo = :correo
                  ORDER BY id DESC
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById(int $id) {
        $query = "SELECT id, nombre, correo, password, rol, activo,
                         estado_cuenta,
                         verification_code_hash, verification_expires_at, email_verified_at
                  FROM " . $this->table . "
                  WHERE id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function login($correo, $password) {
        $user = $this->findByCorreo($correo);

        if (!$user) {
            return [
                'success' => false,
                'reason' => 'not_found',
            ];
        }

        if (!password_verify($password, $user['password'])) {
            return [
                'success' => false,
                'reason' => 'invalid_password',
            ];
        }

        $estadoCuenta = strtoupper((string)($user['estado_cuenta'] ?? ''));

        if ((int)($user['activo'] ?? 0) !== 1 || $estadoCuenta !== 'ACTIVO') {
            $pendingVerification = $estadoCuenta === 'PENDIENTE' || (!empty($user['verification_code_hash']) && empty($user['email_verified_at']));
            return [
                'success' => false,
                'reason' => $pendingVerification ? 'pending_verification' : 'inactive',
            ];
        }

        return [
            'success' => true,
            'user' => $user,
        ];
    }

    public function emailExists($correo) {
        $query = "SELECT id FROM " . $this->table . "
                  WHERE correo = :correo
                    AND estado_cuenta IN ('PENDIENTE', 'ACTIVO')
                  LIMIT 1";
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
        $activo = (int)($data['activo'] ?? 1);
        $estadoCuenta = strtoupper(trim((string)($data['estado_cuenta'] ?? ($activo === 1 ? 'ACTIVO' : 'PENDIENTE'))));

        if ($this->emailExists($correo)) {
            throw new InvalidArgumentException('El correo ya está registrado.');
        }

        if (!in_array($rol, ['ADMIN', 'MAESTRO', 'ALUMNO'], true)) {
            throw new InvalidArgumentException('Rol inválido. Usa ADMIN, MAESTRO o ALUMNO.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO " . $this->table . "
                  (nombre, correo, password, rol, activo, estado_cuenta, created_at)
                  VALUES (:nombre, :correo, :password, :rol, :activo, :estado_cuenta, NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':password', $hash);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindValue(':activo', $activo, PDO::PARAM_INT);
        $stmt->bindValue(':estado_cuenta', $estadoCuenta);

        if (!$stmt->execute()) {
            throw new RuntimeException('No se pudo crear el usuario.');
        }

        $usuarioId = (int)$this->conn->lastInsertId();

        return [
            'id' => $usuarioId,
            'nombre' => $nombre,
            'correo' => $correo,
            'rol' => $rol,
            'activo' => $activo,
            'estado_cuenta' => $estadoCuenta,
        ];
    }

    public function setVerificationCode(int $userId, string $codeHash, string $expiresAt): bool {
        $query = "UPDATE " . $this->table . "
                  SET verification_code_hash = :code_hash,
                      verification_expires_at = :expires_at
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':code_hash', $codeHash);
        $stmt->bindValue(':expires_at', $expiresAt);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function markEmailVerified(int $userId): bool {
        $query = "UPDATE " . $this->table . "
                  SET activo = 1,
                      estado_cuenta = 'ACTIVO',
                      verification_code_hash = NULL,
                      verification_expires_at = NULL,
                      email_verified_at = NOW()
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deactivateAccount(int $userId): bool {
        $query = "UPDATE " . $this->table . "
                  SET activo = 0,
                      estado_cuenta = 'ELIMINADO',
                      verification_code_hash = NULL,
                      verification_expires_at = NULL
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
