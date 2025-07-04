1. Import the Database
Create a new database named 'partner_matcher' using phpMyAdmin. Then import the provided 'schema.sql'
file. This includes tables: users, skills, team_requests, projects, project_members, project_chats, etc.
2. Configure Database Connection (db.php)
Edit db.php to match your MySQL settings:
$db = new PDO("mysql:host=localhost;dbname=partner_matcher", "root", "");
3. Set Up PHPMailer
1. Download PHPMailer from: https://github.com/PHPMailer/PHPMailer
2. Place the folder in your project root as 'phpmailer/'
3. In your PHP files (e.g., forgot_password.php), include:
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';
4. Gmail SMTP Setup
1. Enable 2-Step Verification in your Google Account.
2. Generate an App Password from https://myaccount.google.com/apppasswords
3. Use the app password in your PHPMailer SMTP configuration:
$mail->Username = 'your_email@gmail.com';
$mail->Password = 'your_app_password';
5. Run the Project Locally with XAMPP
1. Place the project in 'htdocs' folder of XAMPP.
2. Start Apache and MySQL from XAMPP Control Panel.
3. Visit http://localhost/team/ in your browser.
6. Admin Login
Default admin credentials:
Email: admin@example.com
Password: admin123
