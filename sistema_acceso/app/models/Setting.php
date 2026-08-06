<?php
class Setting {
    private $conn;
    private $table = "settings";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener un valor por su nombre (Ej: 'entry_time')
    public function get($name) {
        $query = "SELECT setting_value FROM " . $this->table . " WHERE setting_name = :name LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['setting_value'] : null;
    }

    // Guardar un valor
    public function set($name, $value) {
        $query = "UPDATE " . $this->table . " SET setting_value = :val WHERE setting_name = :name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':val', $value);
        $stmt->bindParam(':name', $name);
        return $stmt->execute();
    }
}
?>