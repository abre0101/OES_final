<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Create Student</title>
</head>

<body>
<?php

	$ID=$_POST['txtRoll'];
	$Name=$_POST['txtName'];
        $StudDept=$_POST['cmbDept'];
        $StudYear=$_POST['cmbYear'];
        $StudSem=$_POST['cmbSem'];
        $UserName=$_POST['txtUserName'];
        $Password=$_POST['txtPassword'];
        $Sex=$_POST['gender'];
        $is_active=$_POST['cmbStatus'];

	// Establish Connection with MYSQL
	$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
	// Specify the query to Insert Record
	$stmt = $con->prepare("Insert INTO students(Id,Name,department_name,year,semester,Sex,username,password,is_active) values(?,?,?,?,?,?,?,?,?)");
	$stmt->bind_param("ssssissss", $ID, $Name, $StudDept, $StudYear, $StudSem, $Sex, $UserName, $Password, $is_active);
	// execute query
	$stmt->execute();
	$stmt->close();
	// Close The Connection
	$con->close();
	echo '<script type="text/javascript">alert("New Student Inserted Successfully");window.location=\'Student.php\';</script>';

?>
</body>
</html>