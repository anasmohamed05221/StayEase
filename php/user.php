<?php
require_once 'config.php';
session_start();

header('Content-Type: application/json');

function send($data) {
    echo json_encode($data);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    send(['logged_in' => false]);
}

$user_id = $_SESSION['user_id'];
$action  = $_GET['action'] ?? '';

if ($action === 'bookings') {
    $stmt = $pdo->prepare("
        SELECT
            bookings.id,
            bookings.check_in,
            bookings.check_out,
            bookings.status,
            bookings.created_at,
            rooms.name        AS room_name,
            rooms.type        AS room_type,
            rooms.price_per_night,
            hotels.id         AS hotel_id,
            hotels.name       AS hotel_name,
            hotels.city       AS hotel_city,
            hotels.image      AS hotel_image,
            DATEDIFF(bookings.check_out, bookings.check_in) AS nights
        FROM bookings
        JOIN rooms  ON bookings.room_id  = rooms.id
        JOIN hotels ON rooms.hotel_id    = hotels.id
        WHERE bookings.user_id = ?
        ORDER BY bookings.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll();

    $today = strtotime(date('Y-m-d'));
    foreach ($bookings as &$b) {
        $nights           = max(1, (int) $b['nights']);
        $b['nights']      = $nights;
        $b['total_price'] = number_format($nights * $b['price_per_night'], 2);
        $b['can_cancel']  = ($b['status'] !== 'cancelled' && strtotime($b['check_in']) > time());

        $checkIn  = strtotime($b['check_in']);
        $checkOut = strtotime($b['check_out']);

        if ($b['status'] === 'cancelled') {
            $b['display_status'] = 'cancelled';
        } elseif ($checkOut < $today) {
            $b['display_status'] = 'past';
        } elseif ($checkIn <= $today && $today <= $checkOut) {
            $b['display_status'] = 'active';
        } else {
            $b['display_status'] = 'upcoming';
        }
    }

    send(['logged_in' => true, 'bookings' => $bookings]);
}

if ($action === 'cancel') {
    $booking_id = $_POST['booking_id'] ?? null;

    if (!$booking_id) {
        send(['logged_in' => true, 'success' => false, 'message' => 'Missing booking ID.']);
    }

    $stmt = $pdo->prepare("SELECT id, check_in, status FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        send(['logged_in' => true, 'success' => false, 'message' => 'Booking not found.']);
    }

    if ($booking['status'] === 'cancelled') {
        send(['logged_in' => true, 'success' => false, 'message' => 'Already cancelled.']);
    }

    if (strtotime($booking['check_in']) <= time()) {
        send(['logged_in' => true, 'success' => false, 'message' => 'Cannot cancel a past or ongoing booking.']);
    }

    $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$booking_id]);

    send(['logged_in' => true, 'success' => true]);
}

send(['logged_in' => true, 'success' => false, 'message' => 'Invalid action.']);