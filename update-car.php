<?php

require_once "cors.php";
require_once "db.php";

header("Content-Type: application/json");

// allow PATCH or POST (React fetch can vary)
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'PATCH' && $method !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Method not allowed"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid JSON"
    ]);
    exit;
}

$id = $data['car_id'] ?? null;

if (!$id) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing car_id"
    ]);
    exit;
}

// sanitize inputs (important)
$brand = $data['brand'] ?? '';
$model = $data['model'] ?? '';
$year = $data['year'] ?? '';
$plate = $data['plate'] ?? '';
$price = $data['price'] ?? '';
$fuel = $data['fuel'] ?? '';
$transmission = $data['transmission'] ?? '';
$status = $data['status'] ?? '';

$stmt = $conn->prepare("
    UPDATE cars 
    SET brand = ?, 
        model = ?, 
        year = ?, 
        plate = ?, 
        price = ?, 
        fuel = ?, 
        transmission = ?, 
        status = ?
    WHERE car_id = ?
");

$stmt->bind_param(
    "ssssssssi",
    $brand,
    $model,
    $year,
    $plate,
    $price,
    $fuel,
    $transmission,
    $status,
    $id
);

$success = $stmt->execute();

if ($success) {
    echo json_encode([
        "status" => "success",
        "message" => "Car updated successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Error updating the car",
        "debug" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();