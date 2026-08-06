<?php
// public/fix_password.php

require_once '../app/config/db.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // 1. Definimos la contraseña que queremos usar
    $passwordPlain = 'admin123'; 
    
    // 2. La encriptamos de verdad
    $passwordHash = password_hash($passwordPlain, PASSWORD_DEFAULT);

    // 3. Actualizamos el usuario 'admin' en la base de datos
    $query = "UPDATE users SET password = :pass WHERE username = 'admin'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':pass', $passwordHash);
    
    if($stmt->execute()) {
        echo "<h1 style='color:green'>¡Contraseña Actualizada con Éxito!</h1>";
        echo "<p>Ahora la contraseña del usuario <b>admin</b> es: <b>admin123</b> (encriptada correctamente).</p>";
        echo "<a href='index.php?c=Auth&a=login'>Volver al Login</a>";
    } else {
        echo "Hubo un error al actualizar.";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>