<?php

header("Content-Type: application/json");

require_once "session.php";
require_once "cors.php";
require_once "db.php";

$userId = $_SESSION['user_id'];

if($_SERVER['REQUEST_METHOD'] !== "PATCH") {

echo json_encode([
    "success" => false,
    "message" => "Invalid request method."
]);
  exit;
}

$data = json_decode(file_get_contents("php://input"),true);


if (!$data) {
    echo json_encode([
        "success" => false,
        "message" => "JSON invalide."
    ]);
    exit;
}


$oldPass = htmlspecialchars(trim($data['oldPass']));
$newPass = htmlspecialchars(trim($data['newPass']));



if(empty($oldPass) || empty($newPass)) {
  echo json_encode([
    "success" => false,
    "message" => "All fields are required."
  ]);
  exit;
}

$stmt = $conn->prepare("SELECT password,role FROM users WHERE id = ?");

$stmt->bind_param("s",$userId);
$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();



if($user['role'] === "demo") {
  echo json_encode([
    "success" => false,
    "message" => "It is not allowed to change the password for a demo account."
  ]);
  exit;
}

if(!password_verify($oldPass,$user['password'])) {
    echo json_encode([
    "success" => false,
    "message" => "The old password is incorrect."
  ]);
  exit;
} 

$newHashedPass = password_hash($newPass, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("ss",$newHashedPass,$userId);



if($stmt->execute()) {
  echo json_encode([
    "success" => true,
    "message" => "The password was changed successfully."
  ]);
} else {
  echo json_encode([
    "success" => false,
    "message" => "An error occurred while changing the password."
  ]);
}

$stmt->close();
$conn->close();




?>