<?php
// theme.php
session_start();

// Retrieve ContactID from query parameters
$contactID = $_GET['contactID'] ?? 'default';

// Decide theme based on contactID
if ($contactID === 'CI001049') {
    // $userPreference = 'ptr';
    $userPreference = 'vupico-2';
} elseif ($contactID === 'CI001197' || $contactID === 'CI005985') {
    $userPreference = 'erpt';
} elseif ($contactID === 'CI008574') {
    $userPreference = 'capac';
} elseif ($contactID === 'CI006606') {
    $userPreference = 'vupico-2';
} else {
    $userPreference = 'jalur';
}

// Define themes and their corresponding colors
$themes = [
    'jalur' => [
        '--dark' => '#000000',
        '--primary' => 'rgb(255, 25, 25)',
        '--secondary' => '#ffffff',
        '--dark-light' => '#666666',
        '--primary-light' => 'rgba(255, 25, 25, 0.5)',
        '--secondary-light' => '#d3d3d3',
        '--skill-inactive-bg' => '#ff8282',
        '--skill-active-bg' => '#ff1919',
        '--skill-active-text' => '#ffffff',
    ],
    'ptr' => [
        '--dark' => '#000000',
        '--primary' => 'rgb(23, 55, 94)',
        '--secondary' => 'rgb(236, 151, 82)',
        '--dark-light' => '#666666',
        '--primary-light' => 'rgba(23, 55, 94, 0.5)',
        '--secondary-light' => '#d3d3d3',
        '--skill-inactive-bg' => '#d3dbe8',
        '--skill-active-bg' => '#17375e',
        '--skill-active-text' => '#ffffff',
    ],
    'erpt' => [
        '--dark' => '#000000',
        '--primary' => '#204D74',
        '--secondary' => '#ffffff',
        '--dark-light' => '#666666',
        '--primary-light' => '#B0CFE7',
        '--secondary-light' => 'rgba(87, 160, 211, 0.5)',
        '--skill-inactive-bg' => '#9db8d2',
        '--skill-active-bg' => '#204d74',
        '--skill-active-text' => '#ffffff',
    ],
    'capac' => [
        '--dark' => '#3D2B1F',
        '--primary' => '#B89B5F',
        '--secondary' => '#ffffff',
        '--dark-light' => '#7B5E42',
        '--primary-light' => 'rgba(184, 155, 95, 0.5)',
        '--secondary-light' => '#f2f2f2',
        '--skill-inactive-bg' => '#e2d1b0', // light cream
        '--skill-active-bg' => '#b89b5f',   // gold
        '--skill-active-text' => '#000000', // black text on gold
    ],
    'vupico' => [
        '--dark' => '#1A1A1A',
        '--primary' => '#22367A',
        '--primary-light' => '#6983D5',
        '--secondary' => '#32A6AA',
        '--secondary-light' => '#88DADC',
        '--dark-light' => '#444B66',
        '--skill-inactive-bg' => '#B8C3F2',
        '--skill-active-bg' => '#22367A',
        '--skill-active-text' => '#FFFFFF',
    ],
    'vupico-2' => [
        '--dark' => '#1C1C1C',
        '--primary' => '#22367A',
        '--primary-light' => '#E4EBF8',
        '--secondary' => '#FFFFFF',
        '--secondary-light' => '#D4F4F4',
        '--dark-light' => '#4A5580',
        '--skill-inactive-bg' => '#AFC6F2',
        '--skill-active-bg' => '#22367A',
        '--skill-active-text' => '#FFFFFF',
    ],
];


header('Content-Type: application/json');
echo json_encode(['colors' => $themes[$userPreference] ?? $themes['jalur']]);
