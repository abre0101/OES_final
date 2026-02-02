<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../index.php");
    exit();
}

$Id = $_GET['student_id'];
$Year = $_POST['cmbYear'];
$is_active = $_POST['cmbStatus'];
$Sem = $_POST['cmbSem'];
$Department = $_POST['cmbDep'];

$con = require_once(__DIR__ . "/../Connections/OES.php");

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Convert to integers
$Department = intval($Department);
$is_active = intval($is_active);
$Sem = intval($Sem);

$stmt = $con->prepare("UPDATE students SET department_id=?, is_active=?, semester=? WHERE student_id=?");
$stmt->bind_param("iiii", $Department, $is_active, $Sem, $Id);
$stmt->execute();
$stmt->close();

$con->close();

header("Location: Student.php?msg=updated");
exit();
?>
