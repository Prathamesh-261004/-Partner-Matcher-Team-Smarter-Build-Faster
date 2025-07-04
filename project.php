<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$uid = $user['id'];
$message = '';

// Fetch accepted teammates (excluding self)
$stmt = $db->prepare("
    SELECT u.id, u.name FROM team_requests tr
    JOIN users u ON (
        (tr.sender_id = :uid AND tr.receiver_id = u.id)
        OR (tr.receiver_id = :uid AND tr.sender_id = u.id)
    )
    WHERE tr.status = 'accepted' AND u.id != :uid
");
$stmt->execute(['uid' => $uid]);
$teammates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle project creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $members = $_POST['members'] ?? [];

    if ($name) {
        // Insert project
        $stmt = $db->prepare("INSERT INTO projects (name, description, creator_id) VALUES (?, ?, ?)");
        $stmt->execute([$name, $desc, $uid]);
        $project_id = $db->lastInsertId();

        // Add members (avoid duplicates)
        foreach ($members as $member_id) {
            // Check if already added
            $check = $db->prepare("SELECT id FROM project_members WHERE project_id = ? AND user_id = ?");
            $check->execute([$project_id, $member_id]);

            if ($check->rowCount() == 0) {
                $db->prepare("INSERT INTO project_members (project_id, user_id) VALUES (?, ?)")
                    ->execute([$project_id, $member_id]);

                // Notify member
                $msg = "📁 You've been added to a new project: <b>$name</b>";
                $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")
                   ->execute([$member_id, $msg]);
            }
        }

        $message = "✅ Project created successfully!";
    } else {
        $message = "⚠️ Project name is required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Create Project</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right, #e3f2fd, #fff);
      margin: 0;
      padding: 30px;
    }

    .container {
      max-width: 600px;
      margin: auto;
      background: white;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      color: #333;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
      color: #444;
    }

    input[type="text"], textarea, select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 2px solid #ccc;
      border-radius: 8px;
      box-sizing: border-box;
    }

    textarea {
      resize: vertical;
    }

    button {
      margin-top: 20px;
      background: #4caf50;
      color: white;
      padding: 12px 20px;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background: #388e3c;
    }

    .message {
      margin-top: 15px;
      padding: 10px;
      border-radius: 6px;
      background: #e3f2fd;
      color: #333;
      font-weight: bold;
    }

    .back-btn {
      display: inline-block;
      margin-top: 20px;
      text-decoration: none;
      background: #4facfe;
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: bold;
    }

    .back-btn:hover {
      background: #00c6ff;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>📁 Create New Project</h2>

  <?php if ($message): ?>
    <div class="message"><?= $message ?></div>
  <?php endif; ?>

  <form method="post">
    <label>Project Name:</label>
    <input type="text" name="name" required>

    <label>Description:</label>
    <textarea name="description" rows="4" placeholder="Project goals, tech stack, etc."></textarea>

    <label>Select Teammates to Add:</label>
    <select name="members[]" multiple size="5">
      <?php foreach ($teammates as $mate): ?>
        <option value="<?= $mate['id'] ?>"><?= htmlspecialchars($mate['name']) ?></option>
      <?php endforeach; ?>
    </select>

    <button type="submit">🚀 Create Project</button>
  </form>

  <a href="project_dashboard.php" class="back-btn">← Back to Projects</a>
</div>

</body>
</html>
