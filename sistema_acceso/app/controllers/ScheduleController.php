<?php
require_once '../app/config/db.php';
require_once '../app/models/Schedule.php';

class ScheduleController {
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
        $this->model = new ScheduleModel($this->db);
    }

    public function index() {
        $search = trim((string)($_GET['q'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));

        $total = $this->model->countAll($search);
        $items = $this->model->readPaginated($search, $page, $this->perPage);
        $totalPages = max(1, (int)ceil($total / $this->perPage));

        $assignments = $this->db->query(
            "SELECT gmm.id,
                    g.nombre AS grupo_nombre, g.grado, g.semestre,
                    m.clave AS materia_clave, m.nombre AS materia_nombre,
                    ma.nombre AS maestro_nombre, ma.apellido_paterno, ma.apellido_materno
             FROM grupo_materia_maestro gmm
             INNER JOIN grupo_materias gm ON gmm.grupo_materia_id = gm.id
             INNER JOIN grupos g ON gm.grupo_id = g.id
             INNER JOIN materias m ON gm.materia_id = m.id
             INNER JOIN maestros ma ON gmm.maestro_id = ma.id
             ORDER BY g.nombre ASC, m.nombre ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        require_once '../app/views/schedules/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Schedule");
            exit;
        }

        $data = [
            'grupo_materia_maestro_id' => (int)($_POST['grupo_materia_maestro_id'] ?? 0),
            'dia_semana' => strtoupper(trim((string)($_POST['dia_semana'] ?? ''))),
            'hora_inicio' => trim((string)($_POST['hora_inicio'] ?? '')),
            'hora_fin' => trim((string)($_POST['hora_fin'] ?? '')),
            'aula' => trim((string)($_POST['aula'] ?? '')),
            'minutos_antes' => (int)($_POST['minutos_antes'] ?? 15),
            'minutos_despues' => (int)($_POST['minutos_despues'] ?? 15),
            'activo' => isset($_POST['activo']) ? (int)$_POST['activo'] : 1,
        ];

        $diasValidos = ['LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO'];
        if ($data['grupo_materia_maestro_id'] > 0 && in_array($data['dia_semana'], $diasValidos, true) && $data['hora_inicio'] !== '' && $data['hora_fin'] !== '') {
            $this->model->create($data);
            header("Location: ?c=Schedule&msg=creado");
            exit;
        }

        header("Location: ?c=Schedule&err=datos_invalidos");
        exit;
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $item = $this->model->getById($id);
        if (!$item) {
            header("Location: ?c=Schedule&err=no_encontrado");
            exit;
        }

        $assignments = $this->db->query(
            "SELECT gmm.id,
                    g.nombre AS grupo_nombre, g.grado, g.semestre,
                    m.clave AS materia_clave, m.nombre AS materia_nombre,
                    ma.nombre AS maestro_nombre, ma.apellido_paterno, ma.apellido_materno
             FROM grupo_materia_maestro gmm
             INNER JOIN grupo_materias gm ON gmm.grupo_materia_id = gm.id
             INNER JOIN grupos g ON gm.grupo_id = g.id
             INNER JOIN materias m ON gm.materia_id = m.id
             INNER JOIN maestros ma ON gmm.maestro_id = ma.id
             ORDER BY g.nombre ASC, m.nombre ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        require_once '../app/views/schedules/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Schedule");
            exit;
        }

        $data = [
            'id' => (int)($_POST['id'] ?? 0),
            'grupo_materia_maestro_id' => (int)($_POST['grupo_materia_maestro_id'] ?? 0),
            'dia_semana' => strtoupper(trim((string)($_POST['dia_semana'] ?? ''))),
            'hora_inicio' => trim((string)($_POST['hora_inicio'] ?? '')),
            'hora_fin' => trim((string)($_POST['hora_fin'] ?? '')),
            'aula' => trim((string)($_POST['aula'] ?? '')),
            'minutos_antes' => (int)($_POST['minutos_antes'] ?? 15),
            'minutos_despues' => (int)($_POST['minutos_despues'] ?? 15),
            'activo' => isset($_POST['activo']) ? (int)$_POST['activo'] : 1,
        ];

        $diasValidos = ['LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO'];
        if ($data['id'] > 0 && $data['grupo_materia_maestro_id'] > 0 && in_array($data['dia_semana'], $diasValidos, true) && $data['hora_inicio'] !== '' && $data['hora_fin'] !== '') {
            $this->model->update($data);
            header("Location: ?c=Schedule&msg=actualizado");
            exit;
        }

        header("Location: ?c=Schedule&err=datos_invalidos");
        exit;
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header("Location: ?c=Schedule&err=datos_invalidos");
            exit;
        }
        if ($this->model->hasRelations($id)) {
            header("Location: ?c=Schedule&err=tiene_relaciones");
            exit;
        }
        $this->model->delete($id);
        header("Location: ?c=Schedule&msg=eliminado");
        exit;
    }

    public function toggle() {
        $id = (int)($_GET['id'] ?? 0);
        $status = isset($_GET['status']) ? (int)$_GET['status'] : -1;
        if ($id <= 0 || !in_array($status, [0, 1], true)) {
            header("Location: ?c=Schedule&err=datos_invalidos");
            exit;
        }

        $newStatus = ($status === 1) ? 0 : 1;
        $this->model->toggleStatus($id, $newStatus);
        header("Location: ?c=Schedule&msg=estado_cambiado");
        exit;
    }
}
?>
