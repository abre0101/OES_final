<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Register Course";

$message = '';
$messageType = '';

// Get department ID from session
$deptId = $_SESSION['DeptId'] ?? null;

// Function to generate next course code
function generateNextCourseCode($con, $deptId) {
    // Get department code
    $dept_query = "SELECT department_code FROM departments WHERE department_id = ?";
    $stmt = $con->prepare($dept_query);
    $stmt->bind_param("i", $deptId);
    $stmt->execute();
    $dept_result = $stmt->get_result();
    
    if($dept_result->num_rows == 0) {
        return 'CRS001'; // Fallback
    }
    
    $dept_code = $dept_result->fetch_assoc()['department_code'];
    
    // Get the last course code for this department
    $query = "SELECT course_code FROM courses 
              WHERE department_id = ? AND course_code LIKE ? 
              ORDER BY course_code DESC LIMIT 1";
    $pattern = $dept_code . '%';
    $stmt = $con->prepare($query);
    $stmt->bind_param("is", $deptId, $pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastCode = $row['course_code'];
        // Extract number from NURS101 format
        $number = intval(preg_replace('/[^0-9]/', '', $lastCode));
        $nextNumber = $number + 1;
        return $dept_code . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    } else {
        // First course for this department
        return $dept_code . '101';
    }
}

// Generate next course code
$nextCourseCode = generateNextCourseCode($con, $deptId);

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_course'])) {
    // Auto-generate course code
    $course_code = generateNextCourseCode($con, $deptId);
    $course_name = mysqli_real_escape_string($con, $_POST['course_name']);
    $credit_hours = mysqli_real_escape_string($con, $_POST['credit_hours']);
    $semester = mysqli_real_escape_string($con, $_POST['semester']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    
    // Check if course code already exists (shouldn't happen with auto-generation, but safety check)
    $check_query = "SELECT * FROM courses WHERE course_code = ?";
    $stmt = $con->prepare($check_query);
    $stmt->bind_param("s", $course_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $message = "Course code already exists! Please try again.";
        $messageType = "error";
    } else {
        // Insert new course
        $insert_query = "INSERT INTO courses (course_code, course_name, credit_hours, semester, description, department_id, is_active) 
                        VALUES (?, ?, ?, ?, ?, ?, 1)";
        $stmt = $con->prepare($insert_query);
        $stmt->bind_param("ssiisi", $course_code, $course_name, $credit_hours, $semester, $description, $deptId);
        
        if($stmt->execute()) {
            $message = "Course registered successfully with code: " . $course_code;
            $messageType = "success";
            // Regenerate next course code for display
            $nextCourseCode = generateNextCourseCode($con, $deptId);
        } else {
            $message = "Error registering course: " . $con->error;
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
    <title>Register Course - Department Head</title>
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
                <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: var(--primary-color); font-weight: 700;">Register New Course</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 1.05rem;">
                    Add a new course to <?php echo $_SESSION['Dept']; ?> Department
                </p>
            </div>

            <?php if($message): ?>
            <div class="alert alert-<?php echo $messageType == 'success' ? 'success' : 'danger'; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>Course Information</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Course Code (Auto-generated)</label>
                                    <input type="text" class="form-control" value="<?php echo $nextCourseCode; ?>" readonly style="background-color: #f0f0f0; font-weight: 600; color: #007bff; cursor: not-allowed;">
                                    <small class="form-text text-muted">This code will be automatically assigned</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Course Name *</label>
                                    <input type="text" name="course_name" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Credit Hours *</label>
                                    <input type="number" name="credit_hours" class="form-control" required min="1" max="10">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Semester *</label>
                                    <select name="semester" class="form-control" required>
                                        <option value="">Select Semester</option>
                                        <option value="1">Semester 1</option>
                                        <option value="2">Semester 2</option>
                                        <option value="3">Semester 3</option>
                                        <option value="4">Semester 4</option>
                                        <option value="5">Semester 5</option>
                                        <option value="6">Semester 6</option>
                                        <option value="7">Semester 7</option>
                                        <option value="8">Semester 8</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Department</label>
                                    <input type="text" class="form-control" value="<?php echo $_SESSION['Dept']; ?>" readonly style="background-color: #f0f0f0;">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Course Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Enter course description..."></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="register_course" class="btn btn-primary">Register Course</button>
                            <a href="Courses.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
