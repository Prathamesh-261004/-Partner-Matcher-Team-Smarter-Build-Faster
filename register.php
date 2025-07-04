<?php
session_start();
require 'db.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $otp = rand(100000, 999999); // generate 6-digit OTP

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $message = "❗ Email already registered.";
    } else {
        // Insert user with verified = 0 and otp_code
        $stmt = $db->prepare("INSERT INTO users (name, email, password, verified, otp_code) VALUES (?, ?, ?, 0, ?)");
        $stmt->execute([$name, $email, $pass, $otp]);

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
$mail->Subject = 'Your OTP for Partner Matcher Verification';

$mail->Body = "
Hi $name,

🎉 Welcome to Partner Matcher – where dream teams are made!

To verify your email address and activate your account, please use the OTP below:

🔑 Your OTP: $otp

This OTP is valid for 10 minutes.  
Please do not share it with anyone.

If you didn’t sign up for Partner Matcher, you can safely ignore this email.

Cheers,  
– Team Partner Matcher
";

            $mail->send();
        } catch (Exception $e) {
            $message = "❌ OTP sending failed. Try again.";
        }

        $_SESSION['pending_email'] = $email;
        header("Location: verify.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Register - Partner Matcher</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: 'Segoe UI', sans-serif; background: linear-gradient(to right, #4facfe, #00f2fe); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">

<div style="background: white; padding: 30px; width: 100%; max-width: 400px; border-radius: 12px; box-shadow: 0 0 15px rgba(0,0,0,0.2); animation: fadeIn 0.6s ease;">
  <h2 style="text-align:center; color:#333;">📝 Register</h2>
  <p style="text-align:center; color:#666; font-size: 14px;">Create your account to get started</p>

  <?php if ($message): ?>
    <div style="margin-top:15px; color: red; background: #ffebee; padding:10px; border-radius: 6px;">
      <?= $message ?>
    </div>
  <?php endif; ?>

  <form method="post" style="margin-top: 20px;">
    <input type="text" name="name" placeholder="Full Name" required style="margin-bottom: 15px; width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ccc;">
    <input type="email" name="email" placeholder="Email" required style="margin-bottom: 15px; width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ccc;">
    <input type="password" name="password" placeholder="Password" required style="margin-bottom: 20px; width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ccc;">
    <button type="submit" style="width: 100%; padding: 12px; background: linear-gradient(45deg, #4facfe, #00f2fe); color: white; border: none; border-radius: 8px; font-weight: 600;">Register</button>
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

  <p style="text-align: center; margin-top: 15px;">
    Already have an account? <a href="login.php" style="color: #4facfe; font-weight: bold;">Login</a>
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
