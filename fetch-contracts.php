<?php
// 1. Set header early
header('Content-Type: application/json; charset=utf-8');

require_once "cors.php";
require_once 'db.php'; 

// 2. FORCE UTF8MB4 connection immediately
$conn->set_charset("utf8mb4");

// Adjust the query
$query = "SELECT 
            c.id, 
            c.rental_id, 
            c.created_at, 
            c.file_path, 
            cl.full_name AS client_name, 
            cl.license_number AS license 
          FROM contracts c
          JOIN rentals r ON c.rental_id = r.id
          JOIN clients cl ON r.client_id = cl.client_id
          ORDER BY c.created_at DESC";

$result = $conn->query($query);

$contracts = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $contracts[] = $row;
    }
    // 3. FORCE UNICODE preservation
    echo json_encode($contracts, JSON_UNESCAPED_UNICODE);
} else {
    // Return error with actual DB error for debugging
    echo json_encode(['error' => 'Unable to fetch contracts', 'details' => $conn->error], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>