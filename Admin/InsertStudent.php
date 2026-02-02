<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Create Student</title>
</head>

<body>
<?php
	require_once(__DIR__ . "/../utils/password_helper.php");

	// Function to generate next student code
	function generateNextStudentCode($con) {
		$query = "SELECT student_code FROM students WHERE student_code LIKE 'STU%' ORDER BY student_code DESC LIMIT 1";
		$result = $con->query($query);
		
		if($result && $result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$lastCode = $row['student_code'];
			// Extract number from STU004 format
			$number = intval(substr($lastCode, 3));
			$nextNumber = $number + 1;
			return 'STU' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
		} else {
			// First student
			return 'STU001';
		}
	}

	// Establish Connection with MYSQL
	$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
	
	// Auto-generate student code
	$ID = generateNextStudentCode($con);
	
	$Name=$_POST['txtName'];
        $StudDept=$_POST['cmbDept'];
        $StudYear=$_POST['cmbYear'];
        $StudSem=$_POST['cmbSem'];
        $UserName=$_POST['txtUserName'];
        $Password=$_POST['txtPassword'];
        $Sex=$_POST['gender'];
        $is_active=$_POST['cmbStatus'];

	// Hash the password before storing
	$hashedPassword = hashPassword($Password);

	// Specify the query to Insert Record
	$stmt = $con->prepare("Insert INTO students(Id,Name,department_name,year,semester,Sex,username,password,is_active) values(?,?,?,?,?,?,?,?,?)");
	$stmt->bind_param("ssssissss", $ID, $Name, $StudDept, $StudYear, $StudSem, $Sex, $UserName, $hashedPassword, $is_active);
	// execute query
	$stmt->execute();
	$stmt->close();
	// Close The Connection
	$con->close();
	echo '<script type="text/javascript">alert("New Student Inserted Successfully with Code: ' . $ID . '");window.location=\'Student.php\';</script>';

?>
</body>
</html>