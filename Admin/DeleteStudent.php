<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../index.php");
    exit();
}

$Id = $_GET['student_id'];
$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$stmt = $con->prepare("delete FROM students where Id=?");
$stmt->bind_param("s", $Id);
$stmt->execute();
$stmt->close();
$con->close();

header("Location: Student.php?msg=deleted");
exit();
?>
