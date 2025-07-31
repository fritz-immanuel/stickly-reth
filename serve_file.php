<?php
session_start();

// Check if user is logged in
// if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // http_response_code(403); // Forbidden
    // echo "Access denied";
    // exit;
// }

// Get the requested file
$file = $_GET['file'] ?? '';
$baseDir = realpath($_SERVER['DOCUMENT_ROOT'] . '/../protected_files/logos/');
$filePath = $baseDir ? $baseDir . DIRECTORY_SEPARATOR . basename($file) : false;

// Validate the file path
if (!$baseDir || !$filePath || !file_exists($filePath)) {
    // http_response_code(404); // Not Found
    // echo "File not found.";
    exit;
}

// Optionally restrict allowed file types
$allowedExtensions = ['png', 'webp', 'jpg', 'jpeg', 'gif', 'pdf', 'docx', 'xlsx', 'txt'];
$extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
if (!in_array($extension, $allowedExtensions)) {
    http_response_code(403); // Forbidden
    echo "File type not allowed.";
    exit;
}

// Serve the file
header('Content-Type: ' . mime_content_type($filePath));
header('Content-Length: ' . filesize($filePath));
header('Content-Disposition: inline; filename="' . basename($filePath) . '"'); // Use 'attachment;' for downloads
readfile($filePath);
exit;
?>
