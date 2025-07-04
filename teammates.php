<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$uid = $_SESSION['user']['id'];

// Fetch accepted teammates excluding self and remove duplicates
$stmt = $db->prepare("
    SELECT DISTINCT u.id, u.name, u.email, u.branch, u.semester
    FROM team_requests tr
    JOIN users u ON (
        (tr.sender_id = :uid AND tr.receiver_id = u.id)
        OR (tr.receiver_id = :uid AND tr.sender_id = u.id)
    )
    WHERE tr.status = 'accepted'
      AND u.id != :uid
");
$stmt->execute(['uid' => $uid]);
$teammates = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>👥 My Teammates</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 30px; font-family: 'Segoe UI', sans-serif; background: linear-gradient(to right, #e0f7fa, #fff);">

<div style="max-width: 700px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 0 15px rgba(0,0,0,0.1); animation: fadeIn 0.6s ease;">
  <h2 style="color: #333; text-align: center; margin-bottom: 20px;">👥 My Teammates</h2>

  <?php if (count($teammates) == 0): ?>
    <p style="text-align: center; color: #777;">No teammates yet.</p>
  <?php else: ?>
    <?php foreach ($teammates as $mate): ?>
      <div style="margin-bottom: 15px; padding: 15px; background: #f0f9ff; border-left: 5px solid #4facfe; border-radius: 8px;">
        <p style="margin: 0;"><strong style="font-size: 16px;"><?= htmlspecialchars($mate['name']) ?></strong></p>
        <p style="margin: 3px 0 0; color: #555;">📧 <?= htmlspecialchars($mate['email']) ?></p>
        <p style="margin: 3px 0 0; color: #555;">📚 Branch: <?= htmlspecialchars($mate['branch']) ?></p>
        <p style="margin: 3px 0 0; color: #555;">📅 Semester: <?= htmlspecialchars($mate['semester']) ?></p>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <a href="dashboard.php" style="display: inline-block; margin-top: 25px; text-decoration: none; background: #4facfe; color: white; padding: 12px 20px; border-radius: 8px; font-weight: bold; transition: background 0.3s;">
    ← Back to Dashboard
  </a>
</div>

<style>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}
a:hover {
  background: #00c6ff;
}
</style>

</body>
</html>
