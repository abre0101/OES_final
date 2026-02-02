<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location: ../index.php");
    exit();
}

// Database connection
$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Function to generate next student code
function generateNextStudentCode($con) {
    $query = "SELECT student_code FROM students WHERE student_code LIKE 'STU%' ORDER BY student_code DESC LIMIT 1";
    $result = $con->query($query);
    
    if($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastCode = $row['student_code'];
        // Extract number from STU004 format
        $number = intval(substr($lastCode, 3));
        $nextNumber = $number + 1;
        return 'STU' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    } else {
        // First student
        return 'STU001';
    }
}

// Generate next student code
$nextStudentCode = generateNextStudentCode($con);

// Get departments for dropdown
$query_Recordsetd = "SELECT * FROM departments ORDER BY department_name ASC";
$Recordsetd = $con->query($query_Recordsetd);
$departments = [];
if($Recordsetd->num_rows > 0) {
    while($row = $Recordsetd->fetch_assoc()) {
        $departments[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - Admin Dashboard</title>
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
        
        .students-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .student-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 2px solid #e8eef3;
            position: relative;
            overflow: hidden;
        }
        
        .student-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
        }
        
        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 123, 255, 0.15);
            border-color: #007bff;
        }
        
        .student-header {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
            padding-bottom: 1.25rem;
            border-bottom: 2px solid #f0f4f8;
        }
        
        .student-avatar {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 900;
            color: white;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
            flex-shrink: 0;
        }
        
        .student-info {
            flex: 1;
            min-width: 0;
        }
        
        .student-id {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-weight: 600;
            margin-bottom: 0.35rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .student-name {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .student-status {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.75rem;
            border-radius: var(--radius-md);
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .student-status.active {
            background: #d4edda;
            color: #155724;
        }
        
        .student-status.inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .student-details {
            margin-bottom: 1.25rem;
        }
        
        .student-detail-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0;
            font-size: 0.95rem;
            color: var(--text-secondary);
        }
        
        .student-detail-item .icon {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }
        
        .student-detail-item .value {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .student-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .student-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.75rem;
            background: #f0f4f8;
            border-radius: var(--radius-md);
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-secondary);
        }
        
        .student-actions {
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
        .form-group input[type="password"],
        .form-group select {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e0e0e0;
            border-radius: var(--radius-md);
            font-size: 1.05rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(0, 51, 102, 0.1);
        }
        
        .form-group-inline {
            display: flex;
            gap: 1rem;
        }
        
        .form-group-inline .form-group {
            flex: 1;
        }
        
        .radio-group {
            display: flex;
            gap: 1.5rem;
            margin-top: 0.5rem;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .radio-option input[type="radio"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .radio-option label {
            margin: 0;
            cursor: pointer;
            font-weight: 600;
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
            
            .students-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group-inline {
                flex-direction: column;
            }
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php 
        $pageTitle = 'Student Management';
        include 'header-component.php'; 
        ?>

        <div class="admin-content">
            <!-- Page Header with Action Button -->
            <div class="page-header-actions">
                <div class="page-title-section">
                    <h1><span>👨‍🎓</span> Student Management</h1>
                    <p>Create and manage students in the system</p>
                </div>
                <button class="btn-create-new" onclick="openCreateModal()">
                    <span>➕</span> Create New Student
                </button>
            </div>

            <!-- Students Display Grid -->
            <div class="students-grid">
                <?php
                $sql = "SELECT s.*, d.department_name 
                        FROM students s 
                        LEFT JOIN departments d ON s.department_id = d.department_id 
                        ORDER BY s.full_name ASC";
                $result = $con->query($sql);

                if($result && $result->num_rows > 0) {
                    while($row = $result->fetch_array()) {
                        $Id = $row['student_id'];
                        $Name = $row['full_name'];
                        $Department = $row['department_name'] ?? 'N/A';
                        $Year = $row['semester'] ?? 'N/A'; // Using semester as year
                        $Semester = $row['semester'];
                        $Sex = $row['gender'] ?? 'N/A';
                        $UserName = $row['username'];
                        $is_active = $row['is_active'];
                        $Status = $is_active == 1 ? 'Active' : 'Inactive';
                        $initial = strtoupper(substr($Name, 0, 1));
                ?>
                <div class="student-card">
                    <div class="student-header">
                        <div class="student-avatar"><?php echo $initial; ?></div>
                        <div class="student-info">
                            <div class="student-id">ID: <?php echo $Id; ?></div>
                            <div class="student-name" title="<?php echo $Name; ?>"><?php echo $Name; ?></div>
                            <span class="student-status <?php echo strtolower($Status); ?>">
                                <span><?php echo $Status === 'Active' ? '●' : '○'; ?></span>
                                <?php echo $Status; ?>
                            </span>
                        </div>
                    </div>
                    <div class="student-details">
                        <div class="student-detail-item">
                            <span class="icon">🏢</span>
                            <span class="value"><?php echo $Department; ?></span>
                        </div>
                        <div class="student-detail-item">
                            <span class="icon">👤</span>
                            <span class="value"><?php echo $UserName; ?></span>
                        </div>
                        <div class="student-detail-item">
                            <span class="icon"><?php echo $Sex === 'Female' ? '👩' : '👨'; ?></span>
                            <span class="value"><?php echo $Sex; ?></span>
                        </div>
                    </div>
                    <div class="student-badges">
                        <div class="student-badge">
                            <span>📅</span>
                            <span>Year <?php echo $Year; ?></span>
                        </div>
                        <div class="student-badge">
                            <span>📚</span>
                            <span>Sem <?php echo $Semester; ?></span>
                        </div>
                    </div>
                    <div class="student-actions">
                        <a href="EditStudent.php?student_id=<?php echo $Id; ?>" class="action-btn edit">
                            <span>✏️</span> Edit
                        </a>
                        <a href="DeleteStudent.php?Id=<?php echo $Id; ?>" class="action-btn delete" onclick="return confirm('Are you sure you want to delete this student?')">
                            <span>🗑️</span> Delete
                        </a>
                    </div>
                </div>
                <?php
                    }
                } else {
                ?>
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <div class="empty-state-icon">👨‍🎓</div>
                    <h3>No Students Found</h3>
                    <p>Click "Create New Student" to add your first student</p>
                </div>
                <?php
                }
                $con->close();
                ?>
            </div>
        </div>
    </div>

    <!-- Create Student Modal -->
    <div class="modal" id="createModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><span>➕</span> Create New Student</h2>
                <button class="modal-close" onclick="closeCreateModal()">×</button>
            </div>
            <form method="post" action="InsertStudent.php">
                <div class="form-group">
                    <label for="txtRoll">Student Code (Auto-generated):</label>
                    <input type="text" name="txtRoll" id="txtRoll" value="<?php echo $nextStudentCode; ?>" readonly style="background-color: #f0f0f0; font-weight: 600; color: #007bff; cursor: not-allowed;">
                    <small style="display: block; margin-top: 0.5rem; color: #6c757d; font-size: 0.9rem;">This code will be automatically assigned</small>
                </div>
                
                <div class="form-group">
                    <label for="txtName">Student Name:</label>
                    <input type="text" name="txtName" id="txtName" required placeholder="Enter Full Name">
                </div>
                
                <div class="form-group">
                    <label for="cmbDept">Department:</label>
                    <select name="cmbDept" id="cmbDept" required>
                        <option value="">-- Select Department --</option>
                        <?php
                        foreach($departments as $dept) {
                        ?>
                        <option value="<?php echo $dept['department_name']?>"><?php echo $dept['department_name']?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group-inline">
                    <div class="form-group">
                        <label for="cmbYear">Year:</label>
                        <select name="cmbYear" id="cmbYear" required>
                            <option value="">-- Select Year --</option>
                            <option value="1">Year 1</option>
                            <option value="2">Year 2</option>
                            <option value="3">Year 3</option>
                            <option value="4">Year 4</option>
                            <option value="5">Year 5</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="cmbSem">Semester:</label>
                        <select name="cmbSem" id="cmbSem" required>
                            <option value="">-- Select Semester --</option>
                            <option value="1">Semester 1</option>
                            <option value="2">Semester 2</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Gender:</label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="gender" id="male" value="Male" required>
                            <label for="male">Male</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="gender" id="female" value="Female" required>
                            <label for="female">Female</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="txtUserName">Username:</label>
                    <input type="text" name="txtUserName" id="txtUserName" required placeholder="Enter Username">
                </div>
                
                <div class="form-group">
                    <label for="txtPassword">Password:</label>
                    <input type="password" name="txtPassword" id="txtPassword" required placeholder="Enter Password">
                </div>
                
                <div class="form-group">
                    <label for="cmbStatus">Status:</label>
                    <select name="cmbStatus" id="cmbStatus" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <span>✓</span> Create Student
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
