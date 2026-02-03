<?php
require_once(__DIR__ . "/../utils/session_manager.php");
SessionManager::startSession('DepartmentHead');

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'DepartmentHead'){
    SessionManager::destroySession();
    header("Location:../auth/institute-login.php");
    exit();
}

$pageTitle = "Settings";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            max-width: 900px;
            margin: 0 auto;
        }
        
        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .settings-card {
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            cursor: pointer;
            text-align: center;
            border: 2px solid transparent;
        }
        
        .settings-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
            border-color: #667eea;
        }
        
        .settings-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            display: block;
        }
        
        .settings-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #003366;
            margin: 0 0 1rem 0;
        }
        
        .settings-description {
            color: #6c757d;
            font-size: 1rem;
            line-height: 1.6;
            margin: 0;
        }
        
        .settings-features {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0 0 0;
            text-align: left;
        }
        
        .settings-features li {
            padding: 0.5rem 0;
            color: #495057;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .settings-features li:before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
            font-size: 1.1rem;
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <!-- Page Header -->
            <div style="margin-bottom: 3rem; text-align: center;">
                <h1 style="font-size: 2.5rem; margin: 0 0 0.5rem 0; color: #003366; font-weight: 700;">
                    ⚙️ Settings
                </h1>
                <p style="margin: 0; color: #6c757d; font-size: 1.1rem;">
                    Manage your profile and security settings
                </p>
            </div>

            <!-- Settings Cards -->
            <div class="settings-grid">
                <!-- Profile Card -->
                <div class="settings-card" onclick="window.location.href='Profile.php'">
                    <span class="settings-icon">👤</span>
                    <h2 class="settings-title">My Profile</h2>
                    <p class="settings-description">
                        View and update your personal information, contact details, and account preferences.
                    </p>
                    <ul class="settings-features">
                        <li>View profile information</li>
                        <li>Update personal details</li>
                        <li>Manage contact information</li>
                        <li>Edit account preferences</li>
                    </ul>
                </div>

                <!-- Security Card -->
                <div class="settings-card" onclick="window.location.href='ChangePassword.php'">
                    <span class="settings-icon">🔒</span>
                    <h2 class="settings-title">Privacy & Security</h2>
                    <p class="settings-description">
                        Manage your password, security settings, and privacy preferences to keep your account safe.
                    </p>
                    <ul class="settings-features">
                        <li>Change password</li>
                        <li>Security settings</li>
                        <li>Login history</li>
                        <li>Privacy preferences</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
