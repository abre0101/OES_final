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

$query_Recordsetd = "SELECT * FROM departments ORDER BY department_name ASC";
$Recordsetd = $con->query($query_Recordsetd);
$departments = [];
if($Recordsetd->num_rows > 0) {
    while($row = $Recordsetd->fetch_assoc()) {
        $departments[] = $row;
    }
}

$query_Recordseti = "SELECT * FROM instructors ORDER BY full_name ASC";
$Recordseti = $con->query($query_Recordseti);
$instructors = [];
if($Recordseti->num_rows > 0) {
    while($row = $Recordseti->fetch_assoc()) {
        $instructors[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management - Admin Dashboard</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
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
        
        .page-title-section p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 1.05rem;
            font-weight: 500;
        }
        
        .btn-create-new {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1rem 2rem;
            border-radius: var(--radius-lg);
            text-decoration: none;
            font-weight: 700;
            font-size: 1.05rem;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3);
            border: none;
            cursor: pointer;
        }
        
        .btn-create-new:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 51, 102, 0.4);
            background: linear-gradient(135deg, var(--primary-dark) 0%, #001a33 100%);
        }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .course-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 2px solid #e8eef3;
            position: relative;
            overflow: hidden;
        }
        
        .course-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #6f42c1 0%, #007bff 100%);
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(111, 66, 193, 0.15);
            border-color: #6f42c1;
        }
        
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.25rem;
        }
        
        .course-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #6f42c1 0%, #007bff 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.2);
        }
        
        .course-credit {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            font-weight: 800;
            font-size: 0.9rem;
            box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
        }
        
        .course-id {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .course-name {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 1rem;
            line-height: 1.3;
        }
        
        .course-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .course-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #f0f4f8;
            border-radius: var(--radius-md);
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-secondary);
        }
        
        .course-actions {
            display: flex;
            gap: 0.75rem;
            padding-top: 1rem;
            border-top: 2px solid #f0f4f8;
        }
        
        .action-btn {
            flex: 1;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
        }
        
        .action-btn.edit {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            color: white;
        }
        
        .action-btn.edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }
        
        .action-btn.delete {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        
        .action-btn.delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        .empty-state-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }
        
        .empty-state p {
            color: var(--text-secondary);
            font-size: 1.05rem;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease;
        }
        
        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid var(--secondary-color);
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .modal-close {
            background: #f0f4f8;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-close:hover {
            background: #dc3545;
            color: white;
            transform: rotate(90deg);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.05rem;
        }
        
        .form-group input[type="text"],
        .form-group select {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e0e0e0;
            border-radius: var(--radius-md);
            font-size: 1.05rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-group input[type="text"]:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 51, 102, 0.1);
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn-submit {
            flex: 1;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 1.05rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 51, 102, 0.3);
        }
        
        .btn-cancel {
            padding: 1rem 2rem;
            background: #f0f4f8;
            color: var(--text-primary);
            border: none;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 1.05rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background: #e0e0e0;
        }
        
        @media (max-width: 768px) {
            .page-header-actions {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .btn-create-new {
                width: 100%;
                justify-content: center;
            }
            
            .courses-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php 
        $pageTitle = 'Course Management';
        include 'header-component.php'; 
        ?>

        <div class="admin-content">
            <!-- Page Header with Action Button -->
            <div class="page-header-actions">
                <div class="page-title-section">
                    <h1><span>📚</span> Course Management</h1>
                    <p>Create and manage courses in the system</p>
                </div>
                <button class="btn-create-new" onclick="openCreateModal()">
                    <span>➕</span> Create New Course
                </button>
            </div>

            <!-- Courses Display Grid -->
            <div class="courses-grid">
                <?php
                $sql = "SELECT c.*, d.department_name, 
                        GROUP_CONCAT(i.full_name SEPARATOR ', ') as instructor_names
                        FROM courses c 
                        LEFT JOIN departments d ON c.department_id = d.department_id 
                        LEFT JOIN instructor_courses ic ON c.course_id = ic.course_id
                        LEFT JOIN instructors i ON ic.instructor_id = i.instructor_id
                        GROUP BY c.course_id
                        ORDER BY c.course_name ASC";
                $result = $con->query($sql);

                if($result && $result->num_rows > 0) {
                    while($row = $result->fetch_array()) {
                        $Id = $row['course_id'];
                        $Name = $row['course_name'];
                        $Semester = $row['semester'];
                        $Credit = $row['credit_hours'] ?? 'N/A';
                        $Dept = $row['department_name'] ?? 'N/A';
                        $Instructor = $row['instructor_names'] ?? 'Not Assigned';
                ?>
                <div class="course-card">
                    <div class="course-header">
                        <div class="course-icon">📚</div>
                        <div class="course-credit"><?php echo $Credit; ?> Credits</div>
                    </div>
                    <div class="course-id">ID: <?php echo $Id; ?></div>
                    <div class="course-name"><?php echo $Name; ?></div>
                    <div class="course-meta">
                        <div class="course-meta-item">
                            <span>📅</span>
                            <span>Semester <?php echo $Semester; ?></span>
                        </div>
                        <div class="course-meta-item">
                            <span>🏢</span>
                            <span><?php echo $Dept; ?></span>
                        </div>
                        <div class="course-meta-item">
                            <span>👨‍🏫</span>
                            <span><?php echo $Instructor; ?></span>
                        </div>
                    </div>
                    <div class="course-actions">
                        <a href="EditCourse.php?CourseId=<?php echo $Id; ?>" class="action-btn edit">
                            <span>✏️</span> Edit
                        </a>
                        <a href="DeleteCourse.php?CourseId=<?php echo $Id; ?>" class="action-btn delete" onclick="return confirm('Are you sure you want to delete this course?')">
                            <span>🗑️</span> Delete
                        </a>
                    </div>
                </div>
                <?php
                    }
                } else {
                ?>
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <div class="empty-state-icon">📚</div>
                    <h3>No Courses Found</h3>
                    <p>Click "Create New Course" to add your first course</p>
                </div>
                <?php
                }
                $con->close();
                ?>
            </div>
        </div>
    </div>

    <!-- Create Course Modal -->
    <div class="modal" id="createModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><span>➕</span> Create New Course</h2>
                <button class="modal-close" onclick="closeCreateModal()">×</button>
            </div>
            <form method="post" action="InsertCourse.php">
                <div class="form-group">
                    <label for="txtDeptID">Course ID:</label>
                    <input type="text" name="txtDeptID" id="txtDeptID" required placeholder="Enter Course ID (e.g., CS101)">
                </div>
                
                <div class="form-group">
                    <label for="txtDeptName">Course Name:</label>
                    <input type="text" name="txtDeptName" id="txtDeptName" required placeholder="Enter Course Name">
                </div>
                
                <div class="form-group">
                    <label for="txtDeptCredit">Credit Hour:</label>
                    <input type="text" name="txtDeptCredit" id="txtDeptCredit" required placeholder="Enter Credit Hour (e.g., 3)">
                </div>
                
                <div class="form-group">
                    <label for="cmbSem">Semester:</label>
                    <select name="cmbSem" id="cmbSem" required>
                        <option value="">-- Select Semester --</option>
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="cmbDept">Department:</label>
                    <select name="cmbDept" id="cmbDept" required>
                        <option value="">-- Select Department --</option>
                        <?php
                        foreach($departments as $dept) {
                        ?>
                        <option value="<?php echo $dept['department_id']?>"><?php echo $dept['department_name']?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="cmbInst">Instructor:</label>
                    <select name="cmbInst" id="cmbInst" required>
                        <option value="">-- Select Instructor --</option>
                        <?php
                        foreach($instructors as $instructor) {
                        ?>
                        <option value="<?php echo $instructor['instructor_id']?>"><?php echo $instructor['full_name']?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <span>✓</span> Create Course
                    </button>
                    <button type="button" class="btn-cancel" onclick="closeCreateModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js?v=<?php echo time(); ?>"></script>
    <script>
        function openCreateModal() {
            document.getElementById('createModal').classList.add('active');
        }
        
        function closeCreateModal() {
            document.getElementById('createModal').classList.remove('active');
        }
        
        // Close modal when clicking outside
        document.getElementById('createModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCreateModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCreateModal();
            }
        });
    </script>
</body>
</html>
