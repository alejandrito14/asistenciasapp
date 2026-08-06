<?php
class Visitor {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. REGISTRAR ENTRADA
    public function registerEntry($dni, $name, $company, $reason) {
        // Verificar si existe
        $queryCheck = "SELECT id FROM visitors WHERE dni = :dni LIMIT 1";
        $stmtCheck = $this->conn->prepare($queryCheck);
        $stmtCheck->bindParam(':dni', $dni);
        $stmtCheck->execute();
        
        if ($row = $stmtCheck->fetch(PDO::FETCH_ASSOC)) {
            $visitorId = $row['id'];
        } else {
            // Crear nuevo
            $queryNew = "INSERT INTO visitors (dni, full_name, company) VALUES (:dni, :name, :company)";
            $stmtNew = $this->conn->prepare($queryNew);
            $stmtNew->bindParam(':dni', $dni);
            $stmtNew->bindParam(':name', $name);
            $stmtNew->bindParam(':company', $company);
            $stmtNew->execute();
            $visitorId = $this->conn->lastInsertId();
        }

        // Log de visita
        $queryLog = "INSERT INTO visitor_logs (visitor_id, reason, check_in) VALUES (:vid, :reason, NOW())";
        $stmtLog = $this->conn->prepare($queryLog);
        $stmtLog->bindParam(':vid', $visitorId);
        $stmtLog->bindParam(':reason', $reason);
        
        return $stmtLog->execute();
    }

    // 2. REGISTRAR SALIDA
    public function registerExit($dni) {
        $query = "SELECT l.id, v.full_name 
                  FROM visitor_logs l
                  JOIN visitors v ON l.visitor_id = v.id
                  WHERE v.dni = :dni AND l.check_out IS NULL 
                  ORDER BY l.id DESC LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dni', $dni);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $update = "UPDATE visitor_logs SET check_out = NOW() WHERE id = :logId";
            $stmtUp = $this->conn->prepare($update);
            $stmtUp->bindParam(':logId', $row['id']);
            $stmtUp->execute();
            return "Salida registrada para: " . $row['full_name'];
        } else {
            return false;
        }
    }

    // 3. HISTORIAL CON FILTROS (Esta es la función que te falta)
    public function getHistoryWithFilters($start, $end, $search = "") {
        $query = "SELECT l.id, l.reason, l.check_in, l.check_out, 
                         v.dni, v.full_name, v.company 
                  FROM visitor_logs l
                  INNER JOIN visitors v ON l.visitor_id = v.id
                  WHERE l.check_in BETWEEN :start AND :end";
        
        // Si hay texto de búsqueda, agregamos las condiciones OR
        if (!empty($search)) {
            $query .= " AND (v.full_name LIKE :search OR v.dni LIKE :search OR v.company LIKE :search)";
        }
        
        $query .= " ORDER BY l.check_in DESC";
        
        $stmt = $this->conn->prepare($query);
        
        // Concatenamos horas para cubrir todo el día
        $startFull = $start . " 00:00:00";
        $endFull = $end . " 23:59:59";
        
        $stmt->bindParam(':start', $startFull);
        $stmt->bindParam(':end', $endFull);
        
        // Bind de búsqueda
        if (!empty($search)) {
            $searchTerm = "%" . $search . "%";
            $stmt->bindParam(':search', $searchTerm);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>