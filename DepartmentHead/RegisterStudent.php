<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
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
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $academic_year = mysqli_real_escape_string($con, $_POST['academic_year']);
    
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
        $insert_query = "INSERT INTO students (student_code, username, password, full_name, email, phone, department_id, academic_year, is_active) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = $con->prepare($insert_query);
        $stmt->bind_param("ssssssss", $student_code, $username, $hashed_password, $full_name, $email, $phone, $deptId, $academic_year);
        
        if($stmt->execute()) {
            $message = "Student registered successfully!";
            $messageType = "success";
        } else {
            $message = "Error registering student: " . $con->error;
            $messageType = "error";
        }
    }
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
                                    <input type="text" name="full_name" class="form-control" required>
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
                                    <label>Username *</label>
                                    <input type="text" name="username" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Password *</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
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
</body>
</html>
