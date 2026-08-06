<?php
require_once '../app/config/db.php';
require_once '../app/models/GroupMateriaMaestro.php';

class GroupMateriaMaestroController {
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
        $this->model = new GroupMateriaMaestroModel($this->db);
    }

    public function index() {
        $search = trim((string)($_GET['q'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));

        $total = $this->model->countAll($search);
        $items = $this->model->readPaginated($search, $page, $this->perPage);
        $totalPages = max(1, (int)ceil($total / $this->perPage));

        $groupSubjects = $this->db->query(
            "SELECT gm.id,
                    g.nombre AS grupo_nombre, g.grado, g.semestre,
                    m.clave AS materia_clave, m.nombre AS materia_nombre
             FROM grupo_materias gm
             INNER JOIN grupos g ON gm.grupo_id = g.id
             INNER JOIN materias m ON gm.materia_id = m.id
             ORDER BY g.nombre ASC, m.nombre ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $teachers = $this->db->query(
            "SELECT id, nombre, apellido_paterno, apellido_materno
             FROM maestros
             WHERE activo = 1
             ORDER BY nombre ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        require_once '../app/views/group_materia_maestro/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=GroupMateriaMaestro");
            exit;
        }

        $data = [
            'grupo_materia_id' => (int)($_POST['grupo_materia_id'] ?? 0),
            'maestro_id' => (int)($_POST['maestro_id'] ?? 0),
            'activo' => isset($_POST['activo']) ? (int)$_POST['activo'] : 1,
        ];

        if ($data['grupo_materia_id'] > 0 && $data['maestro_id'] > 0) {
            if ($this->model->existsByGroupSubjectAndTeacher($data['grupo_materia_id'], $data['maestro_id'])) {
                header("Location: ?c=GroupMateriaMaestro&err=duplicado");
                exit;
            }
            $this->model->create($data);
            header("Location: ?c=GroupMateriaMaestro&msg=creado");
            exit;
        }

        header("Location: ?c=GroupMateriaMaestro&err=datos_invalidos");
        exit;
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $item = $this->model->getById($id);
        if (!$item) {
            header("Location: ?c=GroupMateriaMaestro&err=no_encontrado");
            exit;
        }

        $groupSubjects = $this->db->query(
            "SELECT gm.id,
                    g.nombre AS grupo_nombre, g.grado, g.semestre,
                    m.clave AS materia_clave, m.nombre AS materia_nombre
             FROM grupo_materias gm
             INNER JOIN grupos g ON gm.grupo_id = g.id
             INNER JOIN materias m ON gm.materia_id = m.id
             ORDER BY g.nombre ASC, m.nombre ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $teachers = $this->db->query(
            "SELECT id, nombre, apellido_paterno, apellido_materno
             FROM maestros
             WHERE activo = 1
             ORDER BY nombre ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        require_once '../app/views/group_materia_maestro/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=GroupMateriaMaestro");
            exit;
        }

        $data = [
            'id' => (int)($_POST['id'] ?? 0),
            'grupo_materia_id' => (int)($_POST['grupo_materia_id'] ?? 0),
            'maestro_id' => (int)($_POST['maestro_id'] ?? 0),
            'activo' => isset($_POST['activo']) ? (int)$_POST['activo'] : 1,
        ];

        if ($data['id'] > 0 && $data['grupo_materia_id'] > 0 && $data['maestro_id'] > 0) {
            if ($this->model->existsByGroupSubjectAndTeacher($data['grupo_materia_id'], $data['maestro_id'], $data['id'])) {
                header("Location: ?c=GroupMateriaMaestro&a=edit&id=" . $data['id'] . "&err=duplicado");
                exit;
            }
            $this->model->update($data);
            header("Location: ?c=GroupMateriaMaestro&msg=actualizado");
            exit;
        }

        header("Location: ?c=GroupMateriaMaestro&err=datos_invalidos");
        exit;
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header("Location: ?c=GroupMateriaMaestro&err=datos_invalidos");
            exit;
        }

        if ($this->model->hasRelations($id)) {
            header("Location: ?c=GroupMateriaMaestro&err=tiene_relaciones");
            exit;
        }

        $this->model->delete($id);
        header("Location: ?c=GroupMateriaMaestro&msg=eliminado");
        exit;
    }

    public function toggle() {
        $id = (int)($_GET['id'] ?? 0);
        $status = isset($_GET['status']) ? (int)$_GET['status'] : -1;
        if ($id <= 0 || !in_array($status, [0, 1], true)) {
            header("Location: ?c=GroupMateriaMaestro&err=datos_invalidos");
            exit;
        }

        $newStatus = ($status === 1) ? 0 : 1;
        $this->model->toggleStatus($id, $newStatus);
        header("Location: ?c=GroupMateriaMaestro&msg=estado_cambiado");
        exit;
    }
}
?>
