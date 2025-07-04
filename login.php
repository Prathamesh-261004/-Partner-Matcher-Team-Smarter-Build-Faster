<?php
session_start();
require 'db.php';

$message = '';
if (isset($_SESSION['login_message'])) {
    $message = $_SESSION['login_message'];
    unset($_SESSION['login_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {
        if ($user['verified']) {
            $_SESSION['user'] = $user;
            header('Location: dashboard.php');
            exit;
        } else {
            $_SESSION['pending_email'] = $email;
            header('Location: verify.php');
            exit;
        }
    } else {
        $message = "❌ Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login - Partner Matcher</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: 'Segoe UI', sans-serif; background: linear-gradient(to right, #4facfe, #00f2fe); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">

<div style="background: white; padding: 30px; width: 100%; max-width: 400px; border-radius: 12px; box-shadow: 0 0 15px rgba(0,0,0,0.2); animation: fadeIn 0.6s ease;">
  <h2 style="text-align:center; color:#333;">🔐 Login</h2>
  <p style="text-align:center; color:#666; font-size: 14px;">Access your account</p>

  <?php if ($message): ?>
    <div style="margin-top:15px; color: <?= strpos($message, '✅') !== false ? 'green' : 'red' ?>; background: #f9f9f9; padding:10px; border-radius: 6px;">
      <?= $message ?>
    </div>
  <?php endif; ?>

  <form method="post" style="margin-top: 20px;">
    <input type="email" name="email" placeholder="Email" required style="margin-bottom: 15px; width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ccc;">
    <input type="password" name="password" placeholder="Password" required style="margin-bottom: 20px; width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ccc;">
    <button type="submit" name="login" style="width: 100%; padding: 12px; background: linear-gradient(45deg, #4facfe, #00f2fe); color: white; border: none; border-radius: 8px; font-weight: 600;">Login</button>
  </form>
  
<div style="
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 30px;
">
  <a href="index.php" style="
    padding: 12px 24px;
    background: linear-gradient(45deg, #4facfe, #00f2fe);
    color: #fff;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    text-align: center;
  " onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
    🔙 Back to Dashboard
  </a>
</div>


  <div style="text-align:center; margin-top: 10px;">
    <a href="forgot_password.php" style="color: #4facfe; font-size: 13px;">Forgot Password?</a>
  </div>

  <p style="text-align: center; margin-top: 15px;">
    New user? <a href="register.php" style="color: #4facfe; font-weight: bold;">Register</a>
  </p>
  
</div>

<style>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}
</style>
</body>
</html>
