<?php
require_once '../app/config/db.php';
require_once '../app/models/Attendance.php';
require_once '../app/models/Employee.php';
require_once '../app/models/Setting.php'; // <--- IMPORTANTE: Nuevo Modelo
require_once '../app/models/Group.php';
require_once '../app/models/Subject.php';
require_once '../app/models/Alumno.php';

class DashboardController {
    private $db;
    private $attendanceModel;
    private $employeeModel;
    private $settingModel; // Variable para la configuración
    private $groupModel;
    private $subjectModel;
    private $alumnoModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        
        // 1. Verificar Login
        if (!isset($_SESSION['user_id'])) {
            header("Location: ?c=Auth&a=login");
            exit;
        }

        // 2. Conexión y Modelos
        $database = new Database();
        $this->db = $database->getConnection();
        
        $this->attendanceModel = new Attendance($this->db);
        $this->employeeModel = new Employee($this->db);
        $this->settingModel = new Setting($this->db); // <--- Inicializamos
        $this->groupModel = new GroupModel($this->db);
        $this->subjectModel = new SubjectModel($this->db);
        $this->alumnoModel = new Alumno($this->db);
    }

    public function index() {
        $totalGroups = $this->groupModel->countAll();
        $totalSubjects = $this->subjectModel->countAll();
        $totalStudents = $this->countStudents();
        $recentJustifications = $this->getRecentJustifications();

        require_once '../app/views/dashboard/index.php';
    }

    private function countStudents() {
        $query = "SELECT COUNT(*) as total FROM alumnos WHERE activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    private function getRecentJustifications() {
        $query = "SELECT j.id,
                         j.motivo,
                         j.evidencia,
                         j.estatus,
                         j.created_at,
                         a.nombre AS alumno_nombre,
                         a.apellido_paterno,
                         a.apellido_materno,
                         m.nombre AS materia_nombre,
                         g.nombre AS grupo_nombre
                  FROM justificaciones j
                  INNER JOIN alumnos a ON a.id = j.alumno_id
                  INNER JOIN sesiones_clase sc ON sc.id = j.sesion_clase_id
                  INNER JOIN grupo_materia_maestro gmm ON gmm.id = sc.grupo_materia_maestro_id
                  INNER JOIN grupo_materias gm ON gm.id = gmm.grupo_materia_id
                  INNER JOIN materias m ON m.id = gm.materia_id
                  INNER JOIN grupos g ON g.id = gm.grupo_id
                  ORDER BY j.id DESC
                  LIMIT 8";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
