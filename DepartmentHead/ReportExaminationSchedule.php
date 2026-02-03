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
$pageTitle = "Examination Schedule Report";
$deptId = $_SESSION['DeptId'] ?? null;

// Get examination schedule data
$scheduleQuery = "SELECT e.exam_name, e.exam_date, e.start_time, e.duration_minutes,
    e.approval_status, e.is_active,
    c.course_code, c.course_name,
    i.full_name as instructor_name,
    ec.category_name,
    COUNT(DISTINCT sc.student_id) as enrolled_students
    FROM exams e
    INNER JOIN courses c ON e.course_id = c.course_id
    LEFT JOIN instructors i ON e.created_by = i.instructor_id
    LEFT JOIN exam_categories ec ON e.exam_category_id = ec.exam_category_id
    LEFT JOIN student_courses sc ON c.course_id = sc.course_id
    WHERE c.department_id = ? AND e.exam_date >= CURDATE()
    GROUP BY e.exam_id
    ORDER BY e.exam_date, e.start_time";
$stmt = $con->prepare($scheduleQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$exams = $stmt->get_result();

// Check for conflicts (same date/time)
$conflictsQuery = "SELECT e1.exam_date, e1.start_time, COUNT(*) as conflict_count
    FROM exams e1
    INNER JOIN courses c1 ON e1.course_id = c1.course_id
    INNER JOIN exams e2 ON e1.exam_date = e2.exam_date 
        AND e1.start_time = e2.start_time 
        AND e1.exam_id != e2.exam_id
    INNER JOIN courses c2 ON e2.course_id = c2.course_id
    WHERE c1.department_id = ? AND e1.exam_date >= CURDATE()
    GROUP BY e1.exam_date, e1.start_time
    HAVING conflict_count > 0";
$stmt = $con->prepare($conflictsQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$conflicts = $stmt->get_result();

// Get statistics
$statsQuery = "SELECT 
    COUNT(DISTINCT e.exam_id) as upcoming_exams,
    COUNT(DISTINCT CASE WHEN e.approval_status = 'approved' THEN e.exam_id END) as approved_exams,
    COUNT(DISTINCT CASE WHEN e.approval_status = 'pending' THEN e.exam_id END) as pending_exams,
    COUNT(DISTINCT CASE WHEN e.is_active = 1 THEN e.exam_id END) as published_exams
    FROM exams e
    INNER JOIN courses c ON e.course_id = c.course_id
    WHERE c.department_id = ? AND e.exam_date >= CURDATE()";
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
    <title>Examination Schedule Report - Department Head</title>
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
        .data-table { width: 100%; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); margin-bottom: 2rem; }
        .data-table table { width: 100%; border-collapse: collapse; }
        .data-table th { background: #003366; color: white; padding: 1rem; text-align: left; font-weight: 600; font-size: 0.9rem; }
        .data-table td { padding: 1rem; border-bottom: 1px solid #e0e0e0; font-size: 0.9rem; }
        .data-table tr:hover { background: #f8f9fa; }
        .status-badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-published { background: #d1ecf1; color: #0c5460; }
        .alert-warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; }
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
                    <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: #003366; font-weight: 700;">📅 Examination Schedule Report</h1>
                    <p style="margin: 0; color: #6c757d; font-size: 1.05rem;">
                        Centralized exam planning - prevents scheduling conflicts
                    </p>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button onclick="exportToExcel()" class="btn btn-success">📥 Export to Excel</button>
                    <button onclick="window.print()" class="btn btn-primary">🖨️ Print Report</button>
                </div>
            </div>

            <!-- Conflict Warning -->
            <?php if($conflicts->num_rows > 0): ?>
            <div class="alert-warning">
                <strong>⚠️ Schedule Conflicts Detected!</strong> There are <?php echo $conflicts->num_rows; ?> time slot(s) with multiple exams scheduled. 
                Review the schedule below to resolve conflicts.
            </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div style="font-size: 2rem;">📅</div>
                    <div class="stat-value"><?php echo $stats['upcoming_exams']; ?></div>
                    <div class="stat-label">Upcoming Exams</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem;">✅</div>
                    <div class="stat-value" style="color: #28a745;"><?php echo $stats['approved_exams']; ?></div>
                    <div class="stat-label">Approved</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem;">⏳</div>
                    <div class="stat-value" style="color: #ffc107;"><?php echo $stats['pending_exams']; ?></div>
                    <div class="stat-label">Pending Approval</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem;">📢</div>
                    <div class="stat-value" style="color: #17a2b8;"><?php echo $stats['published_exams']; ?></div>
                    <div class="stat-label">Published</div>
                </div>
            </div>

            <!-- Schedule Table -->
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Exam Name</th>
                            <th>Course</th>
                            <th>Category</th>
                            <th>Instructor</th>
                            <th>Duration</th>
                            <th>Enrolled</th>
                            <th>Approval</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($exams->num_rows > 0): ?>
                            <?php while($exam = $exams->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo date('M d, Y', strtotime($exam['exam_date'])); ?></strong></td>
                                <td><?php echo date('h:i A', strtotime($exam['start_time'])); ?></td>
                                <td><?php echo htmlspecialchars($exam['exam_name']); ?></td>
                                <td><?php echo htmlspecialchars($exam['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($exam['category_name']); ?></td>
                                <td><?php echo htmlspecialchars($exam['instructor_name'] ?? 'N/A'); ?></td>
                                <td><?php echo $exam['duration_minutes']; ?> min</td>
                                <td><?php echo $exam['enrolled_students']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $exam['approval_status']; ?>">
                                        <?php echo ucfirst($exam['approval_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $exam['is_active'] ? 'status-published' : 'status-pending'; ?>">
                                        <?php echo $exam['is_active'] ? 'Published' : 'Draft'; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 2rem; color: #6c757d;">
                                    No upcoming exams scheduled.
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
            window.location.href = 'ExportScheduleReport.php';
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
