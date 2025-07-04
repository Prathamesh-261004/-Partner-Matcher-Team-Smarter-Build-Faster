<?php
session_start();
require 'db.php';

if (!isset($_SESSION['reset_email'])) {
    header('Location: forgot_password.php');
    exit;
}

$email = $_SESSION['reset_email'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp = $_POST['otp'];
    $newpass = password_hash($_POST['newpass'], PASSWORD_BCRYPT);

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND otp_code = ?");
    $stmt->execute([$email, $otp]);

    if ($stmt->rowCount() > 0) {
        $db->prepare("UPDATE users SET password = ?, otp_code = NULL WHERE email = ?")->execute([$newpass, $email]);
        unset($_SESSION['reset_email']);
        $message = "<span style='color: green;'>✅ Password reset successful. <a href='login.php' style='color: #185a9d;'>Login</a></span>";
    } else {
        $message = "<span style='color: red;'>❌ Invalid OTP.</span>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Reset Password</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0; padding:0; font-family:'Segoe UI', sans-serif; background:linear-gradient(to right, #43cea2, #185a9d); height:100vh; display:flex; align-items:center; justify-content:center;">

<div style="background:white; padding:30px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.2); width:90%; max-width:400px; animation:fadeIn 0.6s ease;">
  <h2 style="text-align:center; color:#333;">🔑 Reset Password</h2>
  <p style="text-align:center; color:#666; font-size:14px;">Enter the OTP sent to <b><?= htmlspecialchars($email) ?></b></p>

  <?php if ($message): ?>
    <div style="margin: 15px 0; background: #f9f9f9; padding: 10px; border-left: 4px solid #ccc; border-radius: 6px;">
      <?= $message ?>
    </div>
  <?php endif; ?>

  <form method="post" style="margin-top: 20px;">
    <input type="text" name="otp" placeholder="Enter OTP" required style="width:100%; padding:12px; margin-bottom:15px; border-radius:8px; border:1px solid #ccc;">
    <input type="password" name="newpass" placeholder="New Password" required style="width:100%; padding:12px; margin-bottom:20px; border-radius:8px; border:1px solid #ccc;">
    <button type="submit" style="width:100%; padding:12px; background:linear-gradient(to right, #43cea2, #185a9d); color:white; border:none; border-radius:8px; font-weight:bold; font-size:16px; cursor:pointer;">🔁 Reset Password</button>
  </form>

  <div style="text-align:center; margin-top:20px;">
    <a href="login.php" style="color:#185a9d; font-weight:bold;">← Back to Login</a>
  </div>
</div>

<style>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
</body>
</html>
