<?php
require_once '../app/config/db.php';
require_once '../app/models/Department.php';

class DepartmentController {
    private $deptModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header("Location: ?c=Auth&a=login");
            exit;
        }
        
        // BLOQUEO DE ROL
        if ($_SESSION['role'] != 'admin') {
            header("Location: ?c=Dashboard");
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        $this->deptModel = new Department($db);
    }

    // ... (Tus métodos index, store, edit, update, toggle, delete siguen igual) ...
    
    public function index() {
        $departments = $this->deptModel->readAll();
        require_once '../app/views/departments/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            if (!empty($name)) $this->deptModel->create($name);
            header("Location: ?c=Department&msg=creado");
        }
    }

    public function edit() {
        if (isset($_GET['id'])) {
            $dept = $this->deptModel->getById($_GET['id']);
            if ($dept) require_once '../app/views/departments/edit.php';
            else header("Location: ?c=Department");
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            if (!empty($name) && !empty($id)) $this->deptModel->update($id, $name);
            header("Location: ?c=Department&msg=actualizado");
        }
    }

    public function toggle() {
        if (isset($_GET['id']) && isset($_GET['status'])) {
            $id = $_GET['id'];
            $newStatus = ($_GET['status'] == 'activo') ? 'inactivo' : 'activo';
            $this->deptModel->toggleStatus($id, $newStatus);
            header("Location: ?c=Department&msg=estado_cambiado");
        }
    }

    public function delete() {
        if (isset($_GET['id'])) {
            $this->deptModel->delete($_GET['id']);
            header("Location: ?c=Department&msg=eliminado");
        }
    }
}
?>