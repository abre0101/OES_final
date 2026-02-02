<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../index.php");
    exit();
}

// Database connection
$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$Id = $_SESSION['ID'];
$sql = "select * FROM administrators where admin_id='".$Id."'";
$result = $con->query($sql);
$row = $result->fetch_array();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Dashboard</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .page-header {
            text-align: center;
        }
        
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .profile-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            padding: 2rem;
            text-align: center;
            color: white;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-color) 0%, #d4af37 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 900;
            color: var(--primary-dark);
            margin: 0 auto 1rem;
            border: 5px solid white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .profile-name {
            font-size: 1.8rem;
            font-weight: 800;
            margin: 0;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .profile-role {
            font-size: 1.1rem;
            opacity: 0.95;
            margin-top: 0.5rem;
            color: var(--secondary-color);
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        .profile-body {
            padding: 2rem;
        }
        
        .profile-section {
            margin-bottom: 2rem;
        }
        
        .profile-section h3 {
            color: var(--primary-color);
            font-size: 1.2rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .profile-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        
        .profile-info-item {
            display: flex;
            flex-direction: column;
        }
        
        .profile-info-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .profile-info-value {
            font-size: 1.1rem;
            color: var(--primary-color);
            font-weight: 700;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: var(--radius-md);
            border-left: 4px solid var(--secondary-color);
        }
        
        .profile-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e0e0e0;
        }
        
        @media (max-width: 768px) {
            .profile-info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header">
                <h1>👤 My Profile</h1>
                <p>View and manage your account information</p>
            </div>

            <div class="profile-container">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php echo strtoupper(substr($row['full_name'], 0, 1)); ?>
                        </div>
                        <h2 class="profile-name"><?php echo htmlspecialchars($row['full_name']); ?></h2>
                        <p class="profile-role">System Administrator</p>
                    </div>
                    
                    <div class="profile-body">
                        <div class="profile-section">
                            <h3>📋 Account Information</h3>
                            <div class="profile-info-grid">
                                <div class="profile-info-item">
                                    <span class="profile-info-label">Admin ID</span>
                                    <div class="profile-info-value"><?php echo $row['admin_id']; ?></div>
                                </div>
                                
                                <div class="profile-info-item">
                                    <span class="profile-info-label">Full Name</span>
                                    <div class="profile-info-value"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                </div>
                                
                                <div class="profile-info-item">
                                    <span class="profile-info-label">Email Address</span>
                                    <div class="profile-info-value"><?php echo htmlspecialchars($row['email']); ?></div>
                                </div>
                                
                                <div class="profile-info-item">
                                    <span class="profile-info-label">Username</span>
                                    <div class="profile-info-value"><?php echo htmlspecialchars($row['username']); ?></div>
                                </div>
                                <div class="profile-info-item">
                                    <span class="profile-info-label">Username</span>
                                    <div class="profile-info-value"><?php echo $row['username']; ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="profile-actions">
                            <a href="EditProfile.php?Id=<?php echo $Id;?>" class="btn btn-primary">
                                ✏️ Edit Profile
                            </a>
                            <a href="index.php" class="btn btn-secondary">
                                ← Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit',
                hour12: true 
            });
            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }
        updateTime();
        setInterval(updateTime, 1000);
    </script>
</body>
</html>
<?php 
$con->close();
?>
