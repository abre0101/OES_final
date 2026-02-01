<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../index.php");
    exit();
}

$Id = $_POST['txtDeptID'];
$Name = $_POST['txtCourseName'];
$Credit = $_POST['txtCredit'];
$Sem = $_POST['cmbSem'];
$Dept = $_POST['cmbDept'];
$Inst = $_POST['cmbInst'];

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$stmt = $con->prepare("UPDATE courses SET course_name=?, credit_hr=?, semester=?, department_name=(SELECT department_name FROM departments WHERE deptno=?), full_name=(SELECT full_name FROM instructors WHERE instructor_id=?) WHERE course_id=?");
$stmt->bind_param("ssisss", $Name, $Credit, $Sem, $Dept, $Inst, $Id);
$stmt->execute();
$stmt->close();
$con->close();

header("Location: Course.php?msg=updated");
exit();
?>
