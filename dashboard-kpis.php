<?php

require_once "db.php";
require_once "cors.php";

header('Content-Type: application/json');

$today = date('Y-m-d');

/*
-------------------------------------------------
1. CARS RETURNING TODAY
-------------------------------------------------
*/
$returningToday = $conn->query("
    SELECT COUNT(*) AS total
    FROM rentals
    WHERE status IN ('confirmed', 'active')
      AND end_date = CURDATE()
")->fetch_assoc()['total'];

/*
-------------------------------------------------
2. ACTIVE RENTALS
-------------------------------------------------
*/
$active = $conn->query("
    SELECT COUNT(*) AS total
    FROM rentals
    WHERE status = 'active'
")->fetch_assoc()['total'];

/*
-------------------------------------------------
3. OVERDUE RENTALS
-------------------------------------------------
*/
$overdue = $conn->query("
    SELECT COUNT(*) AS total
    FROM rentals
    WHERE status IN ('confirmed','active')
      AND end_date < CURDATE()
")->fetch_assoc()['total'];

echo json_encode([
    "status" => "success",
    "data" => [
        "returning_today" => $returningToday,
        "active_rentals" => $active,
        "overdue_rentals" => $overdue
    ]
]);

$conn->close();