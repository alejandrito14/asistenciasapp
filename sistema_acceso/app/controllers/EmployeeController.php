<?php
require_once '../app/config/db.php';
require_once '../app/models/Employee.php';
require_once '../app/models/Usuario.php';
require_once '../app/models/Maestro.php';

class EmployeeController {
    private $employeeModel;
    private $db;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        
        // 1. Verificar Login
        if (!isset($_SESSION['user_id'])) {
            header("Location: ?c=Auth&a=login");
            exit;
        }
        
        // 2. Bloqueo de Rol (Solo Admin)
        if ($_SESSION['role'] != 'admin') {
            header("Location: ?c=Dashboard");
            exit;
        }

        $database = new Database();
        $this->db = $database->getConnection();
        $this->employeeModel = new Employee($this->db);
    }

    // 1. LISTAR EMPLEADOS (Y CARGAR DATOS PARA EL MODAL)
    public function index() {
        $search = isset($_GET['q']) ? $_GET['q'] : "";
        $employees = $this->employeeModel->read($search);
        require_once '../app/views/employees/index.php';
    }

    // 2. GUARDAR NUEVO EMPLEADO (Desde el Modal)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim((string)($_POST['nombre'] ?? ''));
            $apellidoPaterno = trim((string)($_POST['apellido_paterno'] ?? ''));
            $apellidoMaterno = trim((string)($_POST['apellido_materno'] ?? ''));
            $telefono = trim((string)($_POST['telefono'] ?? ''));
            $correo = trim((string)($_POST['correo'] ?? ''));
            $password = (string)($_POST['password'] ?? '');

            if ($nombre === '' || $correo === '' || $password === '') {
                header("Location: ?c=Employee&err=datos_invalidos");
                exit;
            }

            try {
                $this->db->beginTransaction();
                $usuarioModel = new Usuario($this->db);
                $maestroModel = new Maestro($this->db);

                $userData = $usuarioModel->register([
                    'nombre' => $nombre,
                    'correo' => $correo,
                    'password' => $password,
                    'rol' => 'MAESTRO',
                ]);

                $maestroModel->createForUsuario((int)$userData['id'], [
                    'nombre' => $nombre,
                    'apellido_paterno' => $apellidoPaterno,
                    'apellido_materno' => $apellidoMaterno,
                    'telefono' => $telefono,
                    'correo' => $correo,
                ]);

                $this->db->commit();
                header("Location: ?c=Employee&msg=guardado");
            } catch (Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                header("Location: ?c=Employee&err=error");
                exit;
            }
        }
    }

    // 3. MOSTRAR FORMULARIO DE EDICIÓN (Página aparte)
    public function edit() {
        if (isset($_GET['id'])) {
            $emp = $this->employeeModel->getById($_GET['id']);
            if ($emp) {
                require_once '../app/views/employees/edit.php';
            } else {
                header("Location: ?c=Employee");
            }
        }
    }

    // 4. GUARDAR CAMBIOS DE EDICIÓN
    public function update_data() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $_POST['id'],
                'nombre' => $_POST['nombre'],
                'apellido_paterno' => $_POST['apellido_paterno'],
                'apellido_materno' => $_POST['apellido_materno'],
                'telefono' => $_POST['telefono'],
                'correo' => $_POST['correo'],
                'activo' => isset($_POST['activo']) ? (int)$_POST['activo'] : 1,
            ];
            $this->employeeModel->update($data);
            header("Location: ?c=Employee&msg=actualizado");
            exit;
        }
    }

    // 5. ACTIVAR / DESACTIVAR
    public function toggle() {
        if (isset($_GET['id']) && isset($_GET['status'])) {
            $id = $_GET['id'];
            $currentStatus = $_GET['status'];
            $newStatus = ($currentStatus == 1) ? 0 : 1;
            $this->employeeModel->toggleStatus($id, $newStatus);
            header("Location: ?c=Employee&msg=estado_cambiado");
            exit;
        }
    }

    // 6. ELIMINAR (Opcional, ya que usamos desactivar)
    public function delete() {
        if(isset($_GET['id'])) {
            $this->employeeModel->delete($_GET['id']);
            header("Location: ?c=Employee&msg=eliminado");
            exit;
        }
    }
}
?>
