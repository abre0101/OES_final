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
$pageTitle = "Edit Student";

// Get student ID from URL
$studentId = $_GET['id'] ?? null;

if (!$studentId) {
    header("Location: Students.php");
    exit();
}

// Get department ID from session
$deptId = $_SESSION['DeptId'] ?? null;

// Get student details - ensure they belong to this department
$stmt = $con->prepare("SELECT s.* FROM students s WHERE s.student_id = ? AND s.department_id = ?");
$stmt->bind_param("ii", $studentId, $deptId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: Students.php");
    exit();
}

$student = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $gender = $_POST['gender'];
    $semester = intval($_POST['semester']);
    $academicYear = intval($_POST['academic_year']);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    $updateStmt = $con->prepare("UPDATE students SET full_name = ?, email = ?, phone = ?, gender = ?, semester = ?, academic_year = ?, is_active = ? WHERE student_id = ?");
    $updateStmt->bind_param("ssssiiii", $fullName, $email, $phone, $gender, $semester, $academicYear, $isActive, $studentId);
    
    if ($updateStmt->execute()) {
        // Check if semester changed - if so, re-enroll in new semester courses
        if($semester != $student['semester']) {
            // Remove old enrollments
            $delete_query = "DELETE FROM student_courses WHERE student_id = ?";
            $delete_stmt = $con->prepare($delete_query);
            $delete_stmt->bind_param("i", $studentId);
            $delete_stmt->execute();
            
            // Enroll in new semester courses
            $courses_query = "SELECT course_id FROM courses 
                             WHERE department_id = ? 
                             AND semester = ?
                             AND is_active = 1";
            $course_stmt = $con->prepare($courses_query);
            $course_stmt->bind_param("ii", $student['department_id'], $semester);
            $course_stmt->execute();
            $courses = $course_stmt->get_result();
            
            $enrolled_count = 0;
            while($course = $courses->fetch_assoc()) {
                $enroll_query = "INSERT INTO student_courses (student_id, course_id) VALUES (?, ?)";
                $enroll_stmt = $con->prepare($enroll_query);
                $enroll_stmt->bind_param("ii", $studentId, $course['course_id']);
                if($enroll_stmt->execute()) {
                    $enrolled_count++;
                }
            }
            
            $_SESSION['success_message'] = "Student updated successfully and re-enrolled in $enrolled_count course(s) for the new semester!";
        } else {
            $_SESSION['success_message'] = "Student updated successfully!";
        }
        
        header("Location: ViewStudent.php?id=" . $studentId);
        exit();
    } else {
        $error = "Error updating student: " . $con->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Department Head</title>
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
                <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: var(--primary-color); font-weight: 700;">Edit Student</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 1.05rem;">
                    Update information for <?php echo htmlspecialchars($student['full_name']); ?>
                </p>
            </div>

            <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>Student Information</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="grid grid-2">
                            <div class="form-group">
                                <label for="full_name">Full Name *</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($student['email']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" id="phone" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="gender">Gender *</label>
                                <select id="gender" name="gender" class="form-control" required>
                                    <option value="Male" <?php echo $student['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $student['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo $student['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="academic_year">Academic Year *</label>
                                <select id="academic_year" name="academic_year" class="form-control" required>
                                    <option value="1" <?php echo $student['academic_year'] == 1 ? 'selected' : ''; ?>>Year 1</option>
                                    <option value="2" <?php echo $student['academic_year'] == 2 ? 'selected' : ''; ?>>Year 2</option>
                                    <option value="3" <?php echo $student['academic_year'] == 3 ? 'selected' : ''; ?>>Year 3</option>
                                    <option value="4" <?php echo $student['academic_year'] == 4 ? 'selected' : ''; ?>>Year 4</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="semester">Semester *</label>
                                <select id="semester" name="semester" class="form-control" required>
                                    <option value="1" <?php echo $student['semester'] == 1 ? 'selected' : ''; ?>>Semester 1</option>
                                    <option value="2" <?php echo $student['semester'] == 2 ? 'selected' : ''; ?>>Semester 2</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_active" <?php echo $student['is_active'] ? 'checked' : ''; ?>>
                                <span>Active Account</span>
                            </label>
                        </div>

                        <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end;">
                            <a href="Students.php" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                💾 Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php
$con->close();
?>

