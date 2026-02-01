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
    <title>System Settings - Admin Dashboard</title>
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
            background: linear-gradient(135deg, rgba(0, 51, 102, 0.05) 0%, rgba(0, 85, 170, 0.05) 100%);
            padding: 2rem;
            border-radius: var(--radius-lg);
            border: 2px solid rgba(0, 51, 102, 0.1);
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
        
        .page-subtitle {
            margin: 0;
            color: var(--text-secondary);
            font-size: 1.05rem;
            font-weight: 500;
        }
        
        .settings-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .settings-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .settings-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            border-color: var(--secondary-color);
        }
        
        .settings-card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }
        
        .settings-icon {
            font-size: 2.5rem;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border-radius: var(--radius-md);
            color: white;
        }
        
        .settings-card-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }
        
        .settings-card-desc {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        
        .settings-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .settings-item:last-child {
            border-bottom: none;
        }
        
        .settings-item-label {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .settings-item-value {
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .toggle-switch {
            position: relative;
            width: 50px;
            height: 26px;
            background: #ccc;
            border-radius: 26px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .toggle-switch.active {
            background: var(--success-color);
        }
        
        .toggle-switch::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: white;
            top: 3px;
            left: 3px;
            transition: left 0.3s;
        }
        
        .toggle-switch.active::after {
            left: 27px;
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php 
        $pageTitle = 'System Settings';
        include 'header-component.php'; 
        ?>

        <div class="admin-content">
            <div class="page-header-actions">
                <div class="page-title-section">
                    <h1><span>⚙️</span> System Settings</h1>
                    <p class="page-subtitle">Configure and manage your examination system</p>
                </div>
            </div>

            <div class="settings-container">
                <div class="settings-grid">
                    <!-- General Settings -->
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="settings-icon">🔧</div>
                            <h3 class="settings-card-title">General Settings</h3>
                        </div>
                        <p class="settings-card-desc">Basic system configuration and preferences</p>
                        <div class="settings-item">
                            <span class="settings-item-label">System Name</span>
                            <span class="settings-item-value">OES</span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-item-label">Institution</span>
                            <span class="settings-item-value">Debre Markos University</span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-item-label">Academic Year</span>
                            <span class="settings-item-value">2025/2026</span>
                        </div>
                        <div style="margin-top: 1.5rem;">
                            <a href="#" class="btn btn-primary btn-sm">Edit Settings</a>
                        </div>
                    </div>

                    <!-- Database Settings -->
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="settings-icon">💾</div>
                            <h3 class="settings-card-title">Database</h3>
                        </div>
                        <p class="settings-card-desc">Database connection and backup settings</p>
                        <div class="settings-item">
                            <span class="settings-item-label">Database is_active</span>
                            <span class="settings-item-value" style="color: var(--success-color); font-weight: 700;">✓ Connected</span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-item-label">Last Backup</span>
                            <span class="settings-item-value">Today, 2:30 AM</span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-item-label">Auto Backup</span>
                            <div class="toggle-switch active" onclick="this.classList.toggle('active')"></div>
                        </div>
                        <div style="margin-top: 1.5rem;">
                            <a href="#" class="btn btn-success btn-sm">Backup Now</a>
                        </div>
                    </div>

                    <!-- Security Settings -->
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="settings-icon">🔒</div>
                            <h3 class="settings-card-title">Security</h3>
                        </div>
                        <p class="settings-card-desc">Security and authentication settings</p>
                        <div class="settings-item">
                            <span class="settings-item-label">Two-Factor Auth</span>
                            <div class="toggle-switch" onclick="this.classList.toggle('active')"></div>
                        </div>
                        <div class="settings-item">
                            <span class="settings-item-label">Session Timeout</span>
                            <span class="settings-item-value">30 minutes</span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-item-label">Password Policy</span>
                            <span class="settings-item-value">Strong</span>
                        </div>
                        <div style="margin-top: 1.5rem;">
                            <a href="#" class="btn btn-warning btn-sm">Configure</a>
                        </div>
                    </div>

                    <!-- Email Settings -->
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="settings-icon">📧</div>
                            <h3 class="settings-card-title">Email</h3>
                        </div>
                        <p class="settings-card-desc">Email notifications and SMTP configuration</p>
                        <div class="settings-item">
                            <span class="settings-item-label">Email Notifications</span>
                            <div class="toggle-switch active" onclick="this.classList.toggle('active')"></div>
                        </div>
                        <div class="settings-item">
                            <span class="settings-item-label">SMTP is_active</span>
                            <span class="settings-item-value" style="color: var(--success-color); font-weight: 700;">✓ Configured</span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-item-label">From Email</span>
                            <span class="settings-item-value">noreply@dmu.edu</span>
                        </div>
                        <div style="margin-top: 1.5rem;">
                            <a href="#" class="btn btn-primary btn-sm">Test Email</a>
                        </div>
                    </div>

                    <!-- Exam Settings -->
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="settings-icon">📝</div>
                            <h3 class="settings-card-title">Examination</h3>
                        </div>
                        <p class="settings-card-desc">Exam configuration and rules</p>
                        <div class="settings-item">
                            <span class="settings-item-label">Auto Submit</span>
                            <div class="toggle-switch active" onclick="this.classList.toggle('active')"></div>
                        </div>
                        <div class="settings-item">
                            <span class="settings-item-label">Shuffle Questions</span>
                            <div class="toggle-switch active" onclick="this.classList.toggle('active')"></div>
                        </div>
                        <div class="settings-item">
                            <span class="settings-item-label">Show Results</span>
                            <div class="toggle-switch" onclick="this.classList.toggle('active')"></div>
                        </div>
                        <div style="margin-top: 1.5rem;">
                            <a href="GradingSettings.php" class="btn btn-primary btn-sm">Configure Grading</a>
                        </div>
                    </div>

                    <!-- System Maintenance -->
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div class="settings-icon">🛠️</div>
                            <h3 class="settings-card-title">Maintenance</h3>
                        </div>
                        <p class="settings-card-desc">System maintenance and optimization</p>
                        <div class="settings-item">
                            <span class="settings-item-label">Maintenance Mode</span>
                            <div class="toggle-switch" onclick="this.classList.toggle('active')"></div>
                        </div>
                        <div class="settings-item">
                            <span class="settings-item-label">Cache is_active</span>
                            <span class="settings-item-value">Enabled</span>
                        </div>
                        <div class="settings-item">
                            <span class="settings-item-label">System Version</span>
                            <span class="settings-item-value">v2.0.0</span>
                        </div>
                        <div style="margin-top: 1.5rem;">
                            <a href="#" class="btn btn-danger btn-sm">Clear Cache</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
