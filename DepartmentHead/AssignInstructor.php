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
$pageTitle = "Assign Instructors";

$message = '';
$messageType = '';
$deptId = $_SESSION['DeptId'] ?? null;

// Get all courses in this department with instructor assignments
$courses_query = "SELECT c.*, 
                  COUNT(DISTINCT ic.instructor_id) as instructor_count
                  FROM courses c
                  LEFT JOIN instructor_courses ic ON c.course_id = ic.course_id
                  WHERE c.department_id = ? AND c.is_active = 1
                  GROUP BY c.course_id
                  ORDER BY c.course_code";
$stmt = $con->prepare($courses_query);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$courses = $stmt->get_result();

// Get all instructors in this department
$instructors_query = "SELECT * FROM instructors WHERE department_id = ? AND is_active = 1 ORDER BY full_name";
$stmt = $con->prepare($instructors_query);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$instructors_result = $stmt->get_result();

// Store instructors in an array for reuse
$instructors = [];
while($row = $instructors_result->fetch_assoc()) {
    $instructors[] = $row;
}

// Handle assignment
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_instructor'])) {
    $instructor_id = $_POST['instructor_id'];
    $course_id = $_POST['course_id'];
    
    // Check if already assigned
    $check_query = "SELECT * FROM instructor_courses WHERE instructor_id = ? AND course_id = ?";
    $stmt = $con->prepare($check_query);
    $stmt->bind_param("ii", $instructor_id, $course_id);
    $stmt->execute();
    
    if($stmt->get_result()->num_rows > 0) {
        $message = "This instructor is already assigned to this course!";
        $messageType = "error";
    } else {
        $insert_query = "INSERT INTO instructor_courses (instructor_id, course_id) VALUES (?, ?)";
        $stmt = $con->prepare($insert_query);
        $stmt->bind_param("ii", $instructor_id, $course_id);
        
        if($stmt->execute()) {
            $message = "Instructor assigned successfully!";
            $messageType = "success";
            // Redirect to clear POST data and prevent resubmission
            header("Location: AssignInstructor.php?success=1");
            exit();
        } else {
            $message = "Error assigning instructor: " . $con->error;
            $messageType = "error";
        }
    }
}

// Handle unassignment
if(isset($_GET['unassign'])) {
    $instructor_id = $_GET['instructor_id'];
    $course_id = $_GET['course_id'];
    
    $delete_query = "DELETE FROM instructor_courses WHERE instructor_id = ? AND course_id = ?";
    $stmt = $con->prepare($delete_query);
    $stmt->bind_param("ii", $instructor_id, $course_id);
    
    if($stmt->execute()) {
        // Redirect to clear GET parameters
        header("Location: AssignInstructor.php?removed=1");
        exit();
    }
}

// Set messages from redirects
if(isset($_GET['success'])) {
    $message = "Instructor assigned successfully!";
    $messageType = "success";
}
if(isset($_GET['removed'])) {
    $message = "Instructor unassigned successfully!";
    $messageType = "success";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Instructors - Department Head</title>
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
                <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: var(--primary-color); font-weight: 700;">Assign Instructors to Courses</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 1.05rem;">
                    Manage instructor assignments for <?php echo $_SESSION['Dept']; ?> Department courses
                </p>
            </div>

            <?php if($message): ?>
            <div class="alert alert-<?php echo $messageType == 'success' ? 'success' : 'danger'; ?>" style="margin-bottom: 1.5rem;">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>Course Instructor Assignments</h3>
                </div>
                <div class="card-body">
                    <?php if($courses->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th>Assigned Instructors</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $courses->data_seek(0);
                                while($course = $courses->fetch_assoc()): 
                                    // Get assigned instructors for this course
                                    $assigned_query = "SELECT i.* 
                                                      FROM instructors i 
                                                      INNER JOIN instructor_courses ic ON i.instructor_id = ic.instructor_id 
                                                      WHERE ic.course_id = ?";
                                    $stmt = $con->prepare($assigned_query);
                                    $stmt->bind_param("i", $course['course_id']);
                                    $stmt->execute();
                                    $assigned = $stmt->get_result();
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($course['course_code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                    <td>
                                        <?php if($assigned->num_rows > 0): ?>
                                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                                <?php while($inst = $assigned->fetch_assoc()): ?>
                                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem; background: #f8f9fa; border-radius: 6px;">
                                                    <span>
                                                        <strong><?php echo htmlspecialchars($inst['full_name']); ?></strong>
                                                        <small style="color: #6c757d;"> (<?php echo htmlspecialchars($inst['instructor_code']); ?>)</small>
                                                    </span>
                                                    <a href="?unassign=1&instructor_id=<?php echo $inst['instructor_id']; ?>&course_id=<?php echo $course['course_id']; ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Remove this instructor from the course?')">
                                                        ✕ Remove
                                                    </a>
                                                </div>
                                                <?php endwhile; ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: #6c757d; font-style: italic;">No instructors assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" 
                                                onclick="openAssignModal(<?php echo $course['course_id']; ?>, '<?php echo htmlspecialchars(addslashes($course['course_code'])); ?>', '<?php echo htmlspecialchars(addslashes($course['course_name'])); ?>')">
                                            ➕ Assign Instructor
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <p>No courses found in your department. <a href="RegisterCourse.php">Register a new course</a></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row" style="margin-top: 2rem;">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h4>Department Instructors</h4>
                            <p style="color: #6c757d; font-size: 0.9rem; margin-bottom: 1rem;">All instructors and their current course load</p>
                            <?php 
                            // Re-fetch instructors for the list
                            $instructors_list_query = "SELECT * FROM instructors WHERE department_id = ? AND is_active = 1 ORDER BY full_name";
                            $stmt_list = $con->prepare($instructors_list_query);
                            $stmt_list->bind_param("i", $deptId);
                            $stmt_list->execute();
                            $instructors_list = $stmt_list->get_result();
                            
                            if($instructors_list->num_rows > 0): ?>
                            <ul style="list-style: none; padding: 0; margin: 1rem 0 0 0;">
                                <?php 
                                while($instructor = $instructors_list->fetch_assoc()): 
                                    // Count courses for this instructor
                                    $count_query = "SELECT COUNT(*) as count FROM instructor_courses WHERE instructor_id = ?";
                                    $stmt_count = $con->prepare($count_query);
                                    $stmt_count->bind_param("i", $instructor['instructor_id']);
                                    $stmt_count->execute();
                                    $count = $stmt_count->get_result()->fetch_assoc()['count'];
                                ?>
                                <li style="padding: 0.5rem; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong><?php echo htmlspecialchars($instructor['full_name']); ?></strong>
                                        <br>
                                        <small style="color: #6c757d;"><?php echo htmlspecialchars($instructor['instructor_code']); ?></small>
                                    </div>
                                    <span class="badge <?php echo $count > 0 ? 'badge-primary' : 'badge-secondary'; ?>">
                                        <?php echo $count; ?> course<?php echo $count != 1 ? 's' : ''; ?>
                                    </span>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                            <?php else: ?>
                            <p style="color: #6c757d;">No instructors available in your department.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Modal -->
    <div id="assignModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; padding: 2rem; border-radius: 12px; max-width: 500px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
            <h3 style="margin: 0 0 1.5rem 0; color: var(--primary-color);">Assign Instructor</h3>
            <form method="POST" action="">
                <input type="hidden" name="course_id" id="modal_course_id">
                
                <div style="margin-bottom: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <strong id="modal_course_info"></strong>
                </div>

                <div class="form-group">
                    <label>Select Instructor *</label>
                    <select name="instructor_id" class="form-control" required>
                        <option value="">-- Select Instructor --</option>
                        <?php foreach($instructors as $instructor): ?>
                        <option value="<?php echo $instructor['instructor_id']; ?>">
                            <?php echo htmlspecialchars($instructor['full_name']); ?> (<?php echo htmlspecialchars($instructor['instructor_code']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" name="assign_instructor" class="btn btn-primary" style="flex: 1;">Assign Instructor</button>
                    <button type="button" onclick="closeAssignModal()" class="btn btn-secondary" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAssignModal(courseId, courseCode, courseName) {
            document.getElementById('modal_course_id').value = courseId;
            document.getElementById('modal_course_info').textContent = courseCode + ' - ' + courseName;
            document.getElementById('assignModal').style.display = 'flex';
        }

        function closeAssignModal() {
            document.getElementById('assignModal').style.display = 'none';
        }

        // Close modal when clicking outside
        document.getElementById('assignModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAssignModal();
            }
        });
    </script>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>

