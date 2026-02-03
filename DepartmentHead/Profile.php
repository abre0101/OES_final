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

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "My Profile";
$ID = $_SESSION['ID'];

// Get department head details
$query = $con->prepare("SELECT dh.*, d.department_name 
    FROM department_heads dh
    LEFT JOIN departments d ON dh.department_id = d.department_id
    WHERE dh.department_head_id = ?");
$query->bind_param("i", $ID);
$query->execute();
$profile = $query->get_result()->fetch_assoc();

if(!$profile) {
    die("Profile not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        .profile-card { background: white; border-radius: 12px; padding: 2.5rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); margin-bottom: 2rem; }
        .profile-header { text-align: center; padding: 2rem; background: linear-gradient(135deg, #003366 0%, #0055aa 100%); border-radius: 12px; color: white; margin-bottom: 2rem; }
        .profile-avatar { width: 120px; height: 120px; border-radius: 50%; background: white; color: #003366; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 700; margin: 0 auto 1rem; }
        .profile-name { font-size: 1.8rem; font-weight: 700; margin: 0; }
        .profile-role { font-size: 1rem; opacity: 0.9; margin-top: 0.5rem; }
        .profile-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .info-item { padding: 1.5rem; background: #f8f9fa; border-radius: 8px; }
        .info-label { font-size: 0.85rem; color: #6c757d; margin-bottom: 0.5rem; font-weight: 600; text-transform: uppercase; }
        .info-value { font-size: 1.1rem; color: #003366; font-weight: 600; }
        .btn { padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-block; transition: all 0.3s ease; }
        .btn-primary { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3); }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header">
                <h1>👤 My Profile</h1>
                <p>View and manage your profile information</p>
            </div>

            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($profile['full_name'], 0, 1)); ?>
                    </div>
                    <h2 class="profile-name"><?php echo htmlspecialchars($profile['full_name']); ?></h2>
                    <p class="profile-role">Department Head - <?php echo htmlspecialchars($profile['department_name'] ?? 'N/A'); ?></p>
                </div>

                <div class="profile-info">
                    <div class="info-item">
                        <div class="info-label">Department Head ID</div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['head_code']); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Username</div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['username']); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['email'] ?? 'Not set'); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Phone</div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['phone'] ?? 'Not set'); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Gender</div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['gender'] ?? 'Not set'); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Account Status</div>
                        <div class="info-value">
                            <?php echo $profile['is_active'] ? '<span style="color: #28a745;">✓ Active</span>' : '<span style="color: #dc3545;">✗ Inactive</span>'; ?>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 2rem; text-align: center;">
                    <a href="EditProfile.php" class="btn btn-primary">
                        ✏️ Edit Profile
                    </a>
                    <a href="ChangePassword.php" class="btn btn-primary" style="margin-left: 1rem;">
                        🔒 Change Password
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>