<?php
require_once 'db.php';

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT file_path FROM contracts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $filePath = $row['file_path'];
    
    if (file_exists($filePath)) {
        // Clear headers to ensure a clean download
        if (ob_get_level()) ob_end_clean();
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        // 'attachment' forces the browser to download instead of opening in a tab
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        
        readfile($filePath);
        exit;
    }
}
echo "Error: File not found.";
$conn->close();