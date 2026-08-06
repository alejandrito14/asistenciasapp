<?php
class Incident {
    private $conn;
    private $table = "incidents";

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. REGISTRAR INCIDENCIA (Ahora con adjunto opcional)
    public function create($title, $description, $severity, $userId, $attachment = null) {
        $query = "INSERT INTO " . $this->table . " (title, description, severity, created_by, attachment) 
                  VALUES (:title, :desc, :sev, :uid, :att)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':desc', $description);
        $stmt->bindParam(':sev', $severity);
        $stmt->bindParam(':uid', $userId);
        $stmt->bindParam(':att', $attachment);
        return $stmt->execute();
    }

    // 2. LISTAR CON FILTROS
    public function getWithFilters($start, $end, $search = "") {
        $query = "SELECT i.*, u.username as reporter 
                  FROM " . $this->table . " i
                  LEFT JOIN users u ON i.created_by = u.id
                  WHERE (i.created_at BETWEEN :start AND :end)";

        if (!empty($search)) {
            $query .= " AND (i.title LIKE :search OR i.description LIKE :search OR u.username LIKE :search)";
        }

        $query .= " ORDER BY i.created_at DESC";

        $stmt = $this->conn->prepare($query);
        
        $startFull = $start . " 00:00:00";
        $endFull = $end . " 23:59:59";

        $stmt->bindParam(':start', $startFull);
        $stmt->bindParam(':end', $endFull);

        if (!empty($search)) {
            $term = "%" . $search . "%";
            $stmt->bindParam(':search', $term);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>