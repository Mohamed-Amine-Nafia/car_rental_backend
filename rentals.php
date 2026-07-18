<?php

require_once "db.php";


require_once "cors.php";

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

/**
 * Read JSON
 */
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
  http_response_code(400);
  echo json_encode(["success" => false, "message" => "Invalid JSON"]);
  exit;
}

/**
 * INPUTS
 */
$car_id     = intval($data["car_id"] ?? 0);
$start_date = $data["start_date"] ?? "";
$end_date   = $data["end_date"] ?? "";
$full_name  = trim($data["full_name"] ?? "");
$phone      = trim($data["phone"] ?? "");
$license    = trim($data["license"] ?? "");

/**
 * VALIDATION
 */
if (!$car_id || !$start_date || !$end_date || !$full_name || !$phone || !$license) {
  http_response_code(400);
  echo json_encode(["success" => false, "message" => "Missing required fields"]);
  exit;
}

/**
 * START TRANSACTION (important for consistency)
 */
$conn->begin_transaction();

try {

  /**
   * 1. CREATE A NEW CLIENT RECORD FOR THIS RESERVATION
   */
  $stmt = $conn->prepare("
    INSERT INTO clients (full_name, phone, license_number)
    VALUES (?, ?, ?)
  ");
  $stmt->bind_param("sss", $full_name, $phone, $license);
  $stmt->execute();
  $client_id = $stmt->insert_id;

  /**
   * 2. GET CAR PRICE
   */
  $stmt = $conn->prepare("SELECT price FROM cars WHERE car_id = ?");
  $stmt->bind_param("i", $car_id);
  $stmt->execute();
  $car = $stmt->get_result()->fetch_assoc();

  if (!$car) {
    throw new Exception("Car not found");
  }

  $price = $car["price"];

  /**
   * 3. VALIDATE DATE RANGE
   */
$start = new DateTime($start_date);
$end   = new DateTime($end_date);

/**
 * HARD VALIDATION (this is what you are missing)
 */
if ($start >= $end) {
  http_response_code(400);
  echo json_encode([
    "success" => false,
    "message" => "The start date must be earlier than the end date"
  ]);
  exit;
}

$days = $start->diff($end)->days;

  /**
   * 4. CHECK OVERLAP (FIXED - CLEAN VERSION)
   * Simple correct rule:
   * overlap exists if NOT (end < start OR start > end)
   */
  $stmt = $conn->prepare("
    SELECT id FROM rentals
    WHERE car_id = ?
    AND status IN ('PENDING','CONFIRMED')
    AND NOT (end_date < ? OR start_date > ?)
    LIMIT 1
  ");

  $stmt->bind_param(
    "iss",
    $car_id,
    $start_date,
    $end_date
  );

  $stmt->execute();
  $conflict = $stmt->get_result();

  if ($conflict->num_rows > 0) {
    http_response_code(409);
    echo json_encode([
      "success" => false,
      "message" => "This vehicle is already reserved for this period."
    ]);
    exit;
  }

  /**
   * 5. TOTAL PRICE
   */
  $total_price = $days * $price;

  /**
   * 6. INSERT RENTAL
   */
  $stmt = $conn->prepare("
    INSERT INTO rentals (car_id, client_id, start_date, end_date, status, total_price)
    VALUES (?, ?, ?, ?, 'PENDING', ?)
  ");

  $stmt->bind_param(
    "iissd",
    $car_id,
    $client_id,
    $start_date,
    $end_date,
    $total_price
  );

  $stmt->execute();


  

  $rental_id = $stmt->insert_id;

  $stmt = $conn->prepare("UPDATE cars SET status = 'reserved' WHERE car_id = ?");
  $stmt->bind_param("i", $car_id);
  $stmt->execute();

  $conn->commit();

  /**
   * SUCCESS
   */
  echo json_encode([
    "success" => true,
    "message" => "Reservation saved successfully. To confirm your reservation, our agency will contact you as soon as possible.",
    "client_id" => $client_id,
    "rental_id" => $rental_id,
    "total_price" => $total_price
  ]);

} catch (Exception $e) {
  $conn->rollback();

  http_response_code(500);
  echo json_encode([
    "success" => false,
    "message" => $e->getMessage()
  ]);
}

?>