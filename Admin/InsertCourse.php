<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<?php
        $Id=$_POST['txtDeptID'];
	$Name=$_POST['txtDeptName'];
        $Credit=$_POST['txtDeptCredit'];
	$Sem=$_POST['cmbSem'];
        $Dept=$_POST['cmbDept'];
        $Inst=$_POST['cmbInst'];
	
	// Establish Connection with MYSQL
	$con = new mysqli("localhost","root");
	// Select Database
	$con->select_db("oes");
	
	// Convert to integers
	$Sem = intval($Sem);
	$Dept = intval($Dept);
	$Inst = intval($Inst);
	
	// Insert course
	$sql = "INSERT INTO courses (course_id, course_name, credit_hours, semester, department_id) 
	        VALUES('".$Id."','".$Name."','".$Credit."',".$Sem.",".$Dept.")";
	$con->query($sql);
	
	// Insert instructor assignment
	$sql2 = "INSERT INTO instructor_courses (course_id, instructor_id, is_active) 
	         VALUES('".$Id."',".$Inst.", 1)";
	$con->query($sql2);
	
	// Close The Connection
	$con->close();
	echo '<script type="text/javascript">alert("New Course Inserted Successfully");window.location=\'Course.php\';</script>';

?>
</body>
</html>
