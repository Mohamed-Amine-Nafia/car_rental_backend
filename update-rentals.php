<?php

require_once "cors.php";
require_once "auth.php";
require_once "session.php";
require_once "db.php";



if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
  exit("Invalid Request Method");
}
error_log($_SERVER['REQUEST_METHOD']);

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
  echo json_encode(["message" => "Invalid JSON"]);
  exit;
}

$id = intval($data['id'] ?? 0);
$newStatus = trim((string)($data['status'] ?? ''));

$stmt = $conn->prepare("SELECT status, car_id FROM rentals WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$oldRental = $stmt->get_result()->fetch_assoc();

$oldStatus = trim((string)($oldRental['status'] ?? ''));
$carId = intval($oldRental['car_id'] ?? 0);

$stmt = $conn->prepare("
  UPDATE rentals
  SET status = ?
  WHERE id = ?
");

$stmt->bind_param("si", $newStatus, $id);
$success = $stmt->execute();

if ($success && $carId) {
  $newStatusUpper = strtoupper($newStatus);

  if (in_array($newStatusUpper, ['CONFIRMED', 'ACTIVE'], true)) {
    $stmt = $conn->prepare("UPDATE cars SET status = 'reserved' WHERE car_id = ?");
    $stmt->bind_param("i", $carId);
    $stmt->execute();
  } else {
    $stmt = $conn->prepare("SELECT 1 FROM rentals WHERE car_id = ? AND status IN ('CONFIRMED','ACTIVE') AND id != ? LIMIT 1");
    $stmt->bind_param("ii", $carId, $id);
    $stmt->execute();
    $hasOtherActiveRental = $stmt->get_result()->num_rows > 0;

    if (!$hasOtherActiveRental) {
      $stmt = $conn->prepare("UPDATE cars SET status = 'available' WHERE car_id = ?");
      $stmt->bind_param("i", $carId);
      $stmt->execute();
    }
  }

  if ($newStatusUpper === 'ACTIVE' && strtoupper($oldStatus) !== 'ACTIVE') {
    $stmt = $conn->prepare("
        UPDATE rentals
        SET pickup_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
  }
}


echo json_encode([
  "status" => $success ? "success" : "error",
  "message" => $success 
    ? "Reservation updated successfully"
    : "Failed to update the reservation"
]);

$stmt->close();
$conn->close();