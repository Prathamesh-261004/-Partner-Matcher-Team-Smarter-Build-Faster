<?php
session_start();
require 'db.php';

$admin_email = 'admin@example.com';
$admin_pass  = 'admin123';

if (!isset($_SESSION['admin'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        if ($email === $admin_email && $password === $admin_pass) {
            $_SESSION['admin'] = true;
            header("Location: admin_stats.php");
            exit;
        } else {
            $error = "❌ Invalid credentials.";
        }
    }

    // Login Form
    echo '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Login</title>
    </head>
    <body style="font-family: Arial; background: #eef2f3; display: flex; align-items: center; justify-content: center; height: 100vh;">
        <form method="post" style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.2); width: 100%; max-width: 350px;">
            <h2 style="margin-bottom: 20px; text-align:center;">🔐 Admin Login</h2>
            '. (isset($error) ? "<p style='color:red;'>$error</p>" : "") .'
            <input type="email" name="email" placeholder="Admin Email" required style="width:100%; padding:10px; margin-bottom: 15px; border:1px solid #ccc; border-radius:5px;">
            <input type="password" name="password" placeholder="Password" required style="width:100%; padding:10px; margin-bottom: 20px; border:1px solid #ccc; border-radius:5px;">
            <button type="submit" style="width:100%; padding:10px; background:#4facfe; border:none; color:white; border-radius:5px; font-weight:bold;">Login</button>
        </form>
    </body>
    </html>';
    exit;
}

// Fetch data only if admin is logged in
$skills = $db->query("SELECT skill_name, COUNT(*) as total FROM skills GROUP BY skill_name")->fetchAll(PDO::FETCH_ASSOC);
$domains = $db->query("SELECT domain, COUNT(*) as total FROM preferences GROUP BY domain")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard - Partner Matcher</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body style="font-family: Arial; padding: 40px; background: #f9f9f9;">
  <h2>📊 Admin Statistics</h2>

  <div style="display:flex; flex-wrap:wrap; gap: 40px; justify-content: center;">
    <div style="width:400px; background:white; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);">
      <h3>Skills Distribution</h3>
      <canvas id="skillsChart" height="200"></canvas>
    </div>

    <div style="width:400px; background:white; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);">
      <h3>Domain Preferences</h3>
      <canvas id="domainsChart" height="200"></canvas>
    </div>
  </div>

  <br><br>
  <a href="dashboard.php" style="text-decoration:none; padding:10px 20px; background:#4facfe; color:white; border-radius:6px;">← Back to Dashboard</a>
  <a href="?logout=1" style="margin-left: 20px; text-decoration:none; padding:10px 20px; background:#f44336; color:white; border-radius:6px;">Logout</a>

<script>
const skillCtx = document.getElementById('skillsChart').getContext('2d');
const domainCtx = document.getElementById('domainsChart').getContext('2d');

new Chart(skillCtx, {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_column($skills, 'skill_name')) ?>,
    datasets: [{
      label: 'Skill Count',
      data: <?= json_encode(array_column($skills, 'total')) ?>,
      backgroundColor: 'rgba(54, 162, 235, 0.6)'
    }]
  }
});

new Chart(domainCtx, {
  type: 'pie',
  data: {
    labels: <?= json_encode(array_column($domains, 'domain')) ?>,
    datasets: [{
      label: 'Domain Count',
      data: <?= json_encode(array_column($domains, 'total')) ?>,
      backgroundColor: ['#f44336','#4caf50','#2196f3','#ff9800','#9c27b0','#00bcd4']
    }]
  }
});
</script>
</body>
</html>

<?php
// Admin logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin']);
    header("Location: admin_stats.php");
    exit;
}
?>
