<?php

require_once "cors.php";
require_once "db.php";

$sql = "
SELECT 
    c.car_id,
    c.brand,
    c.model,
    c.year,
    c.plate,
    c.price,
    c.image,
    c.fuel,
    c.transmission,
    c.status AS availability_status
FROM cars c
WHERE LOWER(TRIM(COALESCE(c.status, ''))) IN ('available', 'disponible', '')
";

$result = $conn->query($sql);

$cars = [];

while ($row = $result->fetch_assoc()) {
    $cars[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $cars
]);

$conn->close();
?>