<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$Id = $_GET['InstId'] ?? $_SESSION['ID'];
$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
$pageTitle = "Edit Profile";

// Get instructor details
$sql = "SELECT * FROM instructors WHERE instructor_id='".$con->real_escape_string($Id)."'";
$result = $con->query($sql);
$instructor = $result->fetch_assoc();

if(!$instructor) {
    header("Location: Profile.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Instructor</title>
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
                <h1>👤 Edit Profile</h1>
                <p>Update your account information</p>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Profile Information</h3>
                </div>
                <div style="padding: 2rem;">
                    <!-- Current Information Display -->
                    <div style="background: var(--bg-light); padding: 2rem; border-radius: var(--radius-lg); margin-bottom: 2rem; border-left: 4px solid var(--primary-color);">
                        <h3 style="margin: 0 0 1.5rem 0; color: var(--primary-color);">Current Information</h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                            <div>
                                <strong style="color: var(--text-secondary); display: block; margin-bottom: 0.5rem;">Instructor ID:</strong>
                                <p style="margin: 0; font-size: 1.1rem; font-weight: 600;"><?php echo htmlspecialchars($instructor['instructor_id']); ?></p>
                            </div>
                            <div>
                                <strong style="color: var(--text-secondary); display: block; margin-bottom: 0.5rem;">Name:</strong>
                                <p style="margin: 0; font-size: 1.1rem; font-weight: 600;"><?php echo htmlspecialchars($instructor['full_name']); ?></p>
                            </div>
                            <div>
                                <strong style="color: var(--text-secondary); display: block; margin-bottom: 0.5rem;">Email:</strong>
                                <p style="margin: 0; font-size: 1.1rem; font-weight: 600;"><?php echo htmlspecialchars($instructor['email']); ?></p>
                            </div>
                            <div>
                                <strong style="color: var(--text-secondary); display: block; margin-bottom: 0.5rem;">Department:</strong>
                                <p style="margin: 0; font-size: 1.1rem; font-weight: 600;"><?php echo htmlspecialchars($instructor['department_name']); ?></p>
                            </div>
                            <div>
                                <strong style="color: var(--text-secondary); display: block; margin-bottom: 0.5rem;">Status:</strong>
                                <p style="margin: 0; font-size: 1.1rem; font-weight: 600;">
                                    <span style="color: var(--success-color);"><?php echo htmlspecialchars($instructor['is_active']); ?></span>
                                </p>
                            </div>
                            <div>
                                <strong style="color: var(--text-secondary); display: block; margin-bottom: 0.5rem;">Current Username:</strong>
                                <p style="margin: 0; font-size: 1.1rem; font-weight: 600;"><?php echo htmlspecialchars($instructor['username']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Update Form -->
                    <form method="post" action="UpdateProfile.php?Id=<?php echo $instructor['instructor_id']; ?>" onsubmit="return validateForm()">
                        <h3 style="margin: 0 0 1.5rem 0; color: var(--primary-color);">Update Credentials</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>New Username</label>
                                <input type="text" name="txtUser" id="txtUser" class="form-control" 
                                       placeholder="Enter new username" 
                                       value="<?php echo htmlspecialchars($instructor['username']); ?>" required>
                                <small style="color: var(--text-secondary);">Leave as is if you don't want to change</small>
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="txtPass" id="txtPass" class="form-control" 
                                       placeholder="Enter new password">
                                <small style="color: var(--text-secondary);">Leave empty if you don't want to change</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="txtPassConfirm" id="txtPassConfirm" class="form-control" 
                                       placeholder="Confirm new password">
                                <small id="passwordError" style="color: #dc3545; display: none;">Passwords do not match</small>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                💾 Update Profile
                            </button>
                            <a href="Profile.php" class="btn btn-secondary">
                                ← Back to Profile
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function validateForm() {
            const password = document.getElementById('txtPass').value;
            const confirmPassword = document.getElementById('txtPassConfirm').value;
            const passwordError = document.getElementById('passwordError');
            
            // If password field is not empty, check if they match
            if (password !== '' && password !== confirmPassword) {
                passwordError.style.display = 'block';
                return false;
            }
            
            passwordError.style.display = 'none';
            return true;
        }
        
        // Real-time password match validation
        document.getElementById('txtPassConfirm').addEventListener('input', function() {
            const password = document.getElementById('txtPass').value;
            const confirmPassword = this.value;
            const passwordError = document.getElementById('passwordError');
            
            if (password !== '' && confirmPassword !== '' && password !== confirmPassword) {
                passwordError.style.display = 'block';
            } else {
                passwordError.style.display = 'none';
            }
        });
    </script>
</body>
</html>
<?php $con->close(); ?>
