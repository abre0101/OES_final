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

// Get departments for dropdown
$query_Recordsetd = "SELECT * FROM departments";
$Recordsetd = $con->query($query_Recordsetd);
$row_Recordsetd = $Recordsetd->fetch_assoc();
$totalRows_Recordsetd = $Recordsetd->num_rows;

// Get student data
$student_id = $_GET['student_id'];
$stmt = $con->prepare("select * FROM students where Id=?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if($row = $result->fetch_array()) {
    $Id = $row['Id'];
    $Name = $row['Name'];
    $StudDept = $row['department_name'];
    $StudYear = $row['year'];
    $Semester = $row['semester'];
    $StudSex = $row['Sex'];
    $Email = $row['email'];
    $UserName = $row['username'];
    $Password = $row['password'];
    $is_active = $row['is_active'];
} else {
    header("Location: Student.php");
    exit();
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Admin Dashboard</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .page-header {
            text-align: center;
        }
        
        .page-header h1 {
            margin-bottom: 0.5rem;
        }
        
        .page-header p {
            margin: 0;
        }
        
        .edit-container {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 800px;
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
        
        .form-section {
            margin-top: 2rem;
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
                <h1>✏️ Edit Student Information</h1>
                <p>UPDATE students details and is_active</p>
            </div>

            <div class="edit-container">
                <!-- Current Information Section -->
                <div class="info-section">
                    <h3>📋 Current Student Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Student ID</span>
                            <span class="info-value"><?php echo $Id; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Student Name</span>
                            <span class="info-value"><?php echo $Name; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Gender</span>
                            <span class="info-value"><?php echo $StudSex; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Current Department</span>
                            <span class="info-value"><?php echo $StudDept; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Current Year</span>
                            <span class="info-value"><?php echo $StudYear; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Current Semester</span>
                            <span class="info-value"><?php echo $Semester; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Username</span>
                            <span class="info-value"><?php echo $UserName; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Current is_active</span>
                            <span class="info-value"><?php echo $Status; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Edit Form Section -->
                <div class="form-section">
                    <h3>🔄 UPDATE students Information</h3>
                    <form method="post" action="UpdateStudent.php?Id=<?php echo $Id;?>">
                        <div class="form-group">
                            <label for="cmbDep">Change Department:</label>
                            <select name="cmbDep" id="cmbDep">
                                <option value="<?php echo $StudDept; ?>"><?php echo $StudDept; ?> (Current)</option>
                                <?php
                                do {
                                    if($row_Recordsetd['department_name'] != $StudDept) {
                                ?>
                                <option value="<?php echo $row_Recordsetd['department_name']?>"><?php echo $row_Recordsetd['department_name']?></option>
                                <?php
                                    }
                                } while ($row_Recordsetd = $Recordsetd->fetch_assoc());
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="cmbYear">Change Year:</label>
                            <select name="cmbYear" id="cmbYear">
                                <option value="<?php echo $StudYear; ?>"><?php echo $StudYear; ?> (Current)</option>
                                <?php for($i = 1; $i <= 7; $i++) {
                                    if($i != $StudYear) {
                                        echo "<option value='$i'>$i</option>";
                                    }
                                } ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="cmbSem">Change Semester:</label>
                            <select name="cmbSem" id="cmbSem">
                                <option value="<?php echo $Semester; ?>"><?php echo $Semester; ?> (Current)</option>
                                <?php 
                                if($Semester != 1) echo "<option value='1'>1</option>";
                                if($Semester != 2) echo "<option value='2'>2</option>";
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="cmbStatus">Change Status:</label>
                            <select name="cmbStatus" id="cmbStatus">
                                <option value="<?php echo $Status; ?>"><?php echo $Status; ?> (Current)</option>
                                <?php 
                                if($is_active != 'Active') echo "<option value='Active'>Active</option>";
                                if($is_active != 'InActive') echo "<option value='InActive'>Inactive</option>";
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                ✓ UPDATE students
                            </button>
                            <a href="Student.php" class="btn btn-secondary">
                                ← Back to Students
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
