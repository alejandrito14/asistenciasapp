<?php
require_once '../app/config/db.php';
require_once '../app/models/GroupSubject.php';

class GroupSubjectController {
    private $model;
    private $db;
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
        $this->db = $database->getConnection();
        $this->model = new GroupSubjectModel($this->db);
    }

    public function index() {
        $search = trim((string)($_GET['q'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $total = $this->model->countAll($search);
        $items = $this->model->readPaginated($search, $page, $this->perPage);
        $totalPages = max(1, (int)ceil($total / $this->perPage));

        $groups = $this->db->query("SELECT id, nombre, grado, semestre FROM grupos ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        $subjects = $this->db->query("SELECT id, clave, nombre FROM materias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

        require_once '../app/views/group_subjects/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ?c=GroupSubject"); exit; }
        $data = [
            'grupo_id' => (int)($_POST['grupo_id'] ?? 0),
            'materia_id' => (int)($_POST['materia_id'] ?? 0),
        ];
        if ($data['grupo_id'] > 0 && $data['materia_id'] > 0) {
            if ($this->model->existsByGroupAndSubject($data['grupo_id'], $data['materia_id'])) {
                header("Location: ?c=GroupSubject&err=duplicado");
                exit;
            }
            $this->model->create($data);
            header("Location: ?c=GroupSubject&msg=creado"); exit;
        }
        header("Location: ?c=GroupSubject&err=datos_invalidos"); exit;
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $item = $this->model->getById($id);
        if (!$item) { header("Location: ?c=GroupSubject&err=no_encontrado"); exit; }
        $groups = $this->db->query("SELECT id, nombre, grado, semestre FROM grupos ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        $subjects = $this->db->query("SELECT id, clave, nombre FROM materias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        require_once '../app/views/group_subjects/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ?c=GroupSubject"); exit; }
        $data = [
            'id' => (int)($_POST['id'] ?? 0),
            'grupo_id' => (int)($_POST['grupo_id'] ?? 0),
            'materia_id' => (int)($_POST['materia_id'] ?? 0),
        ];
        if ($data['id'] > 0 && $data['grupo_id'] > 0 && $data['materia_id'] > 0) {
            if ($this->model->existsByGroupAndSubject($data['grupo_id'], $data['materia_id'], $data['id'])) {
                header("Location: ?c=GroupSubject&a=edit&id=" . $data['id'] . "&err=duplicado");
                exit;
            }
            $this->model->update($data);
            header("Location: ?c=GroupSubject&msg=actualizado"); exit;
        }
        header("Location: ?c=GroupSubject&err=datos_invalidos"); exit;
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { header("Location: ?c=GroupSubject&err=datos_invalidos"); exit; }
        if ($this->model->hasRelations($id)) { header("Location: ?c=GroupSubject&err=tiene_relaciones"); exit; }
        $this->model->delete($id);
        header("Location: ?c=GroupSubject&msg=eliminado"); exit;
    }
}
?>
