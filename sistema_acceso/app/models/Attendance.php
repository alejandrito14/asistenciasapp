<?php
class Attendance {
    private $conn;
    private $table = "attendance_logs";

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. OBTENER LOS ÚLTIMOS 10 REGISTROS (Para tabla de recientes)
    public function getRecentLogs() {
        $query = "SELECT a.*, e.first_name, e.last_name, e.employee_code 
                  FROM " . $this->table . " a
                  INNER JOIN employees e ON a.employee_id = e.id
                  ORDER BY a.id DESC LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. CONTAR CUÁNTOS HAN MARCADO HOY (Para tarjeta verde)
    public function countToday() {
        $today = date('Y-m-d');
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE date_log = :today";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':today', $today);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // 3. OBTENER HISTORIAL POR RANGO DE FECHAS (Para Reporte Excel)
    public function getHistoryByDate($start, $end) {
        $query = "SELECT a.date_log, a.check_in_time, a.check_out_time, a.status,
                         e.first_name, e.last_name, e.employee_code, d.name as department
                  FROM " . $this->table . " a
                  INNER JOIN employees e ON a.employee_id = e.id
                  LEFT JOIN departments d ON e.department_id = d.id
                  WHERE a.date_log BETWEEN :start AND :end
                  ORDER BY a.date_log DESC, a.check_in_time DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $start);
        $stmt->bindParam(':end', $end);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. ESTADÍSTICAS ÚLTIMOS 7 DÍAS (Para el Gráfico)
    public function getWeeklyStats() {
        $query = "SELECT date_log, COUNT(*) as total 
                  FROM " . $this->table . " 
                  GROUP BY date_log 
                  ORDER BY date_log DESC 
                  LIMIT 7";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // 5. HISTORIAL CON FILTROS (Para Pantalla Historial)
    public function getLogsWithFilters($employee_id, $start, $end) {
        $query = "SELECT a.date_log, a.check_in_time, a.check_out_time, a.status,
                         e.first_name, e.last_name, e.employee_code, d.name as department
                  FROM " . $this->table . " a
                  INNER JOIN employees e ON a.employee_id = e.id
                  LEFT JOIN departments d ON e.department_id = d.id
                  WHERE a.date_log BETWEEN :start AND :end";
        
        if (!empty($employee_id)) {
            $query .= " AND e.id = :eid";
        }
        $query .= " ORDER BY a.date_log DESC, a.check_in_time DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $start);
        $stmt->bindParam(':end', $end);
        if (!empty($employee_id)) {
            $stmt->bindParam(':eid', $employee_id);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 6. CONTAR TARDANZAS DE HOY (NUEVO PARA DASHBOARD)
    public function countLatesToday($horaLimite) {
        $today = date('Y-m-d');
        // Lógica: Es hoy Y la hora de entrada es mayor al límite
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table . " 
                  WHERE date_log = :today AND check_in_time > :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':today', $today);
        $stmt->bindParam(':limit', $horaLimite);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>