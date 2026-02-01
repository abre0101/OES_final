<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../index.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;

// Create password reset requests table if not exists
$con->query("CREATE TABLE IF NOT EXISTS `password_reset_requests` (
    `request_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(100) NOT NULL,
    `user_type` ENUM('student', 'instructor', 'exam_committee') NOT NULL,
    `user_name` VARCHAR(200) NOT NULL,
    `user_email` VARCHAR(100),
    `reason` TEXT,
    `request_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `is_active` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `processed_by` VARCHAR(100),
    `processed_date` DATETIME,
    `notes` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$message = '';
$messageType = '';

// Handle request approval/rejection
if(isset($_POST['process_request'])) {
    $requestId = $_POST['request_id'];
    $action = $_POST['action'];
    $adminName = $_SESSION['username'];
    $processDate = date('Y-m-d H:i:s');
    
    if($action == 'approve') {
        // Get request details
        $request = $con->query("SELECT * FROM password_reset_requests WHERE request_id = $requestId")->fetch_assoc();
        
        // Generate temporary password
        $tempPassword = 'Temp' . rand(1000, 9999);
        
        // Reset password based on user type
        switch($request['user_type']) {
            case 'student':
                $con->query("UPDATE students SET Password = '$tempPassword' WHERE Id = '{$request['user_id']}'");
                break;
            case 'instructor':
                $con->query("UPDATE instructors SET Password = '$tempPassword' WHERE instructor_id = '{$request['user_id']}'");
                break;
            case 'exam_committee':
                $con->query("UPDATE exam_committee_members SET Password = '$tempPassword' WHERE committee_member_id = '{$request['user_id']}'");
                break;
        }
        
        // Update request status
        $con->query("UPDATE password_reset_requests SET status = 'approved', processed_by = '$adminName', processed_date = '$processDate', notes = 'Temporary password: $tempPassword' WHERE request_id = $requestId");
        
        $message = "Request approved! Temporary password: <strong>$tempPassword</strong> - Please inform the user.";
        $messageType = 'success';
    } elseif($action == 'reject') {
        $con->query("UPDATE password_reset_requests SET status = 'rejected', processed_by = '$adminName', processed_date = '$processDate' WHERE request_id = $requestId");
        $message = 'Request rejected.';
        $messageType = 'warning';
    }
}

// Get pending requests
$pendingRequests = $con->query("SELECT * FROM password_reset_requests WHERE status = 'pending' ORDER BY created_at DESC");

$message = '';
$messageType = '';

// Handle password reset
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $userType = $_POST['user_type'];
    $userId = $_POST['user_id'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if($newPassword !== $confirmPassword) {
        $message = 'Passwords do not match!';
        $messageType = 'danger';
    } elseif(strlen($newPassword) < 6) {
        $message = 'Password must be at least 6 characters long!';
        $messageType = 'danger';
    } else {
        // Hash password for security
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password based on user type
        $updated = false;
        switch($userType) {
            case 'student':
                $stmt = $con->prepare("UPDATE students SET Password = ? WHERE Id = ?");
                $stmt->bind_param("ss", $newPassword, $userId);
                $updated = $stmt->execute();
                break;
            case 'instructor':
                $stmt = $con->prepare("UPDATE instructors SET Password = ? WHERE instructor_id = ?");
                $stmt->bind_param("ss", $newPassword, $userId);
                $updated = $stmt->execute();
                break;
            case 'exam_committee':
                $stmt = $con->prepare("UPDATE exam_committee_members SET Password = ? WHERE committee_member_id = ?");
                $stmt->bind_param("ss", $newPassword, $userId);
                $updated = $stmt->execute();
                break;
            case 'admin':
                $stmt = $con->prepare("UPDATE administrators SET Password = ? WHERE username = ?");
                $stmt->bind_param("ss", $newPassword, $userId);
                $updated = $stmt->execute();
                break;
        }
        
        if($updated) {
            $message = 'Password reset successfully! User can now login with the new password.';
            $messageType = 'success';
        } else {
            $message = 'Failed to reset password. Please try again.';
            $messageType = 'danger';
        }
    }
}

// Get only users who have pending password reset requests
$students = $con->query("
    SELECT DISTINCT s.student_id as Id, s.full_name as Name, s.email as Email, prr.created_at as request_date
    FROM students s 
    INNER JOIN password_reset_requests prr ON s.student_id = prr.user_id 
    WHERE prr.user_type = 'student' AND prr.status = 'pending'
    ORDER BY prr.created_at DESC
");

$instructors = $con->query("
    SELECT DISTINCT i.instructor_id, i.full_name as Name, i.email as Email, prr.created_at as request_date
    FROM instructors i 
    INNER JOIN password_reset_requests prr ON i.instructor_id = prr.user_id 
    WHERE prr.user_type = 'instructor' AND prr.status = 'pending'
    ORDER BY prr.created_at DESC
");

$committees = $con->query("
    SELECT DISTINCT ecm.committee_member_id as committee_id, ecm.full_name as Name, ecm.email as Email, prr.created_at as request_date
    FROM exam_committee_members ecm 
    INNER JOIN password_reset_requests prr ON ecm.committee_member_id = prr.user_id 
    WHERE prr.user_type = 'exam_committee' AND prr.status = 'pending'
    ORDER BY prr.created_at DESC
");

$admins = $con->query("SELECT username, username as Name FROM administrators ORDER BY username");

// Get users again for the cards (since we'll iterate through them twice)
$students_cards = $con->query("
    SELECT DISTINCT s.student_id as Id, s.full_name as Name, s.email as Email, prr.created_at as request_date, prr.reason
    FROM students s 
    INNER JOIN password_reset_requests prr ON s.student_id = prr.user_id 
    WHERE prr.user_type = 'student' AND prr.status = 'pending'
    ORDER BY prr.created_at DESC
");

$instructors_cards = $con->query("
    SELECT DISTINCT i.instructor_id, i.full_name as Name, i.email as Email, prr.created_at as request_date, prr.reason
    FROM instructors i 
    INNER JOIN password_reset_requests prr ON i.instructor_id = prr.user_id 
    WHERE prr.user_type = 'instructor' AND prr.status = 'pending'
    ORDER BY prr.created_at DESC
");

$committees_cards = $con->query("
    SELECT DISTINCT ecm.committee_member_id, ecm.full_name as Name, ecm.email as Email, prr.created_at as request_date, prr.reason
    FROM exam_committee_members ecm 
    INNER JOIN password_reset_requests prr ON ecm.committee_member_id = prr.user_id 
    WHERE prr.user_type = 'exam_committee' AND prr.status = 'pending'
    ORDER BY prr.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Admin</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .page-header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 2rem;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 197, 253, 0.05) 100%);
            padding: 2rem;
            border-radius: var(--radius-lg);
            border: 2px solid rgba(59, 130, 246, 0.1);
        }
        
        .page-title-section h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .page-title-section h1 span {
            -webkit-text-fill-color: initial;
            background: none;
        }
        
        .page-title-section p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 1rem;
            font-weight: 500;
        }
        
        .password-strength {
            height: 5px;
            border-radius: 3px;
            margin-top: 0.5rem;
            transition: all 0.3s;
        }
        .strength-weak { background: #dc3545; width: 33%; }
        .strength-medium { background: #ffc107; width: 66%; }
        .strength-strong { background: #28a745; width: 100%; }
        
        .user-card {
            background: white;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        .user-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .user-card.selected {
            border-color: var(--primary-color);
            background: rgba(0, 123, 255, 0.05);
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php 
        $pageTitle = 'Reset Password';
        include 'header-component.php'; 
        ?>

        <div class="admin-content">
            <!-- Page Header -->
            <div class="page-header-actions">
                <div class="page-title-section">
                    <h1><span>🔐</span> Reset User Password</h1>
                    <p>Reset password for any user in the system</p>
                </div>
            </div>

            <?php if($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 2rem; padding: 1.25rem; border-radius: var(--radius-lg); background: <?php echo $messageType == 'success' ? 'rgba(40, 167, 69, 0.1)' : 'rgba(220, 53, 69, 0.1)'; ?>; border-left: 4px solid <?php echo $messageType == 'success' ? 'var(--success-color)' : '#dc3545'; ?>;">
                <strong><?php echo $messageType == 'success' ? '✓' : '✗'; ?></strong> <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <!-- Pending Password Reset Requests -->
            <?php if($pendingRequests && $pendingRequests->num_rows > 0): ?>
            <div class="card" style="margin-bottom: 2rem; border: 3px solid var(--warning-color);">
                <div class="card-header" style="background: var(--warning-color); color: white;">
                    <h3 class="card-title" style="margin: 0;">🔔 Pending Password Reset Requests (<?php echo $pendingRequests->num_rows; ?>)</h3>
                </div>
                <div style="padding: 2rem;">
                    <?php while($request = $pendingRequests->fetch_assoc()): ?>
                    <div style="background: var(--bg-light); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1rem; border-left: 4px solid var(--warning-color);">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.5rem 0; color: var(--primary-color);">
                                    <?php echo $request['user_name']; ?>
                                </h4>
                                <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                                    <strong>Type:</strong> <?php echo ucfirst(str_replace('_', ' ', $request['user_type'])); ?> | 
                                    <strong>ID:</strong> <?php echo $request['user_id']; ?> | 
                                    <strong>Email:</strong> <?php echo $request['user_email']; ?>
                                </div>
                                <div style="font-size: 0.85rem; color: var(--text-secondary);">
                                    <strong>Requested:</strong> <?php echo date('M d, Y H:i', strtotime($request['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div style="background: white; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem;">
                            <strong>Reason:</strong>
                            <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">
                                <?php echo htmlspecialchars($request['reason']); ?>
                            </p>
                        </div>

                        <div style="display: flex; gap: 1rem;">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" name="process_request" class="btn btn-success" onclick="return confirm('Approve this request and reset password?')">
                                    ✓ Approve & Reset Password
                                </button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" name="process_request" class="btn btn-danger" onclick="return confirm('Reject this request?')">
                                    ✗ Reject Request
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Manual Password Reset Section -->
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3 class="card-title" style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <span>🔑</span> Manual Password Reset
                    </h3>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: var(--text-secondary);">Reset password for any user in the system</p>
                </div>

                <div style="padding: 2rem;">
                    <div class="grid grid-2">
                        <!-- Reset Password Form -->
                        <div>
                            <h4 style="margin: 0 0 1.5rem 0; color: var(--primary-color); font-size: 1.2rem;">
                                📝 Reset Form
                            </h4>
                            <form method="POST" id="resetForm">
                                <div class="form-group">
                                    <label>Select User Type *</label>
                                    <select name="user_type" id="userType" class="form-control" required onchange="loadUsers()">
                                        <option value="">-- Select User Type --</option>
                                        <option value="student">👨‍🎓 Student</option>
                                        <option value="instructor">👨‍🏫 Instructor</option>
                                        <option value="exam_committee">👥 Exam Committee</option>
                                        <option value="admin">🔐 Administrator</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Select User *</label>
                                    <select name="user_id" id="userId" class="form-control" required>
                                        <option value="">-- First select user type --</option>
                                    </select>
                                    <small id="userSelectHint" style="color: var(--text-secondary);">Select the user whose password you want to reset</small>
                                </div>

                                <div class="form-group">
                                    <label>New Password *</label>
                                    <div style="position: relative;">
                                        <input type="password" name="new_password" id="newPassword" class="form-control" required minlength="6" oninput="checkPasswordStrength()" style="padding-right: 3rem;">
                                        <button type="button" onclick="togglePassword('newPassword')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.2rem;">
                                            👁️
                                        </button>
                                    </div>
                                    <div id="passwordStrength" class="password-strength"></div>
                                    <small id="strengthText" style="color: var(--text-secondary);"></small>
                                </div>

                                <div class="form-group">
                                    <label>Confirm Password *</label>
                                    <input type="password" name="confirm_password" id="confirmPassword" class="form-control" required minlength="6">
                                    <small style="color: var(--text-secondary);">Re-enter the new password</small>
                                </div>

                                <div style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 152, 0, 0.1) 100%); padding: 1.25rem; border-radius: var(--radius-md); margin: 1.5rem 0; border-left: 4px solid var(--warning-color);">
                                    <strong style="color: var(--warning-color);">⚠️ Important:</strong>
                                    <ul style="margin: 0.5rem 0 0 1.5rem; color: var(--text-secondary); font-size: 0.9rem;">
                                        <li>Password must be at least 6 characters</li>
                                        <li>User can login immediately with new password</li>
                                        <li>Inform the user securely about their new password</li>
                                        <li>Recommend user to change password after first login</li>
                                    </ul>
                                </div>

                                <div class="form-actions" style="display: flex; gap: 1rem;">
                                    <button type="submit" name="reset_password" class="btn btn-primary" style="flex: 1;">
                                        🔐 Reset Password
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        Clear
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Quick Access User Lists -->
                        <div>
                            <h4 style="margin: 0 0 1.5rem 0; color: var(--primary-color); font-size: 1.2rem;">
                                👥 Quick User Selection
                            </h4>
                            <div class="tabs" style="margin-bottom: 1.5rem; display: flex; gap: 0.5rem; border-bottom: 2px solid var(--border-color);">
                                <button class="tab-btn active" onclick="showTab('students')" style="padding: 0.75rem 1.5rem; border: none; background: none; cursor: pointer; border-bottom: 3px solid var(--primary-color); font-weight: 600; color: var(--primary-color);">
                                    👨‍🎓 Students
                                </button>
                                <button class="tab-btn" onclick="showTab('instructors')" style="padding: 0.75rem 1.5rem; border: none; background: none; cursor: pointer; border-bottom: 3px solid transparent; font-weight: 600; color: var(--text-secondary);">
                                    👨‍🏫 Instructors
                                </button>
                                <button class="tab-btn" onclick="showTab('committees')" style="padding: 0.75rem 1.5rem; border: none; background: none; cursor: pointer; border-bottom: 3px solid transparent; font-weight: 600; color: var(--text-secondary);">
                                    👥 Committees
                                </button>
                            </div>

                        <!-- Students Tab -->
                        <div id="students-tab" class="tab-content" style="max-height: 500px; overflow-y: auto;">
                            <?php if($students_cards->num_rows > 0): ?>
                                <?php while($student = $students_cards->fetch_assoc()): ?>
                                <div class="user-card" onclick="selectUser('student', '<?php echo $student['Id']; ?>', '<?php echo htmlspecialchars($student['Name']); ?>')" style="border-left: 4px solid var(--warning-color);">
                                    <div style="display: flex; align-items: start; gap: 1rem;">
                                        <div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), #667eea); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem; flex-shrink: 0;">
                                            <?php echo strtoupper(substr($student['Name'], 0, 1)); ?>
                                        </div>
                                        <div style="flex: 1;">
                                            <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;"><?php echo $student['Name']; ?></div>
                                            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                                                <strong>ID:</strong> <?php echo $student['Id']; ?> | 
                                                <strong>Email:</strong> <?php echo $student['Email']; ?>
                                            </div>
                                            <div style="font-size: 0.8rem; color: var(--warning-color); background: rgba(255, 193, 7, 0.1); padding: 0.35rem 0.75rem; border-radius: 6px; display: inline-block;">
                                                🕒 Requested: <?php echo date('M d, Y H:i', strtotime($student['request_date'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                                    <p style="margin: 0; font-weight: 600;">No pending requests from students</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Instructors Tab -->
                        <div id="instructors-tab" class="tab-content" style="display: none; max-height: 500px; overflow-y: auto;">
                            <?php if($instructors_cards->num_rows > 0): ?>
                                <?php while($instructor = $instructors_cards->fetch_assoc()): ?>
                                <div class="user-card" onclick="selectUser('instructor', '<?php echo $instructor['instructor_id']; ?>', '<?php echo htmlspecialchars($instructor['Name']); ?>')" style="border-left: 4px solid var(--success-color);">
                                    <div style="display: flex; align-items: start; gap: 1rem;">
                                        <div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, var(--success-color), #20c997); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem; flex-shrink: 0;">
                                            <?php echo strtoupper(substr($instructor['Name'], 0, 1)); ?>
                                        </div>
                                        <div style="flex: 1;">
                                            <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;"><?php echo $instructor['Name']; ?></div>
                                            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                                                <strong>ID:</strong> <?php echo $instructor['instructor_id']; ?> | 
                                                <strong>Email:</strong> <?php echo $instructor['Email']; ?>
                                            </div>
                                            <div style="font-size: 0.8rem; color: var(--warning-color); background: rgba(255, 193, 7, 0.1); padding: 0.35rem 0.75rem; border-radius: 6px; display: inline-block;">
                                                🕒 Requested: <?php echo date('M d, Y H:i', strtotime($instructor['request_date'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                                    <p style="margin: 0; font-weight: 600;">No pending requests from instructors</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Committees Tab -->
                        <div id="committees-tab" class="tab-content" style="display: none; max-height: 500px; overflow-y: auto;">
                            <?php if($committees_cards->num_rows > 0): ?>
                                <?php while($committee = $committees_cards->fetch_assoc()): ?>
                                <div class="user-card" onclick="selectUser('exam_committee', '<?php echo $committee['committee_member_id']; ?>', '<?php echo htmlspecialchars($committee['Name']); ?>')" style="border-left: 4px solid #6f42c1;">
                                    <div style="display: flex; align-items: start; gap: 1rem;">
                                        <div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #6f42c1, #9b59b6); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem; flex-shrink: 0;">
                                            <?php echo strtoupper(substr($committee['Name'], 0, 1)); ?>
                                        </div>
                                        <div style="flex: 1;">
                                            <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;"><?php echo $committee['Name']; ?></div>
                                            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                                                <strong>ID:</strong> <?php echo $committee['committee_member_id']; ?> | 
                                                <strong>Email:</strong> <?php echo $committee['Email']; ?>
                                            </div>
                                            <div style="font-size: 0.8rem; color: var(--warning-color); background: rgba(255, 193, 7, 0.1); padding: 0.35rem 0.75rem; border-radius: 6px; display: inline-block;">
                                                🕒 Requested: <?php echo date('M d, Y H:i', strtotime($committee['request_date'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                                    <p style="margin: 0; font-weight: 600;">No pending requests from committee members</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Password Reset Guidelines -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">📚 Password Reset Guidelines</h3>
                </div>
                <div style="padding: 2rem;">
                    <div class="grid grid-3">
                        <div>
                            <h4 style="color: var(--primary-color); margin-bottom: 1rem;">🔒 Security Best Practices</h4>
                            <ul style="color: var(--text-secondary); line-height: 1.8;">
                                <li>Use strong passwords (mix of letters, numbers, symbols)</li>
                                <li>Never share passwords via email or unsecured channels</li>
                                <li>Recommend users change password after reset</li>
                                <li>Keep a secure log of password reset activities</li>
                            </ul>
                        </div>
                        <div>
                            <h4 style="color: var(--primary-color); margin-bottom: 1rem;">✅ When to Reset</h4>
                            <ul style="color: var(--text-secondary); line-height: 1.8;">
                                <li>User forgot their password</li>
                                <li>Account security has been compromised</li>
                                <li>User is locked out of their account</li>
                                <li>New user needs initial password</li>
                            </ul>
                        </div>
                        <div>
                            <h4 style="color: var(--primary-color); margin-bottom: 1rem;">📞 User Communication</h4>
                            <ul style="color: var(--text-secondary); line-height: 1.8;">
                                <li>Inform user via phone or in-person</li>
                                <li>Provide temporary password securely</li>
                                <li>Instruct to change password immediately</li>
                                <li>Verify user identity before resetting</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        const userData = {
            student: <?php 
                $students_js = $con->query("
                    SELECT DISTINCT s.student_id as Id, s.full_name as Name 
                    FROM students s 
                    INNER JOIN password_reset_requests prr ON s.student_id = prr.user_id 
                    WHERE prr.user_type = 'student' AND prr.status = 'pending'
                    ORDER BY s.full_name
                ");
                $arr = [];
                while($s = $students_js->fetch_assoc()) $arr[] = $s;
                echo json_encode($arr);
            ?>,
            instructor: <?php 
                $instructors_js = $con->query("
                    SELECT DISTINCT i.instructor_id as Id, i.full_name as Name 
                    FROM instructors i 
                    INNER JOIN password_reset_requests prr ON i.instructor_id = prr.user_id 
                    WHERE prr.user_type = 'instructor' AND prr.status = 'pending'
                    ORDER BY i.full_name
                ");
                $arr = [];
                while($i = $instructors_js->fetch_assoc()) $arr[] = $i;
                echo json_encode($arr);
            ?>,
            exam_committee: <?php 
                $committees_js = $con->query("
                    SELECT DISTINCT ecm.committee_member_id as Id, ecm.full_name as Name 
                    FROM exam_committee_members ecm 
                    INNER JOIN password_reset_requests prr ON ecm.committee_member_id = prr.user_id 
                    WHERE prr.user_type = 'exam_committee' AND prr.status = 'pending'
                    ORDER BY ecm.full_name
                ");
                $arr = [];
                while($c = $committees_js->fetch_assoc()) $arr[] = $c;
                echo json_encode($arr);
            ?>,
            admin: <?php 
                $admins_js = $con->query("SELECT username as Id, username as Name FROM administrators ORDER BY username");
                $arr = [];
                while($a = $admins_js->fetch_assoc()) $arr[] = $a;
                echo json_encode($arr);
            ?>
        };

        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            field.type = field.type === 'password' ? 'text' : 'password';
        }

        function loadUsers() {
            const userType = document.getElementById('userType').value;
            const userSelect = document.getElementById('userId');
            const hintText = document.getElementById('userSelectHint');
            
            userSelect.innerHTML = '';
            userSelect.disabled = false;
            
            if(!userType) {
                userSelect.innerHTML = '<option value="">-- First select user type --</option>';
                hintText.style.color = 'var(--text-secondary)';
                hintText.textContent = 'Select the user whose password you want to reset';
                return;
            }
            
            if(userData[userType] && userData[userType].length > 0) {
                userSelect.innerHTML = '<option value="">-- Select User --</option>';
                userData[userType].forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.Id;
                    option.textContent = `${user.Name} (ID: ${user.Id})`;
                    userSelect.appendChild(option);
                });
                hintText.style.color = 'var(--success-color)';
                hintText.innerHTML = `✓ ${userData[userType].length} user(s) with pending password reset requests`;
            } else {
                userSelect.innerHTML = '<option value="">📭 No pending password reset requests</option>';
                userSelect.disabled = true;
                
                const userTypeText = userType === 'student' ? 'students' : 
                                   userType === 'instructor' ? 'instructors' : 
                                   userType === 'exam_committee' ? 'exam committee members' : 'administrators';
                
                hintText.style.color = 'var(--warning-color)';
                hintText.innerHTML = `⚠️ No ${userTypeText} have requested a password reset. Users must submit a request from the login page first.`;
            }
        }

        function selectUser(type, id, name) {
            document.getElementById('userType').value = type;
            loadUsers();
            setTimeout(() => {
                document.getElementById('userId').value = id;
            }, 100);
            
            // Visual feedback
            document.querySelectorAll('.user-card').forEach(card => card.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
            
            // Scroll to form
            document.getElementById('resetForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function checkPasswordStrength() {
            const password = document.getElementById('newPassword').value;
            const strengthBar = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            if(password.length >= 6) strength++;
            if(password.length >= 10) strength++;
            if(/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if(/\d/.test(password)) strength++;
            if(/[^a-zA-Z0-9]/.test(password)) strength++;
            
            if(strength <= 2) {
                strengthBar.className = 'password-strength strength-weak';
                strengthText.textContent = '⚠️ Weak password';
                strengthText.style.color = '#dc3545';
            } else if(strength <= 3) {
                strengthBar.className = 'password-strength strength-medium';
                strengthText.textContent = '⚡ Medium strength';
                strengthText.style.color = '#ffc107';
            } else {
                strengthBar.className = 'password-strength strength-strong';
                strengthText.textContent = '✓ Strong password';
                strengthText.style.color = '#28a745';
            }
        }

        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
                btn.style.borderBottomColor = 'transparent';
                btn.style.color = 'var(--text-secondary)';
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').style.display = 'block';
            
            // Add active class to clicked button
            event.target.classList.add('active');
            event.target.style.borderBottomColor = 'var(--primary-color)';
            event.target.style.color = 'var(--primary-color)';
        }
        
        // Form validation
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const newPass = document.getElementById('newPassword').value;
            const confirmPass = document.getElementById('confirmPassword').value;
            
            if(newPass !== confirmPass) {
                e.preventDefault();
                alert('❌ Passwords do not match! Please check and try again.');
                return false;
            }
            
            if(newPass.length < 6) {
                e.preventDefault();
                alert('❌ Password must be at least 6 characters long!');
                return false;
            }
            
            return confirm('🔐 Are you sure you want to reset this user\'s password?');
        });
    </script>
    <style>
        .tabs {
            display: flex;
            gap: 0.5rem;
            border-bottom: 2px solid var(--border-color);
        }
        .tab-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            background: transparent;
            cursor: pointer;
            font-weight: 600;
            color: var(--text-secondary);
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        .tab-btn:hover {
            color: var(--primary-color);
        }
        .tab-btn.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }
    </style>
</body>
</html>
<?php $con->close(); ?>
