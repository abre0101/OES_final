<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>SU OES</title>
</head>

<body>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include session manager, audit logger and password helper
require_once('../utils/session_manager.php');
require_once('../utils/audit_logger.php');
require_once('../utils/password_helper.php');

$UserName=$_POST['txtUserName'] ?? '';
$Password=$_POST['txtPassword'] ?? '';
$UserType=$_POST['cmbType'] ?? '';

// Check if form was submitted
if(empty($UserName) || empty($Password) || empty($UserType)) {
    echo '<script type="text/javascript">alert("Please fill in all fields");window.location=\'student-login.php\';</script>';
    exit();
}

if($UserType==="Administrator")
{
 require_once('../Connections/OES.php');
$stmt = $con->prepare("select * from administrators where username=?");
$stmt->bind_param("s", $UserName);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_array();
$num_row = $result->num_rows;

//echo $records;
if ($num_row==0 || !verifyPassword($Password, $row['password']))
{
$logger = new AuditLogger($con);
$logger->logLogin(null, 'admin', false, $UserName);
echo '<script type="text/javascript">alert("Wrong UserName or Password");window.location=\'index.php\';</script>';
//die(header("location:index.php"));
}
else
{
// Start Administrator session
SessionManager::startSession('Administrator');
$_SESSION['ID']=$row['admin_id'];
$_SESSION['username']=$row['username'];
$_SESSION['UserType']='Administrator';

$logger = new AuditLogger($con);
$logger->logLogin($row['admin_id'], 'admin', true, $UserName);
header("location:Admin/index.php");
} 
$stmt->close();
$con->close();
}

else if ($UserType=="Instructor")
{
    require_once('../Connections/OES.php');
$stmt = $con->prepare("select * from instructors where username=? and is_active=1");
$stmt->bind_param("s", $UserName);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_array();
$records = $result->num_rows;
if ($records==0 || !verifyPassword($Password, $row['password']))
{
$logger = new AuditLogger($con);
$logger->logLogin(null, 'instructor', false, $UserName);
echo '<script type="text/javascript">alert("Wrong UserName or Password Or You are Inactivated");window.location=\'index.php\';</script>';
}
else
{
// Start Instructor session
SessionManager::startSession('Instructor');
$_SESSION['ID']=$row['instructor_id'];
$_SESSION['Name']=$row['full_name'];
$_SESSION['Dept']=$row['department_id'];
$_SESSION['Email']=$row['email'];
$_SESSION['UserType']='Instructor';
$logger = new AuditLogger($con);
$logger->logLogin($row['instructor_id'], 'instructor', true, $UserName);
header("location:../Instructor/index.php");
} 
$stmt->close();
$con->close();
}

else if ($UserType=="DepartmentHead")
{
require_once('../Connections/OES.php');
$stmt = $con->prepare("SELECT ecm.*, d.department_name 
                       FROM exam_committee_members ecm 
                       LEFT JOIN departments d ON ecm.department_id = d.department_id 
                       WHERE ecm.username=? AND ecm.is_active=1");
$stmt->bind_param("s", $UserName);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_array();
$records = $result->num_rows;
if ($records==0 || !verifyPassword($Password, $row['password']))
{
$logger = new AuditLogger($con);
$logger->logLogin(null, 'department_head', false, $UserName);
echo '<script type="text/javascript">alert("Wrong UserName or Password");window.location=\'index.php\';</script>';
}
else
{
// Start Department Head session
SessionManager::startSession('DepartmentHead');
$_SESSION['ID']=$row['committee_member_id'];
$_SESSION['Name']=$row['full_name'];
$_SESSION['Dept']=$row['department_name'] ?? 'Not Set';
$_SESSION['DeptId']=$row['department_id'];
$_SESSION['UserType']='DepartmentHead';

$logger = new AuditLogger($con);
$logger->logLogin($row['committee_member_id'], 'department_head', true, $UserName);
header("location:../DepartmentHead/index.php");
} 
$stmt->close();
$con->close();
}

else if ($UserType=="Student")
{
 require_once('../Connections/OES.php');
 
 // Check connection
 if ($con->connect_error) {
     die("Connection failed: " . $con->connect_error);
 }
 
$stmt = $con->prepare("select * from students where username=? and is_active=1");

if (!$stmt) {
    die("Prepare failed: " . $con->error);
}

$stmt->bind_param("s", $UserName);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_array();
$records = $result->num_rows;

if ($records==0 || !verifyPassword($Password, $row['password']))
{
$logger = new AuditLogger($con);
$logger->logLogin(null, 'student', false, $UserName);
echo '<script type="text/javascript">alert("Wrong UserName or Password or Inactivated");window.location=\'student-login.php\';</script>';
}
else
{
// Start Student session
SessionManager::startSession('Student');
$_SESSION['ID']=$row['student_id'];
$_SESSION['Name']=$row['full_name'];
$_SESSION['Dept']=$row['department_id'];
$_SESSION['Sem']=$row['semester'];
$_SESSION['UserType']='Student';
$logger = new AuditLogger($con);
$logger->logLogin($row['student_id'], 'student', true, $UserName);
header("location:../Student/index.php");
exit();
} 
$stmt->close();
$con->close();

}

?>
</body>
</html>
