<?php
// Cargar configuración si no se ha cargado antes
require_once '../app/config/db.php';

class AuthController {
    
    public function login() {
        // Si ya hay sesión iniciada, mandar al Dashboard directo
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['user_id'])) {
            header("Location: ?c=Dashboard");
            exit;
        }
        require_once '../app/views/auth/login.php';
    }

    public function authenticate() {
        $database = new Database();
        $db = $database->getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            // 1. BUSCAR USUARIO
            // IMPORTANTE: Agregamos "AND status = 'activo'" para bloquear usuarios desactivados
            $query = "SELECT * FROM users WHERE username = :username AND status = 'activo' LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. VERIFICAR CONTRASEÑA
            if ($user && password_verify($password, $user['password'])) {
                
                // Iniciar Sesión
                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                
                // Redirigir al Dashboard
                header("Location: ?c=Dashboard");
                exit;
            } else {
                // Si falla, redirigir con mensaje de error
                // Esto cubre: usuario no existe, contraseña mal, O usuario inactivo
                $error = "Usuario o contraseña incorrectos, o cuenta desactivada.";
                header("Location: ?c=Auth&a=login&error=" . urlencode($error));
                exit;
            }
        }
    }

    public function logout() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        session_destroy();
        header("Location: ?c=Auth&a=login");
    }
}
?>