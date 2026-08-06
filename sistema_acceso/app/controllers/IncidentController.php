<?php
require_once '../app/config/db.php';
require_once '../app/models/Incident.php';

class IncidentController {
    private $incidentModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: ?c=Auth&a=login");
            exit;
        }

        $database = new Database();
        $this->incidentModel = new Incident($database->getConnection());
    }

    public function index() {
        $start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d');
        $end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
        $search = isset($_GET['search']) ? $_GET['search'] : '';

        $incidents = $this->incidentModel->getWithFilters($start, $end, $search);
        require_once '../app/views/incidents/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $desc = $_POST['description'];
            $severity = $_POST['severity'];
            $userId = $_SESSION['user_id'];
            
            // LÓGICA DE SUBIDA DE ARCHIVO
            $filename = null;
            
            // Verificamos si se subió un archivo y no tiene errores
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
                $file = $_FILES['attachment'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION); // Obtener extensión (jpg, png, pdf)
                
                // Generar nombre único: evidencia_TIMESTAMP.jpg
                $filename = 'evidencia_' . time() . '.' . $ext;
                
                // Mover de temporal a carpeta uploads
                // La ruta es relativa a public/index.php
                move_uploaded_file($file['tmp_name'], 'uploads/' . $filename);
            }

            if ($this->incidentModel->create($title, $desc, $severity, $userId, $filename)) {
                header("Location: ?c=Incident&msg=guardado");
            } else {
                header("Location: ?c=Incident&err=error");
            }
        }
    }
}
?>