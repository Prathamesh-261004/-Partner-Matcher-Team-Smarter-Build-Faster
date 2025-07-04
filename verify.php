<?php
session_start();
require 'db.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

if (!isset($_SESSION['pending_email'])) {
    header("Location: index.php");
    exit;
}

$email = $_SESSION['pending_email'];
$message = '';

// Handle OTP form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $otp = $_POST['otp'];

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND otp_code = ?");
    $stmt->execute([$email, $otp]);

    if ($stmt->rowCount() > 0) {
        $db->prepare("UPDATE users SET verified = 1, otp_code = NULL WHERE email = ?")->execute([$email]);

        $user = $db->prepare("SELECT * FROM users WHERE email = ?");
        $user->execute([$email]);
        $_SESSION['user'] = $user->fetch();

        unset($_SESSION['pending_email']);
        header("Location: login.php");
        exit;
    } else {
        $message = "❌ Incorrect OTP. Please try again.";
    }
}

// Resend OTP
if (isset($_POST['resend'])) {
    $otp = rand(100000, 999999);
    $db->prepare("UPDATE users SET otp_code = ? WHERE email = ?")->execute([$otp, $email]);

    $stmt = $db->prepare("SELECT name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $name = $stmt->fetchColumn();

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
       

      $mail->Username = 'your@gmail.com';
    $mail->Password = 'your_app_password'; // Use App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('your@gmail.com', 'Partner Matcher');    
        $mail->addAddress($email, $name);
        $mail->Subject = 'Resent OTP';
        $mail->Body    = "Hi $name,\n\nYour new OTP is: $otp";

        $mail->send();
        $message = "✅ OTP has been resent.";
    } catch (Exception $e) {
        $message = "❌ Failed to resend OTP.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Verify OTP</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: 'Segoe UI', sans-serif; background: linear-gradient(to right, #4facfe, #00f2fe); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">

<div style="background: white; padding: 30px; width: 100%; max-width: 400px; border-radius: 12px; box-shadow: 0 0 15px rgba(0,0,0,0.2); animation: fadeIn 0.6s ease;">
  <h2 style="text-align:center; color:#333;">🔐 Email Verification</h2>
  <p style="text-align:center; color:#666; font-size: 14px;">Enter the OTP sent to <b><?= htmlspecialchars($email) ?></b></p>

  <?php if ($message): ?>
    <div style="margin-top:15px; color: <?= strpos($message, '✅') !== false ? 'green' : 'red' ?>; background: #f0f0f0; padding:10px; border-radius: 6px;">
      <?= $message ?>
    </div>
  <?php endif; ?>

  <form method="post" style="margin-top: 20px;">
    <input type="text" name="otp" placeholder="Enter OTP" required style="margin-bottom: 20px; width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ccc; text-align:center;">
    <button type="submit" style="width: 100%; padding: 12px; background: linear-gradient(45deg, #4facfe, #00f2fe); color: white; border: none; border-radius: 8px; font-weight: 600;">Verify OTP</button>
  </form>

  <form method="post" style="margin-top: 15px; text-align: center;">
    <button name="resend" type="submit" style="background: none; border: none; color: #4facfe; font-size: 14px; text-decoration: underline; cursor: pointer;">Resend OTP</button>
  </form>

  <p style="text-align: center; margin-top: 10px; font-size: 13px;">
    <a href="index.php" style="color: #999;">⏪ Back to Home</a>
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
