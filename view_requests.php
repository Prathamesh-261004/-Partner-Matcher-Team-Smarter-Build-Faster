<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user'])) header("Location: index.php");

$uid = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $req_id = $_POST['req_id'];
    $action = $_POST['action']; // accept or reject

    if ($action === 'accept') {
        $db->prepare("UPDATE team_requests SET status='accepted' WHERE id=? AND receiver_id=?")
           ->execute([$req_id, $uid]);
    } elseif ($action === 'reject') {
        $db->prepare("UPDATE team_requests SET status='rejected' WHERE id=? AND receiver_id=?")
           ->execute([$req_id, $uid]);
    }
    header("Location: view_requests.php");
    exit;
}

$stmt = $db->prepare("
    SELECT tr.id, u.name, u.email FROM team_requests tr
    JOIN users u ON tr.sender_id = u.id
    WHERE tr.receiver_id = ? AND tr.status = 'pending'
");
$stmt->execute([$uid]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <title>📩 Incoming Team Requests</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: 'Segoe UI', sans-serif; background: linear-gradient(to right, #e3f2fd, #fff); margin: 0; padding: 30px;">

<div style="max-width: 700px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 0 15px rgba(0,0,0,0.1); animation: fadeIn 0.5s ease;">
  <h2 style="text-align: center; color: #333; margin-bottom: 25px;">📩 Incoming Team Requests</h2>

  <?php if (count($requests) == 0): ?>
    <p style="text-align:center; color: #666;">No new team requests at the moment.</p>
  <?php else: ?>
    <?php foreach ($requests as $r): ?>
      <div style="margin-bottom: 15px; padding: 15px; background: #f0f9ff; border-left: 5px solid #4facfe; border-radius: 8px;">
        <p style="margin: 0 0 5px;"><strong><?= htmlspecialchars($r['name']) ?></strong> (<?= $r['email'] ?>)</p>
        <form method="post" style="display: inline-block; margin-top: 5px;">
          <input type="hidden" name="req_id" value="<?= $r['id'] ?>">
          <button name="action" value="accept" style="background: #4caf50; color: white; border: none; padding: 8px 16px; margin-right: 10px; border-radius: 6px; cursor: pointer;">✅ Accept</button>
          <button name="action" value="reject" style="background: #f44336; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer;">❌ Reject</button>
        </form>
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
