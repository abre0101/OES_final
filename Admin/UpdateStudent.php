<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../index.php");
    exit();
}

$Id = $_GET['Id'];
$Year = $_POST['cmbYear'];
$is_active = $_POST['cmbStatus'];
$Sem = $_POST['cmbSem'];
$Department = $_POST['cmbDep'];

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$stmt = $con->prepare("UPDATE students SET department_name=?, is_active=?, year=?, semester=? WHERE Id=?");
$stmt->bind_param("sssis", $Department, $is_active, $Year, $Sem, $Id);
$stmt->execute();
$stmt->close();
$con->close();

header("Location: Student.php?msg=updated");
exit();
?>
