<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Administrator session
SessionManager::startSession('Administrator');

// Check if user is logged in
if(!isset($_SESSION['username'])){
    header("Location: ../auth/staff-login.php");
    exit();
}

// Validate user role
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Administrator'){
    SessionManager::destroySession();
    header("Location: ../auth/staff-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");

// Function to generate next instructor code
function generateNextInstructorCode($con) {
    $query = "SELECT instructor_code FROM instructors WHERE instructor_code LIKE 'INST%' ORDER BY instructor_code DESC LIMIT 1";
    $result = mysqli_query($con, $query);
    
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $lastCode = $row['instructor_code'];
        // Extract number from INST001 format
        $number = intval(substr($lastCode, 4));
        $nextNumber = $number + 1;
        return 'INST' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    } else {
        // First instructor
        return 'INST001';
    }
}

// Generate next instructor code
$nextInstructorCode = generateNextInstructorCode($con);

$query_Recordsetd = "SELECT * FROM departments ORDER BY department_name ASC";
$Recordsetd = mysqli_query($con,$query_Recordsetd) or die(mysqli_error($con));
$departments = [];
if(mysqli_num_rows($Recordsetd) > 0) {
    while($row = mysqli_fetch_assoc($Recordsetd)) {
        $departments[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Management - Admin Dashboard</title>
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
        
        .instructors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .instructor-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 2px solid #e8eef3;
            position: relative;
            overflow: hidden;
        }
        
        .instructor-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #17a2b8 0%, #138496 100%);
        }
        
        .instructor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(23, 162, 184, 0.15);
            border-color: #17a2b8;
        }
        
        .instructor-header {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
            padding-bottom: 1.25rem;
            border-bottom: 2px solid #f0f4f8;
        }
        
        .instructor-avatar {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 900;
            color: white;
            box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
            flex-shrink: 0;
        }
        
        .instructor-info {
            flex: 1;
            min-width: 0;
        }
        
        .instructor-id {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-weight: 600;
            margin-bottom: 0.35rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .instructor-name {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .instructor-status {
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
        
        .instructor-status.active {
            background: #d4edda;
            color: #155724;
        }
        
        .instructor-status.inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .instructor-details {
            margin-bottom: 1.25rem;
        }
        
        .instructor-detail-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0;
            font-size: 0.95rem;
            color: var(--text-secondary);
        }
        
        .instructor-detail-item .icon {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }
        
        .instructor-detail-item .value {
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .instructor-actions {
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
        .form-group input[type="email"],
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
            
            .instructors-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php 
        $pageTitle = 'Instructor Management';
        include 'header-component.php'; 
        ?>

        <div class="admin-content">
            <!-- Page Header with Action Button -->
            <div class="page-header-actions">
                <div class="page-title-section">
                    <h1><span>👨‍🏫</span> Instructor Management</h1>
                    <p>Create and manage instructors in the system</p>
                </div>
                <button class="btn-create-new" onclick="openCreateModal()">
                    <span>➕</span> Create New Instructor
                </button>
            </div>

            <!-- Instructors Display Grid -->
            <div class="instructors-grid">
                <?php
                $sql = "SELECT i.*, d.department_name 
                        FROM instructors i 
                        LEFT JOIN departments d ON i.department_id = d.department_id 
                        ORDER BY i.full_name ASC";
                $result = mysqli_query($con,$sql);

                if(mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_array($result)) {
                        $Id = $row['instructor_id'];
                        $Name = $row['full_name'];
                        $Email = $row['email'] ?? 'N/A';
                        $Department = $row['department_name'] ?? 'N/A';
                        $UserName = $row['username'];
                        $is_active = $row['is_active'];
                        $Status = $is_active == 1 ? 'Active' : 'Inactive';
                        $initial = strtoupper(substr($Name, 0, 1));
                ?>
                <div class="instructor-card">
                    <div class="instructor-header">
                        <div class="instructor-avatar"><?php echo $initial; ?></div>
                        <div class="instructor-info">
                            <div class="instructor-id">ID: <?php echo $Id; ?></div>
                            <div class="instructor-name" title="<?php echo $Name; ?>"><?php echo $Name; ?></div>
                            <span class="instructor-status <?php echo strtolower($Status); ?>">
                                <span><?php echo $Status === 'Active' ? '●' : '○'; ?></span>
                                <?php echo $Status; ?>
                            </span>
                        </div>
                    </div>
                    <div class="instructor-details">
                        <div class="instructor-detail-item">
                            <span class="icon">📧</span>
                            <span class="value" title="<?php echo $Email; ?>"><?php echo $Email; ?></span>
                        </div>
                        <div class="instructor-detail-item">
                            <span class="icon">🏢</span>
                            <span class="value"><?php echo $Department; ?></span>
                        </div>
                        <div class="instructor-detail-item">
                            <span class="icon">👤</span>
                            <span class="value"><?php echo $UserName; ?></span>
                        </div>
                    </div>
                    <div class="instructor-actions">
                        <a href="EditInstructor.php?Id=<?php echo $Id; ?>" class="action-btn edit">
                            <span>✏️</span> Edit
                        </a>
                        <a href="DeleteInstructor.php?Id=<?php echo $Id; ?>" class="action-btn delete" onclick="return confirm('Are you sure you want to delete this instructor?')">
                            <span>🗑️</span> Delete
                        </a>
                    </div>
                </div>
                <?php
                    }
                } else {
                ?>
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <div class="empty-state-icon">👨‍🏫</div>
                    <h3>No Instructors Found</h3>
                    <p>Click "Create New Instructor" to add your first instructor</p>
                </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Create Instructor Modal -->
    <div class="modal" id="createModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><span>➕</span> Create New Instructor</h2>
                <button class="modal-close" onclick="closeCreateModal()">×</button>
            </div>
            <form method="post" action="InsertInstructor.php">
                <div class="form-group">
                    <label for="instID">Instructor Code (Auto-generated):</label>
                    <input type="text" name="instID" id="instID" value="<?php echo $nextInstructorCode; ?>" readonly style="background-color: #f0f0f0; font-weight: 600; color: #17a2b8; cursor: not-allowed;">
                    <small style="display: block; margin-top: 0.5rem; color: #6c757d; font-size: 0.9rem;">This code will be automatically assigned</small>
                </div>
                
                <div class="form-group">
                    <label for="instName">Instructor Name:</label>
                    <input type="text" name="instName" id="instName" required placeholder="Enter Full Name">
                </div>
                
                <div class="form-group">
                    <label for="instEmail">Email:</label>
                    <input type="email" name="instEmail" id="instEmail" required placeholder="Enter Email Address">
                </div>
                
                <div class="form-group">
                    <label for="instUName">Username:</label>
                    <input type="text" name="instUName" id="instUName" required placeholder="Enter Username">
                    <small style="display: block; margin-top: 0.5rem; color: #6c757d; font-size: 0.9rem;">Auto-suggested based on name, but you can edit it</small>
                </div>
                
                <div class="form-group">
                    <label for="instPassword">Password:</label>
                    <input type="password" name="instPassword" id="instPassword" required placeholder="Enter Password">
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
                    <label for="cmbStatus">Status:</label>
                    <select name="cmbStatus" id="cmbStatus" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <span>✓</span> Create Instructor
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
        
        // Auto-suggest username based on instructor name
        const instructorNameInput = document.getElementById('instName');
        const usernameInput = document.getElementById('instUName');
        let manuallyEdited = false;
        
        if (instructorNameInput && usernameInput) {
            instructorNameInput.addEventListener('input', function() {
                const fullName = this.value.trim();
                
                // Only auto-fill if username hasn't been manually edited
                if (fullName && !manuallyEdited) {
                    // Convert name to username format
                    const nameParts = fullName.toLowerCase().split(' ').filter(part => part.length > 0);
                    
                    if (nameParts.length > 0) {
                        let username = '';
                        
                        if (nameParts.length === 1) {
                            // Single name: use first 6 characters
                            username = nameParts[0].substring(0, 6);
                        } else {
                            // Multiple names: first name + first letter of last name
                            const firstName = nameParts[0];
                            const lastInitial = nameParts[nameParts.length - 1].charAt(0);
                            username = firstName + lastInitial;
                        }
                        
                        // Remove special characters and spaces
                        username = username.replace(/[^a-z0-9]/g, '');
                        
                        // Set the username
                        usernameInput.value = username;
                    }
                }
            });
            
            // Mark as manually edited when user types in username field
            usernameInput.addEventListener('input', function() {
                manuallyEdited = true;
            });
            
            // Reset manual edit flag when username is cleared
            usernameInput.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    manuallyEdited = false;
                }
            });
        }
    </script>
</body>
</html>
