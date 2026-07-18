<?php
// 1. Silence warnings to keep JSON pure, force JSON header
error_reporting(0);
header("Content-Type: application/json; charset=utf-8");

// 2. Include Composer's autoloader and Dompdf classes
require_once "vendor/autoload.php"; 
use Dompdf\Dompdf;
use Dompdf\Options;

require_once "cors.php";
require_once "db.php";
require_once "./templates/contract.php";

$data = json_decode(file_get_contents("php://input"), true);
$rental_id = $data['rental_id'] ?? null;

if (!$rental_id) {
    echo json_encode(["status" => "error", "message" => "Rental ID is missing"]);
    exit;
}

/*
===================================================================
1. FETCH DATA FROM DATABASE
===================================================================
*/
$rental = $conn->query("SELECT * FROM rentals WHERE id = " . intval($rental_id))->fetch_assoc();
if (!$rental) {
    echo json_encode(["status" => "error", "message" => "Rental not found"]);
    exit;
}

$client = $conn->query("SELECT * FROM clients WHERE client_id = " . intval($rental['client_id']))->fetch_assoc() ?? [];
$car    = $conn->query("SELECT * FROM cars WHERE car_id = " . intval($rental['car_id']))->fetch_assoc() ?? [];

/*
===================================================================
2. CHECK IF CONTRACT ALREADY EXISTS (Avoid creating duplicates)
===================================================================
*/
$existing = $conn->query("SELECT file_path FROM contracts WHERE rental_id = " . intval($rental_id))->fetch_assoc();

if ($existing) {
    // If it already exists, just return the existing path to the frontend
    echo json_encode([
        "status" => "success",
        "message" => "Existing contract retrieved",
        "file_path" => $existing['file_path']
    ]);
    exit;
}

/*
===================================================================
3. GENERATE THE PDF USING DOMPDF
===================================================================
*/
// Set up clear contract identifier strings
$year = date('Y');
$padded_id = str_pad($rental_id, 5, '0', STR_PAD_LEFT);
$contract_number = "LOC-{$year}-{$padded_id}";

// Generate the beautiful HTML layout we made earlier
$html_content = renderContract($client, $car, $rental);

// Configure Dompdf
// ... inside create-contract.php around step 3 ...
$options = new Options();
$options->set('defaultFont', 'Helvetica');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // CRUCIAL: allows Dompdf to load Google Fonts

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html_content);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

/*
===================================================================
4. SAVE THE PHYSICAL FILE & LOG IN DATABASE
===================================================================
*/
// Define naming conventions and folder structures
$filename = "contract_" . $rental_id . "_" . time() . ".pdf";
$relative_path = "uploads/contracts/" . $filename;
$physical_path = __DIR__ . "/" . $relative_path;

// Save the raw PDF stream bytes directly to the disk folder
if (file_put_contents($physical_path, $dompdf->output())) {
    
    // Save record to the 'contracts' table
    $stmt = $conn->prepare("INSERT INTO contracts (rental_id, contract_number, file_path) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $rental_id, $contract_number, $relative_path);
    $stmt->execute();

    // Return the URL path to React
    echo json_encode([
        "status" => "success",
        "message" => "Contract generated and saved successfully",
        "file_path" => $relative_path
    ]);
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "Unable to write the file to the server. Please check the permissions of the uploads/contracts/ folder."
    ]);
}
exit;