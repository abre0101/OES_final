<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "View Course";

// Get course ID from URL
$courseId = $_GET['id'] ?? null;

if (!$courseId) {
    header("Location: Courses.php");
    exit();
}

// Get department ID from session
$deptId = $_SESSION['DeptId'] ?? null;

// Get course details - ensure it belongs to this department
$stmt = $con->prepare("SELECT c.*, d.department_name, f.faculty_name 
    FROM courses c 
    LEFT JOIN departments d ON c.department_id = d.department_id 
    LEFT JOIN faculties f ON d.faculty_id = f.faculty_id 
    WHERE c.course_id = ? AND c.department_id = ?");
$stmt->bind_param("ii", $courseId, $deptId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: Courses.php");
    exit();
}

$course = $result->fetch_assoc();

// Get assigned instructors
$instructors_query = "SELECT i.* 
                     FROM instructors i 
                     INNER JOIN instructor_courses ic ON i.instructor_id = ic.instructor_id 
                     WHERE ic.course_id = ?";
$stmt = $con->prepare($instructors_query);
$stmt->bind_param("i", $courseId);
$stmt->execute();
$instructors = $stmt->get_result();

// Get enrolled students
$students_query = "SELECT s.* 
                  FROM students s 
                  INNER JOIN student_courses sc ON s.student_id = sc.student_id 
                  WHERE sc.course_id = ?
                  ORDER BY s.full_name";
$stmt = $con->prepare($students_query);
$stmt->bind_param("i", $courseId);
$stmt->execute();
$students = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Course - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .course-header {
            text-align: center;
            padding: 2.5rem 2rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, #0056b3 100%);
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            color: white;
            margin: -1.5rem -1.5rem 0 -1.5rem;
        }
        .course-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        .course-header h2 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: 700;
        }
        .course-header p {
            margin: 0;
            opacity: 0.95;
            font-size: 1.1rem;
        }
        .info-section {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        .info-section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }
        .info-section-icon {
            font-size: 1.75rem;
        }
        .info-section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.25rem;
        }
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }
        .info-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 1.05rem;
            color: var(--text-primary);
            font-weight: 500;
        }
        .people-list {
            display: grid;
            gap: 1rem;
        }
        .person-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: var(--radius-md);
            border: 1px solid #e9ecef;
        }
        .person-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, #0056b3 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .person-info {
            flex: 1;
        }
        .person-name {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
        }
        .person-details {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border-color);
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>
    
    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>
        
        <div class="admin-content">
            <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: var(--primary-color); font-weight: 700;">Course Details</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 1.05rem;">
                    Complete information for <?php echo htmlspecialchars($course['course_name']); ?>
                </p>
            </div>

            <div class="card">
                <div class="course-header">
                    <div class="course-icon">
                        📚
                    </div>
                    <h2 style="color: #FFD700;"><?php echo htmlspecialchars($course['course_name']); ?></h2>
                    <p style="color: #90EE90; font-weight: 600;"><?php echo htmlspecialchars($course['course_code']); ?></p>
                </div>

                <div style="padding: 2rem;">
                    <!-- Course Information -->
                    <div class="info-section">
                        <div class="info-section-header">
                            <span class="info-section-icon">📋</span>
                            <h3 class="info-section-title">Course Information</h3>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Course Code</span>
                                <span class="info-value"><?php echo htmlspecialchars($course['course_code']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Course Name</span>
                                <span class="info-value"><?php echo htmlspecialchars($course['course_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Credit Hours</span>
                                <span class="info-value"><?php echo htmlspecialchars($course['credit_hours'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Semester</span>
                                <span class="info-value">Semester <?php echo htmlspecialchars($course['semester']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Department</span>
                                <span class="info-value"><?php echo htmlspecialchars($course['department_name'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Faculty</span>
                                <span class="info-value"><?php echo htmlspecialchars($course['faculty_name'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Status</span>
                                <span class="info-value">
                                    <?php if($course['is_active']): ?>
                                    <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                    <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <?php if($course['description']): ?>
                        <div style="margin-top: 1.5rem;">
                            <span class="info-label">Description</span>
                            <p style="margin-top: 0.5rem; color: var(--text-primary);"><?php echo htmlspecialchars($course['description']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Assigned Instructors -->
                    <div class="info-section">
                        <div class="info-section-header">
                            <span class="info-section-icon">👨‍🏫</span>
                            <h3 class="info-section-title">Assigned Instructors (<?php echo $instructors->num_rows; ?>)</h3>
                        </div>
                        <?php if($instructors->num_rows > 0): ?>
                        <div class="people-list">
                            <?php while($instructor = $instructors->fetch_assoc()): ?>
                            <div class="person-card">
                                <div class="person-avatar">
                                    <?php echo strtoupper(substr($instructor['full_name'], 0, 1)); ?>
                                </div>
                                <div class="person-info">
                                    <div class="person-name"><?php echo htmlspecialchars($instructor['full_name']); ?></div>
                                    <div class="person-details">
                                        <?php echo htmlspecialchars($instructor['instructor_code']); ?>
                                        <?php if($instructor['email']): ?>
                                        • <?php echo htmlspecialchars($instructor['email']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <?php else: ?>
                        <p style="color: var(--text-secondary); font-style: italic;">No instructors assigned to this course yet.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Enrolled Students -->
                    <div class="info-section">
                        <div class="info-section-header">
                            <span class="info-section-icon">👨‍🎓</span>
                            <h3 class="info-section-title">Enrolled Students (<?php echo $students->num_rows; ?>)</h3>
                        </div>
                        <?php if($students->num_rows > 0): ?>
                        <div class="people-list">
                            <?php while($student = $students->fetch_assoc()): ?>
                            <div class="person-card">
                                <div class="person-avatar">
                                    <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                                </div>
                                <div class="person-info">
                                    <div class="person-name"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                    <div class="person-details">
                                        <?php echo htmlspecialchars($student['student_code']); ?>
                                        <?php if($student['email']): ?>
                                        • <?php echo htmlspecialchars($student['email']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <?php else: ?>
                        <p style="color: var(--text-secondary); font-style: italic;">No students enrolled in this course yet.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <a href="Courses.php" class="btn btn-secondary" style="padding: 0.75rem 2rem; font-size: 1rem;">
                            ← Back to Courses
                        </a>
                        <a href="EditCourse.php?id=<?php echo $course['course_id']; ?>" class="btn btn-warning" style="padding: 0.75rem 2rem; font-size: 1rem;">
                            ✏️ Edit Course
                        </a>
                        <a href="AssignInstructor.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1rem;">
                            👨‍🏫 Assign Instructor
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $con->close(); ?>
    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
