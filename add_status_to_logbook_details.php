<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

class Migration {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function run() {
        $sql = "ALTER TABLE logbook_details ADD COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected') DEFAULT 'draft'";
        try {
            $this->db->query($sql);
            $this->db->execute();
            echo "Migration successful: Added status column to logbook_details.\n";
        } catch (PDOException $e) {
            echo "Migration failed: " . $e->getMessage() . "\n";
        }
    }
}

$migration = new Migration();
$migration->run();
