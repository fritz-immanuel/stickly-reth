<?php
require_once __DIR__ . '/config/init.php';

function dbConnect($schema)
{
    if ($schema === 'pmo') {
        $db_host = $_ENV['DB_PMO_HOST'];
        $db_username = $_ENV['DB_PMO_USER'];
        $db_userpass = $_ENV['DB_PMO_PASS'];
        $db_name = $_ENV['DB_PMO_NAME'];
        $db_port = $_ENV['DB_PMO_PORT'];
    } else if ($schema === 'jalur') {
        $db_host = $_ENV['DB_HOST'];
        $db_username = $_ENV['DB_USER'];
        $db_userpass = $_ENV['DB_PASS'];
        $db_name = $_ENV['DB_NAME'];
        $db_port = $_ENV['DB_PORT'];
    }

    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $db_username, $db_userpass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("PDO connection failed: " . $e->getMessage());
    }
}

