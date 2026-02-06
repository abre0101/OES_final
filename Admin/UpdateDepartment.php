<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Administrator session
SessionManager::startSession('Administrator');

if(!isset($_SESSION['username'])){
    header("Location:../auth/staff-login.php");
    exit();
}

$Id = $_GET['ID'];
$Name = $_POST['txtID'];
$Faculty = isset($_POST['cmbFaculty']) ? $_POST['cmbFaculty'] : '';

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

if($Faculty) {
    $stmt = $con->prepare("UPDATE departments SET department_name=?, faculty_id=? WHERE department_id=?");
    $stmt->bind_param("sii", $Name, $Faculty, $Id);
} else {
    $stmt = $con->prepare("UPDATE departments SET department_name=? WHERE department_id=?");
    $stmt->bind_param("si", $Name, $Id);
}

$stmt->execute();
$stmt->close();
$con->close();

header("Location: Department.php?msg=updated");
exit();
?>
