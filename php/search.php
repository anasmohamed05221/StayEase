<?php
require_once __DIR__ . '/config.php';

$city     = trim($_GET['city']     ?? '');
$checkIn  = trim($_GET['check_in'] ?? '');
$checkOut = trim($_GET['check_out'] ?? '');
$sort     = $_GET['sort'] ?? 'rating';

$nights = 1;
if ($checkIn && $checkOut) {
    $d1 = new DateTime($checkIn);
    $d2 = new DateTime($checkOut);
    $nights = max(1, $d2->diff($d1)->days);
}

$orderBy = match($sort) {
    'price_asc'  => 'min_price ASC',
    'price_desc' => 'min_price DESC',
    default      => 'avg_rating DESC',
};

$stmt = $pdo->prepare("
    SELECT h.id, h.name, h.city, h.location, h.description, h.image, h.stars,
           MIN(r.price_per_night)      AS min_price,
           ROUND(AVG(rv.rating), 1)    AS avg_rating,
           COUNT(rv.id)                AS review_count
    FROM hotels h
    LEFT JOIN rooms   r  ON r.hotel_id  = h.id AND r.is_available = 1
    LEFT JOIN reviews rv ON rv.hotel_id = h.id
    WHERE (:city = '' OR h.city LIKE :cityLike)
    GROUP BY h.id, h.name, h.city, h.location, h.description, h.image, h.stars
    ORDER BY $orderBy
");
$stmt->execute([':city' => $city, ':cityLike' => '%' . $city . '%']);
$hotels = $stmt->fetchAll();