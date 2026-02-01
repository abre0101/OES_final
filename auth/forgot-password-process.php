<?php
$StudentName = $_POST['txtStudentName'];
$StudentEmail = $_POST['txtStudentEmail'];

// Connect to database
$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Check if student exists with matching name and email
$stmt = $con->prepare("SELECT * FROM students WHERE Name=? AND email=?");
$stmt->bind_param("ss", $StudentName, $StudentEmail);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_array();
$records = $result->num_rows;

if ($records > 0) {
    $password = $row['password'];
    $username = $row['username'];
    
    // In a real application, you would send an email here
    // For now, we'll just show a success message
    
    // Example email sending code (requires mail configuration):
    /*
    $to = $StudentEmail;
    $subject = "Password Recovery - Debre Markos University OES";
    $message = "Dear " . $StudentName . ",\n\n";
    $message .= "Your login credentials are:\n";
    $message .= "Username: " . $username . "\n";
    $message .= "Password: " . $password . "\n\n";
    $message .= "Please login at: http://yoursite.com/index.php\n\n";
    $message .= "Best regards,\n";
    $message .= "Debre Markos University Health Campus";
    
    $headers = "From: noreply@dmu.edu.et";
    
    if(mail($to, $subject, $message, $headers)) {
        echo '<script type="text/javascript">alert("Password has been sent to your email address!");window.location=\'index.php\';</script>';
    } else {
        echo '<script type="text/javascript">alert("Failed to send email. Please contact administrator.");window.location=\'forgot-password.php\';</script>';
    }
    */
    
    // For demonstration purposes (remove in production):
    echo '<script type="text/javascript">alert("Password recovery successful!\\n\\nUsername: ' . $username . '\\nPassword: ' . $password . '\\n\\nIn production, this would be sent to your email: ' . $StudentEmail . '");window.location=\'index.php\';</script>';
    
} else {
    echo '<script type="text/javascript">alert("No account found with that name and email address. Please check your details and try again.");window.location=\'forgot-password.php\';</script>';
}

$stmt->close();
$con->close();
?>
