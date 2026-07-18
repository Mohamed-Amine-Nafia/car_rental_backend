<?php


require_once "cors.php";

require_once "auth.php";
require_once "db.php";





if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if($_SERVER['REQUEST_METHOD'] !== "DELETE"){
  exit("Invalid request method");
}

$data = json_decode(file_get_contents("php://input"),true);

$id = $data['id'] ?? '';



$stmt = $conn->prepare("
UPDATE cars
SET is_deleted = 1,
    deleted_at = NOW(),
    status = 'deleted'
WHERE car_id = ?
");


$stmt->bind_param("i",$id);
$stmt->execute();

echo json_encode([
  "success" => true,
  "message" => "The car was deleted successfully."
]);

$stmt->close();
$conn->close();


?>