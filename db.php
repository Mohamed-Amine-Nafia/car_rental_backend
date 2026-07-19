<?php

$conn = new mysqli(
    "tokaido.proxy.rlwy.net",
    "root",
    "ZuBybrYUuViUahqOHnsYRqjnRaySVbtU",
    "railway",
    29303
);

$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]));
}