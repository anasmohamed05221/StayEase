<?php require_once 'php/home.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>StayEase. | Hotel Discovery & Booking</title>
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!--Flatpickr calender-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/main.css">
</head>

<body>
  <nav class="navbar">
    <div class="container nav-inner">
      <a href="index.php" class="logo">StayEase<span>.</span></a>
      <div class="nav-links">
        <a href="index.php" class="active">Home</a>
        <a href="search-results.php">Discover</a>
        <a href="about.html">About</a>
        <a href="reviews.html">Reviews</a>
      </div>
      <div class="nav-auth">
        <a href="dashboard.html" class="profile-icon"><i class="fa-regular fa-circle-user"></i></a>
        <a href="login.html" class="btn-login">Login</a>
      </div>
    </div>
  </nav>

  <section class="hero" style="background-image: url('assets/images/hero-bg-v2.png');">

    <h1>Find your next escape</h1>
    <p>Discover handpicked boutique hotels and luxury stays for your perfect getaway.</p>

    <form class="search-box" id="searchForm" action="search-results.php" method="GET">

      <div class="search-field">
        <i class="fa-solid fa-location-dot"></i>
        <div class="search-field-text">
          <label for="city">Destination</label>
          <input type="text" id="city" name="city" placeholder="Where are you going?" autocomplete="off">
        </div>
      </div>

      <div class="search-field">
        <i class="fa-solid fa-calendar-days"></i>
        <div class="search-field-text">
          <label for="check_in">Check-in</label>
          <input type="date" id="check_in" name="check_in" placeholder="Add date">
        </div>
      </div>

      <div class="search-field">
        <i class="fa-solid fa-calendar-check"></i>
        <div class="search-field-text">
          <label for="check_out">Check-out</label>
          <input type="date" id="check_out" name="check_out" placeholder="Add date">
        </div>
      </div>

      <button type="submit" class="search-btn">
        <i class="fa-solid fa-magnifying-glass"></i>
        Search
      </button>

    </form>

    <!-- Shown by main.js if check-out is before check-in -->
    <p class="form-error" id="dateError">
      <i class="fa-solid fa-circle-exclamation"></i>
      Check-out date must be after check-in date.
    </p>

  </section>

  <section class="section">
    <div class="container">
      <div class="section-header">
        <h2>Featured Destinations</h2>
        <a href="search-results.php">View All Destinations ›</a>
      </div>
      <p class="section-sub">Explore our most-loved cities this season.</p>

      <div class="hotels-grid">

        <?php if (empty($hotels)): ?>
          <p style="color:var(--muted); font-size:14px;">No featured hotels available yet.</p>
        <?php else: ?>
          <?php foreach ($hotels as $h):
            $stars    = str_repeat('★', (int)$h['stars']) . str_repeat('☆', 5 - (int)$h['stars']);
            $minPrice = $h['min_price'] ? '$' . number_format($h['min_price'], 0) : 'N/A';
          ?>
            <a href="hotel-detail.php?id=<?= $h['id'] ?>" class="hotel-card">
              <div class="hotel-card-img">
                <img src="<?= htmlspecialchars($h['image']) ?>" alt="<?= htmlspecialchars($h['name']) ?>">
              </div>
              <div class="hotel-card-body">
                <div class="hotel-stars"><?= $stars ?></div>
                <h3><?= htmlspecialchars($h['name']) ?></h3>
                <p class="city"><i class="fa-solid fa-location-dot fa-xs"></i> <?= htmlspecialchars($h['city']) ?></p>
              </div>
              <div class="hotel-card-footer">
                <div class="hotel-price">
                  <small>Starting from</small>
                  <strong><?= $minPrice ?><span style="font-size:13px;font-weight:400;color:var(--muted)"> /night</span></strong>
                </div>
                <span class="btn-card">View Hotel</span>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>

      </div>
    </div>
  </section>

  <!-- WHY CHOOSE US -->
  <section class="why-section">
    <div class="container">
      <h2>Why Choose StayEase?</h2>
      <p class="why-sub">We've simplified travel planning so you can focus on making memories.</p>
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon"><i class="fa-solid fa-dollar-sign"></i></div>
          <h3>Best Price Guarantee</h3>
          <p>Found a lower price elsewhere? We'll match it plus give you an extra 10% off your stay.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><i class="fa-solid fa-headset"></i></div>
          <h3>24/7 Premium Support</h3>
          <p>Our dedicated concierge team is available around the clock to assist with any request.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><i class="fa-solid fa-comment-dots"></i></div>
          <h3>Verified Guest Reviews</h3>
          <p>Read authentic experiences from travelers like you. No bots, no fake feedback, ever.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA BANNER -->
  <section class="cta-section">
    <div class="container">
      <div class="cta-banner">
        <div class="cta-text">
          <h2>Join the world's most rewarding hotel club.</h2>
          <p>Unlock exclusive rates, late check-outs, and room upgrades starting from your first booking.</p>
        </div>
        <div class="cta-form">
          <input type="email" placeholder="Enter your email">
          <button type="button">Sign Up Free</button>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
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

  <script src="js/main.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    flatpickr("#check_in", {
      minDate: "today"
    });
    flatpickr("#check_out", {
      minDate: "today"
    });
  </script>
</body>

</html>