<?php
require_once(__DIR__ . "/../utils/session_manager.php");
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

require_once(__DIR__ . "/../utils/password_helper.php");
require_once(__DIR__ . "/../utils/audit_logger.php");

$HeadCode = $_POST['txtHeadCode'];
$Name = $_POST['txtName'];
$Email = $_POST['txtEmail'];
$Phone = $_POST['txtPhone'] ?? '';
$UserName = $_POST['txtUName'];
$Password = $_POST['txtPassword'];
$Department = $_POST['cmbDept'];
$is_active = $_POST['cmbStatus'];

// Hash the password before storing
$hashedPassword = hashPassword($Password);

// Establish Connection
$con = require_once(__DIR__ . "/../Connections/OES.php");

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Insert into department_heads table
$stmt = $con->prepare("INSERT INTO department_heads (head_code, username, password, full_name, email, phone, department_id, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssis", $HeadCode, $UserName, $hashedPassword, $Name, $Email, $Phone, $Department, $is_active);

if($stmt->execute()) {
    $insertedId = $con->insert_id;
    
    // Log the creation
    $auditLogger = new AuditLogger($con);
    $auditLogger->logCreate($_SESSION['ID'] ?? null, getUserTypeForAudit(), 'department_heads', $insertedId, "Created department head: $Name ($HeadCode)");
    
    $stmt->close();
    $con->close();
    echo '<script type="text/javascript">alert("Department Head Created Successfully");window.location="DepartmentHead.php";</script>';
} else {
    $error = $stmt->error;
    $stmt->close();
    $con->close();
    echo '<script type="text/javascript">alert("Error: ' . $error . '");window.history.back();</script>';
}
?>
