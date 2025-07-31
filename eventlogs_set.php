<?php
/* Boilerplate Start ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
header('Content-Type: application/json');
include 'prepare_data_variables_for_php.php';
include 'db_con_w_auth_pdo.php';
$pdo = dbConnect('jalur', $contactID, $encrypKey);
/* Boilerplate End ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

$ObjectType = $d['ObjectType'] ?? null;
$ObjectID = $d['ObjectID'] ?? null;
$EventID = $d['EventID'] ?? null;
$EventNote = $d['EventNote'] ?? null;
$RecordStatus = $d['RecordStatus'] ?? 'A';
$AssignedTo = $d['AssignedTo'] ?? null;
$PlannedExecutionDate = $d['PlannedExecutionDate'] ?? null;
$CreatedBy = $contactID;

if (!$ObjectType || !$ObjectID || !$EventID) {
    echo json_encode(['success' => false, 'message' => 'Required parameters missing']);
    exit;
}

try {
    $stmt = $pdo->prepare('CALL sp_event_and_subsequent_event_creation(
        :ObjectType,
        :ObjectID,
        :EventID,
        :EventNote,
        :RecordStatus,
        :AssignedTo,
        :PlannedExecutionDate,
        :CreatedBy
    )');

    $stmt->bindParam(':ObjectType', $ObjectType);
    $stmt->bindParam(':ObjectID', $ObjectID);
    $stmt->bindParam(':EventID', $EventID);
    $stmt->bindParam(':EventNote', $EventNote);
    $stmt->bindParam(':RecordStatus', $RecordStatus);
    $stmt->bindParam(':AssignedTo', $AssignedTo);
    $stmt->bindParam(':PlannedExecutionDate', $PlannedExecutionDate);
    $stmt->bindParam(':CreatedBy', $CreatedBy);

    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Event and subsequent events logged successfully']);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => true, 'message' => 'Transaction failed']);
}
