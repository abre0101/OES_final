<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Department Head session
SessionManager::startSession('DepartmentHead');

// Check if user is logged in
if(!isset($_SESSION['Name'])){
    header("Location:../auth/staff-login.php");
    exit();
}

// Validate user role
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'DepartmentHead'){
    SessionManager::destroySession();
    header("Location:../auth/staff-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Register Student";

$message = '';
$messageType = '';

// Get department ID from session
$deptId = $_SESSION['DeptId'] ?? null;

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

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_student'])) {
    $student_code = generateNextStudentCode($con); // Auto-generate student code
    $full_name = mysqli_real_escape_string($con, $_POST['full_name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $academic_year = mysqli_real_escape_string($con, $_POST['academic_year']);
    $semester = intval($_POST['semester']);
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if username already exists
    $check_query = "SELECT * FROM students WHERE username = ?";
    $stmt = $con->prepare($check_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $message = "Username already exists!";
        $messageType = "error";
    } else {
        // Insert new student
        $insert_query = "INSERT INTO students (student_code, username, password, full_name, email, phone, gender, department_id, academic_year, semester, is_active) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = $con->prepare($insert_query);
        $stmt->bind_param("ssssssssii", $student_code, $username, $hashed_password, $full_name, $email, $phone, $gender, $deptId, $academic_year, $semester);
        
        if($stmt->execute()) {
            $student_id = $con->insert_id;
            
            // Automatically enroll student in courses for their department and semester
            $courses_query = "SELECT course_id FROM courses 
                             WHERE department_id = ? 
                             AND semester = ?
                             AND is_active = 1";
            $course_stmt = $con->prepare($courses_query);
            $course_stmt->bind_param("ii", $deptId, $semester);
            $course_stmt->execute();
            $courses = $course_stmt->get_result();
            
            $enrolled_count = 0;
            while($course = $courses->fetch_assoc()) {
                $enroll_query = "INSERT INTO student_courses (student_id, course_id) VALUES (?, ?)";
                $enroll_stmt = $con->prepare($enroll_query);
                $enroll_stmt->bind_param("ii", $student_id, $course['course_id']);
                if($enroll_stmt->execute()) {
                    $enrolled_count++;
                }
            }
            
            $message = "Student registered successfully and automatically enrolled in $enrolled_count course(s)!";
            $messageType = "success";
            
            // Redirect to clear form and show success message
            header("Location: RegisterStudent.php?success=1&enrolled=" . $enrolled_count);
            exit();
        } else {
            $message = "Error registering student: " . $con->error;
            $messageType = "error";
        }
    }
}

// Check for success message from redirect
if(isset($_GET['success']) && $_GET['success'] == 1) {
    $enrolled_count = isset($_GET['enrolled']) ? intval($_GET['enrolled']) : 0;
    $message = "Student registered successfully and automatically enrolled in $enrolled_count course(s)!";
    $messageType = "success";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Student - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>
    
    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>
        
        <div class="admin-content">
            <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: var(--primary-color); font-weight: 700;">Register New Student</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 1.05rem;">
                    Add a new student to <?php echo $_SESSION['Dept']; ?> Department
                </p>
            </div>

            <?php if($message): ?>
            <div class="alert alert-<?php echo $messageType == 'success' ? 'success' : 'danger'; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>Student Information</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Student Code (Auto-generated)</label>
                                    <input type="text" class="form-control" value="<?php echo $nextStudentCode; ?>" readonly style="background-color: #f0f0f0; font-weight: 600; color: #007bff;">
                                    <small class="form-text text-muted">This code will be automatically assigned</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Full Name *</label>
                                    <input type="text" name="full_name" id="full_name" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email *</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="text" name="phone" class="form-control" placeholder="+251...">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Gender *</label>
                                    <select name="gender" class="form-control" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Username *</label>
                                    <input type="text" name="username" id="username" class="form-control" required>
                                    <small class="form-text text-muted">Auto-suggested based on name, but you can edit it</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Password *</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Academic Year *</label>
                                    <select name="academic_year" class="form-control" required>
                                        <option value="">Select Year</option>
                                        <option value="Year 1">Year 1</option>
                                        <option value="Year 2">Year 2</option>
                                        <option value="Year 3">Year 3</option>
                                        <option value="Year 4">Year 4</option>
                                        <option value="Year 5">Year 5</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Semester *</label>
                                    <select name="semester" class="form-control" required>
                                        <option value="">Select Semester</option>
                                        <option value="1">Semester 1</option>
                                        <option value="2">Semester 2</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Department</label>
                                    <input type="text" class="form-control" value="<?php echo $_SESSION['Dept']; ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="register_student" class="btn btn-primary">Register Student</button>
                            <a href="Students.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        // Auto-suggest username based on full name
        const fullNameInput = document.getElementById('full_name');
        const usernameInput = document.getElementById('username');
        let manuallyEdited = false;
        
        if (fullNameInput && usernameInput) {
            fullNameInput.addEventListener('input', function() {
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

