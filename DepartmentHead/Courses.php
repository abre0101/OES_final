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
$pageTitle = "Manage Courses";

// Get department ID from session
$deptId = $_SESSION['DeptId'] ?? null;

// Get all courses in this department
$courses_query = "SELECT c.*, d.department_name, 
                  COUNT(DISTINCT ic.instructor_id) as instructor_count,
                  COUNT(DISTINCT sc.student_id) as student_count
                  FROM courses c 
                  LEFT JOIN departments d ON c.department_id = d.department_id 
                  LEFT JOIN instructor_courses ic ON c.course_id = ic.course_id
                  LEFT JOIN student_courses sc ON c.course_id = sc.course_id
                  WHERE c.department_id = ? 
                  GROUP BY c.course_id
                  ORDER BY c.course_code ASC";
$stmt = $con->prepare($courses_query);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$courses = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Department Head</title>
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
                <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: var(--primary-color); font-weight: 700;">Manage Departmental Courses</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 1.05rem;">
                    View and manage courses in <?php echo $_SESSION['Dept']; ?> Department
                </p>
            </div>

            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>Courses List</h3>
                    <a href="RegisterCourse.php" class="btn btn-primary">
                        ➕ Register New Course
                    </a>
                </div>
                <div class="card-body">
                    <?php if($courses->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th>Credit Hours</th>
                                    <th>Instructors</th>
                                    <th>Students</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($course = $courses->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($course['course_code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                    <td><?php echo htmlspecialchars($course['credit_hours'] ?? 'N/A'); ?></td>
                                    <td><?php echo $course['instructor_count']; ?> instructor(s)</td>
                                    <td><?php echo $course['student_count']; ?> student(s)</td>
                                    <td>
                                        <?php if($course['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="ViewCourse.php?id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-info">View</a>
                                        <a href="EditCourse.php?id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="AssignInstructor.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-primary">Assign</a>
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
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
