<?php

class ActivityType extends Model {
    public function getAllActivityTypes() {
        $this->db->query("SELECT * FROM activity_types ORDER BY name ASC");
        return $this->db->resultSet();
    }

    public function addActivityType($data) {
        $this->db->query("INSERT INTO activity_types (name) VALUES (:name)");
        $this->db->bind(':name', $data['name']);
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function updateActivityType($data) {
        $this->db->query("UPDATE activity_types SET name = :name WHERE id = :id");
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':id', $data['id']);
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteActivityType($id) {
        $this->db->query("DELETE FROM activity_types WHERE id = :id");
        $this->db->bind(':id', $id);
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function getActivityTypeById($id) {
        $this->db->query("SELECT * FROM activity_types WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
}
