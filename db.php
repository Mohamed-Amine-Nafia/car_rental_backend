<?php

$conn = new mysqli(
    getenv("DB_HOST"),
    getenv("DB_USERNAME"),
    getenv("DB_PASSWORD"),
    getenv("DB_DATABASE"),
    (int) getenv("DB_PORT")
);

if ($conn->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]));
}

$conn->set_charset("utf8mb4");
