<?php
require_once(__DIR__ . "/../utils/session_manager.php");
require_once(__DIR__ . "/../utils/audit_logger.php");
require_once(__DIR__ . "/../utils/get_user_type.php");

// Start Administrator session
SessionManager::startSession('Administrator');

if(!isset($_SESSION['username'])){
    header("Location:../auth/staff-login.php");
    exit();
}

$Id = $_GET['Id'];
$con = require_once(__DIR__ . "/../Connections/OES.php");

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Get department head name before deleting
$stmt = $con->prepare("SELECT full_name, head_code FROM department_heads WHERE department_head_id=?");
$stmt->bind_param("i", $Id);
$stmt->execute();
$result = $stmt->get_result();
$head = $result->fetch_assoc();
$stmt->close();

// Delete the record
$stmt = $con->prepare("DELETE FROM department_heads WHERE department_head_id=?");
$stmt->bind_param("i", $Id);
$stmt->execute();
$stmt->close();

// Log the deletion
if($head) {
    $auditLogger = new AuditLogger($con);
    $auditLogger->logDelete($_SESSION['ID'] ?? null, getUserTypeForAudit(), 'department_heads', $Id, "Deleted department head: {$head['full_name']} ({$head['head_code']})");
}

$con->close();

header("Location: DepartmentHead.php?msg=deleted");
exit();
?>
