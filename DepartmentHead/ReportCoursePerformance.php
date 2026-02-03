<?php
require_once(__DIR__ . "/../utils/session_manager.php");
SessionManager::startSession('DepartmentHead');

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'DepartmentHead'){
    SessionManager::destroySession();
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Course Performance Report";
$deptId = $_SESSION['DeptId'] ?? null;

// Get course performance data
$performanceQuery = "SELECT c.course_code, c.course_name, c.credit_hours,
    i.full_name as instructor_name,
    COUNT(DISTINCT sc.student_id) as enrolled_students,
    COUNT(DISTINCT e.exam_id) as total_exams,
    COUNT(DISTINCT er.result_id) as total_attempts,
    AVG(er.total_points_earned) as avg_score,
    SUM(CASE WHEN er.total_points_earned >= e.pass_marks THEN 1 ELSE 0 END) as passed_count,
    COUNT(DISTINCT er.result_id) as attempt_count,
    MAX(er.total_points_earned) as highest_score,
    MIN(er.total_points_earned) as lowest_score
    FROM courses c
    LEFT JOIN student_courses sc ON c.course_id = sc.course_id
    LEFT JOIN instructor_courses ic ON c.course_id = ic.course_id
    LEFT JOIN instructors i ON ic.instructor_id = i.instructor_id
    LEFT JOIN exams e ON c.course_id = e.course_id
    LEFT JOIN exam_results er ON e.exam_id = er.exam_id
    WHERE c.department_id = ? AND c.is_active = 1
    GROUP BY c.course_id
    ORDER BY c.course_code";
$stmt = $con->prepare($performanceQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$courses = $stmt->get_result();

// Get overall statistics
$overallQuery = "SELECT 
    COUNT(DISTINCT c.course_id) as total_courses,
    COUNT(DISTINCT er.result_id) as total_attempts,
    AVG(er.total_points_earned) as overall_avg,
    SUM(CASE WHEN er.total_points_earned >= e.pass_marks THEN 1 ELSE 0 END) as total_passed,
    COUNT(DISTINCT er.result_id) as total_results
    FROM courses c
    LEFT JOIN exams e ON c.course_id = e.course_id
    LEFT JOIN exam_results er ON e.exam_id = er.exam_id
    WHERE c.department_id = ?";
$stmt = $con->prepare($overallQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$overall = $stmt->get_result()->fetch_assoc();

$overall_pass_rate = $overall['total_results'] > 0 ? round(($overall['total_passed'] / $overall['total_results']) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Performance Report - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); text-align: center; }
        .stat-value { font-size: 2.5rem; font-weight: 700; color: #003366; margin: 0.5rem 0; }
        .stat-label { color: #6c757d; font-size: 0.9rem; font-weight: 600; }
        .data-table { width: 100%; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); }
        .data-table table { width: 100%; border-collapse: collapse; }
        .data-table th { background: #003366; color: white; padding: 1rem; text-align: left; font-weight: 600; font-size: 0.9rem; }
        .data-table td { padding: 1rem; border-bottom: 1px solid #e0e0e0; font-size: 0.9rem; }
        .data-table tr:hover { background: #f8f9fa; }
        .performance-badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .perf-excellent { background: #d4edda; color: #155724; }
        .perf-good { background: #d1ecf1; color: #0c5460; }
        .perf-average { background: #fff3cd; color: #856404; }
        .perf-poor { background: #f8d7da; color: #721c24; }
        .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-block; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: #003366; font-weight: 700;">📈 Course Performance & Results Report</h1>
                    <p style="margin: 0; color: #6c757d; font-size: 1.05rem;">
                        Exam outcomes and course effectiveness analysis
                    </p>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button onclick="exportToExcel()" class="btn btn-success">📥 Export to Excel</button>
                    <button onclick="window.print()" class="btn btn-primary">🖨️ Print Report</button>
                </div>
            </div>

            <!-- Overall Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div style="font-size: 2rem;">📚</div>
                    <div class="stat-value"><?php echo $overall['total_courses']; ?></div>
                    <div class="stat-label">Active Courses</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem;">✍️</div>
                    <div class="stat-value"><?php echo $overall['total_attempts']; ?></div>
                    <div class="stat-label">Total Exam Attempts</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem;">📊</div>
                    <div class="stat-value"><?php echo round($overall['overall_avg'], 2); ?></div>
                    <div class="stat-label">Overall Average Score</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem;">✅</div>
                    <div class="stat-value" style="color: #28a745;"><?php echo $overall_pass_rate; ?>%</div>
                    <div class="stat-label">Overall Pass Rate</div>
                </div>
            </div>

            <!-- Course Performance Table -->
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Instructor</th>
                            <th>Enrolled</th>
                            <th>Exams</th>
                            <th>Attempts</th>
                            <th>Avg Score</th>
                            <th>Pass Rate</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($courses->num_rows > 0): ?>
                            <?php 
                            $courses->data_seek(0);
                            while($course = $courses->fetch_assoc()): 
                                $pass_rate = $course['attempt_count'] > 0 ? round(($course['passed_count'] / $course['attempt_count']) * 100, 2) : 0;
                                
                                // Determine performance level
                                if($pass_rate >= 80) {
                                    $perf_class = 'perf-excellent';
                                    $perf_label = 'Excellent';
                                } elseif($pass_rate >= 70) {
                                    $perf_class = 'perf-good';
                                    $perf_label = 'Good';
                                } elseif($pass_rate >= 50) {
                                    $perf_class = 'perf-average';
                                    $perf_label = 'Average';
                                } else {
                                    $perf_class = 'perf-poor';
                                    $perf_label = 'Needs Improvement';
                                }
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($course['course_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($course['instructor_name'] ?? 'Not Assigned'); ?></td>
                                <td><?php echo $course['enrolled_students']; ?></td>
                                <td><?php echo $course['total_exams']; ?></td>
                                <td><?php echo $course['total_attempts']; ?></td>
                                <td><?php echo $course['avg_score'] ? round($course['avg_score'], 2) : 'N/A'; ?></td>
                                <td><strong><?php echo $pass_rate; ?>%</strong></td>
                                <td>
                                    <span class="performance-badge <?php echo $perf_class; ?>">
                                        <?php echo $perf_label; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 2rem; color: #6c757d;">
                                    No course performance data available.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 2rem;">
                <a href="Reports.php" class="btn btn-secondary">← Back to Reports</a>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function exportToExcel() {
            window.location.href = 'ExportCoursePerformance.php';
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
