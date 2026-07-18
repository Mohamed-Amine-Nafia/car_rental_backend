<?php

require_once "db.php";

if (php_sapi_name() !== 'cli' && (!isset($_GET['key']) || $_GET['key'] !== 'SECRET123')) {
    http_response_code(403);
    exit("Forbidden");
}

$logFile = __DIR__ . "/cron-log.txt";

file_put_contents($logFile, date("Y-m-d H:i:s") . " - Cron started\n", FILE_APPEND);

/*
-------------------------------------------------
1. EXPIRE ONLY VALID RENTALS
-------------------------------------------------
*/
$stmt = $conn->prepare("
    UPDATE rentals
    SET status = 'expired'
    WHERE status IN ('confirmed', 'active')
      AND end_date < CURDATE()
");

$stmt->execute();
$stmt->close();

/*
-------------------------------------------------
2. FIX CAR STATUS SAFELY
-------------------------------------------------
IMPORTANT:
Only set to disponible if there is truly NO active/confirmed rental left.
*/
$conn->query("
    UPDATE cars c
    SET c.status = 'available'
    WHERE c.status = 'reserved'
    AND NOT EXISTS (
        SELECT 1
        FROM rentals r
        WHERE r.car_id = c.car_id
          AND r.status IN ('confirmed', 'active')
    )
");

/*
-------------------------------------------------
3. (OPTIONAL BUT IMPORTANT SAFETY FIX)
Ensure cars with active rentals are ALWAYS marked reserved
-------------------------------------------------
*/
$conn->query("
    UPDATE cars c
    SET c.status = 'reserved'
    WHERE EXISTS (
        SELECT 1
        FROM rentals r
        WHERE r.car_id = c.car_id
          AND r.status IN ('confirmed', 'active')
    )
");

file_put_contents($logFile, date("Y-m-d H:i:s") . " - Cron finished\n", FILE_APPEND);

$conn->close();