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
$pageTitle = "Exam Participation Report";
$deptId = $_SESSION['DeptId'] ?? null;

// Get exam participation data
$participationQuery = "SELECT e.exam_name, e.exam_date, e.start_time,
    c.course_code, c.course_name,
    COUNT(DISTINCT sc.student_id) as enrolled_students,
    COUNT(DISTINCT er.student_id) as participated_students,
    COUNT(DISTINCT er.result_id) as total_attempts,
    (COUNT(DISTINCT sc.student_id) - COUNT(DISTINCT er.student_id)) as no_shows
    FROM exams e
    INNER JOIN courses c ON e.course_id = c.course_id
    LEFT JOIN student_courses sc ON c.course_id = sc.course_id
    LEFT JOIN exam_results er ON e.exam_id = er.exam_id
    WHERE c.department_id = ? AND e.exam_date IS NOT NULL
    GROUP BY e.exam_id
    ORDER BY e.exam_date DESC";
$stmt = $con->prepare($participationQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$exams = $stmt->get_result();

// Get overall statistics
$statsQuery = "SELECT 
    COUNT(DISTINCT e.exam_id) as total_exams,
    COUNT(DISTINCT er.result_id) as total_attempts,
    COUNT(DISTINCT er.student_id) as unique_participants
    FROM exams e
    INNER JOIN courses c ON e.course_id = c.course_id
    LEFT JOIN exam_results er ON e.exam_id = er.exam_id
    WHERE c.department_id = ?";
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
    <title>Exam Participation Report - Department Head</title>
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
        .participation-badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .part-excellent { background: #d4edda; color: #155724; }
        .part-good { background: #d1ecf1; color: #0c5460; }
        .part-average { background: #fff3cd; color: #856404; }
        .part-poor { background: #f8d7da; color: #721c24; }
        .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-block; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .highlight-box { background: #e7f3ff; border-left: 4px solid #007bff; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: #003366; font-weight: 700;">✅ Exam Participation & Attendance Report</h1>
                    <p style="margin: 0; color: #6c757d; font-size: 1.05rem;">
                        Automated attendance tracking - eliminates manual paperwork
                    </p>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button onclick="exportToExcel()" class="btn btn-success">📥 Export to Excel</button>
                    <button onclick="window.print()" class="btn btn-primary">🖨️ Print Report</button>
                </div>
            </div>

            <!-- Highlight Box -->
            <div class="highlight-box">
                <strong>📋 Automation Benefit:</strong> This report replaces manual attendance forms (Table 4 from requirements). 
                All attendance is automatically tracked when students take exams - no more paperwork!
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div style="font-size: 2rem;">📝</div>
                    <div class="stat-value"><?php echo $stats['total_exams']; ?></div>
                    <div class="stat-label">Total Exams</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem;">✍️</div>
                    <div class="stat-value"><?php echo $stats['total_attempts']; ?></div>
                    <div class="stat-label">Total Attempts</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem;">👥</div>
                    <div class="stat-value"><?php echo $stats['unique_participants']; ?></div>
                    <div class="stat-label">Unique Participants</div>
                </div>
            </div>

            <!-- Participation Data Table -->
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Exam Name</th>
                            <th>Course</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Enrolled</th>
                            <th>Participated</th>
                            <th>No-Shows</th>
                            <th>Participation Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($exams->num_rows > 0): ?>
                            <?php while($exam = $exams->fetch_assoc()): 
                                $participation_rate = $exam['enrolled_students'] > 0 ? 
                                    round(($exam['participated_students'] / $exam['enrolled_students']) * 100, 2) : 0;
                                
                                // Determine participation level
                                if($participation_rate >= 90) {
                                    $part_class = 'part-excellent';
                                    $part_label = 'Excellent';
                                } elseif($participation_rate >= 75) {
                                    $part_class = 'part-good';
                                    $part_label = 'Good';
                                } elseif($participation_rate >= 50) {
                                    $part_class = 'part-average';
                                    $part_label = 'Average';
                                } else {
                                    $part_class = 'part-poor';
                                    $part_label = 'Poor';
                                }
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($exam['exam_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($exam['course_code']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($exam['exam_date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($exam['start_time'])); ?></td>
                                <td><?php echo $exam['enrolled_students']; ?></td>
                                <td><strong><?php echo $exam['participated_students']; ?></strong></td>
                                <td style="color: #dc3545; font-weight: 600;"><?php echo $exam['no_shows']; ?></td>
                                <td>
                                    <span class="participation-badge <?php echo $part_class; ?>">
                                        <?php echo $participation_rate; ?>% - <?php echo $part_label; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2rem; color: #6c757d;">
                                    No exam participation data available.
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
            window.location.href = 'ExportParticipationReport.php';
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
