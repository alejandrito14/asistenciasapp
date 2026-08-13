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
        $entry_time = $this->settingModel->get('entry_time');
        $school_name = $this->settingModel->get('school_name');
        $school_rfc = $this->settingModel->get('school_rfc');
        $school_place = $this->settingModel->get('school_place');
        $school_address = $this->settingModel->get('school_address');
        $school_logo = $this->settingModel->get('school_logo');
        require_once '../app/views/settings/index.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $time = trim((string)($_POST['entry_time'] ?? ''));
            $schoolName = trim((string)($_POST['school_name'] ?? ''));
            $schoolRfc = trim((string)($_POST['school_rfc'] ?? ''));
            $schoolPlace = trim((string)($_POST['school_place'] ?? ''));
            $schoolAddress = trim((string)($_POST['school_address'] ?? ''));

            $this->settingModel->set('entry_time', $time);
            $this->settingModel->set('school_name', $schoolName);
            $this->settingModel->set('school_rfc', $schoolRfc);
            $this->settingModel->set('school_place', $schoolPlace);
            $this->settingModel->set('school_address', $schoolAddress);

            if (!empty($_FILES['school_logo']['tmp_name'])) {
                $uploadDir = __DIR__ . '/../public/uploads/settings';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }

                $originalName = basename((string)($_FILES['school_logo']['name'] ?? 'logo'));
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $safeName = 'school_logo_' . time();
                if ($extension !== '') {
                    $safeName .= '.' . $extension;
                }

                $destination = $uploadDir . '/' . $safeName;
                if (move_uploaded_file($_FILES['school_logo']['tmp_name'], $destination)) {
                    $this->settingModel->set('school_logo', 'uploads/settings/' . $safeName);
                }
            }

            header("Location: ?c=Setting&msg=guardado");
        }
    }
}
?>
