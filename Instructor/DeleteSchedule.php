<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<?php

	$Id=$_GET['exam_id'];
	// Establish Connection with MYSQL
	$con = require_once(__DIR__ . "/../Connections/OES.php");
	
	// Specify the query to Insert Record
	$sql = "delete FROM exams where exam_id='".$Id."'";
	// execute query
	$con->query ($sql);
	// Close The Connection
	$con->close ();
	echo '<script type="text/javascript">alert("Schedule Deleted Succesfully");window.location=\'Schedule.php\';</script>';

?>
</body>
</html>
