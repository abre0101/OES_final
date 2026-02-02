<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<?php
     $SID=$_POST['scheduleID'];
	$cmbExam=$_POST['cmbExam'];
	$cmbSem=$_POST['cmbSem'];
	$cmbCourse=$_POST['cmbCourse'];
	$txtDate=$_POST['txtDate'];
	$txtTime=$_POST['txtTime'];
	
	// Establish Connection with Database
$con = new mysqli("localhost","root");
// Select Database
$con->select_db("oes");
// Specify the query to execute

	$sql = "insert INTO exams (exam_id,exam_name,semester,course_name,exam_date,exam_time) 
	values('".$SID."','".$cmbExam."','".$cmbSem."','".$cmbCourse."','".$txtDate."','".$txtTime."')";
// Execute query
$result = $con->query($sql);
	// Close The Connection
	//$con->close ();
	echo '<script type="text/javascript">alert("Exam Schedule Inserted Succesfully");window.location=\'Schedule.php\';</script>';

?>
</body>
</html>
