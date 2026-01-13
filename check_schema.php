<?php
require_once 'config/database.php';

try {
    $stmt = $pdo->query("DESCRIBE logbook_details");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in logbook_details:\n";
    foreach ($columns as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
