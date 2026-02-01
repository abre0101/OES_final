<?php
session_start();

$UserName = $_POST['txtUserName'];
$Password = $_POST['txtPassword'];

// Try Administrator first
$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
$stmt = $con->prepare("SELECT * FROM administrators WHERE username=? AND password=?");
$stmt->bind_param("ss", $UserName, $Password);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_array();
$num_row = $result->num_rows;

if ($num_row > 0) {
    $_SESSION['ID'] = $row['admin_id'];
    $_SESSION['username'] = $row['username'];
    $stmt->close();
    $con->close();
    header("location:../Admin/index.php");
    exit();
}
$stmt->close();

// Try Instructor
$stmt = $con->prepare("SELECT * FROM instructors WHERE username=? AND password=? AND is_active=1");
$stmt->bind_param("ss", $UserName, $Password);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_array();
$records = $result->num_rows;

if ($records > 0) {
    $_SESSION['ID'] = $row['instructor_id'];
    $_SESSION['Name'] = $row['full_name'];
    $_SESSION['Dept'] = 'Not Set'; // Set default since table doesn't have department_name
    $_SESSION['Course'] = 'Not Set'; // Set default since table doesn't have course_name
    $_SESSION['Email'] = $row['email'];
    $stmt->close();
    $con->close();
    header("location:../Instructor/index.php");
    exit();
}
$stmt->close();

// Try Exam Committee
$stmt = $con->prepare("SELECT * FROM exam_committee_members WHERE username=? AND password=? AND is_active=1");
$stmt->bind_param("ss", $UserName, $Password);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_array();
$records = $result->num_rows;

if ($records > 0) {
    $_SESSION['ID'] = $row['committee_member_id'];
    $_SESSION['Name'] = $row['full_name'];
    $_SESSION['Dept'] = 'Not Set'; // Set default
    $stmt->close();
    $con->close();
    header("location:../ExamCommittee/index.php");
    exit();
}
$stmt->close();
$con->close();

// If no match found
echo '<script type="text/javascript">alert("Wrong Username or Password, or Account is Inactive");window.location=\'institute-login.php\';</script>';
?>
