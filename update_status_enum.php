<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

class MigrationRevision {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function run() {
        $sql = "ALTER TABLE logbook_details MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected', 'revision') DEFAULT 'draft'";
        try {
            $this->db->query($sql);
            $this->db->execute();
            echo "Migration successful: Updated status column in logbook_details to include 'revision'.\n";
        } catch (PDOException $e) {
            echo "Migration failed: " . $e->getMessage() . "\n";
        }
    }
}

$migration = new MigrationRevision();
$migration->run();
