<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Administrator session
SessionManager::startSession('Administrator');

if(!isset($_SESSION['username'])){
    header("Location:../auth/staff-login.php");
    exit();
}

// Database connection
$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Get course data
$CourseId = $_GET['CourseId'];
$sql = "SELECT c.*, d.department_name, 
        GROUP_CONCAT(i.full_name SEPARATOR ', ') as instructor_names,
        GROUP_CONCAT(i.instructor_id SEPARATOR ',') as instructor_ids
        FROM courses c 
        LEFT JOIN departments d ON c.department_id = d.department_id 
        LEFT JOIN instructor_courses ic ON c.course_id = ic.course_id
        LEFT JOIN instructors i ON ic.instructor_id = i.instructor_id
        WHERE c.course_id='".$CourseId."'
        GROUP BY c.course_id";
$result = $con->query($sql);

if($row = $result->fetch_array()) {
    $Id = $row['course_id'];
    $Name = $row['course_name'];
    $Credit = isset($row['credit_hours']) ? $row['credit_hours'] : '';
    $Sem = $row['semester'];
    $Dept = isset($row['department_name']) ? $row['department_name'] : '';
    $DeptId = isset($row['department_id']) ? $row['department_id'] : '';
    $Instructor = isset($row['instructor_names']) ? $row['instructor_names'] : 'Not Assigned';
    $InstructorIds = isset($row['instructor_ids']) ? $row['instructor_ids'] : '';
} else {
    header("Location: Course.php");
    exit();
}

// Get departments and instructors for dropdowns
$query_dept = "SELECT * FROM departments";
$result_dept = $con->query($query_dept);

$query_inst = "SELECT * FROM instructors";
$result_inst = $con->query($query_inst);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - Admin Dashboard</title>
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
                <h1>✏️ Edit Course Information</h1>
                <p>UPDATE courses details</p>
            </div>

            <div class="edit-container">
                <div class="info-section">
                    <h3>📋 Current Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Course ID</span>
                            <span class="info-value"><?php echo $Id; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Course Name</span>
                            <span class="info-value"><?php echo $Name; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Credit Hour</span>
                            <span class="info-value"><?php echo $Credit; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Semester</span>
                            <span class="info-value"><?php echo $Sem; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Department</span>
                            <span class="info-value"><?php echo $Dept; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Instructor</span>
                            <span class="info-value"><?php echo $Instructor; ?></span>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>🔄 Update Information</h3>
                    <form method="post" action="UpdateCourse.php">
                        <input type="hidden" name="txtDeptID" value="<?php echo $Id; ?>">
                        
                        <div class="form-group">
                            <label for="txtCourseName">Course Name:</label>
                            <input type="text" name="txtCourseName" id="txtCourseName" required value="<?php echo $Name; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="txtCredit">Credit Hour:</label>
                            <input type="text" name="txtCredit" id="txtCredit" required value="<?php echo $Credit; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="cmbSem">Semester:</label>
                            <select name="cmbSem" id="cmbSem">
                                <option value="<?php echo $Sem; ?>"><?php echo $Sem; ?> (Current)</option>
                                <?php 
                                if($Sem != 1) echo "<option value='1'>1</option>";
                                if($Sem != 2) echo "<option value='2'>2</option>";
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="cmbDept">Department:</label>
                            <select name="cmbDept" id="cmbDept">
                                <option value="<?php echo $DeptId; ?>"><?php echo $Dept; ?> (Current)</option>
                                <?php
                                while($row_dept = $result_dept->fetch_array()) {
                                    if(isset($row_dept['department_name']) && $row_dept['department_id'] != $DeptId) {
                                        echo '<option value="'.$row_dept['department_id'].'">'.$row_dept['department_name'].'</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="cmbInst">Instructor:</label>
                            <select name="cmbInst" id="cmbInst">
                                <?php
                                // Get first instructor ID from the comma-separated list
                                $currentInstructorId = !empty($InstructorIds) ? explode(',', $InstructorIds)[0] : '';
                                ?>
                                <option value="<?php echo $currentInstructorId; ?>"><?php echo $Instructor; ?> (Current)</option>
                                <?php
                                while($row_inst = $result_inst->fetch_array()) {
                                    if($row_inst['instructor_id'] != $currentInstructorId) {
                                        echo '<option value="'.$row_inst['instructor_id'].'">'.$row_inst['full_name'].'</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                ✓ UPDATE courses
                            </button>
                            <a href="Course.php" class="btn btn-secondary">
                                ← Back to Courses
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
