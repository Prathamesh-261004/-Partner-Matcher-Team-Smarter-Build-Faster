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

// Verify user is member or creator
$check = $db->prepare("
    SELECT p.* FROM projects p 
    LEFT JOIN project_members pm ON p.id = pm.project_id 
    WHERE p.id = ? AND (p.creator_id = ? OR pm.user_id = ?)
");
$check->execute([$project_id, $uid, $uid]);
$project = $check->fetch(PDO::FETCH_ASSOC);
if (!$project) {
    die("❌ Access denied or project not found.");
}

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $msg = trim($_POST['message']);
    if ($msg !== '') {
        $stmt = $db->prepare("INSERT INTO project_chats (project_id, user_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$project_id, $uid, $msg]);
    }
    exit;
}

// Fetch chat via AJAX
if (isset($_GET['fetch'])) {
    $chats = $db->prepare("
        SELECT u.name, c.message, c.created_at FROM project_chats c
        JOIN users u ON u.id = c.user_id
        WHERE c.project_id = ?
        ORDER BY c.created_at ASC
    ");
    $chats->execute([$project_id]);
    foreach ($chats as $c) {
        echo "<div><strong>" . htmlspecialchars($c['name']) . "</strong>: " . nl2br(htmlspecialchars($c['message'])) .
             "<br><small style='color:gray'>" . $c['created_at'] . "</small></div><hr>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>💬 Project Chat - <?= htmlspecialchars($project['name']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f4f4f4;
      margin: 0;
      padding: 20px;
    }
    .chat-container {
      max-width: 800px;
      margin: auto;
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 {
      margin-top: 0;
    }
    #chat-box {
      height: 400px;
      overflow-y: scroll;
      border: 1px solid #ccc;
      padding: 15px;
      margin-bottom: 15px;
      background: #fafafa;
    }
    form textarea {
      width: 100%;
      height: 60px;
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #aaa;
      font-size: 15px;
    }
    button {
      background: #4caf50;
      color: white;
      border: none;
      padding: 10px 16px;
      font-size: 15px;
      border-radius: 6px;
      margin-top: 10px;
      cursor: pointer;
    }
    button:hover {
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
    }
  </style>
</head>
<body>
<div class="chat-container">
  <h2>💬 Chat for Project: <?= htmlspecialchars($project['name']) ?></h2>
  <div id="chat-box"></div>

  <form id="chat-form">
    <textarea name="message" id="message" placeholder="Type your message..." required></textarea>
    <button type="submit">Send</button>
  </form>

  <a href="project_dashboard.php" class="back-btn">← Back to Projects</a>
</div>

<script>
function loadChats() {
  const xhr = new XMLHttpRequest();
  xhr.open("GET", "project_67.php?project_id=<?= $project_id ?>&fetch=1", true);
  xhr.onload = function () {
    document.getElementById("chat-box").innerHTML = this.responseText;
    const box = document.getElementById("chat-box");
    box.scrollTop = box.scrollHeight;
  };
  xhr.send();
}

document.getElementById("chat-form").addEventListener("submit", function (e) {
  e.preventDefault();
  const msg = document.getElementById("message").value.trim();
  if (msg !== '') {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "project_67.php?project_id=<?= $project_id ?>", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
      document.getElementById("message").value = '';
      loadChats();
    };
    xhr.send("message=" + encodeURIComponent(msg));
  }
});

setInterval(loadChats, 3000);
loadChats();
</script>
</body>
</html>
