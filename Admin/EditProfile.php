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

$Id = $_GET['Id'];
$sql = "select * FROM administrators where admin_id='".$Id."'";
$result = $con->query($sql);

if($row = $result->fetch_array()) {
    $admin_id = $row['admin_id'];
    $Admin_Name = $row['full_name'];
    $Email = $row['email'];
    $UserName = $row['username'];
    $Password = $row['password'];
} else {
    header("Location: Profile.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Admin Dashboard</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .page-header {
            text-align: center;
        }
        
        .edit-container {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .info-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: var(--radius-md);
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary-color);
        }
        
        .info-section h3 {
            margin: 0 0 1rem 0;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            font-size: 1rem;
            color: var(--primary-color);
            font-weight: 700;
        }
        
        .form-section h3 {
            margin: 0 0 1.5rem 0;
            color: var(--primary-color);
            font-size: 1.2rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1rem;
        }
        
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid #e0e0e0;
        }
        
        @media (max-width: 768px) {
            .info-grid {
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
                <h1>✏️ Edit Profile</h1>
                <p>Update your account credentials</p>
            </div>

            <div class="edit-container">
                <div class="info-section">
                    <h3>📋 Current Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Admin ID</span>
                            <span class="info-value"><?php echo $admin_id; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Full Name</span>
                            <span class="info-value"><?php echo htmlspecialchars($Admin_Name); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?php echo $Email; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Current Username</span>
                            <span class="info-value"><?php echo $UserName; ?></span>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>🔄 Update Credentials</h3>
                    <form method="post" action="UpdateProfile.php?Id=<?php echo $Admin_ID;?>">
                        <div class="form-group">
                            <label for="txtUser">New Username:</label>
                            <input type="text" name="txtUser" id="txtUser" required placeholder="Enter new username" value="<?php echo $UserName; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="txtPass">New Password:</label>
                            <input type="password" name="txtPass" id="txtPass" required placeholder="Enter new password">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                ✓ Update Profile
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

    <script>
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit',
                hour12: true 
            });
            document.getElementById('currentTime').textContent = timeString;
        }
        updateTime();
        setInterval(updateTime, 1000);

        function toggleSidebar() {
            document.querySelector('.admin-sidebar').classList.toggle('open');
        }

        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.admin-sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 1024) {
                if (!sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
    </script>
</body>
</html>
<?php 
$con->close();
?>
