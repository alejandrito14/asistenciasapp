<?php
require_once '../app/config/db.php';
require_once '../app/models/SchoolCalendar.php';

class SchoolCalendarController {
    private $model;
    private $perPage = 10;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
            header("Location: ?c=Dashboard");
            exit;
        }

        $database = new Database();
        $this->model = new SchoolCalendar($database->getConnection());
    }

    public function index() {
        $search = trim((string)($_GET['q'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $total = $this->model->countAll($search);
        $items = $this->model->readPaginated($search, $page, $this->perPage);
        $totalPages = max(1, (int)ceil($total / $this->perPage));
        require_once '../app/views/school_calendar/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=SchoolCalendar");
            exit;
        }

        $data = [
            'fecha_inicio' => trim((string)($_POST['fecha_inicio'] ?? '')),
            'fecha_fin' => trim((string)($_POST['fecha_fin'] ?? '')),
            'tipo' => trim((string)($_POST['tipo'] ?? '')),
            'descripcion' => trim((string)($_POST['descripcion'] ?? '')),
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];

        if ($data['fecha_inicio'] !== '' && $data['fecha_fin'] !== '' && $data['tipo'] !== '') {
            $this->model->create($data);
            header("Location: ?c=SchoolCalendar&msg=creado");
            exit;
        }

        header("Location: ?c=SchoolCalendar&err=datos_invalidos");
        exit;
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $item = $this->model->getById($id);
        if (!$item) {
            header("Location: ?c=SchoolCalendar&err=no_encontrado");
            exit;
        }
        require_once '../app/views/school_calendar/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=SchoolCalendar");
            exit;
        }

        $data = [
            'id' => (int)($_POST['id'] ?? 0),
            'fecha_inicio' => trim((string)($_POST['fecha_inicio'] ?? '')),
            'fecha_fin' => trim((string)($_POST['fecha_fin'] ?? '')),
            'tipo' => trim((string)($_POST['tipo'] ?? '')),
            'descripcion' => trim((string)($_POST['descripcion'] ?? '')),
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];

        if ($data['id'] > 0 && $data['fecha_inicio'] !== '' && $data['fecha_fin'] !== '' && $data['tipo'] !== '') {
            $this->model->update($data);
            header("Location: ?c=SchoolCalendar&msg=actualizado");
            exit;
        }

        header("Location: ?c=SchoolCalendar&err=datos_invalidos");
        exit;
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
        }
        header("Location: ?c=SchoolCalendar&msg=eliminado");
        exit;
    }
}
?>
