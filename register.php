<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: user_dashboard.php');
    exit();
}

$errors  = [];
$success = false;
$old     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old        = $_POST;
    $student_id = trim($_POST['student_id'] ?? '');
    $full_name  = trim($_POST['full_name']  ?? '');
    $grade      = trim($_POST['grade']      ?? '');
    $section    = trim($_POST['section']    ?? '');
    $email      = trim($_POST['email']      ?? '');
    $password   = $_POST['password']        ?? '';
    $confirm    = $_POST['confirm_password']?? '';

    // Validation
    if (empty($student_id)) $errors[] = 'Student ID is required.';
    if (empty($full_name))  $errors[] = 'Full name is required.';
    if (empty($password))   $errors[] = 'Password is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    // Check if student ID already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE student_id = ?");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Student ID already registered.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO users (student_id, full_name, grade, section, email, password)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssss",
            $student_id, $full_name, $grade,
            $section, $email, $password
        );
        if ($stmt->execute()) {
            $success = true;
            $old     = [];
        } else {
            $errors[] = 'Registration failed: ' . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Register — Clinic Inventory System</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-page">
  <div class="login-card" style="max-width:500px;padding:40px 44px;">

    <div class="login-logo">
      <div class="login-logo-icon">
        <img src="assets/images/logo.png" alt="Logo"
             style="width:28px;height:28px;object-fit:contain;">
      </div>
      <div>
        <div class="login-logo-name">Clinic Inventory System</div>
        <div class="login-logo-subtitle">Student Registration</div>
      </div>
    </div>

    <h2>Create Account</h2>
    <p class="subtitle">Register to request medicines from the clinic</p>

    <?php if ($success): ?>
      <div class="alert alert-success">
        ✅ Registration successful! <a href="login.php" style="color:var(--accent)">Login here →</a>
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error" data-auto-dismiss>
        ⚠ <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
      </div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="POST" action="register.php">

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">

        <div class="field" style="grid-column:1/-1">
          <label>Full Name *</label>
          <input type="text" name="full_name"
                 value="<?= htmlspecialchars($old['full_name'] ?? '') ?>"
                 placeholder="e.g. Juan Dela Cruz" required>
        </div>

        <div class="field" style="grid-column:1/-1">
          <label>Student ID *</label>
          <input type="text" name="student_id"
                 value="<?= htmlspecialchars($old['student_id'] ?? '') ?>"
                 placeholder="e.g. 2024-00123" required>
        </div>

        <div class="field">
          <label>Grade / Year</label>
          <select name="grade" style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:11px 14px;font-size:14px;color:var(--text);outline:none;">
            <option value="">— Select —</option>
            <option <?= ($old['grade']??'')==='Grade 7'?'selected':'' ?>>Grade 7</option>
            <option <?= ($old['grade']??'')==='Grade 8'?'selected':'' ?>>Grade 8</option>
            <option <?= ($old['grade']??'')==='Grade 9'?'selected':'' ?>>Grade 9</option>
            <option <?= ($old['grade']??'')==='Grade 10'?'selected':'' ?>>Grade 10</option>
            <option <?= ($old['grade']??'')==='Grade 11'?'selected':'' ?>>Grade 11</option>
            <option <?= ($old['grade']??'')==='Grade 12'?'selected':'' ?>>Grade 12</option>
            <option <?= ($old['grade']??'')==='1st Year'?'selected':'' ?>>1st Year</option>
            <option <?= ($old['grade']??'')==='2nd Year'?'selected':'' ?>>2nd Year</option>
            <option <?= ($old['grade']??'')==='3rd Year'?'selected':'' ?>>3rd Year</option>
            <option <?= ($old['grade']??'')==='4th Year'?'selected':'' ?>>4th Year</option>
          </select>
        </div>

        <div class="field">
          <label>Section</label>
          <input type="text" name="section"
                 value="<?= htmlspecialchars($old['section'] ?? '') ?>"
                 placeholder="e.g. Rizal">
        </div>

        <div class="field" style="grid-column:1/-1">
          <label>Email <span style="color:var(--text3)">(optional)</span></label>
          <input type="email" name="email"
                 value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                 placeholder="e.g. juan@email.com">
        </div>

        <div class="field">
          <label>Password *</label>
          <input type="password" name="password"
                 placeholder="Min. 6 characters" required>
        </div>

        <div class="field">
          <label>Confirm Password *</label>
          <input type="password" name="confirm_password"
                 placeholder="Repeat password" required>
        </div>

      </div>

      <button type="submit" class="btn btn-primary btn-block btn-lg">
        📝 Register
      </button>

    </form>
    <?php endif; ?>

    <div style="text-align:center;margin-top:16px;">
      <a href="login.php" style="color:var(--text3);font-size:13px;">
  Already have an account? Login →
</a>
    </div>

  </div>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>