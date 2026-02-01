<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<?php
$Id = $_GET['Id'];

$UserName=$_POST['txtUser'];
$Password=$_POST['txtPass'];
// Establish Connection with Database
$con = new mysqli("localhost","root");
// Select Database
$con->select_db("oes");
// Specify the query to Update Record
   // $sql = "UPDATE exam_committee_members set exam_committee.username='".$UserName."',exam_committee.password='".$Password."' where committee_member_id='".$Id."'";
	$sql = "UPDATE exam_committee_members set exam_committee.username='".$UserName."',exam_committee.password='".$Password."' where committee_member_id='".$Id."'";

	// execute query
	$con->query ($sql);
	// Close The Connection
	$con->close ();
echo '<script type="text/javascript">alert("Profile Updated Succesfully");window.location=\'Profile.php\';</script>';
?>
</body>
</html>
