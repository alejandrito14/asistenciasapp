<?php
require_once '../app/config/db.php';
require_once '../app/models/Subject.php';

class SubjectController {
    private $subjectModel;
    private $perPage = 10;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id'])) {
            header("Location: ?c=Auth&a=login");
            exit;
        }

        if ($_SESSION['role'] != 'admin') {
            header("Location: ?c=Dashboard");
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        $this->subjectModel = new SubjectModel($db);
    }

    public function index() {
        $search = trim((string)($_GET['q'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));

        $total = $this->subjectModel->countAll($search);
        $subjects = $this->subjectModel->readPaginated($search, $page, $this->perPage);
        $totalPages = max(1, (int)ceil($total / $this->perPage));

        require_once '../app/views/subjects/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Subject");
            exit;
        }

        $data = [
            'clave' => trim((string)($_POST['clave'] ?? '')),
            'nombre' => trim((string)($_POST['nombre'] ?? '')),
            'descripcion' => trim((string)($_POST['descripcion'] ?? '')),
            'activo' => isset($_POST['activo']) ? (int)$_POST['activo'] : 1,
        ];

        if ($data['clave'] !== '' && $data['nombre'] !== '') {
            $this->subjectModel->create($data);
            header("Location: ?c=Subject&msg=creado");
            exit;
        }

        header("Location: ?c=Subject&err=datos_invalidos");
        exit;
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $subject = $this->subjectModel->getById($id);

        if (!$subject) {
            header("Location: ?c=Subject&err=no_encontrado");
            exit;
        }

        require_once '../app/views/subjects/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Subject");
            exit;
        }

        $data = [
            'id' => (int)($_POST['id'] ?? 0),
            'clave' => trim((string)($_POST['clave'] ?? '')),
            'nombre' => trim((string)($_POST['nombre'] ?? '')),
            'descripcion' => trim((string)($_POST['descripcion'] ?? '')),
            'activo' => isset($_POST['activo']) ? (int)$_POST['activo'] : 1,
        ];

        if ($data['id'] > 0 && $data['clave'] !== '' && $data['nombre'] !== '') {
            $this->subjectModel->update($data);
            header("Location: ?c=Subject&msg=actualizado");
            exit;
        }

        header("Location: ?c=Subject&err=datos_invalidos");
        exit;
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header("Location: ?c=Subject&err=datos_invalidos");
            exit;
        }

        if ($this->subjectModel->hasRelations($id)) {
            header("Location: ?c=Subject&err=tiene_relaciones");
            exit;
        }

        $this->subjectModel->delete($id);
        header("Location: ?c=Subject&msg=eliminado");
        exit;
    }

    public function toggle() {
        $id = (int)($_GET['id'] ?? 0);
        $status = isset($_GET['status']) ? (int)$_GET['status'] : -1;
        if ($id <= 0 || !in_array($status, [0, 1], true)) {
            header("Location: ?c=Subject&err=datos_invalidos");
            exit;
        }

        $newStatus = ($status === 1) ? 0 : 1;
        $this->subjectModel->toggleStatus($id, $newStatus);
        header("Location: ?c=Subject&msg=estado_cambiado");
        exit;
    }
}
?>
