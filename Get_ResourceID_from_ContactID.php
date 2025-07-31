<?php
/* Boilerplate Start ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
header('Content-Type: application/json');
include 'prepare_data_variables_for_php.php'; // now vars can be accessed like this: $contactID = $d['ContactID'] ?? null;
include 'db_con_w_auth_pdo.php';
$pdo = dbConnect('jalur', $contactID, $encrypKey);
/* Boilerplate End ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

$contactID = $d['ContactID'] ?? null;

if (!$contactID) {
    echo json_encode(['success' => false, 'message' => 'ContactID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT ResourceID FROM bp_resources WHERE ContactID = :ContactID');
    $stmt->bindParam(':ContactID', $contactID);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode(['success' => true, 'ResourceID' => $result['ResourceID']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ResourceID not found']);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database query failed']);
}
?>