<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>StayEase - Booking</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" href="css/main.css">
  <link rel="stylesheet" href="css/booking.css">
</head>

<body>

<nav class="navbar">
    <div class="container nav-inner">
      <a href="index.php" class="logo">StayEase<span>.</span></a>
      <div class="nav-links">
        <a href="index.php" class="active">Home</a>
        <a href="search-results.php">Discover</a>
        <a href="about.html">About us</a>
      </div>
      <div class="nav-auth">
        <a href="dashboard.php" class="profile-icon"><i class="fa-regular fa-circle-user"></i><?php if (!empty($_SESSION['user_name'])): ?> <span class="nav-user"><?= htmlspecialchars($_SESSION['user_name']) ?></span><?php endif; ?></a>
      </div>
    </div>
  </nav>

<main class="booking-page">

  <!-- Back link to return to search or room results page -->
  <a href="search-results.php" class="back-link">← Back to Search</a>

  <!-- Main layout: left side content + right side price card -->
  <section class="booking-grid">

    <div class="left-column">

      <section class="room-card" id="roomCard">
        <div class="loading-card">Loading selected room...</div>
      </section>

      <!-- Date selection card -->
      <section class="step-card">

        <div class="step-title">
          <div class="step-icon"><i class="fa-solid fa-calendar-days"></i></div>
          <h2>1. Select Dates</h2>
        </div>

        <div class="date-grid">

          <div class="field-group">
            <label for="checkIn">Check-in Date</label>
            <input type="text" id="checkIn" name="check_in" placeholder="Select date" readonly>
          </div>

          <div class="field-group">
            <label for="checkOut">Check-out Date</label>
            <input type="text" id="checkOut" name="check_out" placeholder="Select date" readonly>
          </div>

        </div>
      </section>

      <!-- Guest information card -->
      <section class="step-card">

          <div class="step-title">
          <div class="step-icon"><i class="fa-solid fa-user-pen"></i></div>
          <h2>2. Guest Information</h2>
        </div>

        <form id="bookingForm" action="php/booking.php?action=create" method="POST">

          <input type="hidden" id="roomIdInput" name="room_id">

          <div class="guest-grid">

            <div class="field-group">
              <label for="firstName">First Name</label>
              <input type="text" id="firstName" placeholder="John">
            </div>

            <div class="field-group">
              <label for="lastName">Last Name</label>
              <input type="text" id="lastName" placeholder="Doe">
            </div>

            <div class="field-group full">
              <label for="email">Email Address</label>
              <input type="email" id="email" placeholder="john.doe@example.com">
            </div>

            <div class="field-group">
              <label for="guests">Guests</label>
              <select id="guests">
                <option>1 Guest</option>
                <option selected>2 Adults</option>
                <option>3 Guests</option>
                <option>4 Guests</option>
              </select>
            </div>

            <div class="field-group">
              <label for="requests">Special Requests</label>
              <input type="text" id="requests" placeholder="High floor, early check-in, etc.">
            </div>

          </div>

          <input type="hidden" id="checkInHidden" name="check_in">
          <input type="hidden" id="checkOutHidden" name="check_out">

          <p id="bookingError" class="error-message"></p>

        </form>
      </section>

    </div>

    <aside class="right-column">

      <!-- Price summary card -->
      <section class="price-card">
        <h2>Price Summary</h2>

        <div class="price-row">
          <span><span id="nightsText">0</span> nights</span>
          <strong>$<span id="basePrice">0.00</span></strong>
        </div>

        <div class="price-row">
          <span>Cleaning fee</span>
          <strong>$<span id="cleaningFee">45.00</span></strong>
        </div>

        <div class="price-row">
          <span>Service fee</span>
          <strong>$<span id="serviceFee">0.00</span></strong>
        </div>

        <div class="total-row">
          <span>Total</span>
          <strong>$<span id="totalPrice">0.00</span></strong>
        </div>

        <button class="pay-btn" type="submit" form="bookingForm">
          <i class="fa-solid fa-check-double"></i> Confirm & Pay Now
        </button>

        <p class="cancel-note">Free cancellation until Nov 12, 2026</p>
      </section>

      <!-- Support / trust card -->
      <section class="support-card">

        <div class="support-row">
          <span class="support-icon green"><i class="fa-solid fa-shield-halved"></i></span>
          <div>
            <strong>Secure Payment</strong>
            <p>Encrypted corporate-grade processing</p>
          </div>
        </div>

        <div class="support-row">
          <span class="support-icon blue"><i class="fa-solid fa-headset"></i></span>
          <div>
            <strong>24/7 Support</strong>
            <p>Concierge assistance for all bookings</p>
          </div>
        </div>

      </section>

    </aside>

  </section>

</main>

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

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="js/booking.js"></script>

</body>
</html>