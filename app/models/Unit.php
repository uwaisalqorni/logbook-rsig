<?php

class Unit extends Model {
    public function getAllUnits() {
        $this->db->query("SELECT * FROM units ORDER BY name ASC");
        return $this->db->resultSet();
    }

    public function addUnit($data) {
        $this->db->query("INSERT INTO units (name) VALUES (:name)");
        $this->db->bind(':name', $data['name']);
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function updateUnit($data) {
        $this->db->query("UPDATE units SET name = :name WHERE id = :id");
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':id', $data['id']);
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteUnit($id) {
        $this->db->query("DELETE FROM units WHERE id = :id");
        $this->db->bind(':id', $id);
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function getUnitById($id) {
        $this->db->query("SELECT * FROM units WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function countUnits() {
        $this->db->query("SELECT COUNT(*) as total FROM units");
        $row = $this->db->single();
        return $row['total'];
    }
}
