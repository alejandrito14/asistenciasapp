<?php
class Database {
    private $host = "127.0.0.1";
    private $db_name = "control_asistencia_db";
    private $username = "root";
    private $password = "root"; // En Laragon suele ser vacío
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host .";port=8889;dbname=" . $this->db_name, $this->username, $this->password);
            
            // 1. Configurar caracteres especiales (ñ, tildes)
            $this->conn->exec("set names utf8");
            
            // 2. SINCRONIZAR ZONA HORARIA CON PHP
            // date("P") devuelve la diferencia horaria configurada en index.php (ej: "-05:00")
            // Esto obliga a MySQL a guardar la hora exacta de Perú.
            $offset = date("P"); 
            $this->conn->exec("SET time_zone='$offset';");

        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>