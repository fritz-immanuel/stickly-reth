<?php
// prepare_data_variables_for_php.php

require_once __DIR__ . '/config/init.php';

$environment = $_ENV['ENVIRONMENT'];
if ($environment !== 'PRODUCTION') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
//=============Debugging End
//====================================================
function getRequestData()
{
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

    // Handle JSON data (application/json)
    if (strpos($contentType, 'application/json') !== false) {
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            return $decoded; // Return decoded JSON as an associative array
        }
    }

    // Handle multipart form data (used for file uploads)
    if (strpos($contentType, 'multipart/form-data') !== false) {
        return $_POST; // Return $_POST which contains the form data (including text fields)
    }

    // Default to $_POST if no content type matches
    return $_POST;
}
//====================================================

$d = getRequestData(); // This now works for both JSON and multipart/form-data
$contactID = $d['ContactID'] ?? null;
$encrypKey = $d['EncrypKey'] ?? null;

//=============Debugging Start
function getMainFileName()
{
    $backtrace = debug_backtrace();
    // The main file is the last element in the backtrace array
    $mainFile = end($backtrace);
    return isset($mainFile['file']) ? basename($mainFile['file']) : 'unknown';
}

if ($environment !== 'PRODUCTION') {
    $callingphp = getMainFileName();
    $debugindent = ">>>";
    $message = $debugindent . "\n";
    $message .= $debugindent . "======= START: " . $callingphp . " => " . basename(__FILE__) . " ============================" . "\n";
    foreach ($d as $key => $value) {
        if ((str_contains($key, 'password')) || (str_contains($key, 'Password')) || (str_contains($key, 'pass'))) {
            $value = '********'; // Mask the password for security
        }
        $message .= $debugindent . " Received $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
    }
    $message .= $debugindent . "======= END: " . $callingphp . " => " . basename(__FILE__) . " ============================" . "\n";
    $message .= $debugindent . "\n";
    error_log($message);
}
//=============Debugging End
