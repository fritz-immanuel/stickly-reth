<?php
session_start();
$contactID = $_GET['contactID'] ?? 'default';

// Decide which logo to use based on contactID
if ($contactID === 'CI001049') {
    // $userPreference = 'ptr';
    $userPreference = 'vupico';
} elseif ($contactID === 'CI001197' || $contactID === 'CI005985') {
    $userPreference = 'erpt';
} elseif ($contactID === 'CI008574') {
    $userPreference = 'capac';
} elseif ($contactID === 'CI006606') {
    $userPreference = 'vupico';
} else {
    $userPreference = 'jalur';
}

$logoFiles = [
    'jalur' => '../protected_files/logos/logo_jalur.png',
    'ptr'   => '../protected_files/logos/logo_ptr.png',
    'erpt'  => '../protected_files/logos/logo_erpt.png',
    'capac'  => '../protected_files/logos/logo_capac.png',
    'vupico'  => '../protected_files/logos/logo_vupico.png',
];

$filePath = $logoFiles[$userPreference] ?? $logoFiles['jalur'];

if (file_exists($filePath)) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);

    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
} else {
    header("HTTP/1.0 404 Not Found");
    echo "Logo not found.";
    exit;
}
