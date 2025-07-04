<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user'])) header("Location: index.php");
$user = $_SESSION['user'];
$uid = $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $semester = $_POST['semester'];
    $branch = $_POST['branch'];
    $domain = $_POST['domain'];
    $team_size = $_POST['team_size'];
    $skills = explode(',', $_POST['skills']);
    $tools = explode(',', $_POST['tools']);

    $db->prepare("UPDATE users SET name=?, semester=?, branch=? WHERE id=?")
        ->execute([$name, $semester, $branch, $uid]);

    $db->prepare("DELETE FROM skills WHERE user_id=?")->execute([$uid]);
    foreach ($skills as $s) {
        $db->prepare("INSERT INTO skills (user_id, skill_name) VALUES (?, ?)")
           ->execute([$uid, trim($s)]);
    }

    $db->prepare("DELETE FROM tools WHERE user_id=?")->execute([$uid]);
    foreach ($tools as $t) {
        $db->prepare("INSERT INTO tools (user_id, tool_name) VALUES (?, ?)")
           ->execute([$uid, trim($t)]);
    }

    $db->prepare("DELETE FROM preferences WHERE user_id=?")->execute([$uid]);
    $db->prepare("INSERT INTO preferences (user_id, domain, team_size) VALUES (?, ?, ?)")
       ->execute([$uid, $domain, $team_size]);

    $_SESSION['user']['name'] = $name;
    $message = "✅ Profile updated.";
}

// Fetch current data
$stmt = $db->prepare("SELECT * FROM preferences WHERE user_id = ?");
$stmt->execute([$uid]);
$prefs = $stmt->fetch();

$skill_list = $db->prepare("SELECT skill_name FROM skills WHERE user_id=?");
$skill_list->execute([$uid]);
$skills = implode(',', array_column($skill_list->fetchAll(), 'skill_name'));

$tool_list = $db->prepare("SELECT tool_name FROM tools WHERE user_id=?");
$tool_list->execute([$uid]);
$tools = implode(',', array_column($tool_list->fetchAll(), 'tool_name'));
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Profile</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: 'Segoe UI', sans-serif; background: linear-gradient(to right, #dfe9f3, #ffffff); margin: 0; padding: 0;">

<div style="max-width: 600px; margin: 40px auto; background: #fff; padding: 30px 25px; border-radius: 12px; box-shadow: 0 0 20px rgba(0,0,0,0.1); animation: fadeIn 0.6s ease;">
  <h2 style="text-align: center; color: #333; margin-bottom: 20px;">✏️ Edit Profile</h2>

  <?php if (!empty($message)): ?>
    <div style="background: #e7fbe7; color: #2e7d32; padding: 12px 15px; border-left: 5px solid #4caf50; border-radius: 8px; margin-bottom: 20px;">
      <?= $message ?>
    </div>
  <?php endif; ?>

  <form method="post">
    <label>Name:</label>
    <input name="name" value="<?= htmlspecialchars($user['name']) ?>" required><br>

    <label>Semester:</label>
    <input name="semester" value="<?= htmlspecialchars($user['semester'] ?? '') ?>"><br>

    <label>Branch:</label>
    <input name="branch" value="<?= htmlspecialchars($user['branch'] ?? '') ?>"><br>

    <label>Skills (comma-separated):</label>
    <input name="skills" value="<?= $skills ?>"><br>

    <label>Tools (comma-separated):</label>
    <input name="tools" value="<?= $tools ?>"><br>

    <label>Domain:</label>
    <input name="domain" value="<?= $prefs['domain'] ?? '' ?>"><br>

    <label>Team Size:</label>
    <input name="team_size" value="<?= $prefs['team_size'] ?? 2 ?>"><br>

    <button type="submit">💾 Save</button>
  </form>

  <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
</div>

<style>
  form label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #444;
  }

  form input {
    width: 100%;
    padding: 10px;
    margin-bottom: 18px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    box-sizing: border-box;
    font-size: 14px;
    transition: 0.3s;
  }

  form input:focus {
    border-color: #4facfe;
    box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.2);
    outline: none;
  }

  button {
    width: 100%;
    padding: 12px;
    background: linear-gradient(45deg, #4facfe, #00f2fe);
    border: none;
    color: white;
    font-weight: bold;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
    font-size: 16px;
  }

  button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
  }

  .back-link {
    display: inline-block;
    margin-top: 20px;
    text-decoration: none;
    color: #4facfe;
    font-weight: bold;
    transition: 0.3s;
  }

  .back-link:hover {
    text-decoration: underline;
    color: #00bcd4;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
  }
</style>

</body>
</html>
