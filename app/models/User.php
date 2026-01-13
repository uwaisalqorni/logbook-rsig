<?php

class User extends Model {
    public function login($username, $password) {
        $this->db->query('SELECT * FROM users WHERE username = :username');
        $this->db->bind(':username', $username);

        $row = $this->db->single();

        if ($row) {
            $hashed_password = $row['password'];
            if (password_verify($password, $hashed_password)) {
                return $row;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function findUserByUsername($username) {
        $this->db->query('SELECT * FROM users WHERE username = :username');
        $this->db->bind(':username', $username);

        $row = $this->db->single();

        // Check row
        if ($this->db->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getAllUsers() {
        $this->db->query("SELECT u.*, un.name as unit_name FROM users u LEFT JOIN units un ON u.unit_id = un.id ORDER BY u.name ASC");
        return $this->db->resultSet();
    }

    public function addUser($data) {
        $this->db->query("INSERT INTO users (nik, name, username, password, role, unit_id, position, golongan) VALUES (:nik, :name, :username, :password, :role, :unit_id, :position, :golongan)");
        $this->db->bind(':nik', $data['nik']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':role', $data['role']);
        $this->db->bind(':unit_id', $data['unit_id']);
        $this->db->bind(':position', $data['position']);
        $this->db->bind(':golongan', $data['golongan']);
        
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function updateUser($data) {
        $sql = "UPDATE users SET nik=:nik, name=:name, username=:username, role=:role, unit_id=:unit_id, position=:position, golongan=:golongan, status=:status";
        if (!empty($data['password'])) {
            $sql .= ", password=:password";
        }
        $sql .= " WHERE id=:id";

        $this->db->query($sql);
        $this->db->bind(':nik', $data['nik']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':role', $data['role']);
        $this->db->bind(':unit_id', $data['unit_id']);
        $this->db->bind(':position', $data['position']);
        $this->db->bind(':golongan', $data['golongan']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':id', $data['id']);
        
        if (!empty($data['password'])) {
            $this->db->bind(':password', $data['password']);
        }

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteUser($id) {
        $this->db->query("DELETE FROM users WHERE id = :id");
        $this->db->bind(':id', $id);
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function countUsers() {
        $this->db->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
        $row = $this->db->single();
        return $row['total'];
    }

    public function countActiveEmployees() {
        $this->db->query("SELECT COUNT(*) as total FROM users WHERE role = 'employee' AND status = 'active'");
        $row = $this->db->single();
        return $row['total'];
    }
}
