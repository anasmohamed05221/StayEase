<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'logged_in' => false,
        'message' => 'Please login first.'
    ]);
    exit;
}

if ($action == 'get_room') {

    if (!isset($_GET['room_id'])) {
        echo json_encode([
            'logged_in' => true,
            'success' => false,
            'message' => 'Room ID is missing.'
        ]);
        exit;
    }

    $room_id = intval($_GET['room_id']);

    $stmt = $pdo->prepare("
        SELECT 
            rooms.id AS room_id,
            rooms.name AS room_name,
            rooms.type,
            rooms.price_per_night,
            rooms.description,
            rooms.image,
            rooms.is_available,
            hotels.name AS hotel_name,
            hotels.city
        FROM rooms
        JOIN hotels ON rooms.hotel_id = hotels.id
        WHERE rooms.id = ?
        LIMIT 1
    ");

    $stmt->execute([$room_id]);
    $room = $stmt->fetch();

    if (!$room) {
        echo json_encode([
            'logged_in' => true,
            'success' => false,
            'message' => 'Room not found.'
        ]);
        exit;
    }

    if ($room['is_available'] == 0) {
        echo json_encode([
            'logged_in' => true,
            'success' => false,
            'message' => 'This room is not available.'
        ]);
        exit;
    }

    echo json_encode([
        'logged_in' => true,
        'success' => true,
        'room' => $room
    ]);
    exit;
}

echo json_encode([
    'logged_in' => true,
    'success' => false,
    'message' => 'Invalid action.'
]);
exit;
?>