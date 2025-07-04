<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user'])) header("Location: login.php");

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html>
<head>
  <title>Dashboard - Partner Matcher</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right, #f0f2f5, #d9e7ff);
      margin: 0;
      padding: 20px;
    }

    .container {
      max-width: 800px;
      margin: auto;
      background: white;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
      animation: fadeIn 0.6s ease;
    }

    h2 {
      color: #333;
      margin-bottom: 10px;
    }

    a {
      color: #4facfe;
      text-decoration: none;
      margin: 0 10px;
      font-weight: bold;
    }

    a:hover {
      text-decoration: underline;
    }

    .box {
      background: #f9f9f9;
      padding: 20px;
      margin-top: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    #searchBox {
      width: 100%;
      padding: 12px;
      font-size: 16px;
      border: 2px solid #ddd;
      border-radius: 8px;
      box-sizing: border-box;
      margin-top: 10px;
      transition: 0.3s;
    }

    #searchBox:focus {
      border-color: #4facfe;
      box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.2);
      outline: none;
    }

    #results {
      margin-top: 20px;
      line-height: 1.6;
    }

    .back-btn {
      display: inline-block;
      margin-bottom: 20px;
      background: #4facfe;
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: 0.2s;
    }

    .back-btn:hover {
      background: #00c6ff;
      transform: translateY(-2px);
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

<div class="container">
  <a href="index.php" class="back-btn">← Back to Home</a>

  <h2>Welcome, <?= htmlspecialchars($user['name']) ?> 👋</h2>
  <div style="margin-bottom: 20px;">
    <a href="profile.php">📝 Edit Profile</a> |
    <a href="view_requests.php">📩 Team Requests</a> |
    <a href="teammates.php">👥 My Teammates</a> |
    <a href="logout.php">🚪 Logout</a>|
        <a href="project.php">project</a>|
        <a href="project_dashboard.php">📁 My Projects</a> 

  </div>

  <div class="box">
    <h3>🔍 Search Students by Skill</h3>
    <input type="text" id="searchBox" placeholder="e.g., PHP, Python">
    <div id="results"></div>
  </div>
</div>
<a href="delete_account.php" style="
  display: inline-block;
  background: #f44336;
  color: white;
  padding: 10px 18px;
  text-decoration: none;
  border-radius: 6px;
  font-weight: bold;
  margin-top: 10px;
  transition: background 0.3s ease;">
  🗑️ Delete Account
</a>

<script>
document.getElementById('searchBox').addEventListener('input', function () {
  const skill = this.value;
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "search.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onload = function () {
    document.getElementById('results').innerHTML = this.responseText;
  };
  xhr.send("skill=" + encodeURIComponent(skill));
});
</script>

</body>
</html>
