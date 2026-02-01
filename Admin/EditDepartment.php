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

// Get department data
$ID = $_GET['ID'];
$sql = "select * FROM departments where deptno='".$ID."'";
$result = $con->query($sql);

if($row = $result->fetch_array()) {
    $Id = $row['deptno'];
    $Name = $row['department_name'];
    $Faculty = $row['faculty_name'];
} else {
    header("Location: Department.php");
    exit();
}

// Get faculties for dropdown
$query_faculty = "SELECT * FROM faculties";
$result_faculty = $con->query($query_faculty);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Department - Admin Dashboard</title>
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
            max-width: 600px;
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
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--text-secondary);
        }
        
        .info-value {
            font-weight: 700;
            color: var(--primary-color);
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
        .form-group select {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input[type="text"]:focus,
        .form-group select:focus {
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
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header">
                <h1>✏️ Edit Department Information</h1>
                <p>UPDATE departments details</p>
            </div>

            <div class="edit-container">
                <div class="info-section">
                    <h3>📋 Current Information</h3>
                    <div class="info-item">
                        <span class="info-label">Department ID</span>
                        <span class="info-value"><?php echo $Id; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Department Name</span>
                        <span class="info-value"><?php echo $Name; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">College</span>
                        <span class="info-value"><?php echo $Faculty; ?></span>
                    </div>
                </div>

                <div class="form-section">
                    <h3>🔄 Update Information</h3>
                    <form method="post" action="UpdateDepartment.php?ID=<?php echo $Id;?>">
                        <div class="form-group">
                            <label for="txtID">New Department Name:</label>
                            <input type="text" name="txtID" id="txtID" required placeholder="Enter new department name" value="<?php echo $Name; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="cmbFaculty">Change College:</label>
                            <select name="cmbFaculty" id="cmbFaculty">
                                <option value="<?php echo $Faculty; ?>"><?php echo $Faculty; ?> (Current)</option>
                                <?php
                                while($row_faculty = $result_faculty->fetch_array()) {
                                    if($row_faculty['faculty_name'] != $Faculty) {
                                        echo '<option value="'.$row_faculty['faculty_name'].'">'.$row_faculty['faculty_name'].'</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                ✓ UPDATE departments
                            </button>
                            <a href="Department.php" class="btn btn-secondary">
                                ← Back to Departments
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
