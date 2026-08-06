<?php
require_once '../app/config/db.php';
require_once '../app/models/Visitor.php';

class VisitorAdminController {
    private $visitorModel;
    private $db;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: ?c=Auth&a=login");
            exit;
        }

        $database = new Database();
        $this->db = $database->getConnection();
        $this->visitorModel = new Visitor($this->db);
    }

    public function index() {
        // 1. Capturar Filtros (o poner valores por defecto)
        $start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01'); // Primer día del mes
        $end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');       // Hoy
        $search = isset($_GET['search']) ? $_GET['search'] : '';

        // 2. Obtener datos filtrados
        $visitors = $this->visitorModel->getHistoryWithFilters($start, $end, $search);
        
        // 3. Cargar la vista
        require_once '../app/views/visitors/index.php';
    }
}
?>