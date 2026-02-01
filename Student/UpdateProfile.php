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
// Establish Connection with mysqli
$conn = require_once(__DIR__ . "/../Connections/OES.php"); $con;
// Specify the query to Update Record
$stmt = $conn->prepare("UPDATE students SET username=?, password=? WHERE student_id=?");
$stmt->bind_param("ssi", $UserName, $Password, $Id);
$stmt->execute();
$stmt->close();
// Close The Connection
mysqli_close($conn);
// Check if modern version exists
if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'modern') !== false) {
    echo '<script type="text/javascript">alert("Profile Updated Successfully!");window.location=\'Profile-modern.php\';</script>';
} else {
    echo '<script type="text/javascript">alert("Student Updated Successfully");window.location=\'Profile.php\';</script>';
}
?>
</body>
</html>
