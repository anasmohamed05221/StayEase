<?php
// hotels.php — Member 3 backend for hotel details and room details.
// Place this file inside: php/hotels.php

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/config.php';

function send_json(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function get_positive_int(string $key): int
{
    $value = filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);

    if ($value === false || $value === null) {
        send_json([
            'success' => false,
            'message' => "Missing or invalid {$key}."
        ], 400);
    }

    return (int) $value;
}

$action = $_GET['action'] ?? '';

try {
    if ($action === 'hotel') {
        $hotelId = get_positive_int('hotel_id');

        $hotelSql = "
            SELECT
                h.*,
                COALESCE((SELECT ROUND(AVG(r.rating), 1) FROM reviews r WHERE r.hotel_id = h.id), 0) AS average_rating,
                COALESCE((SELECT COUNT(*) FROM reviews r WHERE r.hotel_id = h.id), 0) AS review_count
            FROM hotels h
            WHERE h.id = ?
            LIMIT 1
        ";

        $hotelStmt = $pdo->prepare($hotelSql);
        $hotelStmt->execute([$hotelId]);
        $hotel = $hotelStmt->fetch();

        if (!$hotel) {
            send_json([
                'success' => false,
                'message' => 'Hotel not found.'
            ], 404);
        }

        $roomsSql = "
            SELECT id, hotel_id, name, type, price_per_night, description, image, is_available
            FROM rooms
            WHERE hotel_id = ?
            ORDER BY is_available DESC, price_per_night ASC, id ASC
        ";

        $roomsStmt = $pdo->prepare($roomsSql);
        $roomsStmt->execute([$hotelId]);
        $rooms = $roomsStmt->fetchAll();

        send_json([
            'success' => true,
            'hotel' => $hotel,
            'rooms' => $rooms
        ]);
    }

    if ($action === 'room') {
        $roomId = get_positive_int('room_id');

        $roomSql = "
            SELECT
                rooms.*,
                hotels.name AS hotel_name,
                hotels.city,
                hotels.location,
                hotels.stars,
                hotels.description AS hotel_description,
                hotels.image AS hotel_image
            FROM rooms
            INNER JOIN hotels ON rooms.hotel_id = hotels.id
            WHERE rooms.id = ?
            LIMIT 1
        ";

        $roomStmt = $pdo->prepare($roomSql);
        $roomStmt->execute([$roomId]);
        $room = $roomStmt->fetch();

        if (!$room) {
            send_json([
                'success' => false,
                'message' => 'Room not found.'
            ], 404);
        }

        send_json([
            'success' => true,
            'room' => $room
        ]);
    }

    send_json([
        'success' => false,
        'message' => 'Invalid action. Use action=hotel or action=room.'
    ], 400);
} catch (PDOException $e) {
    error_log('hotels.php database error: ' . $e->getMessage());

    send_json([
        'success' => false,
        'message' => 'Database error. Check php/config.php and the MySQL tables.'
    ], 500);
}
