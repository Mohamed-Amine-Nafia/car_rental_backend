<?php


require_once "cors.php";
require_once "auth.php";
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}

// validate required fields
$brand  = trim($_POST["brand"] ?? "");
$model  = trim($_POST["model"] ?? "");
$year   = trim($_POST["year"] ?? "");
$plate  = trim($_POST["plate"] ?? "");
$price  = trim($_POST["price"] ?? "");
$status = trim($_POST["status"] ?? "");
$fuel = trim($_POST['fuel']) ?? '';
$transmission = trim($_POST['transmission']) ?? '';

if (!$brand || !$model || !$year || !$plate || !$price || !$status || !$fuel || !$transmission) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields"
    ]);
    exit;
}

// check image
if (!isset($_FILES["image"])) {
    echo json_encode([
        "success" => false,
        "message" => "Image is required"
    ]);
    exit;
}

$image = $_FILES["image"];

$allowedTypes = ["jpg", "jpeg", "png", "webp"];
$extension = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));

if (!in_array($extension, $allowedTypes)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid image type"
    ]);
    exit;
}

// safe unique filename
$newImageName = uniqid("car_", true) . "." . $extension;

// upload folder (IMPORTANT: inside your API project)
$uploadDir = "uploads/cars/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$uploadPath = $uploadDir . $newImageName;

// move file
if (!move_uploaded_file($image["tmp_name"], $uploadPath)) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to upload image"
    ]);
    exit;
}

// insert into DB
$stmt = $conn->prepare("
    INSERT INTO cars (brand, model, year, plate, price, image,status,fuel,transmission)
    VALUES (?, ?, ?, ?, ?, ?, ?,?,?)
");

$stmt->bind_param(
    "sssssssss",
    $brand,
    $model,
    $year,
    $plate,
    $price,
    $newImageName,
    $status,
    $fuel,
    $transmission
);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "The car was added successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Database insert failed"
    ]);
}

$stmt->close();
$conn->close();