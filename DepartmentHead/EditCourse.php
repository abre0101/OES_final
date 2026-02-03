<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Department Head session
SessionManager::startSession('DepartmentHead');

// Check if user is logged in
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

// Validate user role
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'DepartmentHead'){
    SessionManager::destroySession();
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Edit Course";

$message = '';
$messageType = '';

// Get course ID from URL
$courseId = $_GET['id'] ?? null;

if (!$courseId) {
    header("Location: Courses.php");
    exit();
}

// Get department ID from session
$deptId = $_SESSION['DeptId'] ?? null;

// Get course details - ensure it belongs to this department
$stmt = $con->prepare("SELECT * FROM courses WHERE course_id = ? AND department_id = ?");
$stmt->bind_param("ii", $courseId, $deptId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: Courses.php");
    exit();
}

$course = $result->fetch_assoc();

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_course'])) {
    $course_code = mysqli_real_escape_string($con, $_POST['course_code']);
    $course_name = mysqli_real_escape_string($con, $_POST['course_name']);
    $credit_hours = mysqli_real_escape_string($con, $_POST['credit_hours']);
    $semester = mysqli_real_escape_string($con, $_POST['semester']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $is_active = mysqli_real_escape_string($con, $_POST['is_active']);
    
    // Check if course code already exists for another course
    $check_query = "SELECT * FROM courses WHERE course_code = ? AND course_id != ? AND department_id = ?";
    $stmt = $con->prepare($check_query);
    $stmt->bind_param("sii", $course_code, $courseId, $deptId);
    $stmt->execute();
    $check_result = $stmt->get_result();
    
    if($check_result->num_rows > 0) {
        $message = "Course code already exists in your department!";
        $messageType = "error";
    } else {
        // Update course (course_code is readonly, so we use the existing one)
        $update_query = "UPDATE courses SET 
                        course_name = ?, 
                        credit_hours = ?, 
                        semester = ?, 
                        description = ?, 
                        is_active = ?
                        WHERE course_id = ? AND department_id = ?";
        $stmt = $con->prepare($update_query);
        $stmt->bind_param("sissiii", $course_name, $credit_hours, $semester, $description, $is_active, $courseId, $deptId);
        
        if($stmt->execute()) {
            $message = "Course updated successfully!";
            $messageType = "success";
            // Refresh course data
            $stmt = $con->prepare("SELECT * FROM courses WHERE course_id = ?");
            $stmt->bind_param("i", $courseId);
            $stmt->execute();
            $course = $stmt->get_result()->fetch_assoc();
        } else {
            $message = "Error updating course: " . $con->error;
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
    <title>Edit Course - Department Head</title>
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
                <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: var(--primary-color); font-weight: 700;">Edit Course</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 1.05rem;">
                    Update course information for <?php echo htmlspecialchars($course['course_code']); ?>
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
                                    <label>Course Code</label>
                                    <input type="text" name="course_code" class="form-control" value="<?php echo htmlspecialchars($course['course_code']); ?>" readonly style="background-color: #f0f0f0; cursor: not-allowed;">
                                    <small class="form-text text-muted">Course code cannot be changed</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Course Name *</label>
                                    <input type="text" name="course_name" class="form-control" value="<?php echo htmlspecialchars($course['course_name']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Credit Hours *</label>
                                    <input type="number" name="credit_hours" class="form-control" value="<?php echo htmlspecialchars($course['credit_hours']); ?>" min="1" max="10" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Semester *</label>
                                    <select name="semester" class="form-control" required>
                                        <option value="">Select Semester</option>
                                        <option value="1" <?php echo $course['semester'] == 1 ? 'selected' : ''; ?>>Semester 1</option>
                                        <option value="2" <?php echo $course['semester'] == 2 ? 'selected' : ''; ?>>Semester 2</option>
                                        <option value="3" <?php echo $course['semester'] == 3 ? 'selected' : ''; ?>>Semester 3</option>
                                        <option value="4" <?php echo $course['semester'] == 4 ? 'selected' : ''; ?>>Semester 4</option>
                                        <option value="5" <?php echo $course['semester'] == 5 ? 'selected' : ''; ?>>Semester 5</option>
                                        <option value="6" <?php echo $course['semester'] == 6 ? 'selected' : ''; ?>>Semester 6</option>
                                        <option value="7" <?php echo $course['semester'] == 7 ? 'selected' : ''; ?>>Semester 7</option>
                                        <option value="8" <?php echo $course['semester'] == 8 ? 'selected' : ''; ?>>Semester 8</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status *</label>
                                    <select name="is_active" class="form-control" required>
                                        <option value="1" <?php echo $course['is_active'] == 1 ? 'selected' : ''; ?>>Active</option>
                                        <option value="0" <?php echo $course['is_active'] == 0 ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Enter course description (optional)"><?php echo htmlspecialchars($course['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Department</label>
                            <input type="text" class="form-control" value="<?php echo $_SESSION['Dept']; ?>" readonly style="background-color: #f0f0f0;">
                            <small class="form-text text-muted">Department cannot be changed</small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="update_course" class="btn btn-primary">Update Course</button>
                            <a href="ViewCourse.php?id=<?php echo $courseId; ?>" class="btn btn-secondary">Cancel</a>
                            <a href="Courses.php" class="btn btn-secondary">Back to Courses</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>

