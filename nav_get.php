<?php
require_once __DIR__ . '/config/init.php';
/* Boilerplate Start ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
header('Content-Type: application/json');
include 'prepare_data_variables_for_php.php'; // now vars can be accessed like this: $contactID = $d['ContactID'] ?? null;
include 'db_con_w_auth_pdo.php';
$pdo = dbConnect('jalur', $contactID, $encrypKey);
/* Boilerplate End ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

try {
    $pdo->beginTransaction();

    $authorizedContactIds = array_filter(array_map('trim', explode(',', $_ENV['AUTHORIZED_CONTACT_IDS'] ?? '')));
    // if (in_array($contactID, $authorizedContactIds, true)) {
        // $stmt = $pdo->prepare("
            // SELECT NavLinkID, Section, SectionSort, SubSection, SubSectionSort, href, title, PermissionID, PermissionValue, SortOrder, 'N' AS Hidden
            // FROM t_navlinks
            // ORDER BY SectionSort, SubSectionSort, SortOrder
        // ");
    // } 
		if (in_array($contactID, $authorizedContactIds, true)) {
		// Admins get all authorized nav items, but still respect Hidden = 'N'
		$stmt = $pdo->prepare("
			SELECT NavLinkID, Section, SectionSort, SubSection, SubSectionSort, href, title, PermissionID, PermissionValue, SortOrder, Hidden
			FROM t_navlinks
			WHERE Hidden = 'N'
			ORDER BY SectionSort, SubSectionSort, SortOrder
		");
		} else {
        $stmt = $pdo->prepare("
            SELECT NavLinkID, Section, SectionSort, SubSection, SubSectionSort, href, title, PermissionID, PermissionValue, SortOrder, Hidden
            FROM t_navlinks
			WHERE Hidden = 'N'
            ORDER BY SectionSort, SubSectionSort, SortOrder
        ");
    }

    $stmt->execute();
    // error_log("Navigation links query executed.");

    $navLinks = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $permissionID = $row['PermissionID'];
        $permissionValue = $row['PermissionValue'];
        $status = '';

        // Call stored procedure for authorization
        $stmtAuth = $pdo->prepare("CALL sp_ua_authority_check(:contactID, :permissionID, :permissionValue, @status)");
        $stmtAuth->execute([
            ':contactID' => $contactID,
            ':permissionID' => $permissionID,
            ':permissionValue' => $permissionValue
        ]);

        // Get the authorization status
        $statusQuery = $pdo->query("SELECT @status as status");
        $statusRow = $statusQuery->fetch(PDO::FETCH_ASSOC);
        $status = $statusRow['status'];

        // Include the link if authorized
        if ($status === 'AUTHORIZED') {
            $navLinks[] = [
                'Section' => $row['Section'],
                'SectionSort' => $row['SectionSort'],
                'SubSection' => $row['SubSection'],
                'SubSectionSort' => $row['SubSectionSort'],
                'href' => $row['href'],
                'title' => $row['title'],
                'Hidden' => $row['Hidden']
            ];
        }
    }

    $pdo->commit();
    // error_log("Transaction committed.");

    if (!empty($navLinks)) {
        $response['success'] = true;
        $response['navLinks'] = $navLinks;
        $response['message'] = '';
    }

    echo json_encode($response);
    /* error_log("Response sent: " . json_encode($response)); */
} catch (Exception $e) {
    $pdo->rollBack();
    $response['message'] = $e->getMessage();
    echo json_encode($response);
    error_log("Error occurred: " . $e->getMessage());
}
