<?php
// db_con_w_auth_pdo.php

require_once __DIR__ . '/config/init.php';

session_start();

function jsonResponseAndExit($message, $redirect)
{
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => $message, 'redirect' => $redirect]);
    exit();
}

function CheckSystemAuthorization($contactID, $encrypKey, $db_host, $name, $pass)
{
    $db_host = $_ENV['DB_HOST'];
    $db_username = $_ENV['DB_USER'];
    $db_userpass = $_ENV['DB_PASS'];
    $db_name = $_ENV['DB_NAME'];
    $db_port = $_ENV['DB_PORT'];

    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8";
    $authPdo = new PDO($dsn, $db_username, $db_userpass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $stmt = $authPdo->prepare("SELECT COUNT(ContactID) AS AuthCount FROM ua1_users WHERE ContactID = ? AND EncryptionKey = UNHEX(?)");
    $stmt->execute([$contactID, $encrypKey]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row['AuthCount'] != 1) {
        error_log("Unauthorized access attempt by contact '$contactID'.");
        jsonResponseAndExit("Unauthorized: System access denied.", "unauthorized_access.html");
    }
    $stmt->closeCursor();
    $authPdo = null;
}

function CheckUserPageAuthority($contactID, $db_host, $name, $pass)
{
    $db_host = $_ENV['DB_HOST'];
    $db_username = $_ENV['DB_USER'];
    $db_userpass = $_ENV['DB_PASS'];
    $db_name = $_ENV['DB_NAME'];
    $db_port = $_ENV['DB_PORT'];

    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8";
    $pdo = new PDO($dsn, $db_username, $db_userpass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $referrer = $_SERVER['HTTP_REFERER'] ?? '';
    if (empty($referrer)) {
        jsonResponseAndExit("Unauthorized: Page Access Denied.", "unauthorized_access.html");
    }
    $parsedUrl = parse_url($referrer);
    $referringPage = basename($parsedUrl['path'] ?? '');
    if (empty($referringPage)) {
        jsonResponseAndExit("Unauthorized: Invalid referrer detected.", "unauthorized_access.html");
    }

    $sql = "SELECT 1 FROM ua_user_authorized_hrefs WHERE ContactID = :ContactID
            AND LOWER(href) = LOWER(:href) LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':ContactID', $contactID, PDO::PARAM_STR);
    $stmt->bindParam(':href', $referringPage, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        $authorizedContactIds = array_filter(array_map('trim', explode(',', $_ENV['AUTHORIZED_CONTACT_IDS'] ?? '')));
        if (!in_array($contactID, $authorizedContactIds, true)) {
            error_log("Unauthorized access attempt to '$referringPage' by contact '$contactID'.");
            jsonResponseAndExit("Unauthorized Access: You do not have permission to view the '" . $referringPage . "' page.", "unauthorized_access.html");
        }
    }

    $stmt->closeCursor();
    $pdo = null;
}

function logRequest($conn, $contactID)
{
    $script_name = basename($_SERVER['SCRIPT_NAME']);
    $page_url = $_SERVER['HTTP_REFERER'] ?? 'Unknown';

    // List of specific scripts to exclude from logging
    $excluded_scripts = [
        'Get_ResourceID_from_ContactID.php',
        'eventlogs_set.php'
    ];

    // Skip logging if the script name is in the exclusion list OR contains '_get'
    if (stripos($script_name, '_get') !== false || in_array($script_name, $excluded_scripts)) {
        return;
    }
    //Dont log T
    $authorizedContactIds = array_filter(array_map('trim', explode(',', $_ENV['AUTHORIZED_CONTACT_IDS'] ?? '')));
    if (in_array($contactID, $authorizedContactIds, true)) {
        return;
    }
    // Collect and format the variables in JSON format
    $variables = [];
    foreach ($_POST as $key => $value) {
        if ($key !== 'ContactID') { // Exclude ContactID since it's separately logged
            $trimmed_value = is_string($value) && strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value;
            $variables[$key] = $trimmed_value;
        }
    }

    // Ensure JSON encoding is always valid, even when $_POST is empty
    $variables_json = !empty($variables) ? json_encode($variables, JSON_UNESCAPED_UNICODE) : '[]';

    try {
        $sql = "INSERT INTO application_log (page_url, script_name, ContactID, variables_json, log_time)
                VALUES (:page_url, :script_name, :contactid, :variables_json, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':page_url' => $page_url,
            ':script_name' => $script_name,
            ':contactid' => $contactID,
            ':variables_json' => $variables_json
        ]);
    } catch (PDOException $e) {
        error_log("Failed to log request: " . $e->getMessage());
    }
}

function dbConnect($schema, $contactID, $encrypKey)
{
    if ($schema === 'pmo') {
        $db_host = $_ENV['DB_PMO_HOST'];
        $db_username = $_ENV['DB_PMO_USER'];
        $db_userpass = $_ENV['DB_PMO_PASS'];
        $db_name = $_ENV['DB_PMO_NAME'];
        $db_port = $_ENV['DB_PMO_PORT'];
    } else if ($schema === 'jalur') {
        $db_host = $_ENV['DB_HOST'];
        $db_username = $_ENV['DB_USER'];
        $db_userpass = $_ENV['DB_PASS'];
        $db_name = $_ENV['DB_NAME'];
        $db_port = $_ENV['DB_PORT'];
    }

    CheckSystemAuthorization($contactID, $encrypKey, $db_host, $db_username, $db_userpass);
    CheckUserPageAuthority($contactID, $db_host, $db_username, $db_userpass);
    $currentFile = strtoupper(basename($_SERVER['SCRIPT_NAME'])); // Check access to the main PHP file
    ua_permission_check($contactID, 'SP001009', $currentFile);

    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8";
    $conn = new PDO($dsn, $db_username, $db_userpass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Call logRequest function after establishing connection
    logRequest($conn, $contactID);

    return $conn;
}




// $currentFile = basename($_SERVER['SCRIPT_NAME']);
// ua_permission_check($contactID, 'SP001009', $currentFile);

// function ua_permission_check($contactID, $permissionID, $permissionValue) {
// $db_host = $_ENV['DB_HOST'];
// $db_username = $_ENV['DB_USER'];
// $db_userpass = $_ENV['DB_PASS'];
// $db_name = $_ENV['DB_NAME'];

// $permissionValue = strtoupper($permissionValue); // Normalize input

// try {
// $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8";
// $pdo = new PDO($dsn, $db_username, $db_userpass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// $sql = "
// SELECT 1
// FROM ua_user_role_assignments ura
// JOIN ua_permission_values pv ON pv.SecurityRoleID = ura.SecurityRoleID
// WHERE ura.ContactID = :contactID
// AND pv.PermissionID = :permissionID
// AND (UPPER(pv.PermissionValue) = :permissionValue OR pv.PermissionValue = '*')
// AND ura.RecordStatus = 'A'
// AND pv.RecordStatus = 'A'
// LIMIT 1
// ";

// $stmt = $pdo->prepare($sql);
// $stmt->execute([
// ':contactID' => $contactID,
// ':permissionID' => $permissionID,
// ':permissionValue' => $permissionValue
// ]);

// if ($stmt->rowCount() === 0) {
// $sqlWildcard = "
// SELECT 1
// FROM ua_user_role_assignments ura
// JOIN ua_permission_values pv ON pv.SecurityRoleID = ura.SecurityRoleID
// WHERE ura.ContactID = :contactID
// AND pv.PermissionID = :permissionID
// AND UPPER(:permissionValue) LIKE UPPER(REPLACE(pv.PermissionValue, '*', '%'))
// AND ura.RecordStatus = 'A'
// AND pv.RecordStatus = 'A'
// LIMIT 1
// ";

// $stmt = $pdo->prepare($sqlWildcard);
// $stmt->execute([
// ':contactID' => $contactID,
// ':permissionID' => $permissionID,
// ':permissionValue' => $permissionValue
// ]);

// if ($stmt->rowCount() === 0) {
// $script_name = strtoupper(basename($_SERVER['SCRIPT_NAME']));
// $page_url = $_SERVER['HTTP_REFERER'] ?? 'Unknown';
// $variables = json_encode([
// 'PermissionID' => $permissionID,
// 'PermissionValue' => $permissionValue
// ], JSON_UNESCAPED_UNICODE);


// SAP_PROF user StrategistSAP@Gmail.com
// if ($contactID == 'CI006045') {
// $log_sql = "INSERT IGNORE INTO `ua_permission_values` (`ValueID`, `SecurityRoleID`, `PermissionID`, `PermissionValue`, `RecordStatus`)
// VALUES ('XX', 'SR001023', 'SP001009', :script_name, 'A');";
// $log_stmt = $pdo->prepare($log_sql);
// $log_stmt->execute([
// ':script_name' => $script_name
// ]);
// }


// $log_sql = "INSERT INTO application_log (page_url, script_name, ContactID, variables_json, log_time, log_text)
// VALUES (:page_url, :script_name, :contactID, :variables_json, NOW(), :log_text)";
// $log_stmt = $pdo->prepare($log_sql);
// $log_stmt->execute([
// ':page_url' => $page_url,
// ':script_name' => $script_name,
// ':contactID' => $contactID,
// ':variables_json' => $variables,
// ':log_text' => 'Permission check failed'
// ]);

// error_log("Unauthorized access to '$permissionValue' by '$contactID' (no match in permission values).");
// jsonResponseAndExit("Unauthorized: Permission denied for '$permissionValue'.", "unauthorized_access.html");
// }
// }

// $stmt->closeCursor();
// $pdo = null;

// } catch (PDOException $e) {
// error_log("DB error in ua_permission_check(): " . $e->getMessage());
// jsonResponseAndExit("Technical error: Permission check failed.", "unauthorized_access.html");
// }
// }

function ua_permission_check($contactID, $permissionID, $permissionValue)
{
    $db_host = $_ENV['DB_HOST'];
    $db_username = $_ENV['DB_USER'];
    $db_userpass = $_ENV['DB_PASS'];
    $db_name = $_ENV['DB_NAME'];
    $db_port = $_ENV['DB_PORT'];

    $permissionValue = strtoupper($permissionValue); // Normalize input

    try {
        $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8";
        $pdo = new PDO($dsn, $db_username, $db_userpass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $sql = "
            SELECT 1
            FROM ua_user_role_assignments ura
            JOIN ua_permission_values pv ON pv.SecurityRoleID = ura.SecurityRoleID
            WHERE ura.ContactID = :contactID
              AND pv.PermissionID = :permissionID
              AND (UPPER(pv.PermissionValue) = :permissionValue OR pv.PermissionValue = '*' OR UPPER(:permissionValue) LIKE UPPER(REPLACE(pv.PermissionValue, '*', '%')))
              AND ura.RecordStatus = 'A'
              AND pv.RecordStatus = 'A'
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':contactID' => $contactID,
            ':permissionID' => $permissionID,
            ':permissionValue' => $permissionValue
        ]);

        if ($stmt->rowCount() === 0) {
            logUnauthorizedAccess($pdo, $contactID, $permissionID, $permissionValue); // Handles logging and error response
        }

        $stmt->closeCursor();
        $pdo = null;
    } catch (PDOException $e) {
        error_log("DB error in ua_permission_check(): " . $e->getMessage());
        jsonResponseAndExit("Technical error: Permission check failed.", "unauthorized_access.html");
    }
}

function logUnauthorizedAccess($pdo, $contactID, $permissionID, $permissionValue)
{
    $script_name = strtoupper(basename($_SERVER['SCRIPT_NAME']));
    $page_url = $_SERVER['HTTP_REFERER'] ?? 'Unknown';
    $variables = json_encode(['PermissionID' => $permissionID, 'PermissionValue' => $permissionValue], JSON_UNESCAPED_UNICODE);

    // Check for specific user and log an additional record if necessary

    //MR Z / VUPICO...
    // if ($contactID == 'CI001197') {
        // $log_sql = "INSERT IGNORE INTO `ua_permission_values` (`ValueID`, `SecurityRoleID`, `PermissionID`, `PermissionValue`, `RecordStatus`)
                    // VALUES ('XX', 'SR001022', 'SP001009', :script_name, 'A');";
        // $log_stmt = $pdo->prepare($log_sql);
        // $log_stmt->execute([
            // ':script_name' => $script_name
        // ]);
    // }
    //JALUR SHADOW
    // if ($contactID == 'CI006045') {
        // $log_sql = "INSERT IGNORE INTO `ua_permission_values` (`ValueID`, `SecurityRoleID`, `PermissionID`, `PermissionValue`, `RecordStatus`)
                    // VALUES ('XX', 'SR001023', 'SP001009', :script_name, 'A');";
        // $log_stmt = $pdo->prepare($log_sql);
        // $log_stmt->execute([
            // ':script_name' => $script_name
        // ]);
    // }

    // Steve e
	// Role: SR002015
	// Description: Client: Business Development Manager
    // if ($contactID == 'CI008574') {
        // $log_sql = "INSERT IGNORE INTO `ua_permission_values` (`ValueID`, `SecurityRoleID`, `PermissionID`, `PermissionValue`, `RecordStatus`)
                    // VALUES ('XX', 'SR002015', 'SP001009', :script_name, 'A');";
        // $log_stmt = $pdo->prepare($log_sql);
        // $log_stmt->execute([
            // ':script_name' => $script_name
        // ]);
    // }
	
	// Role: SR002010
	// Description: HR: Talent Screening Specialist – Stage 1
    // if ($contactID == 'CI008608') {
        // $log_sql = "INSERT IGNORE INTO `ua_permission_values` (`ValueID`, `SecurityRoleID`, `PermissionID`, `PermissionValue`, `RecordStatus`)
                    // VALUES ('XX', 'SR002010', 'SP001009', :script_name, 'A');";
        // $log_stmt = $pdo->prepare($log_sql);
        // $log_stmt->execute([
            // ':script_name' => $script_name
        // ]);
    // }
	
	// Role: SR002012 
	// Description: HR: Candidate Interview Coordinator
    // if ($contactID == 'CI008615') {
        // $log_sql = "INSERT IGNORE INTO `ua_permission_values` (`ValueID`, `SecurityRoleID`, `PermissionID`, `PermissionValue`, `RecordStatus`)
                    // VALUES ('XX', 'SR002012', 'SP001009', :script_name, 'A');";
        // $log_stmt = $pdo->prepare($log_sql);
        // $log_stmt->execute([
            // ':script_name' => $script_name
        // ]);
    // }
	
    // General log entry for unauthorized access
    $log_sql = "INSERT INTO application_log (page_url, script_name, ContactID, variables_json, log_time, log_text)
                VALUES (:page_url, :script_name, :contactID, :variables_json, NOW(), 'Permission check failed')";
    $log_stmt = $pdo->prepare($log_sql);
    $log_stmt->execute([
        ':page_url' => $page_url,
        ':script_name' => $script_name,
        ':contactID' => $contactID,
        ':variables_json' => $variables,
        ':log_text' => 'Permission check failed'
    ]);

    error_log("Unauthorized access to '$permissionValue' by '$contactID' (no match in permission values).");
    jsonResponseAndExit("Unauthorized: Permission denied for '$permissionValue'.", "unauthorized_access.html");
}
