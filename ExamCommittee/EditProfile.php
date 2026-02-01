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
<title>SUOES Student Profile</title>
<script src="../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<link href="../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<style type="text/css">
 #dep{
        float:left;
  width:550px;
  margin-left:10px;
  padding: 0px 10px 30px 0px;
  display:inline;
    }</style>
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
      <div id="dep">
          <h1>&nbsp;</h1>

			<h1>Welcome <?php echo $_SESSION['Name']   ;?>! You can show and edit your profile</h1>
			<?php
$Id=$_GET['ECID'];
	// Establish Connection with MYSQL
	$con = new mysqli("localhost","root");
	// Select Database
	$con->select_db("oes");
// Specify the query to execute
//$sql = "select * FROM exam_committee_members where committee_member_id ='".$ID."'";
$sql = "select * FROM exam_committee_members where exam_committee.committee_member_id='".$Id."'";
// Execute query
$result =$con->query($sql);
// Loop through each records 
while($row = $result->fetch_array())
{
$Id=$row['committee_member_id'];
$Department=$row['department_name'];
$Name=$row['full_name'];

$Email=$row['email'];

$UserName =$row['username'];
$Password=$row['password'];
$is_active=$row['is_active'];
}
?>
          <form method="post" action="UpdateProfile.php?Id=<?php echo $Id;?>">
                      <table width="100%" border="0">
                        <tr>
                          <td height="32"><span class="style8"><strong>Exam Committee Id</strong></span></td>
                          <td><?php echo $Id;?></td>
                        </tr>
                        <tr>
                          <td height="36"><span class="style8"><strong> Exam Committee Name  :</strong></span></td>
                          <td><?php echo $Name;?></td>
                        </tr>
                           
                          
                        
                           <tr>
                          <td height="36"><span class="style8"><strong>   Email  :</strong></span></td>
                          <td><?php echo $Email;?></td>
                        </tr>
                          <tr>
                          <td height="36"><span class="style8"><strong>   Status  :</strong></span></td>
                          <td><?php echo $Status;?></td>
                        </tr>
                          
                       
                        
                        <tr>
                          <td height="28"><strong>Change username:</strong></td>
                          <td><span id="sprytextfield4">
                            <label>
                            <input type="text" name="txtUser" id="txtUser"  />
                            </label>
                          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                        </tr>
                        <tr>
                          <td height="28"><strong>Change Password:</strong></td>
                          <td><span id="sprytextfield5">
                            <label>
                            <input type="password" name="txtPass" id="txtPass"  />
                            </label>
                          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                        </tr>
                        <tr>
                          <td height="28"></td>
                          <td><label>
                            <input type="submit" name="button" id="button" value="Update Profile" />
                          </label></td>
                        </tr>
                      </table>
          </form>
                  <?php
// Close the connection
$con->close();
?></td>
              </tr>
            </table>
	<p>&nbsp;</p>
	
	<h1>&nbsp;</h1>
	  </div>
		
		<div id="footerline"></div>
	</div>
	
	<div id="footer">Copyright &copy; 2022 SU Online Examination System. All rights reserved.&nbsp;</div>	
</div>
<script type="text/javascript">
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3");
var sprytextfield4 = new Spry.Widget.ValidationTextField("sprytextfield4");
var sprytextfield5 = new Spry.Widget.ValidationTextField("sprytextfield5");
//-->
</script>
</body>
</html>