<?php
require_once '../app/config/db.php';
require_once '../app/models/User.php';

class UserController {
    private $userModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        // Seguridad estricta: Solo Admin puede estar aquí
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
            header("Location: ?c=Dashboard");
            exit;
        }

        $database = new Database();
        $this->userModel = new User($database->getConnection());
    }

    public function index() {
        $users = $this->userModel->readAll();
        require_once '../app/views/users/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $_POST['username'];
            $pass = $_POST['password'];
            $role = $_POST['role'];
            
            if($this->userModel->create($user, $pass, $role)){
                header("Location: ?c=User&msg=creado");
            } else {
                header("Location: ?c=User&err=existe");
            }
        }
    }

    public function edit() {
        if (isset($_GET['id'])) {
            $user = $this->userModel->getById($_GET['id']);
            require_once '../app/views/users/edit.php';
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $user = $_POST['username'];
            $role = $_POST['role'];
            $pass = !empty($_POST['password']) ? $_POST['password'] : null;

            $this->userModel->update($id, $user, $role, $pass);
            header("Location: ?c=User&msg=actualizado");
        }
    }

    // NUEVO: Activar / Desactivar
    public function toggle() {
        if (isset($_GET['id']) && isset($_GET['status'])) {
            $id = $_GET['id'];
            
            // Evitar desactivarse a uno mismo
            if ($id == $_SESSION['user_id']) {
                header("Location: ?c=User&err=self_disable");
                exit;
            }

            $currentStatus = $_GET['status'];
            $newStatus = ($currentStatus == 'activo') ? 'inactivo' : 'activo';
            
            $this->userModel->toggleStatus($id, $newStatus);
            header("Location: ?c=User&msg=estado_cambiado");
        }
    }

    public function change_own_password() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentId = $_SESSION['user_id'];
            $newPass = $_POST['new_password'];
            $confirmPass = $_POST['confirm_password'];

            if ($newPass === $confirmPass) {
                $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                $this->userModel->updatePassword($currentId, $newHash);
                header('Location: ' . $_SERVER['HTTP_REFERER'] . '&msg=pass_ok');
            } else {
                header('Location: ' . $_SERVER['HTTP_REFERER'] . '&err=pass_mismatch');
            }
        }
    }
}
?>