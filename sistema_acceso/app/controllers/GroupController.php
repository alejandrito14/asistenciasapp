<?php
require_once '../app/config/db.php';
require_once '../app/models/Group.php';

class GroupController {
    private $groupModel;
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
        $this->groupModel = new GroupModel($db);
    }

    public function index() {
        $search = trim((string)($_GET['q'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));

        $total = $this->groupModel->countAll($search);
        $groups = $this->groupModel->readPaginated($search, $page, $this->perPage);
        $totalPages = max(1, (int)ceil($total / $this->perPage));

        require_once '../app/views/groups/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Group");
            exit;
        }

        $data = [
            'nombre' => trim((string)($_POST['nombre'] ?? '')),
            'semestre' => trim((string)($_POST['semestre'] ?? '')),
            'turno' => trim((string)($_POST['turno'] ?? '')),
            'ciclo_escolar' => trim((string)($_POST['ciclo_escolar'] ?? '')),
            'codigo_ingreso' => trim((string)($_POST['codigo_ingreso'] ?? '')),
            'requiere_aprobacion' => isset($_POST['requiere_aprobacion']) ? 1 : 0,
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];

        if ($data['nombre'] !== '' && $data['semestre'] !== '' && $data['turno'] !== '' && $data['ciclo_escolar'] !== '') {
            $this->groupModel->create($data);
            header("Location: ?c=Group&msg=creado");
            exit;
        }

        header("Location: ?c=Group&err=datos_invalidos");
        exit;
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $group = $this->groupModel->getById($id);

        if (!$group) {
            header("Location: ?c=Group&err=no_encontrado");
            exit;
        }

        require_once '../app/views/groups/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Group");
            exit;
        }

        $data = [
            'id' => (int)($_POST['id'] ?? 0),
            'nombre' => trim((string)($_POST['nombre'] ?? '')),
            'semestre' => trim((string)($_POST['semestre'] ?? '')),
            'turno' => trim((string)($_POST['turno'] ?? '')),
            'ciclo_escolar' => trim((string)($_POST['ciclo_escolar'] ?? '')),
            'codigo_ingreso' => trim((string)($_POST['codigo_ingreso'] ?? '')),
            'requiere_aprobacion' => isset($_POST['requiere_aprobacion']) ? 1 : 0,
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];

        if ($data['id'] > 0 && $data['nombre'] !== '' && $data['semestre'] !== '' && $data['turno'] !== '' && $data['ciclo_escolar'] !== '') {
            $this->groupModel->update($data);
            header("Location: ?c=Group&msg=actualizado");
            exit;
        }

        header("Location: ?c=Group&err=datos_invalidos");
        exit;
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header("Location: ?c=Group&err=datos_invalidos");
            exit;
        }

        if ($this->groupModel->hasRelations($id)) {
            header("Location: ?c=Group&err=tiene_relaciones");
            exit;
        }

        $this->groupModel->delete($id);
        header("Location: ?c=Group&msg=eliminado");
        exit;
    }
}
?>
