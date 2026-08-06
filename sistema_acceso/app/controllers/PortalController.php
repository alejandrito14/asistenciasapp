<?php
require_once '../app/config/db.php';
require_once '../app/models/Employee.php';
require_once '../app/models/Attendance.php';

class PortalController {
    private $db;
    private $employeeModel;
    private $attendanceModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->employeeModel = new Employee($this->db);
        $this->attendanceModel = new Attendance($this->db);
        
        if (session_status() == PHP_SESSION_NONE) session_start();
    }

    // 1. MOSTRAR LOGIN (Redirige al Login Unificado)
    public function login() {
        // Si ya está logueado, mandar al dashboard
        if (isset($_SESSION['portal_role']) && $_SESSION['portal_role'] == 'empleado') {
            header("Location: ?c=Portal&a=index");
            exit;
        }
        // CORRECCIÓN: Si intentan entrar aquí, los mandamos al Login Principal
        header("Location: ?c=Auth&a=login");
    }

    // 2. PROCESAR LOGIN (Viene desde el Login Unificado)
    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $employee = $this->employeeModel->login($email, $password);

            if ($employee) {
                $_SESSION['portal_id'] = $employee['id'];
                $_SESSION['portal_name'] = $employee['first_name'];
                $_SESSION['portal_code'] = $employee['employee_code'];
                $_SESSION['portal_role'] = 'empleado';

                header("Location: ?c=Portal&a=index");
            } else {
                // Si falla, regresamos al Login Principal con un error
                header("Location: ?c=Auth&a=login&error=Credenciales%20incorrectas");
            }
        }
    }

    // 3. DASHBOARD DEL EMPLEADO
    public function index() {
        // Seguridad: Verificar si es empleado
        if (!isset($_SESSION['portal_role']) || $_SESSION['portal_role'] != 'empleado') {
            // Si no tiene permiso, al login principal
            header("Location: ?c=Auth&a=login");
            exit;
        }

        $empId = $_SESSION['portal_id'];
        $empName = $_SESSION['portal_name'];
        $empCode = $_SESSION['portal_code'];

        // Historial del mes actual
        $start = date('Y-m-01');
        $end = date('Y-m-d');
        $myLogs = $this->attendanceModel->getLogsWithFilters($empId, $start, $end);

        require_once '../app/views/portal/dashboard.php';
    }

    // 4. CAMBIAR CONTRASEÑA
    public function change_password() {
        if (!isset($_SESSION['portal_role'])) { header("Location: ?c=Auth&a=login"); exit; }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPass = $_POST['current_password'];
            $newPass = $_POST['new_password'];
            $confirmPass = $_POST['confirm_password'];
            $empId = $_SESSION['portal_id'];

            $employee = $this->employeeModel->getById($empId);

            if (!password_verify($currentPass, $employee['password'])) {
                header("Location: ?c=Portal&a=index&err=pass_incorrecta");
                exit;
            }

            if ($newPass !== $confirmPass) {
                header("Location: ?c=Portal&a=index&err=no_coinciden");
                exit;
            }

            $newHash = password_hash($newPass, PASSWORD_DEFAULT);
            $this->employeeModel->updatePassword($empId, $newHash);

            header("Location: ?c=Portal&a=index&msg=pass_actualizada");
        }
    }

    // 5. CERRAR SESIÓN (CORREGIDO)
    public function logout() {
        // Limpiamos solo las variables del portal
        unset($_SESSION['portal_id']);
        unset($_SESSION['portal_name']);
        unset($_SESSION['portal_code']);
        unset($_SESSION['portal_role']);
        
        // REDIRECCIÓN AL LOGIN PRINCIPAL
        header("Location: ?c=Auth&a=login");
    }
}
?>