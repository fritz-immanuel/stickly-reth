<?php
/* Boilerplate Start ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
header('Content-Type: application/json');
include 'prepare_data_variables_for_php.php';
include 'db_con_w_auth_pdo.php';
$pdo = dbConnect('jalur', $contactID, $encrypKey);
/* Boilerplate End ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

$EventGroupID = $d['EventGroupID'] ?? null;

if (!$EventGroupID) {
    echo json_encode(['success' => false, 'message' => 'EventGroupID is required']);
    exit;
}

try {
    // Fetch Event Group Description
    $stmt = $pdo->prepare('SELECT EventGrpDesc FROM t_event_groups WHERE EventGroupID = :EventGroupID');
    $stmt->bindParam(':EventGroupID', $EventGroupID);
    $stmt->execute();
    $eventGroupDesc = $stmt->fetchColumn();

    // Fetch Events within this Event Group
    $stmt = $pdo->prepare('SELECT EventID, EventDesc, PlaceHolder FROM t_events WHERE EventGrpID = :EventGroupID');
    $stmt->bindParam(':EventGroupID', $EventGroupID);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => ['eventGroupDesc' => $EventGrpDesc, 'events' => $events]]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to fetch event group data']);
}
?>
