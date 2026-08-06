<?php
class User {
    private $conn;
    private $table = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. LEER TODOS LOS USUARIOS
    public function readAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. OBTENER UN USUARIO (Para editar)
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. CREAR USUARIO (Con status 'activo' por defecto)
    public function create($username, $password, $role) {
        // Verificar si el usuario ya existe para evitar duplicados
        $check = "SELECT id FROM " . $this->table . " WHERE username = :u";
        $stmtCheck = $this->conn->prepare($check);
        $stmtCheck->bindParam(':u', $username);
        $stmtCheck->execute();
        
        if($stmtCheck->rowCount() > 0) {
            return false; // El usuario ya existe
        }

        // Encriptar contraseña
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertar
        $query = "INSERT INTO " . $this->table . " (username, password, role, status) VALUES (:u, :p, :r, 'activo')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':u', $username);
        $stmt->bindParam(':p', $hash);
        $stmt->bindParam(':r', $role);
        return $stmt->execute();
    }

    // 4. ACTUALIZAR USUARIO
    public function update($id, $username, $role, $password = null) {
        if (!empty($password)) {
            // Si escribieron contraseña, la actualizamos encriptada
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE " . $this->table . " SET username = :u, role = :r, password = :p WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':p', $hash);
        } else {
            // Si no, solo actualizamos datos básicos
            $query = "UPDATE " . $this->table . " SET username = :u, role = :r WHERE id = :id";
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->bindParam(':u', $username);
        $stmt->bindParam(':r', $role);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // 5. CAMBIAR ESTADO (ACTIVAR/DESACTIVAR)
    public function toggleStatus($id, $newStatus) {
        $query = "UPDATE " . $this->table . " SET status = :s WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':s', $newStatus);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // 6. CAMBIAR SOLO CONTRASEÑA (Para la función "Cambiar mi clave")
    public function updatePassword($id, $newHash) {
        $query = "UPDATE " . $this->table . " SET password = :p WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':p', $newHash);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // 7. ELIMINAR (Opcional, ya que ahora usamos desactivar)
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>