<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../index.php");
    exit();
}

$FacId = $_GET['FacId'];
$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$stmt = $con->prepare("delete FROM faculties where faculty_id=?");
$stmt->bind_param("s", $FacId);
$stmt->execute();
$stmt->close();
$con->close();

header("Location: Faculty.php?msg=deleted");
exit();
?>
