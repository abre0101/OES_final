<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
$pageTitle = "Change Password";

$message = '';
$messageType = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Get instructor ID from session
    $instId = $_SESSION['ID'];
    
    // Verify current password
    $stmt = $con->prepare("SELECT password FROM instructors WHERE instructor_id = ?");
    $stmt->bind_param("s", $instId);
    $stmt->execute();
    $result = $stmt->get_result();
    $instructor = $result->fetch_assoc();
    $stmt->close();
    
    if($instructor && $instructor['password'] == $currentPassword) {
        // Check if new passwords match
        if($newPassword == $confirmPassword) {
            // Update password
            $updateStmt = $con->prepare("UPDATE instructors SET password = ? WHERE instructor_id = ?");
            $updateStmt->bind_param("ss", $newPassword, $instId);
            
            if($updateStmt->execute()) {
                $message = 'Password changed successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error updating password. Please try again.';
                $messageType = 'error';
            }
            $updateStmt->close();
        } else {
            $message = 'New passwords do not match!';
            $messageType = 'error';
        }
    } else {
        $message = 'Current password is incorrect!';
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Instructor</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header">
                <h1>🔒 Change Password</h1>
                <p>Update your account password</p>
            </div>

            <?php if($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 2rem; padding: 1.25rem; border-radius: var(--radius-lg); background: <?php echo $messageType == 'success' ? 'rgba(40, 167, 69, 0.1)' : 'rgba(220, 53, 69, 0.1)'; ?>; border-left: 4px solid <?php echo $messageType == 'success' ? 'var(--success-color)' : '#dc3545'; ?>;">
                <strong><?php echo $messageType == 'success' ? '✓' : '✗'; ?></strong> <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <div class="card" style="max-width: 600px;">
                <div class="card-header">
                    <h3 class="card-title">Change Your Password</h3>
                </div>
                <div style="padding: 2rem;">
                    <form method="POST" onsubmit="return validatePasswordForm()">
                        <div class="form-group">
                            <label>Current Password *</label>
                            <input type="password" name="current_password" id="current_password" class="form-control" required placeholder="Enter your current password">
                        </div>

                        <div class="form-group">
                            <label>New Password *</label>
                            <input type="password" name="new_password" id="new_password" class="form-control" required placeholder="Enter new password" minlength="6">
                            <small style="color: var(--text-secondary);">Password must be at least 6 characters long</small>
                        </div>

                        <div class="form-group">
                            <label>Confirm New Password *</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required placeholder="Confirm new password">
                            <small id="passwordMatchError" style="color: #dc3545; display: none;">Passwords do not match</small>
                        </div>

                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" id="showPasswords" onclick="togglePasswordVisibility()">
                                <span>Show passwords</span>
                            </label>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                🔒 Change Password
                            </button>
                            <a href="Settings.php" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>

                    <!-- Password Security Tips -->
                    <div style="margin-top: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: var(--radius-md); border-left: 4px solid var(--secondary-color);">
                        <h4 style="margin: 0 0 1rem 0; color: var(--primary-color);">Password Security Tips</h4>
                        <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-secondary);">
                            <li>Use at least 6 characters</li>
                            <li>Include a mix of letters, numbers, and symbols</li>
                            <li>Avoid using personal information</li>
                            <li>Don't reuse passwords from other accounts</li>
                            <li>Change your password regularly</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function validatePasswordForm() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const errorElement = document.getElementById('passwordMatchError');
            
            if (newPassword !== confirmPassword) {
                errorElement.style.display = 'block';
                return false;
            }
            
            if (newPassword.length < 6) {
                alert('Password must be at least 6 characters long');
                return false;
            }
            
            return true;
        }
        
        // Real-time password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            const errorElement = document.getElementById('passwordMatchError');
            
            if (confirmPassword !== '' && newPassword !== confirmPassword) {
                errorElement.style.display = 'block';
            } else {
                errorElement.style.display = 'none';
            }
        });
        
        function togglePasswordVisibility() {
            const checkbox = document.getElementById('showPasswords');
            const passwordFields = [
                document.getElementById('current_password'),
                document.getElementById('new_password'),
                document.getElementById('confirm_password')
            ];
            
            passwordFields.forEach(field => {
                field.type = checkbox.checked ? 'text' : 'password';
            });
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
