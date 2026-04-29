<?php
require_once __DIR__ . '/config.php';

$city     = trim($_GET['city']      ?? '');
$checkIn  = trim($_GET['check_in']  ?? '');
$checkOut = trim($_GET['check_out'] ?? '');
$sort     = $_GET['sort']           ?? 'rating';
$maxPrice = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 1000;
$starsFilter = isset($_GET['stars']) ? array_map('intval', (array)$_GET['stars']) : [];
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 4;
$offset  = ($page - 1) * $perPage;

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

// Build stars filter clause only if specific stars are selected
$starsClause = '';
$params = [':city' => $city, ':cityLike' => '%' . $city . '%', ':maxPrice' => $maxPrice];

if (!empty($starsFilter)) {
    $placeholders = [];
    foreach ($starsFilter as $i => $s) {
        $key = ':stars' . $i;
        $placeholders[] = $key;
        $params[$key] = $s;
    }
    $starsClause = 'AND h.stars IN (' . implode(',', $placeholders) . ')';
}

// Count total results for pagination
$countStmt = $pdo->prepare("
    SELECT COUNT(*) FROM (
        SELECT h.id
        FROM hotels h
        LEFT JOIN rooms   r  ON r.hotel_id  = h.id AND r.is_available = 1
        LEFT JOIN reviews rv ON rv.hotel_id = h.id
        WHERE (:city = '' OR h.city LIKE :cityLike)
        $starsClause
        GROUP BY h.id
        HAVING MIN(r.price_per_night) IS NULL OR MIN(r.price_per_night) <= :maxPrice
    ) AS total
");
$countStmt->execute($params);
$totalPages = (int) ceil($countStmt->fetchColumn() / $perPage);

// Fetch current page results
$stmt = $pdo->prepare("
    SELECT h.id, h.name, h.city, h.location, h.description, h.image, h.stars,
           MIN(r.price_per_night)      AS min_price,
           ROUND(AVG(rv.rating), 1)    AS avg_rating,
           COUNT(rv.id)                AS review_count
    FROM hotels h
    LEFT JOIN rooms   r  ON r.hotel_id  = h.id AND r.is_available = 1
    LEFT JOIN reviews rv ON rv.hotel_id = h.id
    WHERE (:city = '' OR h.city LIKE :cityLike)
    $starsClause
    GROUP BY h.id, h.name, h.city, h.location, h.description, h.image, h.stars
    HAVING min_price IS NULL OR min_price <= :maxPrice
    ORDER BY $orderBy
    LIMIT $perPage OFFSET $offset
");

$stmt->execute($params);
$hotels = $stmt->fetchAll();