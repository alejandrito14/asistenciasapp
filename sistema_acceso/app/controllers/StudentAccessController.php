<?php
require_once '../app/config/db.php';
require_once '../app/models/Alumno.php';

class StudentAccessController {
    private $db;
    private $alumnoModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'guardia'], true)) {
            header("Location: ?c=Auth&a=login");
            exit;
        }

        $database = new Database();
        $this->db = $database->getConnection();
        $this->alumnoModel = new Alumno($this->db);
    }

    public function index() {
        $search = trim((string)($_GET['search'] ?? ''));
        $status = in_array($_GET['status'] ?? '', ['activo', 'inactivo'], true) ? $_GET['status'] : '';
        $students = $this->alumnoModel->getDirectory($search, $status);
        $activeStudents = $this->alumnoModel->countActive();

        require_once '../app/views/student_access/index.php';
    }

    public function export() {
        $search = trim((string)($_GET['search'] ?? ''));
        $status = in_array($_GET['status'] ?? '', ['activo', 'inactivo'], true) ? $_GET['status'] : '';
        $students = $this->alumnoModel->getDirectory($search, $status);

        $filename = 'Listado_Alumnos_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['Nombre', 'Teléfono', 'Correo', 'Tutor', 'Teléfono tutor', 'Correo tutor']);

        foreach ($students as $student) {
            $name = trim(($student['nombre'] ?? '') . ' ' . ($student['apellido_paterno'] ?? '') . ' ' . ($student['apellido_materno'] ?? ''));
            $tutor = trim(($student['tutor_nombre'] ?? '') . ' ' . ($student['tutor_apellido_paterno'] ?? '') . ' ' . ($student['tutor_apellido_materno'] ?? ''));
            fputcsv($output, [
                $name,
                $student['telefono'] ?? '',
                $student['correo'] ?? '',
                $tutor,
                $student['tutor_telefono'] ?? '',
                $student['tutor_correo'] ?? '',
            ]);
        }

        fclose($output);
        exit;
    }

}
?>
