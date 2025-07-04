<?php 
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_email'], $_POST['admin_pass'])) {
    if ($_POST['admin_email'] === 'admin@example.com' && $_POST['admin_pass'] === 'admin123') {
        $_SESSION['admin'] = true;
        header("Location: admin_stats.php");
        exit;
    } else {
        $admin_error = "❌ Invalid admin credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>🤝 Partner Matcher - Welcome</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(-45deg, #00f2fe, #4facfe, #43e97b, #38f9d7);
      background-size: 400% 400%;
      animation: gradientBG 10s ease infinite;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .main-container {
      background: #ffffffee;
      padding: 50px 35px;
      border-radius: 18px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.25);
      width: 90%;
      max-width: 420px;
      text-align: center;
      animation: fadeInUp 1.2s ease;
      position: relative;
    }

    .main-title {
      font-size: 30px;
      font-weight: bold;
      color: #333;
      margin-bottom: 8px;
      animation: popIn 0.6s ease;
    }

    .subtitle {
      color: #555;
      font-size: 15px;
      margin-bottom: 30px;
      animation: fadeIn 1.5s ease;
    }

    .btn {
      display: block;
      width: 100%;
      padding: 14px;
      margin: 10px 0;
      font-size: 16px;
      font-weight: bold;
      border-radius: 8px;
      transition: 0.3s ease;
      cursor: pointer;
      text-decoration: none;
    }

    .btn-primary {
      background: linear-gradient(45deg, #4facfe, #00f2fe);
      border: none;
      color: white;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-secondary {
      background: transparent;
      border: 2px solid #4facfe;
      color: #4facfe;
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
    }

    .admin-box {
      margin-top: 25px;
      display: none;
      text-align: left;
      border-top: 1px dashed #ccc;
      padding-top: 20px;
      animation: slideDown 0.6s ease;
    }

    .admin-box.show {
      display: block;
    }

    .form-input {
      width: 100%;
      padding: 10px;
      margin: 6px 0;
      border-radius: 6px;
      border: 1px solid #bbb;
      transition: 0.3s ease;
    }

    .form-input:focus {
      border-color: #00cfff;
      box-shadow: 0 0 5px #00cfff55;
      outline: none;
    }

    .btn-admin {
      background: #333;
      color: white;
      border: none;
      width: 100%;
      padding: 11px;
      border-radius: 8px;
      margin-top: 12px;
      font-weight: bold;
    }

    .btn-back {
      display: inline-block;
      margin-top: 12px;
      color: #4facfe;
      font-size: 14px;
      text-decoration: none;
      transition: 0.3s ease;
    }

    .btn-back:hover {
      text-decoration: underline;
      color: #00cfff;
    }

    .error-message {
      color: red;
      margin-bottom: 12px;
      font-size: 14px;
      background: #ffe6e6;
      padding: 10px;
      border-radius: 6px;
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes popIn {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes slideDown {
      from { transform: translateY(-10px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    @media (max-width: 500px) {
      .main-container { padding: 35px 20px; }
    }
  </style>
</head>
<body>

  <div class="main-container">
    <h1 class="main-title">🤝 Partner Matcher</h1>
    <p class="subtitle">Find your perfect teammate based on skills</p>

    <a href="register.php" class="btn btn-primary">📝 Register</a>
    <a href="login.php" class="btn btn-secondary">🔐 Login</a>
    <button onclick="toggleAdminBox()" class="btn btn-secondary">👤 Admin Login</button>

    <div id="adminBox" class="admin-box">
      <form method="post">
        <?php if (!empty($admin_error)): ?>
          <div class="error-message"><?= $admin_error ?></div>
        <?php endif; ?>
        <input type="email" name="admin_email" placeholder="Admin Email" required class="form-input">
        <input type="password" name="admin_pass" placeholder="Password" required class="form-input">
        <button type="submit" class="btn-admin">🔒 Admin Access</button>
      </form>
      <a href="index.php" class="btn-back">← Back to Welcome</a>
    </div>
  </div>

  <script>
    function toggleAdminBox() {
      document.getElementById('adminBox').classList.toggle('show');
    }
  </script>
</body>
</html>
