<?php

require_once "cors.php";

require_once "auth.php";
require_once "db.php";
require_once "api-response.php";




if($conn->connect_error) {
  die($conn->connect_error);
}

if($_SERVER['REQUEST_METHOD'] !== 'GET') {
  sendResponse("error",null,"Invalid Request Method",405);
  exit;
}

$sql = "SELECT * FROM clients ORDER BY client_id DESC";

$result = $conn->query($sql);

if(!$result) {
  sendResponse("error",null,"Failed to fetch clients", 500);
  exit;
}

$data = $result->fetch_all(MYSQLI_ASSOC);

sendResponse("success", $data,"Clients fetched successfully",200);

$conn->close();


?>