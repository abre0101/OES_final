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
<style type="text/css">
#left{
    float:middle;
	width:800px;
	margin-left:10px;
	padding: 0px 10px 30px 0px;
	display:inline;
	align: center;
}
</style>
<title>SUOES Instructor</title>
</head>

<body>
<div id="container">
	<div id="header">
	</div>
	<div id="content">
		<?php
		include "Header.php";
		?>
		<div id="left">
		            <div class="login">
<br/> <br/>
			<h1 style="color:blue">Hi! <?php echo $_SESSION['Name'];?>  Welcome To Edit Profile Page</h1>			
	<p>&nbsp;</p>
	<?php
$ID=$_SESSION['ID'];
// Establish Connection with Database
$con = new mysqli("localhost","root");
// Select Database
$con->select_db("oes");
// Specify the query to execute
$sql = "select * FROM instructors where instructor_id ='".$ID."'  ";
// Execute query
$result = $con->query($sql);
// Loop through each records 
$row = $result->fetch_array()
?>             
               <strong>Instructor ID:</strong>
                    <?php echo $row['instructor_id'];?><br/> <br/>
                  <strong>Instructor Name:</strong>
                    <td><?php echo $row['full_name'];?><br/> <br/>
                  <strong>Email :</strong>
               <?php echo $row['email'];?><br/> <br/>
                 <strong>Username:</strong>
                  <?php echo $row['username'];?><br/> <br/>
                 <strong>Password:</strong>
                <?php echo $row['password'];?><br/> <br/>
                   <a href="EditProfile.php?InstId=<?php echo $row['instructor_id']; ?>">Edit Profile</a></td>
               	  </div>


	  </div>
			<h1>&nbsp;</h1> <br/>

		<div id="footerline"></div>
	</div>
	
	<div id="footer">Copyright &copy; 2022 SU Online Examination System.  All rights reserved.&nbsp;</div>
</div>
</body>
</html>