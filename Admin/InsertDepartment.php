<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Insert Department</title>
</head>

<body>
<?php

	$ID=$_POST['txtID'];
	$Name=$_POST['txtName'];
    $DeptFaculty=$_POST['cmbFacult'];
	

	
	// Establish Connection with MYSQL
	$con = new mysqli("localhost","root");
	// Select Database
	$con->select_db("oes");
	// Specify the query to Insert Record
	$sql = "insert INTO departments 	(department_id,department_name,faculty_name) 	values('".$ID."','".$Name."','".$DeptFaculty."' )";
	// execute query
	$con->query ($sql);
	// Close The Connection
	$con->close ();
	echo '<script type="text/javascript">alert("New Department Inserted Succesfully");window.location=\'Department.php\';</script>';

?>

</body>
</html>

