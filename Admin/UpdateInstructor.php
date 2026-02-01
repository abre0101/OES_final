<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../index.php");
    exit();
}

$Id = $_GET['ID'];
$is_active = $_POST['cmbStatus'];
$Department = $_POST['cmbDept'];

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$stmt = $con->prepare("UPDATE instructors SET department_name=(SELECT department_name FROM departments WHERE deptno=?), is_active=? WHERE instructor_id=?");
$stmt->bind_param("sss", $Department, $is_active, $Id);
$stmt->execute();
$stmt->close();
$con->close();

header("Location: Instructor.php?msg=updated");
exit();
?>
