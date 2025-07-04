<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) exit;
$uid = $_SESSION['user']['id'];

$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$uid]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($notes as $n) {
    echo "<p>🔔 " . htmlspecialchars($n['message']) . "</p>";
}
?>
