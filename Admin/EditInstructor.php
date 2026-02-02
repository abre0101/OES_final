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

// Get instructor data
$Id = $_GET['Id'];
$sql = "SELECT i.*, d.department_name 
        FROM instructors i 
        LEFT JOIN departments d ON i.department_id = d.department_id 
        WHERE i.instructor_id='".$Id."'";
$result = $con->query($sql);

if($row = $result->fetch_array()) {
    $Inst_ID = $row['instructor_id'];
    $Inst_Name = $row['full_name'];
    $Email = $row['email'];
    $UserName = $row['username'];
    $Department = isset($row['department_name']) ? $row['department_name'] : 'N/A';
    $DepartmentId = isset($row['department_id']) ? $row['department_id'] : '';
    $is_active = $row['is_active'];
    $Status = ($is_active == 1) ? 'Active' : 'Inactive';
} else {
    header("Location: Instructor.php");
    exit();
}

// Get departments for dropdown
$query_dept = "SELECT * FROM departments";
$result_dept = $con->query($query_dept);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Instructor - Admin Dashboard</title>
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
        
        .form-group select {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
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
                <h1>✏️ Edit Instructor Information</h1>
                <p>UPDATE instructors details</p>
            </div>

            <div class="edit-container">
                <div class="info-section">
                    <h3>📋 Current Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Instructor ID</span>
                            <span class="info-value"><?php echo $Inst_ID; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Name</span>
                            <span class="info-value"><?php echo $Inst_Name; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?php echo $Email; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Username</span>
                            <span class="info-value"><?php echo $UserName; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Department</span>
                            <span class="info-value"><?php echo $Department; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">is_active</span>
                            <span class="info-value"><?php echo $Status; ?></span>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>🔄 Update Information</h3>
                    <form method="post" action="UpdateInstructor.php?ID=<?php echo $Inst_ID;?>">
                        <div class="form-group">
                            <label for="cmbDept">Change Department:</label>
                            <select name="cmbDept" id="cmbDept">
                                <option value="<?php echo $DepartmentId; ?>"><?php echo $Department; ?> (Current)</option>
                                <?php
                                while($row_dept = $result_dept->fetch_array()) {
                                    if(isset($row_dept['department_id']) && $row_dept['department_id'] != $DepartmentId) {
                                        echo '<option value="'.$row_dept['department_id'].'">'.$row_dept['department_name'].'</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="cmbStatus">Change Status:</label>
                            <select name="cmbStatus" id="cmbStatus">
                                <option value="<?php echo $is_active; ?>"><?php echo $Status; ?> (Current)</option>
                                <?php 
                                if($is_active != 1) echo "<option value='1'>Active</option>";
                                if($is_active != 0) echo "<option value='0'>Inactive</option>";
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                ✓ UPDATE instructors
                            </button>
                            <a href="Instructor.php" class="btn btn-secondary">
                                ← Back to Instructors
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
