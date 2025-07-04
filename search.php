<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user'])) exit;

$skill = $_POST['skill'] ?? '';
if (strlen($skill) < 2) exit;

$stmt = $db->prepare("SELECT DISTINCT u.id, u.name, u.semester, u.branch 
                     FROM users u 
                     JOIN skills s ON u.id = s.user_id 
                     WHERE s.skill_name LIKE ? AND u.id != ?");
$stmt->execute(['%' . $skill . '%', $_SESSION['user']['id']]);
$users = $stmt->fetchAll();

foreach ($users as $u) {
    echo "<div style='padding:10px; background:#fff; margin:10px 0; border-radius:5px'>";
    echo "<strong>" . htmlspecialchars($u['name']) . "</strong> - " . $u['branch'] . " (Sem " . $u['semester'] . ")";
    echo " <form method='post' action='send_request.php' style='display:inline'>
              <input type='hidden' name='to' value='" . $u['id'] . "'>
              <button>Send Team Request</button>
           </form>";
    echo "</div>";
}
