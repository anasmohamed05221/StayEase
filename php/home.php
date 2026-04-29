<?php
require_once __DIR__ . '/config.php';

$stmt = $pdo->query("
    SELECT h.id, h.name, h.city, h.image, h.stars,
           MIN(r.price_per_night) AS min_price,
           ROUND(AVG(rv.rating), 1) AS avg_rating
    FROM hotels h
    LEFT JOIN rooms r ON r.hotel_id = h.id AND r.is_available = 1
    LEFT JOIN reviews rv ON rv.hotel_id = h.id
    GROUP BY h.id, h.name, h.city, h.image, h.stars
    ORDER BY avg_rating DESC, RAND()
    LIMIT 4
");

$hotels = $stmt->fetchAll();