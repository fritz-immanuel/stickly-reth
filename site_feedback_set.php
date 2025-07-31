<?php
/* Boilerplate Start ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
header('Content-Type: application/json');
include 'prepare_data_variables_for_php.php'; // Now vars can be accessed like this: $contactID = $d['ContactID'] ?? null;
include 'db_con_w_auth_pdo.php';
$pdo = dbConnect('jalur', $contactID, $encrypKey);
/* Boilerplate End ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

    $pageFilename = $d['pageFilename'] ?? '';
    $comment = $d['comment'] ?? '';

    try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO site_feedback (ContactID, PageFilename, FeedbackComment) VALUES (:contactID, :pageFilename, :comment)");
        $stmt->bindParam(':contactID', $contactID);
        $stmt->bindParam(':pageFilename', $pageFilename);
        $stmt->bindParam(':comment', $comment);
        $stmt->execute();
        $pdo->commit();
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Feedback submission failed']);
    }

?>
