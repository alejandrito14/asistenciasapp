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

    public function attendances() {
        $filters = $this->attendanceFiltersFromRequest();
        $recentAttendances = $this->getAttendancesQuery($filters);
        $attendanceFilters = $filters;
        $attendanceGroups = $this->groupModel->readPaginated('', 1, 1000);
        $attendanceSubjects = $this->subjectModel->readPaginated('', 1, 1000);
        $attendanceStudents = $this->alumnoModel->readPaginated('', 1, 1000);
        require_once '../app/views/dashboard/attendances.php';
    }

    public function exportAttendances() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Dashboard&a=attendances");
            exit;
        }

        $filters = $this->attendanceFiltersFromRequest();
        $data = $this->getAttendancesQuery($filters);
        $filename = "Asistencias_" . date('Ymd') . ".xls";
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        echo '<table border="1">';
        echo '<tr><th>Grupo</th><th>Materia</th><th>Horario</th><th>Alumno</th><th>Fecha</th><th>Hora</th><th>Asistencia</th><th>Falta</th><th>Justificado</th></tr>';
        foreach ($data as $item) {
            $estado = strtoupper((string)($item['estado_nombre'] ?? ''));
            $justificacionStatus = strtoupper((string)($item['justificacion_estatus'] ?? ''));
            $isJustificado = (in_array($justificacionStatus, ['APROBADO'], true) || in_array($estado, ['JUSTIFICADO', 'JUSTIFICADA'], true)) ? 'SI' : '';
            $isAsistencia = (!$isJustificado && in_array($estado, ['ASISTENCIA', 'ASISTIÓ', 'ASISTENTE'], true)) ? 'SI' : '';
            $isFalta = (!$isJustificado && $estado === 'FALTA') ? 'SI' : '';
            $studentName = trim(($item['alumno_nombre'] ?? '') . ' ' . ($item['apellido_paterno'] ?? '') . ' ' . ($item['apellido_materno'] ?? ''));
            $sessionDate = !empty($item['sesion_fecha']) ? date('d/m/Y', strtotime($item['sesion_fecha'])) : '';
            $attendanceTime = !empty($item['hora_registro']) ? date('H:i', strtotime($item['hora_registro'])) : '';
            $schedule = trim((string)($item['hora_inicio'] ?? '')) !== ''
                ? date('H:i', strtotime((string)$item['hora_inicio'])) . ' - ' . date('H:i', strtotime((string)$item['hora_fin']))
                : '';
            echo '<tr>';
            echo '<td>' . htmlspecialchars($item['grupo_nombre'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($item['materia_nombre'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($schedule) . '</td>';
            echo '<td>' . htmlspecialchars($studentName) . '</td>';
            echo '<td>' . htmlspecialchars($sessionDate) . '</td>';
            echo '<td>' . htmlspecialchars($attendanceTime) . '</td>';
            echo '<td>' . htmlspecialchars($isAsistencia) . '</td>';
            echo '<td>' . htmlspecialchars($isFalta) . '</td>';
            echo '<td>' . htmlspecialchars($isJustificado) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        exit;
    }

    public function justifications() {
        $justifications = $this->getAllJustifications();
        $attendanceFilters = $this->attendanceFiltersFromRequest();
        require_once '../app/views/dashboard/justifications.php';
    }

    public function exportJustifications() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Dashboard&a=justifications");
            exit;
        }

        $data = $this->getAllJustifications();
        $filename = "Justificantes_" . date('Ymd') . ".xls";
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        echo '<table border="1">';
        echo '<tr><th>Alumno</th><th>Grupo</th><th>Materia</th><th>Fecha de sesión</th><th>Día y hora</th><th>Motivo</th><th>Estado</th><th>Fecha envío</th></tr>';
        foreach ($data as $item) {
            $studentName = trim(($item['alumno_nombre'] ?? '') . ' ' . ($item['apellido_paterno'] ?? '') . ' ' . ($item['apellido_materno'] ?? ''));
            $sessionDate = !empty($item['sesion_fecha']) ? date('d/m/Y', strtotime($item['sesion_fecha'])) : '';
            $sessionDay = strtoupper((string)($item['dia_sesion'] ?? $item['dia_semana'] ?? ''));
            $sessionTime = trim((string)($item['hora_inicio'] ?? '')) !== ''
                ? date('H:i', strtotime((string)$item['hora_inicio'])) . ' - ' . date('H:i', strtotime((string)$item['hora_fin']))
                : '';
            echo '<tr>';
            echo '<td>' . htmlspecialchars($studentName) . '</td>';
            echo '<td>' . htmlspecialchars($item['grupo_nombre'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($item['materia_nombre'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($sessionDate) . '</td>';
            echo '<td>' . htmlspecialchars(trim($sessionDay . ' ' . $sessionTime)) . '</td>';
            echo '<td>' . htmlspecialchars($item['motivo'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($item['estatus'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars(!empty($item['created_at']) ? date('d/m/Y H:i', strtotime($item['created_at'])) : '') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        exit;
    }

    public function updateJustification() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Dashboard&a=index");
            exit;
        }

        $justificationId = (int)($_POST['justification_id'] ?? 0);
        $status = preg_replace('/\s+/', '', strtoupper((string)($_POST['estatus'] ?? '')));

        if ($justificationId <= 0 || !in_array($status, ['PENDIENTE', 'APROBADO', 'RECHAZADO'], true)) {
            header("Location: ?c=Dashboard&a=index&err=datos_invalidos");
            exit;
        }

        $stmt = $this->db->prepare(
            "SELECT estatus, sesion_clase_id, alumno_id
             FROM justificaciones
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->bindValue(':id', $justificationId, PDO::PARAM_INT);
        $stmt->execute();
        $justification = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$justification) {
            header("Location: ?c=Dashboard&a=index&err=no_encontrado");
            exit;
        }

        if ($status === 'PENDIENTE') {
            header("Location: ?c=Dashboard&a=index&msg=justificacion_actualizada");
            exit;
        }

        $stmt = $this->db->prepare(
            "UPDATE justificaciones
             SET estatus = :estatus,
             fecha_revision = NOW()
             WHERE id = :id"
        );
        $stmt->bindValue(':estatus', $status, PDO::PARAM_STR);
        $stmt->bindValue(':id', $justificationId, PDO::PARAM_INT);
        $stmt->execute();

        if ($status === 'APROBADO') {
            $stmt = $this->db->prepare(
                "SELECT id
                 FROM catalogo_asistencia
                 WHERE UPPER(nombre) IN ('JUSTIFICADA', 'JUSTIFICADO')
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

    private function getRecentAttendances() {
        return $this->getAttendancesQuery([]);
    }

    private function attendanceFiltersFromRequest() {
        $source = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
        return [
            'grupo_id' => trim((string)($source['grupo_id'] ?? '')),
            'materia_id' => trim((string)($source['materia_id'] ?? '')),
            'alumno_id' => trim((string)($source['alumno_id'] ?? '')),
        ];
    }

    private function getAttendancesQuery(array $filters) {
        $query = "SELECT a.id,
                         a.hora_registro,
                         a.observaciones,
                         ca.nombre AS estado_nombre,
                         COALESCE(j.estatus, '') AS justificacion_estatus,
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
                         alu.nombre AS alumno_nombre,
                         alu.apellido_paterno,
                         alu.apellido_materno,
                         m.nombre AS materia_nombre,
                         g.nombre AS grupo_nombre
                  FROM asistencias a
                  INNER JOIN catalogo_asistencia ca ON ca.id = a.estado_id
                  INNER JOIN alumnos alu ON alu.id = a.alumno_id
                  INNER JOIN sesiones_clase sc ON sc.id = a.sesion_clase_id
                  INNER JOIN grupo_materia_maestro gmm ON gmm.id = sc.grupo_materia_maestro_id
                  INNER JOIN grupo_materias gm ON gm.id = gmm.grupo_materia_id
                  INNER JOIN materias m ON m.id = gm.materia_id
                  INNER JOIN grupos g ON g.id = gm.grupo_id
                  LEFT JOIN justificaciones j ON j.sesion_clase_id = a.sesion_clase_id
                      AND j.alumno_id = a.alumno_id
                      AND UPPER(j.estatus) = 'APROBADO'
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
                  )";
        $conditions = [];
        if (!empty($filters['grupo_id'])) {
            $conditions[] = "g.id = :grupo_id";
        }
        if (!empty($filters['materia_id'])) {
            $conditions[] = "m.id = :materia_id";
        }
        if (!empty($filters['alumno_id'])) {
            $conditions[] = "alu.id = :alumno_id";
        }
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }
        $query .= " ORDER BY a.id DESC";
        $stmt = $this->db->prepare($query);
        if (!empty($filters['grupo_id'])) {
            $stmt->bindValue(':grupo_id', (int)$filters['grupo_id'], PDO::PARAM_INT);
        }
        if (!empty($filters['materia_id'])) {
            $stmt->bindValue(':materia_id', (int)$filters['materia_id'], PDO::PARAM_INT);
        }
        if (!empty($filters['alumno_id'])) {
            $stmt->bindValue(':alumno_id', (int)$filters['alumno_id'], PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
