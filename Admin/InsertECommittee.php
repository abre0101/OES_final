
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<?php
        $Id=$_POST['excID'];
         $Name=$_POST['excName']; 
         $Email=$_POST['excEmail'];
         $UserName=$_POST['excUName'];
         $Password=$_POST['excPassword'];
	$Department=$_POST['cmbDep'];       
	$is_active=$_POST['cmbStatus'];
	
	// Establish Connection with MYSQL
	$con = new mysqli("localhost","root");
	// Select Database
	$con->select_db("oes");
	// Specify the query to Insert Record
    $sql = "Insert INTO exam_committee_members	(committee_member_id,full_name,email,username,password,department_name,is_active) 
             values('".$Id."','".$Name."','".$Email."','".$UserName."','".$Password."','".$Department."','".$Status."')";	
	// execute query
	$con->query ($sql);
	// Close The Connection
	$con->close ();
	echo '<script type="text/javascript">alert("User Inserted Succesfully");window.location=\'ECommittee.php\';</script>';

?>
</body>
</html>
