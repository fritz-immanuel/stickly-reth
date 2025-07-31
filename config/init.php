<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Detect current domain
$host = $_SERVER['HTTP_HOST'];

// Determine which .env file to load
$envFile = match (true) {
  str_contains($host, 'jalur.co') => '.env.jalur.co',
  str_contains($host, 'procuretoreport.com') => '.env.procuretoreport.com',
  default => '.env', // fallback
};

// Load the chosen .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, $envFile);
$dotenv->load();

// echo "Loaded env file: $envFile<br>";
// echo "VERIF_LINK: " . ($_ENV['VERIF_LINK'] ?? 'NOT FOUND') . "<br>";
// var_dump($_ENV);
// exit;

// auto migrate
// if (filter_var($_ENV['ENABLE_AUTO_MIGRATION'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
//   require_once __DIR__ . '/../migrate.php';
// }
