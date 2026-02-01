<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../index.php");
    exit();
}

$Id = $_GET['Id'];
$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$stmt = $con->prepare("delete FROM instructors where instructor_id=?");
$stmt->bind_param("s", $Id);
$stmt->execute();
$stmt->close();
$con->close();

header("Location: Instructor.php?msg=deleted");
exit();
?>
