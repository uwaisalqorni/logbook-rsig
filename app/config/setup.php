<?php
require_once 'config.php';

try {
    $dsn = "mysql:host=" . DB_HOST;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $pdo->exec("USE " . DB_NAME);

    // Table: units
    $pdo->exec("CREATE TABLE IF NOT EXISTS units (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL
    )");

    // Table: shifts
    $pdo->exec("CREATE TABLE IF NOT EXISTS shifts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        start_time TIME,
        end_time TIME
    )");

    // Table: activity_types
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL
    )");

    // Table: users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nik VARCHAR(20) UNIQUE NOT NULL,
        name VARCHAR(100) NOT NULL,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'head', 'employee', 'management') NOT NULL,
        unit_id INT,
        position VARCHAR(100),
        status ENUM('active', 'inactive') DEFAULT 'active',
        FOREIGN KEY (unit_id) REFERENCES units(id)
    )");

    // Table: logbooks
    $pdo->exec("CREATE TABLE IF NOT EXISTS logbooks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        date DATE NOT NULL,
        shift_id INT NOT NULL,
        status ENUM('draft', 'submitted', 'approved', 'rejected', 'revision') DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (shift_id) REFERENCES shifts(id)
    )");

    // Table: logbook_details
    $pdo->exec("CREATE TABLE IF NOT EXISTS logbook_details (
        id INT AUTO_INCREMENT PRIMARY KEY,
        logbook_id INT NOT NULL,
        activity_type_id INT,
        description TEXT NOT NULL,
        start_time TIME,
        end_time TIME,
        output VARCHAR(255),
        kendala TEXT,
        FOREIGN KEY (logbook_id) REFERENCES logbooks(id) ON DELETE CASCADE,
        FOREIGN KEY (activity_type_id) REFERENCES activity_types(id)
    )");

    // Table: validations
    $pdo->exec("CREATE TABLE IF NOT EXISTS validations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        logbook_id INT NOT NULL,
        validator_id INT NOT NULL,
        status ENUM('approved', 'rejected', 'revision') NOT NULL,
        notes TEXT,
        validated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (logbook_id) REFERENCES logbooks(id) ON DELETE CASCADE,
        FOREIGN KEY (validator_id) REFERENCES users(id)
    )");

    // Seed Admin User if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (nik, name, username, password, role, status) VALUES ('000', 'Administrator', 'admin', '$password', 'admin', 'active')");
        echo "Admin user created (admin/admin123).<br>";
    }

    echo "Database setup completed successfully.";

} catch (PDOException $e) {
    die("Setup failed: " . $e->getMessage());
}
?>
