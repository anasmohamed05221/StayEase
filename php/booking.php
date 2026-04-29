<?php
// Include database connection file.
// This file should create the $pdo connection.
require_once 'config.php';

// Start session so we can access $_SESSION['user_id'].
session_start();

// Get the action from the URL.
// Example: booking.php?action=room
$action = $_GET['action'] ?? '';

// Helper function to return JSON responses to JavaScript.
function send_json($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/*
|--------------------------------------------------------------------------
| ACTION 1: Get selected room data
|--------------------------------------------------------------------------
| This action is used by booking.html.
| JavaScript sends room_id to this file.
| PHP fetches real room data from the database.
*/
if ($action === 'room') {

    // User must be logged in before booking.
    if (!isset($_SESSION['user_id'])) {
        send_json([
            'success' => false,
            'redirect' => 'login.html'
        ]);
    }

    // Read room_id from URL.
    // Example: booking.php?action=room&room_id=3
    $room_id = $_GET['room_id'] ?? null;

    // If room_id is missing, return error.
    if (!$room_id) {
        send_json([
            'success' => false,
            'message' => 'Room ID is missing.'
        ]);
    }

    // Get room data and hotel name from the database.
    // JOIN is used because room data is in rooms table
    // and hotel name is in hotels table.
    $stmt = $pdo->prepare("
        SELECT
            rooms.id,
            rooms.name,
            rooms.type,
            rooms.price_per_night,
            rooms.description,
            rooms.image,
            rooms.is_available,
            hotels.name AS hotel_name,
            ROUND(AVG(reviews.rating), 1) AS avg_rating
        FROM rooms
        JOIN hotels ON rooms.hotel_id = hotels.id
        LEFT JOIN reviews ON reviews.hotel_id = hotels.id
        WHERE rooms.id = ?
        GROUP BY rooms.id, hotels.id
    ");

    // Execute safely using prepared statement to prevent SQL injection.
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();

    // If room does not exist.
    if (!$room) {
        send_json([
            'success' => false,
            'message' => 'Room was not found.'
        ]);
    }

    // If room is marked unavailable.
    if ((int)$room['is_available'] !== 1) {
        send_json([
            'success' => false,
            'message' => 'This room is currently unavailable.'
        ]);
    }

    // Return room data to JavaScript.
    send_json([
        'success' => true,
        'room' => $room
    ]);
}

/*
|--------------------------------------------------------------------------
| ACTION 2: Create booking
|--------------------------------------------------------------------------
| This action runs when the user clicks Confirm & Pay Now.
| It validates the data, checks availability, prevents overlap,
| then inserts a new booking into the bookings table.
*/
if ($action === 'create') {

    // User must be logged in.
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.html');
        exit;
    }

    // user_id comes from session, not from the form.
    // This is more secure because user cannot book for another user.
    $user_id = $_SESSION['user_id'];

    // Booking data submitted from booking.html.
    $room_id = $_POST['room_id'] ?? null;
    $check_in = $_POST['check_in'] ?? null;
    $check_out = $_POST['check_out'] ?? null;

    // Basic validation: all required fields must exist.
    if (!$room_id || !$check_in || !$check_out) {
        header('Location: ../booking.html?room_id=' . urlencode($room_id) . '&error=' . urlencode('Missing booking data.'));
        exit;
    }

    // Server-side date validation.
    // Check-out must be after check-in.
    if (strtotime($check_out) <= strtotime($check_in)) {
        header('Location: ../booking.html?room_id=' . urlencode($room_id) . '&error=' . urlencode('Check-out date must be after check-in date.'));
        exit;
    }

    // Check that the selected room exists and is available.
    $stmt = $pdo->prepare("SELECT is_available FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();

    // If room does not exist.
    if (!$room) {
        header('Location: ../booking.html?room_id=' . urlencode($room_id) . '&error=' . urlencode('Room not found.'));
        exit;
    }

    // If room is not available.
    if ((int)$room['is_available'] !== 1) {
        header('Location: ../booking.html?room_id=' . urlencode($room_id) . '&error=' . urlencode('This room is unavailable.'));
        exit;
    }

    /*
    Overlap check:
    This prevents two users from booking the same room in the same period.

    Overlap condition:
    old_check_in < new_check_out
    AND
    old_check_out > new_check_in

    Example:
    Old booking: 10 May to 15 May
    New booking: 12 May to 18 May
    This overlaps, so it must be rejected.
    */
    $overlapStmt = $pdo->prepare("
        SELECT id
        FROM bookings
        WHERE room_id = ?
        AND status != 'cancelled'
        AND check_in < ?
        AND check_out > ?
        LIMIT 1
    ");

    $overlapStmt->execute([$room_id, $check_out, $check_in]);
    $overlap = $overlapStmt->fetch();

    // If overlapping booking exists, reject the new booking.
    if ($overlap) {
        header('Location: ../booking.html?room_id=' . urlencode($room_id) . '&error=' . urlencode('This room is already booked for the selected dates.'));
        exit;
    }

    // Insert the booking into the database.
    $insertStmt = $pdo->prepare("
        INSERT INTO bookings (user_id, room_id, check_in, check_out, status)
        VALUES (?, ?, ?, ?, 'confirmed')
    ");

    $insertStmt->execute([
        $user_id,
        $room_id,
        $check_in,
        $check_out
    ]);

    // Get the new booking ID generated by the database.
    $booking_id = $pdo->lastInsertId();

    // Redirect user to confirmation page with booking_id.
    header('Location: ../booking-confirm.html?booking_id=' . $booking_id);
    exit;
}

/*
|--------------------------------------------------------------------------
| ACTION 3: Get booking confirmation data
|--------------------------------------------------------------------------
| This action is used by booking-confirm.html.
| It loads the booking details after successful booking.
*/
if ($action === 'confirmation') {

    // User must be logged in to view booking confirmation.
    if (!isset($_SESSION['user_id'])) {
        send_json([
            'success' => false,
            'redirect' => 'login.html'
        ]);
    }

    // Read booking_id from URL.
    $booking_id = $_GET['booking_id'] ?? null;

    // Current logged-in user.
    $user_id = $_SESSION['user_id'];

    // If booking_id is missing.
    if (!$booking_id) {
        send_json([
            'success' => false,
            'message' => 'Booking ID is missing.'
        ]);
    }

    // Get booking details from bookings, rooms, and hotels.
    // The condition bookings.user_id = ? protects user privacy.
    // It prevents one user from viewing another user's booking.
    $stmt = $pdo->prepare("
        SELECT 
            bookings.id,
            bookings.check_in,
            bookings.check_out,
            bookings.status,
            rooms.name AS room_name,
            rooms.price_per_night,
            rooms.image AS room_image,
            hotels.name AS hotel_name
        FROM bookings
        JOIN rooms ON bookings.room_id = rooms.id
        JOIN hotels ON rooms.hotel_id = hotels.id
        WHERE bookings.id = ?
        AND bookings.user_id = ?
    ");

    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch();

    // If booking does not exist or does not belong to this user.
    if (!$booking) {
        send_json([
            'success' => false,
            'message' => 'Booking was not found.'
        ]);
    }

    // Return booking data to confirmation page.
    send_json([
        'success' => true,
        'booking' => $booking
    ]);
}

// If action is not room/create/confirmation.
send_json([
    'success' => false,
    'message' => 'Invalid action.'
]);