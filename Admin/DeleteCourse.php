<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../index.php");
    exit();
}

$CourseId = $_GET['CourseId'];
$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$stmt = $con->prepare("delete FROM courses where course_id=?");
$stmt->bind_param("s", $CourseId);
$stmt->execute();
$stmt->close();
$con->close();

header("Location: Course.php?msg=deleted");
exit();
?>
