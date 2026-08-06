<?php
// public/init_pass.php
require_once '../app/config/db.php';

$database = new Database();
$db = $database->getConnection();

// La contraseña será "123456"
$passHash = password_hash("123456", PASSWORD_DEFAULT);

$query = "UPDATE employees SET password = :p WHERE password IS NULL";
$stmt = $db->prepare($query);
$stmt->bindParam(':p', $passHash);

if($stmt->execute()) {
    echo "<h1>¡Éxito!</h1>";
    echo "<p>Todos los empleados ahora tienen la contraseña: <b>123456</b></p>";
    echo "<p>Ya puedes borrar este archivo.</p>";
} else {
    echo "Error al actualizar.";
}
?>