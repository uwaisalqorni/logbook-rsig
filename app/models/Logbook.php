<?php

class Logbook extends Model {
    // --- Logbook Header Operations ---

    public function getLogbookByUserAndDate($user_id, $date) {
        $this->db->query("SELECT * FROM logbooks WHERE user_id = :user_id AND date = :date");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':date', $date);
        return $this->db->single();
    }

    public function createLogbook($user_id, $date) {
        $this->db->query("INSERT INTO logbooks (user_id, date, status) VALUES (:user_id, :date, 'draft')");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':date', $date);
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    public function getLogbookById($id) {
        $this->db->query("SELECT * FROM logbooks WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function updateStatus($id, $status) {
        $this->db->query("UPDATE logbooks SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        $result = $this->db->execute();

        if ($result && ($status == 'revision' || $status == 'approved' || $status == 'rejected')) {
            $this->db->query("UPDATE logbook_details SET status = :status WHERE logbook_id = :id AND status = 'submitted'");
            $this->db->bind(':status', $status);
            $this->db->bind(':id', $id);
            $this->db->execute();
        }
        return $result;
    }

    public function getLogbooksByUserId($user_id) {
        $this->db->query("SELECT * FROM logbooks WHERE user_id = :user_id ORDER BY date DESC");
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    public function getLogbooksByUserIdAndDateRange($user_id, $start_date, $end_date) {
        $this->db->query("SELECT * FROM logbooks WHERE user_id = :user_id AND date BETWEEN :start_date AND :end_date ORDER BY date DESC");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':start_date', $start_date);
        $this->db->bind(':end_date', $end_date);
        return $this->db->resultSet();
    }

    public function countLogbooksByUser($user_id) {
        $this->db->query("SELECT COUNT(*) as total FROM logbooks WHERE user_id = :user_id");
        $this->db->bind(':user_id', $user_id);
        $row = $this->db->single();
        return $row['total'];
    }

    public function getAllLogbooks($start_date, $end_date, $unit_id = null) {
        $sql = "SELECT l.*, u.name as user_name, un.name as unit_name 
                FROM logbooks l 
                JOIN users u ON l.user_id = u.id 
                JOIN units un ON u.unit_id = un.id 
                WHERE l.date BETWEEN :start_date AND :end_date";
        
        if ($unit_id) {
            $sql .= " AND u.unit_id = :unit_id";
        }
        
        $sql .= " ORDER BY l.date DESC, u.name ASC";
        
        $this->db->query($sql);
        $this->db->bind(':start_date', $start_date);
        $this->db->bind(':end_date', $end_date);
        
        if ($unit_id) {
            $this->db->bind(':unit_id', $unit_id);
        }
        
        return $this->db->resultSet();
    }

    // --- Logbook Details (Activities) Operations ---

    public function getActivitiesByLogbookId($logbook_id) {
        $this->db->query("SELECT ld.*, at.name as activity_name FROM logbook_details ld LEFT JOIN activity_types at ON ld.activity_type_id = at.id WHERE ld.logbook_id = :logbook_id ORDER BY ld.start_time ASC");
        $this->db->bind(':logbook_id', $logbook_id);
        return $this->db->resultSet();
    }

    public function addActivity($data) {
        $this->db->query("INSERT INTO logbook_details (logbook_id, activity_type_id, description, start_time, end_time, output, kendala, status) VALUES (:logbook_id, :activity_type_id, :description, :start_time, :end_time, :output, :kendala, 'draft')");
        $this->db->bind(':logbook_id', $data['logbook_id']);
        $this->db->bind(':activity_type_id', $data['activity_type_id']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':start_time', $data['start_time']);
        $this->db->bind(':end_time', $data['end_time']);
        $this->db->bind(':output', $data['output']);
        $this->db->bind(':kendala', $data['kendala']);
        return $this->db->execute();
    }

    public function updateActivity($data) {
        $this->db->query("UPDATE logbook_details SET activity_type_id=:activity_type_id, description=:description, start_time=:start_time, end_time=:end_time, output=:output, kendala=:kendala WHERE id=:id");
        $this->db->bind(':activity_type_id', $data['activity_type_id']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':start_time', $data['start_time']);
        $this->db->bind(':end_time', $data['end_time']);
        $this->db->bind(':output', $data['output']);
        $this->db->bind(':kendala', $data['kendala']);
        $this->db->bind(':id', $data['id']);
        return $this->db->execute();
    }

    public function deleteActivity($id) {
        $this->db->query("DELETE FROM logbook_details WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getActivityById($id) {
        $this->db->query("SELECT * FROM logbook_details WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function submitActivity($id) {
        $this->db->query("UPDATE logbook_details SET status = 'submitted' WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
    
    // --- For Head of Unit ---
    public function getPendingLogbooksByUnit($unit_id) {
        $this->db->query("SELECT l.*, u.name as user_name, u.nik FROM logbooks l JOIN users u ON l.user_id = u.id WHERE u.unit_id = :unit_id AND l.status = 'submitted' ORDER BY l.date ASC");
        $this->db->bind(':unit_id', $unit_id);
        return $this->db->resultSet();
    }

    public function getHistoryLogbooksByUnit($unit_id) {
        $this->db->query("SELECT l.*, u.name as user_name, u.nik FROM logbooks l JOIN users u ON l.user_id = u.id WHERE u.unit_id = :unit_id AND l.status IN ('approved', 'rejected') ORDER BY l.date DESC");
        $this->db->bind(':unit_id', $unit_id);
        return $this->db->resultSet();
    }

    // --- For Management ---
    public function getLogbookStatsByUnit() {
        $this->db->query("SELECT un.name, COUNT(l.id) as total FROM units un LEFT JOIN users u ON un.id = u.unit_id LEFT JOIN logbooks l ON u.id = l.user_id AND MONTH(l.date) = MONTH(CURRENT_DATE()) AND YEAR(l.date) = YEAR(CURRENT_DATE()) GROUP BY un.id ORDER BY total DESC");
        return $this->db->resultSet();
    }

    public function getLogbookCountsByUnit($start_date, $end_date) {
        $this->db->query("SELECT un.name as unit_name, COUNT(l.id) as total 
                          FROM logbooks l 
                          JOIN users u ON l.user_id = u.id 
                          JOIN units un ON u.unit_id = un.id 
                          WHERE l.date BETWEEN :start_date AND :end_date 
                          GROUP BY un.id 
                          ORDER BY total DESC");
        $this->db->bind(':start_date', $start_date);
        $this->db->bind(':end_date', $end_date);
        return $this->db->resultSet();
    }
    
    public function getRecentLogbooks() {
         $this->db->query("SELECT l.*, u.name as user_name, un.name as unit_name FROM logbooks l JOIN users u ON l.user_id = u.id JOIN units un ON u.unit_id = un.id ORDER BY l.date DESC LIMIT 5");
         return $this->db->resultSet();
    }

    public function countTodayLogbooks() {
        $this->db->query("SELECT COUNT(DISTINCT user_id) as total FROM logbooks WHERE date = CURRENT_DATE");
        $row = $this->db->single();
        return $row['total'];
    }

    public function countSubmittedLogbooksToday() {
        $this->db->query("SELECT COUNT(DISTINCT user_id) as total FROM logbooks WHERE date = CURRENT_DATE AND status IN ('submitted', 'approved', 'rejected', 'revision')");
        $row = $this->db->single();
        return $row['total'];
    }

    public function addValidation($data) {
        $this->db->query("INSERT INTO validations (logbook_id, validator_id, status, notes) VALUES (:logbook_id, :validator_id, :status, :notes)");
        $this->db->bind(':logbook_id', $data['logbook_id']);
        $this->db->bind(':validator_id', $data['validator_id']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':notes', $data['notes']);
        return $this->db->execute();
    }

    public function getValidationByLogbookId($logbook_id) {
        $this->db->query("SELECT * FROM validations WHERE logbook_id = :logbook_id ORDER BY validated_at DESC LIMIT 1");
        $this->db->bind(':logbook_id', $logbook_id);
        return $this->db->single();
    }
}
