<?php
require_once 'config.php';
session_start();

function clean($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.html");
        exit;
    }
}

requireLogin();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'show') {
    $room_id = $_GET['room_id'] ?? 0;

    $stmt = $pdo->prepare("
        SELECT rooms.*, hotels.name AS hotel_name, hotels.city
        FROM rooms
        JOIN hotels ON rooms.hotel_id = hotels.id
        WHERE rooms.id = ?
    ");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();

    if (!$room) {
        die("Room not found.");
    }

    if ($room['is_available'] != 1) {
        die("This room is currently unavailable.");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>StayEase. | Confirm Booking</title>

  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/booking.css">
</head>
<body>

  <nav class="navbar">
    <div class="logo">StayEase.</div>
    <div class="nav-links">
      <a href="../index.html">Home</a>
      <a href="../dashboard.html">Dashboard</a>
      <a href="../login.html">Login</a>
    </div>
  </nav>

  <main class="container">
    <section class="card">
      <span class="badge">Confirm Booking</span>
      <h1>Complete Your Reservation</h1>

      <div class="room-box">
        <h2><?php echo clean($room['name']); ?></h2>
        <p><strong>Hotel:</strong> <?php echo clean($room['hotel_name']); ?></p>
        <p><strong>City:</strong> <?php echo clean($room['city']); ?></p>
        <p><strong>Room Type:</strong> <?php echo clean($room['type']); ?></p>
        <p><strong>Price Per Night:</strong> $<?php echo clean($room['price_per_night']); ?></p>
      </div>

      <form action="booking.php" method="post">
        <input type="hidden" name="action" value="book">
        <input type="hidden" name="room_id" value="<?php echo clean($room['id']); ?>">

        <label for="check_in">Check-in Date</label>
        <input type="date" id="check_in" name="check_in" required>

        <label for="check_out">Check-out Date</label>
        <input type="date" id="check_out" name="check_out" required>

        <button type="submit">Confirm Booking</button>
      </form>
    </section>
  </main>

</body>
</html>
<?php
    exit;
}

if ($action === 'book') {
    $user_id = $_SESSION['user_id'];
    $room_id = $_POST['room_id'] ?? 0;
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';

    if ($check_in == '' || $check_out == '') {
        die("Please select check-in and check-out dates.");
    }

    if ($check_out <= $check_in) {
        die("Check-out date must be after check-in date.");
    }

    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();

    if (!$room) {
        die("Room not found.");
    }

    if ($room['is_available'] != 1) {
        die("This room is currently unavailable.");
    }

    $overlap = $pdo->prepare("
        SELECT id
        FROM bookings
        WHERE room_id = ?
        AND status != 'cancelled'
        AND ? < check_out
        AND ? > check_in
    ");
    $overlap->execute([$room_id, $check_in, $check_out]);

    if ($overlap->fetch()) {
        die("This room is already booked for the selected dates.");
    }

    $start = new DateTime($check_in);
    $end = new DateTime($check_out);
    $nights = $start->diff($end)->days;
    $total_price = $nights * $room['price_per_night'];

    $insert = $pdo->prepare("
        INSERT INTO bookings (user_id, room_id, check_in, check_out, status)
        VALUES (?, ?, ?, ?, 'pending')
    ");
    $insert->execute([$user_id, $room_id, $check_in, $check_out]);

    $booking_id = $pdo->lastInsertId();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>StayEase. | Booking Confirmed</title>

  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/booking.css">
</head>
<body>

  <nav class="navbar">
    <div class="logo">StayEase.</div>
    <div class="nav-links">
      <a href="../index.html">Home</a>
      <a href="../dashboard.html">Dashboard</a>
      <a href="../my-bookings.html">My Bookings</a>
    </div>
  </nav>

  <main class="container">
    <section class="card success-card">
      <span class="success-icon">✓</span>
      <h1>Booking Confirmed</h1>
      <p>Your reservation has been created successfully.</p>

      <div class="detail-row">
        <span>Booking ID</span>
        <strong>#<?php echo clean($booking_id); ?></strong>
      </div>

      <div class="detail-row">
        <span>Check-in</span>
        <strong><?php echo clean($check_in); ?></strong>
      </div>

      <div class="detail-row">
        <span>Check-out</span>
        <strong><?php echo clean($check_out); ?></strong>
      </div>

      <div class="detail-row">
        <span>Nights</span>
        <strong><?php echo clean($nights); ?></strong>
      </div>

      <div class="detail-row">
        <span>Total Price</span>
        <strong>$<?php echo clean(number_format($total_price, 2)); ?></strong>
      </div>

      <a class="button-link" href="../dashboard.html">Go to Dashboard</a>
      <a class="secondary-link" href="../my-bookings.html">View My Bookings</a>
    </section>
  </main>

</body>
</html>
<?php
    exit;
}

echo "Invalid booking action.";
?>
