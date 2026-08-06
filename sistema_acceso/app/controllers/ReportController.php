<?php
require_once '../app/config/db.php';
require_once '../app/models/Attendance.php';
require_once '../app/models/Employee.php';
require_once '../app/models/Setting.php'; // 1. IMPORTAMOS EL MODELO DE AJUSTES

class ReportController {
    private $attendanceModel;
    private $employeeModel;
    private $settingModel; // 2. VARIABLE NUEVA
    private $db;

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
        $this->attendanceModel = new Attendance($this->db);
        $this->employeeModel = new Employee($this->db);
        $this->settingModel = new Setting($this->db); // 3. INICIALIZAR
    }

    public function index() { require_once '../app/views/reports/index.php'; }

    public function history() {
        $employees = $this->employeeModel->read();
        $start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
        $end_date = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
        $employee_id = isset($_GET['employee_id']) ? $_GET['employee_id'] : '';
        
        $logs = $this->attendanceModel->getLogsWithFilters($employee_id, $start_date, $end_date);
        
        // 4. OBTENER LA HORA REAL DE LA BASE DE DATOS
        $horaEntradaOficial = $this->settingModel->get('entry_time');
        if (!$horaEntradaOficial) $horaEntradaOficial = '08:00:00'; // Fallback por seguridad

        require_once '../app/views/reports/history.php';
    }

    public function export() {
        if (isset($_POST['start_date'])) {
            $start = $_POST['start_date']; $end = $_POST['end_date'];
            
            // OBTENER HORA DE LA BD TAMBIÉN PARA EL EXCEL
            $horaLimite = $this->settingModel->get('entry_time');
            if (!$horaLimite) $horaLimite = '08:00:00';

            $data = $this->attendanceModel->getHistoryByDate($start, $end);
            
            $filename = "Reporte_" . date('Ymd') . ".csv";
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename);
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($output, ['Codigo', 'Empleado', 'Depto', 'Fecha', 'Entrada', 'Salida', 'Horas', 'Estado', 'Puntualidad']);
            
            foreach ($data as $row) {
                $horas = "--"; 
                $puntual = "Puntual";
                
                if ($row['check_out_time']) {
                    $diff = (new DateTime($row['check_in_time']))->diff(new DateTime($row['check_out_time']));
                    $horas = $diff->format('%H:%I:%S');
                }
                
                // USAR LA VARIABLE DE LA BD PARA CALCULAR
                if ($row['check_in_time'] > $horaLimite) $puntual = "TARDE";
                
                $est = ($row['check_out_time']) ? 'Completado' : 'En Turno';
                fputcsv($output, [$row['employee_code'], $row['first_name'].' '.$row['last_name'], $row['department'], $row['date_log'], $row['check_in_time'], $row['check_out_time'], $horas, $est, $puntual]);
            }
            fclose($output); exit;
        }
    }
}
?>