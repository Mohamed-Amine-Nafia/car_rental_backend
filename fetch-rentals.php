<?php

require_once "cors.php";

require_once "auth.php";
require_once "db.php";
require_once "api-response.php";



if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


if($conn->connect_error) {
  echo $conn->connect_error;
  exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'GET') {
  sendResponse('error', null,'Method Not Allowed' , 405);
  exit;
}



$sql = "SELECT 
  id,
  car_id,
  client_id,
  DATE(start_date) AS start_date,
  DATE(end_date) AS end_date,
  total_price,
  status,
  created_at,
  pickup_at
FROM rentals ORDER BY created_at DESC;";

$result = $conn->query($sql);

$rentals = $result->fetch_all(MYSQLI_ASSOC);

sendResponse("success", $rentals,"Data fetched successfuly", 200);

$conn->close();