<?php
// header.php is in /includes/ — use __DIR__ to go up one level
require_once __DIR__ . '/../config/session.php';
requireLogin();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
requireLogin();

// Notification counts for sidebar badges
$expCount = $conn->query("SELECT COUNT(*) AS c FROM medicines WHERE expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetch_assoc()['c'];
$lowCount = $conn->query("SELECT COUNT(*) AS c FROM medicines WHERE quantity <= 10 OR status IN ('Low Stock','Out of Stock')")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'MedVault' ?> — School Clinic</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="topbar">
  <div class="topbar-left">
  <div class="topbar-logo"><img src="assets/images/logo.png" alt="Logo" style="width:52px;height:52px;object-fit:cover;border-radius:50%;border:2px solid #3fb950;"></div>
  <div class="topbar-title">Clinic Inventory System</div>
</div>
  <div class="topbar-right">
    <div class="topbar-user">
      <div class="user-dot"></div>
      <span><?= htmlspecialchars(getCurrentUser()) ?></span>
      <span class="user-role">· Nurse Admin</span>
    </div>
    <a href="profile.php" class="btn btn-secondary btn-sm">⚙ Profile</a>
    <a href="logout.php"  class="btn btn-secondary btn-sm">Sign Out</a>
  </div>
</div>

<div class="layout">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Navigation</div>
      <a href="dashboard.php"    class="nav-item <?= ($activePage==='dashboard')?'active':'' ?>"><span class="nav-icon">📊</span> Dashboard</a>
      <a href="medicines.php"    class="nav-item <?= ($activePage==='medicines')?'active':'' ?>"><span class="nav-icon">💊</span> Medicines</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Alerts</div>
      <a href="expiring.php" class="nav-item <?= ($activePage==='expiring')?'active':'' ?>">
        <span class="nav-icon">⏳</span> Expiring Soon
        <?php if ($expCount > 0): ?><span class="badge-pill"><?= $expCount ?></span><?php endif; ?>
      </a>
      <a href="lowstock.php" class="nav-item <?= ($activePage==='lowstock')?'active':'' ?>">
        <span class="nav-icon">⚠️</span> Low Stock
        <?php if ($lowCount > 0): ?><span class="badge-pill badge-pill-yellow"><?= $lowCount ?></span><?php endif; ?>
      </a>
    </div>
    <div class="sidebar-section">
  <div class="sidebar-label">Students</div>
  <a href="admin_requests.php" class="nav-item <?= ($activePage==='requests')?'active':'' ?>">
    <span class="nav-icon">📋</span> Medicine Requests
    <?php
    $pendingCount = $conn->query("SELECT COUNT(*) AS c FROM medicine_requests WHERE status='Pending'")->fetch_assoc()['c'];
    if ($pendingCount > 0):
    ?>
      <span class="badge-pill"><?= $pendingCount ?></span>
    <?php endif; ?>
  </a>
  <a href="admin_students.php" class="nav-item <?= ($activePage==='students')?'active':'' ?>">
    <span class="nav-icon">👥</span> Students
  </a>
</div>
<div class="sidebar-section">
  <div class="sidebar-label">Reports</div>
  <a href="print_inventory.php" class="nav-item <?= ($activePage==='print')?'active':'' ?>" target="_blank">
    <span class="nav-icon">🖨</span> Print Inventory
  </a>
</div>
    <div class="sidebar-footer">
      <div class="sidebar-build">Clinic Inventory System v1.0</div>
      <div class="sidebar-build">© <?= date('Y') ?> ICAS School Clinic</div>
    </div>
  </aside>

  <main class="main-content">
  <?php $flash = getFlash(); if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" data-auto-dismiss>
      <?= htmlspecialchars($flash['msg']) ?>
    </div>
  <?php endif; ?>
