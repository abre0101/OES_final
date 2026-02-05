<?php
$con = require_once(__DIR__ . "/Connections/OES.php");

// Create table if not exists
$con->query("CREATE TABLE IF NOT EXISTS `password_reset_requests` (
    `request_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(100) NOT NULL,
    `user_type` ENUM('student', 'instructor', 'department_head') NOT NULL,
    `user_name` VARCHAR(200) NOT NULL,
    `user_email` VARCHAR(100),
    `reason` TEXT,
    `request_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `is_active` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `processed_by` VARCHAR(100),
    `processed_date` DATETIME,
    `notes` TEXT,
    INDEX `idx_status` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Get departments for dropdown
$departments = $con->query("SELECT department_id, department_name FROM departments ORDER BY department_name");

$message = '';
$messageType = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userType = $_POST['user_type'];
    $userId = $_POST['user_id'];
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $department = $_POST['department'] ?? '';
    $resetReason = $_POST['reset_reason'];
    $otherReason = $_POST['other_reason'] ?? '';
    $additionalDetails = $_POST['additional_details'] ?? '';
    
    // Build complete reason
    $reason = $resetReason;
    if($resetReason == 'other' && $otherReason) {
        $reason = "Other: " . $otherReason;
    }
    if($additionalDetails) {
        $reason .= "\n\nAdditional Details: " . $additionalDetails;
    }
    if($department) {
        $reason .= "\n\nDepartment: " . $department;
    }
    
    // Verify user exists in database
    $userName = '';
    $userEmail = '';
    $userExists = false;
    
    switch($userType) {
        case 'student':
            $result = $con->query("SELECT full_name as Name, email as Email FROM students WHERE student_code = '$userId'");
            if($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $userName = $user['Name'];
                $userEmail = $user['Email'];
                $userExists = true;
            }
            break;
        case 'instructor':
            $result = $con->query("SELECT full_name as Name, email as Email FROM instructors WHERE instructor_code = '$userId'");
            if($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $userName = $user['Name'];
                $userEmail = $user['Email'];
                $userExists = true;
            }
            break;
        case 'department_head':
            $result = $con->query("SELECT full_name as Name, email as Email FROM department_heads WHERE head_code = '$userId'");
            if($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $userName = $user['Name'];
                $userEmail = $user['Email'];
                $userExists = true;
            }
            break;
    }
    
    if($userExists) {
        // Check if user already has a pending request
        $existingRequest = $con->query("SELECT * FROM password_reset_requests WHERE user_id = '$userId' AND user_type = '$userType' AND is_active = 'pending'");
        
        if($existingRequest && $existingRequest->num_rows > 0) {
            $message = 'You already have a pending password reset request. Please wait for admin approval.';
            $messageType = 'warning';
        } else {
            // Use provided email or database email
            $finalEmail = !empty($email) ? $email : $userEmail;
            
            $stmt = $con->prepare("INSERT INTO password_reset_requests (user_id, user_type, user_name, user_email, reason) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $userId, $userType, $userName, $finalEmail, $reason);
            
            if($stmt->execute()) {
                $message = '✅ Password reset request submitted successfully!<br><br>
                    <strong>What happens next:</strong><br>
                    • An administrator will review your request<br>
                    • You will be contacted via email once processed<br>
                    • Please check your email regularly<br><br>
                    <strong>Request ID:</strong> #' . $stmt->insert_id;
                $messageType = 'success';
            } else {
                $message = 'Error submitting request. Please try again.';
                $messageType = 'danger';
            }
        }
    } else {
        $message = '❌ User ID not found in our system. Please check your ID and try again.<br><br>
            <strong>Tips:</strong><br>
            • Make sure you selected the correct role<br>
            • Double-check your ID number<br>
            • Contact your department if you\'re unsure of your ID';
        $messageType = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
    <link href="assets/css/modern-v2.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .request-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo h1 {
            color: #667eea;
            margin: 0;
            font-size: 2rem;
        }
    </style>
</head>
<body>
    <div class="request-container">
        <div class="logo">
            <h1>🔐 Password Reset Request</h1>
            <p style="color: #6c757d; margin: 0.5rem 0 0 0;">Debre Markos University</p>
        </div>

        <?php if($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 2rem; padding: 1rem; border-radius: 10px;">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>I am a: *</label>
                <select name="user_type" class="form-control" required>
                    <option value="">-- Select Your Role --</option>
                    <option value="student">Student</option>
                    <option value="instructor">Instructor</option>
                    <option value="department_head">Department Head</option>
                </select>
            </div>

            <div class="form-group">
                <label>My ID Number: *</label>
                <input type="text" name="user_id" class="form-control" required placeholder="e.g., STU001, INS001, DH001">
                <small style="color: #6c757d;">Enter your ID in the format: STU001 (not 1, 2, 3...)</small>
            </div>

            <div class="form-group">
                <label>Full Name: *</label>
                <input type="text" name="full_name" class="form-control" required placeholder="Enter your full name">
                <small style="color: #6c757d;">This helps us verify your identity</small>
            </div>

            <div class="form-group">
                <label>Email Address: *</label>
                <input type="email" name="email" class="form-control" required placeholder="your.email@example.com">
                <small style="color: #6c757d;">We'll use this to contact you</small>
            </div>

            <div class="form-group">
                <label>Department/Faculty:</label>
                <select name="department" class="form-control">
                    <option value="">-- Select Department --</option>
                    <?php while($dept = $departments->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($dept['department_name']); ?>">
                            <?php echo htmlspecialchars($dept['department_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <small style="color: #6c757d;">Select your department/faculty</small>
            </div>

            <div class="form-group">
                <label>Reason for Password Reset: *</label>
                <select name="reset_reason" class="form-control" required onchange="toggleOtherReason(this)">
                    <option value="">-- Select Reason --</option>
                    <option value="forgot">I forgot my password</option>
                    <option value="locked">My account is locked</option>
                    <option value="compromised">I think my password was compromised</option>
                    <option value="never_received">I never received my initial password</option>
                    <option value="other">Other (please specify)</option>
                </select>
            </div>

            <div class="form-group" id="otherReasonDiv" style="display: none;">
                <label>Please specify: *</label>
                <textarea name="other_reason" id="otherReasonText" class="form-control" rows="3" placeholder="Explain your situation..."></textarea>
            </div>

            <div class="form-group">
                <label>Additional Details:</label>
                <textarea name="additional_details" class="form-control" rows="4" placeholder="Any additional information that might help us verify your identity (e.g., last login date, courses enrolled, etc.)"></textarea>
            </div>

            <div style="background: #fff3cd; padding: 1rem; border-radius: 10px; border-left: 4px solid #ffc107; margin-bottom: 1.5rem;">
                <strong>⚠️ Important:</strong>
                <ul style="margin: 0.5rem 0 0 1.5rem; font-size: 0.9rem;">
                    <li>All fields marked with * are required</li>
                    <li>Provide accurate information for faster processing</li>
                    <li>An administrator will review your request</li>
                    <li>You'll receive a temporary password once approved</li>
                </ul>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding: 1rem; font-size: 1.1rem;">
                📤 Submit Password Reset Request
            </button>
        </form>

        <script>
            function toggleOtherReason(select) {
                const otherDiv = document.getElementById('otherReasonDiv');
                const otherText = document.getElementById('otherReasonText');
                
                if(select.value === 'other') {
                    otherDiv.style.display = 'block';
                    otherText.required = true;
                } else {
                    otherDiv.style.display = 'none';
                    otherText.required = false;
                }
            }
        </script>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="index.php" style="color: #667eea;">← Back to Login</a>
        </div>
    </div>
</body>
</html>
<?php $con->close(); ?>
