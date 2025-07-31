<?php
// // Display all errors (for debugging purposes - disable this in production)
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

require_once __DIR__ . '/config/init.php';

$db_host = $_ENV['DB_HOST'];
$db_username = $_ENV['DB_USER'];
$db_userpass = $_ENV['DB_PASS'];
$db_name = $_ENV['DB_NAME'];
$db_port = $_ENV['DB_PORT'];

$charset = 'utf8mb4';

// Establish a PDO connection
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
try {
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=$charset", $db_username, $db_userpass, $options);
} catch (\PDOException $e) {
    echo "Connection error: " . $e->getMessage();
    exit;
}
