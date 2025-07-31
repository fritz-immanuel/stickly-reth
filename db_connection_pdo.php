<?php
// db_connection_pdo.php
require_once __DIR__ . '/config/init.php';

$db_host = $_ENV['DB_HOST'];
$db_username = $_ENV['DB_USER'];
$db_userpass = $_ENV['DB_PASS'];
$db_name = $_ENV['DB_NAME'];
$db_port = $_ENV['DB_PORT'];

// Create the PDO connection
try {
    $conn = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name", $db_username, $db_userpass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Send a JSON error response and log the issue
    error_log("Database connection failed: " . $e->getMessage()); // Log the error
    echo json_encode(['error' => 'Database connection failed. Please try again later.']);
    exit;
}
