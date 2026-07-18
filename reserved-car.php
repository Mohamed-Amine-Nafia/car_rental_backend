<?php


require_once "cors.php";
require_once "auth.php";
require_once "session.php";
require_once "db.php";



if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
  exit("Invalid Request Method");
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
  echo json_encode(["message" => "Invalid JSON"]);
  exit;
}

$id = $data['car_id'] ?? '';
$status = $data["status"] ?? "";


$stmt = $conn->prepare("
  UPDATE cars 
  SET status = ?
  WHERE car_id = ?
");

$stmt->bind_param(
  "si",
  $status,
  $id
);

$success = $stmt->execute();

echo json_encode([
  "status" => $success ? "success" : "error",
  "message" => $success 
    ? "Car updated successfully"
    : "Failed to update the car"
]);

$stmt->close();
$conn->close();

?>