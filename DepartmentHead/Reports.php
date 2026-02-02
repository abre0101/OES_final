<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Departmental Reports";
$deptId = $_SESSION['DeptId'] ?? null;

// Get department statistics
$dept_stats = "SELECT 
               COUNT(DISTINCT s.student_id) as total_students,
               COUNT(DISTINCT i.instructor_id) as total_instructors,
               COUNT(DISTINCT c.course_id) as total_courses,
               COUNT(DISTINCT es.schedule_id) as total_exams
               FROM departments d
               LEFT JOIN students s ON d.department_id = s.department_id AND s.is_active = 1
               LEFT JOIN instructors i ON d.department_id = i.department_id AND i.is_active = 1
               LEFT JOIN courses c ON d.department_id = c.department_id AND c.is_active = 1
               LEFT JOIN exam_schedules es ON c.course_id = es.course_id AND es.is_active = 1
               WHERE d.department_id = ?";
$stmt = $con->prepare($dept_stats);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get recent exam statistics
$exam_stats = "SELECT 
               COUNT(DISTINCT es.schedule_id) as total_scheduled,
               COUNT(DISTINCT CASE WHEN es.exam_date >= CURDATE() THEN es.schedule_id END) as upcoming,
               COUNT(DISTINCT CASE WHEN es.exam_date < CURDATE() THEN es.schedule_id END) as completed,
               COUNT(DISTINCT er.result_id) as total_attempts
               FROM exam_schedules es
               LEFT JOIN courses c ON es.course_id = c.course_id
               LEFT JOIN exam_results er ON es.schedule_id = er.schedule_id
               WHERE c.department_id = ? AND es.is_active = 1";
$stmt = $con->prepare($exam_stats);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$exam_data = $stmt->get_result()->fetch_assoc();

// Get course-wise statistics
$course_stats = "SELECT c.course_code, c.course_name,
                 COUNT(DISTINCT sc.student_id) as enrolled_students,
                 COUNT(DISTINCT ic.instructor_id) as assigned_instructors,
                 COUNT(DISTINCT es.schedule_id) as scheduled_exams
                 FROM courses c
                 LEFT JOIN student_courses sc ON c.course_id = sc.course_id
                 LEFT JOIN instructor_courses ic ON c.course_id = ic.course_id
                 LEFT JOIN exam_schedules es ON c.course_id = es.course_id
                 WHERE c.department_id = ? AND c.is_active = 1
                 GROUP BY c.course_id
                 ORDER BY c.course_code";
$stmt = $con->prepare($course_stats);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$courses = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departmental Reports - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>
    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>
        <div class="admin-content">
            <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: var(--primary-color); font-weight: 700;">Departmental Reports</h1>
                    <p style="margin: 0; color: var(--text-secondary); font-size: 1.05rem;">
                        Comprehensive reports for <?php echo $_SESSION['Dept']; ?> Department
                    </p>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="ExportToExcel.php?type=departmental" class="btn btn-success">📊 Export to Excel</a>
                    <a href="ExportToExcel.php?type=students" class="btn btn-info">👥 Export Students</a>
                    <a href="ExportToExcel.php?type=exams" class="btn btn-primary">📝 Export Exams</a>
                </div>
            </div>

            <!-- Department Overview -->
            <div class="row" style="margin-bottom: 2rem;">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon">👨‍🎓</div>
                        <div class="stat-details">
                            <h3><?php echo $stats['total_students']; ?></h3>
                            <p>Total Students</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon">👨‍🏫</div>
                        <div class="stat-details">
                            <h3><?php echo $stats['total_instructors']; ?></h3>
                            <p>Total Instructors</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon">📚</div>
                        <div class="stat-details">
                            <h3><?php echo $stats['total_courses']; ?></h3>
                            <p>Total Courses</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon">📝</div>
                        <div class="stat-details">
                            <h3><?php echo $stats['total_exams']; ?></h3>
                            <p>Total Exams</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exam Statistics -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3>Exam Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <p><strong>Scheduled Exams:</strong> <?php echo $exam_data['total_scheduled']; ?></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Upcoming:</strong> <?php echo $exam_data['upcoming']; ?></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Completed:</strong> <?php echo $exam_data['completed']; ?></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Total Attempts:</strong> <?php echo $exam_data['total_attempts']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course-wise Statistics -->
            <div class="card">
                <div class="card-header">
                    <h3>Course-wise Statistics</h3>
                </div>
                <div class="card-body">
                    <?php if($courses->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th>Enrolled Students</th>
                                    <th>Assigned Instructors</th>
                                    <th>Scheduled Exams</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($course = $courses->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($course['course_code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                    <td><?php echo $course['enrolled_students']; ?></td>
                                    <td><?php echo $course['assigned_instructors']; ?></td>
                                    <td><?php echo $course['scheduled_exams']; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">No courses found.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-top: 2rem;">
                <button onclick="window.print()" class="btn btn-primary">🖨️ Print Report</button>
                <a href="PerformanceReports.php" class="btn btn-secondary">📊 View Performance Reports</a>
            </div>
        </div>
    </div>
</body>
</html>
