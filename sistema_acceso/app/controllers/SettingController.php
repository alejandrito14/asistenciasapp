<?php
require_once '../app/config/db.php';
require_once '../app/models/Setting.php';

class SettingController {
    private $settingModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        // Solo Admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
            header("Location: ?c=Dashboard");
            exit;
        }

        $database = new Database();
        $this->settingModel = new Setting($database->getConnection());
    }

    public function index() {
        // Obtenemos la hora actual guardada
        $entry_time = $this->settingModel->get('entry_time');
        require_once '../app/views/settings/index.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $time = $_POST['entry_time'];
            // Guardamos la nueva hora
            $this->settingModel->set('entry_time', $time);
            header("Location: ?c=Setting&msg=guardado");
        }
    }
}
?>