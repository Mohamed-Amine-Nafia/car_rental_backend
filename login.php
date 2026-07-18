<?php

require_once "cors.php";

require_once "session.php";

require_once "db.php"; 


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

$email = strtolower(trim($data['email'] ?? ''));
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(["message" => "Email and password are required."]);
    exit;
}

// Secure query (prevent SQL injection)
$stmt = $conn->prepare("SELECT id, email, password, role, is_active FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Generic auth failure (important for security)
if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(["message" => "Email or password is incorrect."]);
    exit;
}

// Check account status
if ((int)$user['is_active'] === 0) {
    http_response_code(403);
    echo json_encode(["message" => "This account has been deactivated."]);
    exit;
}

// Secure session
session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];

// Response
echo json_encode([
    "message" => "Login successful",
    "user" => [
        "id" => $user['id'],
        "email" => $user['email'],
        "role" => $user['role']
    ]
]);