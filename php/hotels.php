
<?php
// Very simple Member 3 PHP file: returns hotel details or room details as JSON.

header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/config.php';

$action = $_GET['action'] ?? '';

if ($action == 'hotel') {
    $hotel_id = $_GET['hotel_id'] ?? 1;

    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmt->execute([$hotel_id]);
    $hotel = $stmt->fetch();

    if (!$hotel) {
        echo json_encode(['success' => false, 'message' => 'Hotel not found']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE hotel_id = ? ORDER BY is_available DESC, price_per_night ASC");
    $stmt->execute([$hotel_id]);
    $rooms = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT ROUND(AVG(rating), 1) AS avg_rating, COUNT(*) AS review_count FROM reviews WHERE hotel_id = ?");
    $stmt->execute([$hotel_id]);
    $reviews = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'hotel' => $hotel,
        'rooms' => $rooms,
        'average_rating' => $reviews['avg_rating'] ?? 0,
        'review_count' => $reviews['review_count'] ?? 0
    ]);
    exit;
}

if ($action == 'room') {
    $room_id = $_GET['room_id'] ?? 1;

    $stmt = $pdo->prepare("SELECT rooms.*, hotels.name AS hotel_name, hotels.city, hotels.location, hotels.stars
                           FROM rooms
                           JOIN hotels ON rooms.hotel_id = hotels.id
                           WHERE rooms.id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();

    if (!$room) {
        echo json_encode(['success' => false, 'message' => 'Room not found']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT ROUND(AVG(rating), 1) AS avg_rating FROM reviews WHERE hotel_id = ?");
    $stmt->execute([$room['hotel_id']]);
    $review = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'room' => $room,
        'average_rating' => $review['avg_rating'] ?? 0
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
