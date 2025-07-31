<?php
require_once __DIR__ . '/config/init.php';

function dbConnect()
{
  $db_host = $_ENV['DB_HOST'];
  $db_username = $_ENV['DB_USER'];
  $db_userpass = $_ENV['DB_PASS'];
  $db_name = $_ENV['DB_NAME'];
  $db_port = $_ENV['DB_PORT'];

  $conn = new mysqli($db_host, $db_username, $db_userpass, $db_name, $db_port);

  // Check the connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  return $conn;
}
