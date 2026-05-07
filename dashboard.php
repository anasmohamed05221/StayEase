<?php
session_start();
require_once 'php/config.php';

$logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? '';

$total = 0;
$upcoming = 0;
$past = 0;

if ($logged_in) {
    $uid = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
    $stmt->execute([$uid]);
    $total = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND check_in > CURDATE() AND status != 'cancelled'");
    $stmt->execute([$uid]);
    $upcoming = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND check_out < CURDATE() AND status != 'cancelled'");
    $stmt->execute([$uid]);
    $past = (int) $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | StayEase.</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/main.css">
  <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

  <nav class="navbar">
    <div class="container nav-inner">
      <a href="index.php" class="logo">StayEase<span>.</span></a>
      <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="search-results.php">Discover</a>
        <a href="about.html">About us</a>
      </div>
      <div class="nav-auth">
        <a href="dashboard.php" class="profile-icon"><i class="fa-regular fa-circle-user"></i><?php if (!empty($_SESSION['user_name'])): ?> <span class="nav-user"><?= htmlspecialchars($_SESSION['user_name']) ?></span><?php endif; ?></a>
      </div>
    </div>
  </nav>

<?php if ($logged_in): ?>

  <main class="dashboard-page">

    <section class="dashboard-header">
      <div class="dashboard-title">
        <h1>Welcome back, <span><?= htmlspecialchars($user_name) ?></span></h1>
        <p>Manage your hotel bookings, view your stays, and continue exploring hotels.</p>
      </div>
      <div class="dashboard-actions">
        <a href="my-bookings.html" class="btn btn-primary">My Bookings</a>
        <a href="search-results.php" class="btn btn-secondary">Browse Hotels</a>
        <form method="POST" action="php/auth.php">
          <input type="hidden" name="action" value="logout">
          <button type="submit" class="btn btn-danger"><i class="fa-solid fa-arrow-right-from-bracket"></i></i> &nbsp; Logout</button>
        </form>
      </div>
    </section>

    <section class="stats-grid">
      <div class="stat-card">
        <p class="stat-label">Total Bookings</p>
        <h2 class="stat-number"><?= $total ?></h2>
        <p class="stat-note">All reservations made from your account.</p>
      </div>
      <div class="stat-card">
        <p class="stat-label">Upcoming Bookings</p>
        <h2 class="stat-number"><?= $upcoming ?></h2>
        <p class="stat-note">Bookings with future check-in dates.</p>
      </div>
      <div class="stat-card">
        <p class="stat-label">Past Stays</p>
        <h2 class="stat-number"><?= $past ?></h2>
        <p class="stat-note">Completed hotel stays.</p>
      </div>
    </section>

    <section class="quick-links">
      <h2 class="section-title">Quick Actions</h2>
      <p class="section-subtitle">Access your main account actions quickly.</p>
      <div class="quick-links-grid">
        <a href="my-bookings.html" class="quick-link-card">
          <h3>View My Bookings</h3>
          <p>Check your current, upcoming, and past reservations.</p>
          <span class="btn btn-primary">Open Bookings</span>
        </a>
        <a href="search-results.php" class="quick-link-card">
          <h3>Browse Hotels</h3>
          <p>Discover available hotels and book your next stay.</p>
          <span class="btn btn-secondary">Browse Now</span>
        </a>
      </div>
    </section>

  </main>

<?php else: ?>

  <main class="auth-gate">
    <div class="auth-gate-card">
      <h2>You're not logged in</h2>
      <p>Sign in to access your dashboard, bookings, and more.</p>
      <div class="auth-gate-btns">
        <a href="login.html" class="auth-gate-btn">Login</a>
        <span class="auth-gate-sep">/</span>
        <a href="register.html" class="auth-gate-btn">Register</a>
      </div>
    </div>
  </main>

<?php endif; ?>

  <footer class="footer">
    <div class="footer-inner">
      <div class="footer-logo">StayEase</div>
      <div class="footer-links">
        <a href="about.html">About Us</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Help Center</a>
        <a href="#">Partners</a>
      </div>
      <p class="footer-copy">© 2024 StayEase Inc. All rights reserved.</p>
    </div>
  </footer>

</body>
</html>