<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Administrator session
SessionManager::startSession('Administrator');

if(!isset($_SESSION['username'])){
    header("Location:../auth/staff-login.php");
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

// Validate and convert department_id - if it's not numeric, look it up by name
if (!is_numeric($Dept)) {
    $dept_lookup = $con->prepare("SELECT department_id FROM departments WHERE department_name=?");
    $dept_lookup->bind_param("s", $Dept);
    $dept_lookup->execute();
    $dept_result = $dept_lookup->get_result();
    if ($dept_row = $dept_result->fetch_assoc()) {
        $Dept = $dept_row['department_id'];
    }
    $dept_lookup->close();
}

// Convert to integers where needed
$Sem = intval($Sem);
$Dept = intval($Dept);
$Inst = intval($Inst);

$stmt = $con->prepare("UPDATE courses SET course_name=?, credit_hours=?, semester=?, department_id=? WHERE course_id=?");
$stmt->bind_param("ssiis", $Name, $Credit, $Sem, $Dept, $Id);
$stmt->execute();
$stmt->close();

// Update instructor assignment in instructor_courses table
// First, delete all current assignments for this course
$stmt2 = $con->prepare("DELETE FROM instructor_courses WHERE course_id=?");
$stmt2->bind_param("s", $Id);
$stmt2->execute();
$stmt2->close();

// Then add the new instructor assignment (only if instructor ID is valid and not empty)
if (!empty($Inst) && is_numeric($Inst)) {
    // Verify instructor exists
    $check_inst = $con->prepare("SELECT instructor_id FROM instructors WHERE instructor_id=?");
    $check_inst->bind_param("i", $Inst);
    $check_inst->execute();
    $inst_result = $check_inst->get_result();
    
    if ($inst_result->num_rows > 0) {
        $stmt3 = $con->prepare("INSERT INTO instructor_courses (course_id, instructor_id) VALUES (?, ?)");
        $stmt3->bind_param("ii", $Id, $Inst);
        $stmt3->execute();
        $stmt3->close();
    }
    $check_inst->close();
}

$con->close();

header("Location: Course.php?msg=updated");
exit();
?>
