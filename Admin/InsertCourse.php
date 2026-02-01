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
        $Dept=$_POST['cmbDep'];
        $Inst=$_POST['cmbInst'];
	
	// Establish Connection with MYSQL
	$con = new mysqli("localhost","root");
	// Select Database
	$con->select_db("oes");
	// Specify the query to Insert Record
	$sql = "insert INTO courses 	(course_id,course_name,credit_hr,semester,department_name,full_name) 	values('".$Id."','".$Name."','".$Credit."','".$Sem."','".$Dept."','".$Inst."')";
	// execute query
	$con->query ($sql);
	// Close The Connection
	$con->close ();
	echo '<script type="text/javascript">alert("New Course Inserted Succesfully");window.location=\'Course.php\';</script>';

?>
</body>
</html>
