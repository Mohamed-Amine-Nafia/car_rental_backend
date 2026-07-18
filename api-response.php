<?php

function sendResponse($status,$data = null,$message = null,$httpCode = 200) {
  http_response_code($httpCode);


require_once "cors.php";

  echo json_encode([
    "status" => $status,
    "message" => $message,
    "data" => $data
  ]);
  exit;
}



?>