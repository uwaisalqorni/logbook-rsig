<?php
require_once 'config/database.php';

try {
    $stmt = $pdo->query("DESCRIBE logbooks");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in logbooks:\n";
    foreach ($columns as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
