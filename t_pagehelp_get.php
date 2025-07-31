<?php
/* Boilerplate Start ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
header('Content-Type: application/json');
include 'prepare_data_variables_for_php.php'; // Access vars like $contactID = $d['ContactID'] ?? null;
include 'db_con_w_auth_pdo.php';
$pdo = dbConnect('jalur', $contactID, $encrypKey);
/* Boilerplate End ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

try {
    $pageFilename = $d['pageFilename'] ?? null;

    if (!$pageFilename) {
        throw new Exception('Page filename is required.');
    }

    $stmt = $pdo->prepare("
        SELECT HelpContent
        FROM t_screen_help
        WHERE PageFilename = :pageFilename AND RecordStatus = 'A'
        LIMIT 1
    ");
    $stmt->bindParam(':pageFilename', $pageFilename);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode(['success' => true, 'data' => $result]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No help content found for this page.']);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => true, 'message' => 'Failed to retrieve help content.']);
}
