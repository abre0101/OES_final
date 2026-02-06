<?php
require_once(__DIR__ . "/../utils/session_manager.php");
require_once(__DIR__ . "/../utils/audit_logger.php");
require_once(__DIR__ . "/../utils/get_user_type.php");

// Start Administrator session
SessionManager::startSession('Administrator');

// Check if user is logged in
if(!isset($_SESSION['username'])){
    header("Location:../auth/staff-login.php");
    exit();
}

// Validate user role
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Administrator'){
    SessionManager::destroySession();
    header("Location:../auth/staff-login.php");
    exit();
}

$Id = $_POST['txtId'];
$Name = $_POST['txtName'];
$Email = $_POST['txtEmail'];
$Phone = $_POST['txtPhone'] ?? '';
$Department = $_POST['cmbDept'];
$is_active = $_POST['cmbStatus'];

// Establish Connection
$con = require_once(__DIR__ . "/../Connections/OES.php");

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Update department_heads table
$stmt = $con->prepare("UPDATE department_heads SET full_name=?, email=?, phone=?, department_id=?, is_active=? WHERE department_head_id=?");
$stmt->bind_param("sssiii", $Name, $Email, $Phone, $Department, $is_active, $Id);

if($stmt->execute()) {
    // Log the update
    $auditLogger = new AuditLogger($con);
    $auditLogger->logUpdate($_SESSION['ID'] ?? null, getUserTypeForAudit(), 'department_heads', $Id, null, "Updated department head: $Name");
    
    $stmt->close();
    $con->close();
    echo '<script type="text/javascript">alert("Department Head Updated Successfully");window.location="DepartmentHead.php";</script>';
} else {
    $error = $stmt->error;
    $stmt->close();
    $con->close();
    echo '<script type="text/javascript">alert("Error: ' . $error . '");window.history.back();</script>';
}
?>
