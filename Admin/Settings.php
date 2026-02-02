<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Dashboard</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .setting-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-decoration: none;
            display: block;
            position: relative;
            overflow: hidden;
        }
        
        .setting-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        
        .setting-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            border-color: var(--primary-color);
        }
        
        .setting-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.2);
        }
        
        .setting-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.75rem;
        }
        
        .setting-description {
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        .setting-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1rem;
        }
        
        .setting-action::after {
            content: '→';
            transition: transform 0.3s ease;
        }
        
        .setting-card:hover .setting-action::after {
            transform: translateX(5px);
        }
        
        .page-header-modern {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2);
        }
        
        .page-header-modern h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2.2rem;
            font-weight: 800;
            color: white;
        }
        
        .page-header-modern p {
            margin: 0;
            opacity: 0.95;
            font-size: 1.05rem;
            color: white;
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php 
        $pageTitle = 'Settings';
        include 'header-component.php'; 
        ?>

        <div class="admin-content">
            <div class="page-header-modern">
                <h1>⚙️ System Settings</h1>
                <p>Manage system configuration, security, and maintenance</p>
            </div>

            <div class="settings-grid">
                <!-- Security Logs -->
                <a href="SecurityLogs.php" class="setting-card">
                    <div class="setting-icon">🔒</div>
                    <div class="setting-title">Security Logs</div>
                    <div class="setting-description">
                        View and monitor system security logs, login attempts, and user activities
                    </div>
                    <div class="setting-action">
                        View Security Logs
                    </div>
                </a>

                <!-- Reset Password -->
                <a href="ResetPassword.php" class="setting-card">
                    <div class="setting-icon">🔑</div>
                    <div class="setting-title">Reset Password</div>
                    <div class="setting-description">
                        Change your administrator password and manage account security
                    </div>
                    <div class="setting-action">
                        Reset Password
                    </div>
                </a>

                <!-- Database Backup -->
                <a href="DatabaseBackup.php" class="setting-card">
                    <div class="setting-icon">💾</div>
                    <div class="setting-title">Database Backup</div>
                    <div class="setting-description">
                        Create and manage database backups to protect your data
                    </div>
                    <div class="setting-action">
                        Manage Backups
                    </div>
                </a>

                <!-- System Settings -->
                <a href="SystemSettings.php" class="setting-card">
                    <div class="setting-icon">⚙️</div>
                    <div class="setting-title">System Configuration</div>
                    <div class="setting-description">
                        Configure system-wide settings, preferences, and parameters
                    </div>
                    <div class="setting-action">
                        Configure System
                    </div>
                </a>

                <!-- Profile Settings -->
                <a href="EditProfile.php" class="setting-card">
                    <div class="setting-icon">👤</div>
                    <div class="setting-title">Profile Settings</div>
                    <div class="setting-description">
                        Update your personal information and account details
                    </div>
                    <div class="setting-action">
                        Edit Profile
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js?v=<?php echo time(); ?>"></script>
</body>
</html>
