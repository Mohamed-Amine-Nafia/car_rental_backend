<?php

$conn = new mysqli("localhost", "root", "", "car_rental");
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]));
}


if (!$conn->set_charset("utf8mb4")) {
    printf("Error loading character set utf8mb4: %s\n", $conn->error);
}