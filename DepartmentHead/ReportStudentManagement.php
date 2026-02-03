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
$pageTitle = "Student Management Report";
$deptId = $_SESSION['DeptId'] ?? null;

// Get filters
$status_filter = $_GET['status'] ?? 'all';
$year_filter = $_GET['year'] ?? 'all';

// Build status condition
$status_condition = "";
if($status_filter == 'active') {
    $status_condition = "AND s.is_active = 1";
} elseif($status_filter == 'inactive') {
    $status_condition = "AND s.is_active = 0";
}

// Build year condition
$year_condition = "";
if($year_filter != 'all') {
    $year_condition = "AND s.academic_year = 'Year " . intval($year_filter) . "'";
}

// Get student data
$studentsQuery = "SELECT s.*, 
    COUNT(DISTINCT sc.course_id) as enrolled_courses,
    COUNT(DISTINCT er.result_id) as exam_attempts,
    AVG(er.total_points_earned) as avg_score,
    SUM(CASE WHEN er.total_points_earned >= e.pass_marks THEN 1 ELSE 0 END) as passed_exams,
    COUNT(DISTINCT er.exam_id) as total_exams_taken
    FROM students s
    LEFT JOIN student_courses sc ON s.student_id = sc.student_id
    LEFT JOIN exam_results er ON s.student_id = er.student_id
    LEFT JOIN exams e ON er.exam_id = e.exam_id
    WHERE s.department_id = ? $status_condition $year_condition
    GROUP BY s.student_id
    ORDER BY s.student_code";
$stmt = $con->prepare($studentsQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$students = $stmt->get_result();

// Get statistics
$statsQuery = "SELECT 
    COUNT(DISTINCT s.student_id) as total_students,
    COUNT(DISTINCT CASE WHEN s.is_active = 1 THEN s.student_id END) as active_students,
    COUNT(DISTINCT CASE WHEN s.is_active = 0 THEN s.student_id END) as inactive_students,
    COUNT(DISTINCT sc.course_id) as total_enrollments
    FROM students s
    LEFT JOIN student_courses sc ON s.student_id = sc.student_id
    WHERE s.department_id = ?";
$stmt = $con->prepare($statsQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management Report - Department Head</title>
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
        .filter-section { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); margin-bottom: 2rem; }
        .filter-tabs { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .filter-tab { padding: 0.5rem 1rem; border: 2px solid #e9ecef; border-radius: 8px; background: white; cursor: pointer; transition: all 0.3s; text-decoration: none; color: #495057; font-size: 0.9rem; }
        .filter-tab:hover { border-color: #667eea; color: #667eea; }
        .filter-tab.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-color: #667eea; }
        .data-table { width: 100%; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); }
        .data-table table { width: 100%; border-collapse: collapse; }
        .data-table th { background: #003366; color: white; padding: 1rem; text-align: left; font-weight: 600; font-size: 0.9rem; }
        .data-table td { padding: 1rem; border-bottom: 1px solid #e0e0e0; font-size: 0.9rem; }
        .data-table tr:hover { background: #f8f9fa; }
        .status-badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
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
                    <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: #003366; font-weight: 700;">👨‍🎓 Student Management Report</h1>
                    <p style="margin: 0; color: #6c757d; font-size: 1.05rem;">
                        Complete student oversight for <?php echo htmlspecialchars($_SESSION['Dept']); ?> Department
                    </p>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button onclick="exportToExcel()" class="btn btn-success">📥 Export to Excel</button>
                    <button onclick="window.print()" class="btn btn-primary">🖨️ Print Report</button>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div style="font-size: 2rem;">👥</div>
                    <div class="stat-value"><?php echo $stats['total_students']; ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem;">✅</div>
                    <div class="stat-value" style="color: #28a745;"><?php echo $stats['active_students']; ?></div>
                    <div class="stat-label">Active Students</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem;">❌</div>
                    <div class="stat-value" style="color: #dc3545;"><?php echo $stats['inactive_students']; ?></div>
                    <div class="stat-label">Inactive Students</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem;">📚</div>
                    <div class="stat-value"><?php echo $stats['total_enrollments']; ?></div>
                    <div class="stat-label">Total Enrollments</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-section">
                <h4 style="margin: 0 0 1rem 0;">🔍 Filter Students</h4>
                <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Status:</label>
                        <div class="filter-tabs">
                            <a href="?status=all&year=<?php echo $year_filter; ?>" class="filter-tab <?php echo $status_filter == 'all' ? 'active' : ''; ?>">All</a>
                            <a href="?status=active&year=<?php echo $year_filter; ?>" class="filter-tab <?php echo $status_filter == 'active' ? 'active' : ''; ?>">Active</a>
                            <a href="?status=inactive&year=<?php echo $year_filter; ?>" class="filter-tab <?php echo $status_filter == 'inactive' ? 'active' : ''; ?>">Inactive</a>
                        </div>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Year Level:</label>
                        <div class="filter-tabs">
                            <a href="?status=<?php echo $status_filter; ?>&year=all" class="filter-tab <?php echo $year_filter == 'all' ? 'active' : ''; ?>">All Years</a>
                            <a href="?status=<?php echo $status_filter; ?>&year=1" class="filter-tab <?php echo $year_filter == '1' ? 'active' : ''; ?>">Year 1</a>
                            <a href="?status=<?php echo $status_filter; ?>&year=2" class="filter-tab <?php echo $year_filter == '2' ? 'active' : ''; ?>">Year 2</a>
                            <a href="?status=<?php echo $status_filter; ?>&year=3" class="filter-tab <?php echo $year_filter == '3' ? 'active' : ''; ?>">Year 3</a>
                            <a href="?status=<?php echo $status_filter; ?>&year=4" class="filter-tab <?php echo $year_filter == '4' ? 'active' : ''; ?>">Year 4</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Data Table -->
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Student Code</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Year</th>
                            <th>Enrolled Courses</th>
                            <th>Exams Taken</th>
                            <th>Avg Score</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($students->num_rows > 0): ?>
                            <?php while($student = $students->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($student['student_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['academic_year'] ?? 'N/A'); ?></td>
                                <td><?php echo $student['enrolled_courses']; ?></td>
                                <td><?php echo $student['total_exams_taken']; ?></td>
                                <td><?php echo $student['avg_score'] ? round($student['avg_score'], 2) : 'N/A'; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $student['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $student['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2rem; color: #6c757d;">
                                    No students found matching the selected filters.
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
            window.location.href = 'ExportStudentReport.php?status=<?php echo $status_filter; ?>&year=<?php echo $year_filter; ?>';
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
