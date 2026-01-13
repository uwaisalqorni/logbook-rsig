<?php
require_once 'config/database.php';

// Update User ID 2 (Employee) to have Unit ID 1
$stmt = $pdo->prepare("UPDATE users SET unit_id = 1 WHERE id = 2");
$stmt->execute();

echo "Updated User ID 2 unit_id to 1.<br>";

// Verify
$user = $pdo->query("SELECT * FROM users WHERE id = 2")->fetch(PDO::FETCH_ASSOC);
echo "User 2 Unit ID: " . $user['unit_id'];
?>
