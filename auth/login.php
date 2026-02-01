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

session_start();

// Include security logger
require_once('../utils/security_logger.php');

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
$stmt = $con->prepare("select * from administrators where username=? and password=?");
$stmt->bind_param("ss", $UserName, $Password);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_array();
$num_row = $result->num_rows;

//echo $records;
if ($num_row==0)
{
logFailedLogin($UserName, 'admin', 'Invalid username or password');
echo '<script type="text/javascript">alert("Wrong UserName or Password");window.location=\'index.php\';</script>';
//die(header("location:index.php"));
}
else
{
$_SESSION['ID']=$row['admin_id'];
$_SESSION['username']=$row['username'];

logSuccessfulLogin($UserName, 'admin');
header("location:Admin/index.php");
} 
$stmt->close();
$con->close();
}

else if ($UserType=="Instructor")
{
    require_once('../Connections/OES.php');
$stmt = $con->prepare("select * from instructors where username=? and password=? and is_active=1");
$stmt->bind_param("ss", $UserName, $Password);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_array();
$records = $result->num_rows;
if ($records==0)
{
logFailedLogin($UserName, 'instructor', 'Invalid credentials or account inactive');
echo '<script type="text/javascript">alert("Wrong UserName or Password Or You are Inactivated");window.location=\'index.php\';</script>';
}
else
{
$_SESSION['ID']=$row['instructor_id'];
$_SESSION['Name']=$row['full_name'];
$_SESSION['Dept']=$row['department_id'];
$_SESSION['Email']=$row['email'];
logSuccessfulLogin($UserName, 'instructor');
header("location:../Instructor/index.php");
} 
$stmt->close();
$con->close();
}

else if ($UserType=="ExamCommittee")
{
require_once('../Connections/OES.php');
$stmt = $con->prepare("select * from exam_committee_members where username=? and password=? and is_active=1");
$stmt->bind_param("ss", $UserName, $Password);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_array();
$records = $result->num_rows;
if ($records==0)
{
logFailedLogin($UserName, 'exam_committee', 'Invalid credentials or account inactive');
echo '<script type="text/javascript">alert("Wrong UserName or Password");window.location=\'index.php\';</script>';
}
else
{
$_SESSION['ID']=$row['committee_member_id'];
$_SESSION['Name']=$row['full_name'];
$_SESSION['Dept']=$row['department_id'];

logSuccessfulLogin($UserName, 'exam_committee');
header("location:../ExamCommittee/index.php");
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
 
$stmt = $con->prepare("select * from students where username=? and password=? and is_active=1");

if (!$stmt) {
    die("Prepare failed: " . $con->error);
}

$stmt->bind_param("ss", $UserName, $Password);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_array();
$records = $result->num_rows;

if ($records==0)
{
logFailedLogin($UserName, 'student', 'Invalid credentials or account inactive');
echo '<script type="text/javascript">alert("Wrong UserName or Password or Inactivated");window.location=\'student-login.php\';</script>';
}
else
{
$_SESSION['ID']=$row['student_id'];
$_SESSION['Name']=$row['full_name'];
$_SESSION['Dept']=$row['department_id'];
$_SESSION['Sem']=$row['semester'];
logSuccessfulLogin($UserName, 'student');
header("location:../Student/index.php");
exit();
} 
$stmt->close();
$con->close();

}

?>
</body>
</html>
