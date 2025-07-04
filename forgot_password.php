<?php
session_start();
require 'db.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $otp = rand(100000, 999999);

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $db->prepare("UPDATE users SET otp_code = ? WHERE email = ?")->execute([$otp, $email]);

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
           $mail->Username = 'your@gmail.com';
    $mail->Password = 'your_app_password'; // Use App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('your@gmail.com', 'Partner Matcher');   
            $mail->addAddress($email);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body    = "Hi there,\n\nWe've received a request to reset your Partner Matcher account password.\n\nYour OTP is: $otp\n\nIf you didn't request this, you can ignore this email.\n\nBest,\nTeam Partner Matcher";

            $mail->send();
            $_SESSION['reset_email'] = $email;
            header("Location: reset_password.php");
            exit;
        } catch (Exception $e) {
            $message = "❌ Failed to send OTP.";
        }
    } else {
        $message = "⚠️ Email not found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Forgot Password</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; background: linear-gradient(to right, #43cea2, #185a9d); height: 100vh; display: flex; align-items: center; justify-content: center;">

<div style="background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 90%; max-width: 400px; animation: fadeIn 0.6s ease;">
  <h2 style="text-align:center; margin-bottom: 10px; color: #333;">🔐 Reset Password</h2>
  <p style="text-align:center; color: #666; font-size: 14px;">Enter your email to receive an OTP</p>

  <?php if ($message): ?>
    <div style="margin: 15px 0; background: #ffecec; padding: 10px; border-left: 4px solid #f44336; border-radius: 6px; color: #c00;">
      <?= $message ?>
    </div>
  <?php endif; ?>

  <form method="post" style="margin-top: 20px;">
    <input type="email" name="email" placeholder="Email Address" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ccc; margin-bottom: 15px;">
    <button type="submit" style="width: 100%; padding: 12px; background: linear-gradient(to right, #43cea2, #185a9d); color: white; border: none; border-radius: 8px; font-weight: bold; font-size: 16px; cursor: pointer;">📤 Send OTP</button>
  </form>

  <div style="margin-top: 20px; text-align: center;">
    <a href="login.php" style="text-decoration: none; color: #185a9d; font-weight: bold;">← Back to Login</a>
  </div>
</div>

<style>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}
</style>

</body>
</html>
