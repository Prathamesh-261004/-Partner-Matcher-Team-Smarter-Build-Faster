<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$uid = $user['id'];

// Handle project deletion (only by creator)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_project_id'])) {
    $pid = $_POST['delete_project_id'];

    // Confirm the user is creator
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ? AND creator_id = ?");
    $stmt->execute([$pid, $uid]);
    if ($stmt->rowCount() > 0) {
        $db->prepare("DELETE FROM project_members WHERE project_id = ?")->execute([$pid]);
        $db->prepare("DELETE FROM project_chats WHERE project_id = ?")->execute([$pid]);
        $db->prepare("DELETE FROM projects WHERE id = ?")->execute([$pid]);
    }
    header("Location: project_dashboard.php");
    exit;
}

// Fetch created and member projects
$createdStmt = $db->prepare("SELECT * FROM projects WHERE creator_id = ?");
$createdStmt->execute([$uid]);
$createdProjects = $createdStmt->fetchAll(PDO::FETCH_ASSOC);

$memberStmt = $db->prepare("
    SELECT p.* FROM projects p
    JOIN project_members pm ON pm.project_id = p.id
    WHERE pm.user_id = ? AND p.creator_id != ?
");
$memberStmt->execute([$uid, $uid]);
$memberProjects = $memberStmt->fetchAll(PDO::FETCH_ASSOC);

function getProjectMembers($db, $project_id) {
    $stmt = $db->prepare("SELECT u.name FROM users u
                          JOIN project_members pm ON u.id = pm.user_id
                          WHERE pm.project_id = ?");
    $stmt->execute([$project_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>📁 Project Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right, #f0f2f5, #d9e7ff);
      margin: 0;
      padding: 30px;
    }
    .container {
      max-width: 900px;
      margin: auto;
      background: #fff;
      border-radius: 10px;
      padding: 30px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      color: #333;
      margin-bottom: 20px;
    }
    .project-box {
      background: #f9f9f9;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .project-box h3 {
      margin-top: 0;
      color: #0077cc;
    }
    .project-box p {
      margin: 5px 0;
      color: #333;
    }
    .members {
      margin-top: 10px;
      font-size: 14px;
      color: #555;
    }
    .section-title {
      margin-top: 40px;
      color: #222;
      border-bottom: 2px solid #4facfe;
      padding-bottom: 5px;
    }
    .btn-delete, .btn-chat {
      display: inline-block;
      margin-top: 10px;
      background: #f44336;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      text-decoration: none;
    }
    .btn-chat {
      background: #4caf50;
      margin-left: 10px;
    }
    .btn-delete:hover {
      background: #d32f2f;
    }
    .btn-chat:hover {
      background: #388e3c;
    }
    .back-btn {
      display: inline-block;
      margin-top: 20px;
      background: #4facfe;
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .back-btn:hover {
      background: #00c6ff;
    }
  </style>
</head>
<body>
<div class="container">
  <h2>📁 Project Dashboard</h2>

  <div class="section-title">🛠️ Projects You Created</div>
  <?php if (count($createdProjects)): ?>
    <?php foreach ($createdProjects as $proj): ?>
      <div class="project-box">
        <h3><?= htmlspecialchars($proj['name']) ?></h3>
        <p><?= nl2br(htmlspecialchars($proj['description'])) ?></p>
        <div class="members">
          👥 Members:
          <?php
          $members = getProjectMembers($db, $proj['id']);
          echo $members ? implode(", ", $members) : 'No members yet.';
          ?>
        </div>
        <form method="post" style="display:inline;">
          <input type="hidden" name="delete_project_id" value="<?= $proj['id'] ?>">
          <button type="submit" class="btn-delete">🗑️ Delete</button>
          <a href="add_member.php?project_id=<?= $proj['id'] ?>" class="btn-chat">➕ Add Member</a>

        </form>
        <a href="project_67.php?project_id=<?= $proj['id'] ?>" class="btn-chat">💬 Open Chat</a>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p>No projects created yet.</p>
  <?php endif; ?>

  <div class="section-title">👥 Projects You're Involved In</div>
  <?php if (count($memberProjects)): ?>
    <?php foreach ($memberProjects as $proj): ?>
      <div class="project-box">
        <h3><?= htmlspecialchars($proj['name']) ?></h3>
        <p><?= nl2br(htmlspecialchars($proj['description'])) ?></p>
        <div class="members">
          👥 Members:
          <?php
          $members = getProjectMembers($db, $proj['id']);
          echo $members ? implode(", ", $members) : 'No members yet.';
          ?>
        </div>
        <a href="project_67.php?project_id=<?= $proj['id'] ?>" class="btn-chat">💬 Open Chat</a>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p>You're not added to any projects yet.</p>
  <?php endif; ?>

  <a class="back-btn" href="dashboard.php">← Back to Dashboard</a>
</div>
</body>
</html>
