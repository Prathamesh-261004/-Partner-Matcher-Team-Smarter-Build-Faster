<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user']['id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        // Delete user-related data
        $db->prepare("DELETE FROM skills WHERE user_id = ?")->execute([$uid]);
        $db->prepare("DELETE FROM tools WHERE user_id = ?")->execute([$uid]);
        $db->prepare("DELETE FROM preferences WHERE user_id = ?")->execute([$uid]);
        $db->prepare("DELETE FROM team_requests WHERE sender_id = ? OR receiver_id = ?")->execute([$uid, $uid]);
        $db->prepare("DELETE FROM project_members WHERE user_id = ?")->execute([$uid]);
        $db->prepare("DELETE FROM project_chats WHERE user_id = ?")->execute([$uid]);
        $db->prepare("DELETE FROM notifications WHERE user_id = ?")->execute([$uid]);

        // Delete projects created by this user
        $projIds = $db->prepare("SELECT id FROM projects WHERE creator_id = ?");
        $projIds->execute([$uid]);
        $ids = $projIds->fetchAll(PDO::FETCH_COLUMN);
        foreach ($ids as $pid) {
            $db->prepare("DELETE FROM project_members WHERE project_id = ?")->execute([$pid]);
            $db->prepare("DELETE FROM project_chats WHERE project_id = ?")->execute([$pid]);
        }
        $db->prepare("DELETE FROM projects WHERE creator_id = ?")->execute([$uid]);

        // Delete user
        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);

        session_destroy();
        header("Location: index.php");
        exit;
    } else {
        header("Location: dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>🗑️ Delete Account</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: 'Segoe UI', sans-serif; background: linear-gradient(to right, #ffe0e0, #fff); padding: 30px;">

<div style="max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 0 12px rgba(0,0,0,0.1);">
  <h2 style="color: #d32f2f;">⚠️ Confirm Account Deletion</h2>
  <p style="color: #444;">This action is <strong>permanent</strong> and will delete all your data including your profile, skills, projects, chats, and requests.</p>

  <form method="post">
    <input type="hidden" name="confirm" value="yes">
    <button style="background: #d32f2f; color: white; padding: 12px 20px; border: none; border-radius: 6px; font-weight: bold; margin-top: 10px;">Yes, Delete My Account</button>
    <a href="dashboard.php" style="margin-left: 15px; padding: 10px 18px; background: #4facfe; color: white; text-decoration: none; border-radius: 6px;">Cancel</a>
  </form>
</div>

</body>
</html>
