<?php
// public/fix_passwords.php
require_once '../app/config/db.php';

try {
    echo "<h2>🔧 Reparando Base de Datos...</h2>";
    
    $database = new Database();
    $db = $database->getConnection();

    // 1. Generar el hash de "123456"
    $passHash = password_hash("123456", PASSWORD_DEFAULT);

    // 2. Actualizar solo los que tienen NULL
    $query = "UPDATE employees SET password = :p WHERE password IS NULL OR password = ''";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':p', $passHash);

    if($stmt->execute()) {
        $count = $stmt->rowCount();
        echo "<div style='color: green; font-weight: bold;'>";
        echo "✅ Éxito: Se actualizaron las contraseñas de $count empleados.<br>";
        echo "Ahora todos pueden entrar con la clave: 123456";
        echo "</div>";
        echo "<br><a href='index.php?c=Portal&a=login'>Ir al Login del Empleado</a>";
    } else {
        echo "<div style='color: red;'>❌ Error al actualizar en la BD.</div>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>