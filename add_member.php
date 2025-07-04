<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$uid = $user['id'];
$project_id = $_GET['project_id'] ?? 0;

// Verify the user is the creator of this project
$stmt = $db->prepare("SELECT * FROM projects WHERE id = ? AND creator_id = ?");
$stmt->execute([$project_id, $uid]);
$project = $stmt->fetch();

if (!$project) {
    echo "❌ You are not allowed to add members to this project.";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['member_id'])) {
    $member_id = $_POST['member_id'];

    // Check if member is already added
    $check = $db->prepare("SELECT * FROM project_members WHERE project_id = ? AND user_id = ?");
    $check->execute([$project_id, $member_id]);

    if ($check->rowCount() == 0) {
        // Add to project_members
        $db->prepare("INSERT INTO project_members (project_id, user_id) VALUES (?, ?)")->execute([$project_id, $member_id]);

        // Send notification
        $msg = "You were added to the project: " . htmlspecialchars($project['name']);
        $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$member_id, $msg]);

        echo "<p style='color:green;'>✅ Member added successfully!</p>";
    } else {
        echo "<p style='color:orange;'>⚠️ Member already in project.</p>";
    }
}

// Get all users NOT already in this project (exclude creator too)
$users = $db->prepare("
    SELECT * FROM users 
    WHERE id != ? AND id NOT IN (
        SELECT user_id FROM project_members WHERE project_id = ?
    )
");
$users->execute([$uid, $project_id]);
$availableUsers = $users->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Add Members to Project</title>
  <style>
    body {
      font-family: sans-serif;
      background: #eef2f7;
      padding: 20px;
    }
    .box {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      max-width: 600px;
      margin: auto;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 { margin-top: 0; }
    form {
      display: flex;
      gap: 10px;
      align-items: center;
      margin-bottom: 15px;
    }
    select, button {
      padding: 8px 12px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    a.back {
      display: inline-block;
      margin-top: 10px;
      background: #4facfe;
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
    }
  </style>
</head>
<body>
<div class="box">
  <h2>👥 Add Members to "<?= htmlspecialchars($project['name']) ?>"</h2>

  <?php if (count($availableUsers) > 0): ?>
    <form method="post">
      <select name="member_id" required>
        <option value="">Select User</option>
        <?php foreach ($availableUsers as $u): ?>
          <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= $u['email'] ?>)</option>
        <?php endforeach; ?>
      </select>
      <button type="submit">➕ Add</button>
    </form>
  <?php else: ?>
    <p>No available users to add.</p>
  <?php endif; ?>

  <a href="project_dashboard.php" class="back">← Back to Project Dashboard</a>
</div>
</body>
</html>
