<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../index.php");
    exit();
}

$Id = $_GET['Id'];
$UserName = $_POST['txtUser'];
$Password = $_POST['txtPass'];

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$stmt = $con->prepare("UPDATE administrators SET username=?, password=? WHERE admin_id=?");
$stmt->bind_param("sss", $UserName, $Password, $Id);
$stmt->execute();
$stmt->close();
$con->close();

// Update session username if changed
$_SESSION['username'] = $UserName;

header("Location: Profile.php?msg=updated");
exit();
?>
