<?php

require_once "db.php";
require_once "api-response.php";
require_once "cors.php";

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse('error', null, 'Method not allowed', 405);
}
$sql = "
SELECT *
FROM cars
WHERE is_deleted = 0
";
$query = $conn->query($sql);

if (!$query) {
    sendResponse('error', null, 'Database query failed', 500);
}

$cars = $query->fetch_all(MYSQLI_ASSOC);

sendResponse('success', $cars, 'Cars retrieved successfully');

$conn->close();
?>