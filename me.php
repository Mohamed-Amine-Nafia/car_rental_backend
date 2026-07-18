<?php
require_once "session.php";

require_once "cors.php";

// Check if session exists
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "authenticated" => false
    ]);
    exit;
}

// Optional: fetch user from DB (recommended)
require "db.php";

$stmt = $conn->prepare("SELECT id, email, role, is_active FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['is_active'] == 0) {
    http_response_code(401);
    echo json_encode([
        "authenticated" => false
    ]);
    exit;
}

// Success response
echo json_encode([
    "authenticated" => true,
    "user" => $user
]);