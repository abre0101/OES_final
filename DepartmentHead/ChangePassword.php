<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Department Head session
SessionManager::startSession('DepartmentHead');

// Check if user is logged in
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

// Validate user role
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'DepartmentHead'){
    SessionManager::destroySession();
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
$pageTitle = "Change Password";

$message = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate
    if(empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif(strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Verify current password
        $stmt = $con->prepare("SELECT password FROM exam_committee_members WHERE committee_member_id=?");
        $stmt->bind_param("s", $_SESSION['ID']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if($user && $user['password'] === $current_password) {
            // Update password
            $update = $con->prepare("UPDATE exam_committee_members SET password=?, last_password_change=NOW() WHERE committee_member_id=?");
            $update->bind_param("ss", $new_password, $_SESSION['ID']);
            
            if($update->execute()) {
                $message = 'Password changed successfully!';
            } else {
                $error = 'Failed to update password';
            }
            $update->close();
        } else {
            $error = 'Current password is incorrect';
        }
        $stmt->close();
    }
}

// Get last password change
$last_change = null; // Will be available after migration
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .password-strength {
            height: 8px;
            border-radius: var(--radius-sm);
            background: var(--bg-light);
            margin-top: 0.5rem;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            transition: all 0.3s ease;
        }
        
        .strength-weak { background: #dc3545; width: 33%; }
        .strength-medium { background: #ffc107; width: 66%; }
        .strength-strong { background: #28a745; width: 100%; }
        
        .password-requirements {
            background: var(--bg-light);
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-top: 1rem;
        }
        
        .requirement {
            padding: 0.5rem 0;
            color: var(--text-secondary);
        }
        
        .requirement.met {
            color: var(--success-color);
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header">
                <h1>🔒 Change Password</h1>
                <p>Update your account security</p>
            </div>

            <div style="max-width: 600px; margin: 0 auto;">
                <!-- Last Changed Info -->
                <?php if($last_change && $last_change['last_password_change']): ?>
                <div style="background: rgba(23, 162, 184, 0.1); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 2rem; border-left: 4px solid var(--accent-teal);">
                    <strong>Last changed:</strong> <?php echo date('F d, Y - h:i A', strtotime($last_change['last_password_change'])); ?>
                </div>
                <?php endif; ?>

                <!-- Messages -->
                <?php if($message): ?>
                <div style="background: rgba(40, 167, 69, 0.1); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 2rem; border-left: 4px solid var(--success-color); color: var(--success-color);">
                    <strong>✓ <?php echo $message; ?></strong>
                </div>
                <?php endif; ?>
                
                <?php if($error): ?>
                <div style="background: rgba(220, 53, 69, 0.1); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 2rem; border-left: 4px solid #dc3545; color: #dc3545;">
                    <strong>✗ <?php echo $error; ?></strong>
                </div>
                <?php endif; ?>

                <!-- Change Password Form -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Update Password</h3>
                    </div>
                    <div style="padding: 2rem;">
                        <form method="POST" id="passwordForm">
                            <div class="form-group">
                                <label>Current Password</label>
                                <div style="position: relative;">
                                    <input type="password" name="current_password" id="currentPassword" class="form-control" required>
                                    <button type="button" onclick="togglePassword('currentPassword')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.2rem;">
                                        👁️
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>New Password</label>
                                <div style="position: relative;">
                                    <input type="password" name="new_password" id="newPassword" class="form-control" required oninput="checkPasswordStrength()">
                                    <button type="button" onclick="togglePassword('newPassword')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.2rem;">
                                        👁️
                                    </button>
                                </div>
                                <div class="password-strength">
                                    <div class="password-strength-bar" id="strengthBar"></div>
                                </div>
                                <div id="strengthText" style="font-size: 0.85rem; margin-top: 0.5rem; font-weight: 600;"></div>
                            </div>
                            
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <div style="position: relative;">
                                    <input type="password" name="confirm_password" id="confirmPassword" class="form-control" required>
                                    <button type="button" onclick="togglePassword('confirmPassword')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.2rem;">
                                        👁️
                                    </button>
                                </div>
                            </div>
                            
                            <div class="password-requirements">
                                <strong style="display: block; margin-bottom: 0.5rem;">Password Requirements:</strong>
                                <div class="requirement" id="req-length">✗ At least 6 characters</div>
                                <div class="requirement" id="req-uppercase">✗ At least one uppercase letter</div>
                                <div class="requirement" id="req-lowercase">✗ At least one lowercase letter</div>
                                <div class="requirement" id="req-number">✗ At least one number</div>
                            </div>
                            
                            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                                <button type="submit" class="btn btn-primary" style="flex: 1;">
                                    🔒 Update Password
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Security Tips -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">🛡️ Security Tips</h3>
                    </div>
                    <div style="padding: 1.5rem;">
                        <ul style="line-height: 2;">
                            <li>Use a unique password for this account</li>
                            <li>Don't share your password with anyone</li>
                            <li>Change your password regularly (every 3-6 months)</li>
                            <li>Use a mix of letters, numbers, and symbols</li>
                            <li>Avoid using personal information in passwords</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            field.type = field.type === 'password' ? 'text' : 'password';
        }
        
        function checkPasswordStrength() {
            const password = document.getElementById('newPassword').value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            
            // Check requirements
            const hasLength = password.length >= 6;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            
            document.getElementById('req-length').className = hasLength ? 'requirement met' : 'requirement';
            document.getElementById('req-length').textContent = (hasLength ? '✓' : '✗') + ' At least 6 characters';
            
            document.getElementById('req-uppercase').className = hasUppercase ? 'requirement met' : 'requirement';
            document.getElementById('req-uppercase').textContent = (hasUppercase ? '✓' : '✗') + ' At least one uppercase letter';
            
            document.getElementById('req-lowercase').className = hasLowercase ? 'requirement met' : 'requirement';
            document.getElementById('req-lowercase').textContent = (hasLowercase ? '✓' : '✗') + ' At least one lowercase letter';
            
            document.getElementById('req-number').className = hasNumber ? 'requirement met' : 'requirement';
            document.getElementById('req-number').textContent = (hasNumber ? '✓' : '✗') + ' At least one number';
            
            if(hasLength) strength++;
            if(hasUppercase) strength++;
            if(hasLowercase) strength++;
            if(hasNumber) strength++;
            
            strengthBar.className = 'password-strength-bar';
            if(strength <= 1) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = 'Weak';
                strengthText.style.color = '#dc3545';
            } else if(strength <= 3) {
                strengthBar.classList.add('strength-medium');
                strengthText.textContent = 'Medium';
                strengthText.style.color = '#ffc107';
            } else {
                strengthBar.classList.add('strength-strong');
                strengthText.textContent = 'Strong';
                strengthText.style.color = '#28a745';
            }
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
