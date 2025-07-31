<?php
/* Boilerplate Start */
header('Content-Type: application/json');
include 'prepare_data_variables_for_php.php';
include 'db_con_w_auth_pdo.php';
$pdo = dbConnect('jalur', $contactID, $encrypKey);
/* Boilerplate End */

$mode = $d['mode'] ?? '';

try {
	if ($mode === 'check_alerts') {
		$stmt = $pdo->prepare("SELECT * FROM alerts WHERE ContactID = :ContactID AND RecordStatus = 'U' ORDER BY CreatedOn ASC");
		$stmt->execute([':ContactID' => $contactID]);
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		echo json_encode(['success' => true, 'data' => $data]);
		exit;
	}

	if ($mode === 'mark_read') {
		$msgNo = $d['MsgNo'] ?? null;
		if (!$msgNo) {
			echo json_encode(['success' => false, 'message' => 'MsgNo missing']);
			exit;
		}
		$stmt = $pdo->prepare("UPDATE alerts SET RecordStatus = 'R' WHERE MsgNo = :MsgNo AND ContactID = :ContactID");
		$stmt->execute([':MsgNo' => $msgNo, ':ContactID' => $contactID]);
		echo json_encode(['success' => true]);
		exit;
	}

	echo json_encode(['success' => false, 'message' => 'Invalid mode']);
} catch (Exception $e) {
	error_log($e->getMessage());
	echo json_encode(['success' => false, 'message' => 'Error accessing alerts']);
}
