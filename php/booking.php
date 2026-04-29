<?php
session_start();

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

function sendResponse($data) {
    echo json_encode($data);
    exit;
}

function cleanStatus($status) {
    $allowed = ['pending', 'confirmed', 'cancelled'];

    if (in_array($status, $allowed)) {
        return $status;
    }

    return 'pending';
}

if (!isset($_SESSION['user_id'])) {
    sendResponse([
        'logged_in' => false,
        'message' => 'User is not logged in.'
    ]);
}

$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';

$action = isset($_GET['action']) ? $_GET['action'] : '';

/*
|--------------------------------------------------------------------------
| Fetch Dashboard Data
|--------------------------------------------------------------------------
*/
if ($action === 'dashboard') {

    try {
        $today = date('Y-m-d');

        // Total bookings
        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM bookings WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $totalBookings = $stmt->fetch()['total'];

        // Upcoming bookings
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM bookings
            WHERE user_id = ?
            AND check_in > ?
            AND status != 'cancelled'
        ");
        $stmt->execute([$user_id, $today]);
        $upcomingBookings = $stmt->fetch()['total'];

        // Past stays
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM bookings
            WHERE user_id = ?
            AND check_out < ?
            AND status != 'cancelled'
        ");
        $stmt->execute([$user_id, $today]);
        $pastStays = $stmt->fetch()['total'];

        // Recent bookings
        $stmt = $pdo->prepare("
            SELECT 
                bookings.*,
                rooms.name AS room_name,
                hotels.name AS hotel_name,
                rooms.price_per_night,
                DATEDIFF(bookings.check_out, bookings.check_in) * rooms.price_per_night AS calculated_total_price
            FROM bookings
            JOIN rooms ON bookings.room_id = rooms.id
            JOIN hotels ON rooms.hotel_id = hotels.id
            WHERE bookings.user_id = ?
            ORDER BY bookings.created_at DESC
            LIMIT 3
        ");

        $stmt->execute([$user_id]);
        $recentBookings = $stmt->fetchAll();

        foreach ($recentBookings as &$booking) {
            $booking['status'] = cleanStatus($booking['status']);
        }

        sendResponse([
            'logged_in' => true,
            'user_name' => htmlspecialchars($user_name),
            'stats' => [
                'total_bookings' => $totalBookings,
                'upcoming_bookings' => $upcomingBookings,
                'past_stays' => $pastStays
            ],
            'recent_bookings' => $recentBookings
        ]);

    } catch (PDOException $e) {
        sendResponse([
            'logged_in' => true,
            'success' => false,
            'message' => 'Database error while loading dashboard.'
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| Fetch All Bookings
|--------------------------------------------------------------------------
*/
if ($action === 'bookings') {

    try {
        $today = date('Y-m-d');

        $stmt = $pdo->prepare("
            SELECT 
                bookings.*,
                rooms.name AS room_name,
                hotels.name AS hotel_name,
                rooms.price_per_night,
                DATEDIFF(bookings.check_out, bookings.check_in) * rooms.price_per_night AS calculated_total_price
            FROM bookings
            JOIN rooms ON bookings.room_id = rooms.id
            JOIN hotels ON rooms.hotel_id = hotels.id
            WHERE bookings.user_id = ?
            ORDER BY bookings.created_at DESC
        ");

        $stmt->execute([$user_id]);
        $bookings = $stmt->fetchAll();

        foreach ($bookings as &$booking) {
            $booking['status'] = cleanStatus($booking['status']);

            $totalPrice = $booking['calculated_total_price'];

            if (isset($booking['total_price']) && $booking['total_price'] !== null) {
                $totalPrice = $booking['total_price'];
            }

            $booking['total_price'] = number_format((float)$totalPrice, 2);

            $booking['can_cancel'] =
                $booking['check_in'] > $today &&
                $booking['status'] !== 'cancelled';
        }

        sendResponse([
            'logged_in' => true,
            'bookings' => $bookings
        ]);

    } catch (PDOException $e) {
        sendResponse([
            'logged_in' => true,
            'success' => false,
            'message' => 'Database error while loading bookings.'
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| Cancel Booking
|--------------------------------------------------------------------------
*/
if ($action === 'cancel') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse([
            'logged_in' => true,
            'success' => false,
            'message' => 'Invalid request method.'
        ]);
    }

    if (!isset($_POST['booking_id']) || empty($_POST['booking_id'])) {
        sendResponse([
            'logged_in' => true,
            'success' => false,
            'message' => 'Booking ID is missing.'
        ]);
    }

    $booking_id = intval($_POST['booking_id']);
    $today = date('Y-m-d');

    try {
        // Check if booking belongs to this user and is future booking
        $stmt = $pdo->prepare("
            SELECT id, check_in, status
            FROM bookings
            WHERE id = ?
            AND user_id = ?
            LIMIT 1
        ");

        $stmt->execute([$booking_id, $user_id]);
        $booking = $stmt->fetch();

        if (!$booking) {
            sendResponse([
                'logged_in' => true,
                'success' => false,
                'message' => 'Booking not found.'
            ]);
        }

        if ($booking['status'] === 'cancelled') {
            sendResponse([
                'logged_in' => true,
                'success' => false,
                'message' => 'This booking is already cancelled.'
            ]);
        }

        if ($booking['check_in'] <= $today) {
            sendResponse([
                'logged_in' => true,
                'success' => false,
                'message' => 'You can only cancel bookings before the check-in date.'
            ]);
        }

        // Cancel booking
        $stmt = $pdo->prepare("
            UPDATE bookings
            SET status = 'cancelled'
            WHERE id = ?
            AND user_id = ?
        ");

        $stmt->execute([$booking_id, $user_id]);

        sendResponse([
            'logged_in' => true,
            'success' => true,
            'message' => 'Booking cancelled successfully.'
        ]);

    } catch (PDOException $e) {
        sendResponse([
            'logged_in' => true,
            'success' => false,
            'message' => 'Database error while cancelling booking.'
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| Invalid Action
|--------------------------------------------------------------------------
*/
sendResponse([
    'logged_in' => true,
    'success' => false,
    'message' => 'Invalid action.'
]);