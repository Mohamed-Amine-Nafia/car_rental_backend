<?php

require "session.php";

header("Content-Type: application/json");

echo json_encode([
    "session_id" => session_id(),
    "session" => $_SESSION
]);