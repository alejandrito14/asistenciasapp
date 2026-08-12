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

    public function justifications() {
        $justifications = $this->getAllJustifications();
        require_once '../app/views/dashboard/justifications.php';
    }

    public function updateJustification() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Dashboard&a=index");
            exit;
        }

        $justificationId = (int)($_POST['justification_id'] ?? 0);
        $status = strtoupper(trim((string)($_POST['estatus'] ?? '')));

        if ($justificationId <= 0 || !in_array($status, ['PENDIENTE', 'APROBADO', 'RECHAZADO'], true)) {
            header("Location: ?c=Dashboard&a=index&err=datos_invalidos");
            exit;
        }

        $stmt = $this->db->prepare(
            "UPDATE justificaciones
             SET estatus = :estatus,
                 fecha_revision = NOW()
             WHERE id = :id"
        );
        $stmt->bindValue(':estatus', $status);
        $stmt->bindValue(':id', $justificationId, PDO::PARAM_INT);
        $stmt->execute();

        if ($status === 'APROBADO') {
            $stmt = $this->db->prepare(
                "SELECT estado_id, sesion_clase_id, alumno_id
                 FROM justificaciones
                 WHERE id = :id
                 LIMIT 1"
            );
            $stmt->bindValue(':id', $justificationId, PDO::PARAM_INT);
            $stmt->execute();
            $justification = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($justification) {
                $stmt = $this->db->prepare(
                    "SELECT id
                     FROM catalogo_asistencia
                     WHERE UPPER(nombre) = 'JUSTIFICADA'
                     LIMIT 1"
                );
                $stmt->execute();
                $justifiedState = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($justifiedState) {
                    $stmt = $this->db->prepare(
                        "UPDATE asistencias
                         SET estado_id = :estado_id
                         WHERE sesion_clase_id = :sesion_clase_id
                           AND alumno_id = :alumno_id"
                    );
                    $stmt->bindValue(':estado_id', (int)$justifiedState['id'], PDO::PARAM_INT);
                    $stmt->bindValue(':sesion_clase_id', (int)$justification['sesion_clase_id'], PDO::PARAM_INT);
                    $stmt->bindValue(':alumno_id', (int)$justification['alumno_id'], PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
        }

        header("Location: ?c=Dashboard&a=index&msg=justificacion_actualizada");
        exit;
    }

    private function countStudents() {
        $query = "SELECT COUNT(*) as total FROM alumnos WHERE activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    private function getRecentJustifications() {
        return $this->getJustificationsQuery(8);
    }

    private function getAllJustifications() {
        return $this->getJustificationsQuery(null);
    }

    private function getJustificationsQuery($limit = 8) {
        $query = "SELECT j.id,
                         j.motivo,
                         j.evidencia,
                         j.estatus,
                         j.created_at,
                         sc.fecha AS sesion_fecha,
                         CASE DAYOFWEEK(sc.fecha)
                            WHEN 1 THEN 'DOMINGO'
                            WHEN 2 THEN 'LUNES'
                            WHEN 3 THEN 'MARTES'
                            WHEN 4 THEN 'MIERCOLES'
                            WHEN 5 THEN 'JUEVES'
                            WHEN 6 THEN 'VIERNES'
                            WHEN 7 THEN 'SABADO'
                         END AS dia_sesion,
                         h.hora_inicio,
                         h.hora_fin,
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
                  LEFT JOIN horarios h ON h.id = (
                      SELECT hh.id
                      FROM horarios hh
                      WHERE hh.grupo_materia_maestro_id = gmm.id
                        AND hh.activo = 1
                        AND hh.dia_semana = CASE DAYOFWEEK(sc.fecha)
                            WHEN 1 THEN 'DOMINGO'
                            WHEN 2 THEN 'LUNES'
                            WHEN 3 THEN 'MARTES'
                            WHEN 4 THEN 'MIERCOLES'
                            WHEN 5 THEN 'JUEVES'
                            WHEN 6 THEN 'VIERNES'
                            WHEN 7 THEN 'SABADO'
                        END
                      ORDER BY hh.hora_inicio ASC, hh.id ASC
                      LIMIT 1
                  )
                  ORDER BY j.id DESC";
        if ($limit !== null) {
            $query .= " LIMIT " . (int)$limit;
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
