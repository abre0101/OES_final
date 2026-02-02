<?php
if (!isset($_SESSION)) 
{
  session_start();
  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style1.css" rel="stylesheet" type="text/css" />
<title>SUOES-Department Head Profile Page</title>
</head>

<body>
<div id="container">
<div id="header">
  </div>
  <div id="content">
    
    <?php
    include "left_head.php";
    ?>
    <div id="left">
			<h1>Hi!  <?php echo $_SESSION['Name'];?> Welcome!</h1>
			
	<p>&nbsp;</p>
	<?php
$ID=$_SESSION['ID'];
	// Establish Connection with MYSQL
	$con = new mysqli("localhost","root");
	// Select Database
	$con->select_db("oes");
	$sql = "select * FROM exam_committee_members where committee_member_id ='".$ID."'";
    // Execute query
    $result = 	$con->query ($sql);
    // Loop through each records 
    $row = $result->fetch_array()
?>
                <table width="80%" height="183" border="0" cellpadding="0" cellspacing="0">
                  <tr>
                    <td><strong> ID:</strong></td>
                    <td><?php echo $row['committee_member_id'];?></td>
                  </tr>
                  <tr>
                    <td><strong> Name:</strong></td>
                    <td><?php echo $row['full_name'];?></td>
                  </tr> 
                  <tr>
                    <td><strong>Department:</strong></td>
                    <td><?php echo $row['department_name'];?></td>
                  </tr>
                  <tr>
                    <td><strong>Email :</strong></td>
                    <td><?php echo $row['email'];?></td>
                  </tr>
                  <tr>
                    <td><strong>username :</strong></td>
                    <td><?php echo $row['username'];?></td>
                  </tr>
                  <tr>
                    <td><strong>Password:</strong></td>
                    <td><?php echo $row['password'];?></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td><a href="EditProfile.php?ECID=<?php echo $row['committee_member_id']; ?>">Edit Profile</a></td>
                  </tr>
                </table>
	<h1>&nbsp;</h1>
	  </div>
		
		<div id="footerline"></div>
	</div>
	
	<div id="footer">Copyright &copy; 2022 SU Online Examination System. All rights reserved.&nbsp;</div>	
</div>
</body>
</html>