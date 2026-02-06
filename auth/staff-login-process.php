<?php
require_once(__DIR__ . "/../utils/session_manager.php");
require_once(__DIR__ . "/../utils/password_helper.php");

$UserName = $_POST['txtUserName'];
$Password = $_POST['txtPassword'];

$con = require_once(__DIR__ . "/../Connections/OES.php");

// Try Administrator first
$stmt = $con->prepare("SELECT * FROM administrators WHERE username=?");
$stmt->bind_param("s", $UserName);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_array();

if ($row && verifyPassword($Password, $row['password'])) {
    // Start Administrator session
    SessionManager::startSession('Administrator');
    $_SESSION['ID'] = $row['admin_id'];
    $_SESSION['username'] = $row['username'];
    $_SESSION['Name'] = $row['full_name'];
    $_SESSION['UserType'] = 'Administrator';
    $_SESSION['Email'] = $row['email'] ?? '';
    $stmt->close();
    $con->close();
    header("location:../Admin/index.php");
    exit();
}
$stmt->close();

// Try Instructor
$stmt = $con->prepare("SELECT i.*, d.department_name 
                       FROM instructors i 
                       LEFT JOIN departments d ON i.department_id = d.department_id 
                       WHERE i.username=? AND i.is_active=1");
$stmt->bind_param("s", $UserName);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_array();

if ($row && verifyPassword($Password, $row['password'])) {
    // Start Instructor session
    SessionManager::startSession('Instructor');
    $_SESSION['ID'] = $row['instructor_id'];
    $_SESSION['Name'] = $row['full_name'];
    $_SESSION['Dept'] = $row['department_name'] ?? 'Not Set';
    $_SESSION['DeptId'] = $row['department_id'];
    $_SESSION['Email'] = $row['email'] ?? '';
    $_SESSION['UserType'] = 'Instructor';
    $stmt->close();
    $con->close();
    header("location:../Instructor/index.php");
    exit();
}
$stmt->close();

// Try Department Head
$stmt = $con->prepare("SELECT dh.*, d.department_name 
                       FROM department_heads dh 
                       LEFT JOIN departments d ON dh.department_id = d.department_id 
                       WHERE dh.username=? AND dh.is_active=1");
$stmt->bind_param("s", $UserName);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_array();

if ($row && verifyPassword($Password, $row['password'])) {
    // Start Department Head session
    SessionManager::startSession('DepartmentHead');
    $_SESSION['ID'] = $row['department_head_id'];
    $_SESSION['Name'] = $row['full_name'];
    $_SESSION['Dept'] = $row['department_name'] ?? 'Not Set';
    $_SESSION['DeptId'] = $row['department_id'];
    $_SESSION['Email'] = $row['email'] ?? '';
    $_SESSION['UserType'] = 'DepartmentHead';
    $stmt->close();
    $con->close();
    header("location:../DepartmentHead/index.php");
    exit();
}
$stmt->close();
$con->close();

// If no match found
echo '<script type="text/javascript">alert("Wrong Username or Password, or Account is Inactive");window.location=\'staff-login.php\';</script>';
?>
