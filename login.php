<?php
session_start();
require_once __DIR__ . '/config/db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php'); exit();
}
if (isset($_SESSION['user_id'])) {
    header('Location: user_dashboard.php'); exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password']     ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both ID and password.';
    } else {

        // ── Check Admin table first ──
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($admin && ($admin['password'] === $password || password_verify($password, $admin['password']))) {
            $_SESSION['admin_id']  = $admin['id'];
            $_SESSION['username']  = $admin['username'];
            $_SESSION['role']      = 'admin';
            header('Location: dashboard.php');
            exit();
        }

        // ── Check Users table ──
        $stmt = $conn->prepare("SELECT * FROM users WHERE student_id = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && ($user['password'] === $password || password_verify($password, $user['password']))) {
            if ($user['status'] !== 'Active') {
                $error = 'Your account is inactive. Please contact the clinic.';
            } else {
                $_SESSION['user_id']    = $user['user_id'];
                $_SESSION['user_name']  = $user['full_name'];
                $_SESSION['student_id'] = $user['student_id'];
                $_SESSION['role']       = 'user';
                header('Location: user_dashboard.php');
                exit();
            }
        } else if (!$admin) {
            $error = 'Invalid ID or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Clinic Inventory System</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-page">
  <div class="login-card">

    <div class="login-logo">
      <div class="login-logo-icon">
        <img src="assets/images/logo.png" alt="Logo"
             style="width:28px;height:28px;object-fit:contain;">
      </div>
      <div>
        <div class="login-logo-name">Clinic Inventory System</div>
        <div class="login-logo-subtitle">Inabanga College of Arts and Sciences</div>
      </div>
    </div>

    <h2>Welcome back</h2>
    <p class="subtitle">Sign in to access the clinic system</p>

    <?php if ($error): ?>
      <div class="alert alert-error" data-auto-dismiss>⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="field" style="margin-bottom:16px">
        <label>Username / Student ID</label>
        <input type="text" name="username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
               placeholder="Enter username or Student ID"
               required autofocus>
      </div>
      <div class="field" style="margin-bottom:24px">
        <label>Password</label>
        <input type="password" name="password"
               placeholder="Enter your password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg">
        🔐 Sign In
      </button>
    </form>

    <div style="text-align:center;margin-top:20px;">
      <a href="register.php" style="color:var(--accent);font-size:13px;">
        New student/faculty? Register here →
      </a>
    </div>

    <p style="text-align:center;margin-top:12px;font-size:12px;color:var(--text3)">
      School Clinic · Authorized Personnel Only
    </p>

  </div>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>