<?php
require_once 'config/database.php';

echo "<h1>Debug Data Check</h1>";

echo "<h2>Units</h2>";
$units = $pdo->query("SELECT * FROM units")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($units);
echo "</pre>";

echo "<h2>Users</h2>";
$users = $pdo->query("SELECT id, username, role, unit_id, name FROM users")->fetchAll(PDO::FETCH_ASSOC);
echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Role</th><th>Unit ID</th><th>Name</th></tr>";
foreach ($users as $u) {
    echo "<tr>";
    echo "<td>{$u['id']}</td>";
    echo "<td>{$u['username']}</td>";
    echo "<td>{$u['role']}</td>";
    echo "<td>{$u['unit_id']}</td>";
    echo "<td>{$u['name']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Logbooks</h2>";
$logbooks = $pdo->query("SELECT l.id, l.user_id, u.username, l.date, l.status FROM logbooks l JOIN users u ON l.user_id = u.id")->fetchAll(PDO::FETCH_ASSOC);
echo "<table border='1'><tr><th>ID</th><th>User</th><th>Date</th><th>Status</th></tr>";
foreach ($logbooks as $l) {
    echo "<tr>";
    echo "<td>{$l['id']}</td>";
    echo "<td>{$l['username']} (ID: {$l['user_id']})</td>";
    echo "<td>{$l['date']}</td>";
    echo "<td>{$l['status']}</td>";
    echo "</tr>";
}
echo "</table>";
?>
