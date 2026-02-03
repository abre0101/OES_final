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
$pageTitle = "Instructor Compliance Report";
$deptId = $_SESSION['DeptId'] ?? null;

// Get instructor compliance data
$complianceQuery = "SELECT i.full_name, i.email,
    COUNT(DISTINCT e.exam_id) as total_exams,
    COUNT(DISTINCT CASE WHEN e.approval_status = 'approved' THEN e.exam_id END) as approved_exams,
    COUNT(DISTINCT CASE WHEN e.approval_status = 'pending' THEN e.exam_id END) as pending_exams,
    COUNT(DISTINCT CASE WHEN e.approval_status = 'rejected' THEN e.exam_id END) as rejected_exams,
    COUNT(DISTINCT eq.question_id) as total_questions,
    AVG(DATEDIFF(e.exam_date, e.created_at)) as avg_prep_days
    FROM instructors i
    LEFT JOIN instructor_courses ic ON i.instructor_id = ic.instructor_id
    LEFT JOIN courses c ON ic.course_id = c.course_id
    LEFT JOIN exams e ON c.course_id = e.course_id AND e.created_by = i.instructor_id
    LEFT JOIN exam_questions eq ON e.exam_id = eq.exam_id
    WHERE i.department_id = ? AND i.is_active = 1
    GROUP BY i.instructor_id
    ORDER BY i.full_name";
$stmt = $con->prepare($complianceQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$instructors = $stmt->get_result();

// Get overall statistics
$statsQuery = "SELECT 
    COUNT(DISTINCT i.instructor_id) as total_instructors,
    COUNT(DISTINCT e.exam_id) as total_exams,
    COUNT(DISTINCT CASE WHEN e.approval_status = 'approved' THEN e.exam_id END) as approved_count,
    COUNT(DISTINCT CASE WHEN e.approval_status = 'pending' THEN e.exam_id END) as pending_count
    FROM instructors i
    LEFT JOIN exams e ON i.instructor_id = e.created_by
    LEFT JOIN courses c ON e.course_id = c.course_id
    WHERE i.department_id = ?";
$stmt = $con->prepare($statsQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

$approval_rate = $stats['total_exams'] > 0 ? round(($stats['approved_count'] / $stats['total_exams']) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Compliance Report - Department Head</title>
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
        .compliance-badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .comp-excellent { background: #d4edda; color: #155724; }
        .comp-good { background: #d1ecf1; color: #0c5460; }
        .comp-warning { background: #fff3cd; color: #856404; }
        .comp-poor { background: #f8d7da; color: #721c24; }
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
                    <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: #003366; font-weight: 700;">👨‍🏫 Instructor Compliance Report</h1>
                    <p style="margin: 0; color: #6c757d; font-size: 1.05rem;">
                        Ensures exam procedures are followed according to Exam Committee workflow
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
                    <div style="font-size: 2rem;">👨‍🏫</div>
                    <div class="stat-value"><?php echo $stats['total_instructors']; ?></div>
                    <div class="stat-label">Active Instructors</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem;">📝</div>
                    <div class="stat-value"><?php echo $stats['total_exams']; ?></div>
                    <div class="stat-label">Total Exams Created</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem;">✅</div>
                    <div class="stat-value" style="color: #28a745;"><?php echo $stats['approved_count']; ?></div>
                    <div class="stat-label">Approved Exams</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem;">⏳</div>
                    <div class="stat-value" style="color: #ffc107;"><?php echo $stats['pending_count']; ?></div>
                    <div class="stat-label">Pending Approval</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem;">📊</div>
                    <div class="stat-value"><?php echo $approval_rate; ?>%</div>
                    <div class="stat-label">Approval Rate</div>
                </div>
            </div>

            <!-- Compliance Data Table -->
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Instructor Name</th>
                            <th>Email</th>
                            <th>Total Exams</th>
                            <th>Approved</th>
                            <th>Pending</th>
                            <th>Rejected</th>
                            <th>Questions Created</th>
                            <th>Avg Prep Days</th>
                            <th>Compliance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($instructors->num_rows > 0): ?>
                            <?php while($instructor = $instructors->fetch_assoc()): 
                                $compliance_rate = $instructor['total_exams'] > 0 ? 
                                    round(($instructor['approved_exams'] / $instructor['total_exams']) * 100, 2) : 0;
                                
                                // Determine compliance level
                                if($compliance_rate >= 90 && $instructor['pending_exams'] == 0) {
                                    $comp_class = 'comp-excellent';
                                    $comp_label = 'Excellent';
                                } elseif($compliance_rate >= 75) {
                                    $comp_class = 'comp-good';
                                    $comp_label = 'Good';
                                } elseif($compliance_rate >= 50 || $instructor['pending_exams'] > 0) {
                                    $comp_class = 'comp-warning';
                                    $comp_label = 'Needs Attention';
                                } else {
                                    $comp_class = 'comp-poor';
                                    $comp_label = 'Poor';
                                }
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($instructor['full_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($instructor['email']); ?></td>
                                <td><?php echo $instructor['total_exams']; ?></td>
                                <td style="color: #28a745; font-weight: 600;"><?php echo $instructor['approved_exams']; ?></td>
                                <td style="color: #ffc107; font-weight: 600;"><?php echo $instructor['pending_exams']; ?></td>
                                <td style="color: #dc3545; font-weight: 600;"><?php echo $instructor['rejected_exams']; ?></td>
                                <td><?php echo $instructor['total_questions']; ?></td>
                                <td><?php echo $instructor['avg_prep_days'] ? round($instructor['avg_prep_days']) : 'N/A'; ?> days</td>
                                <td>
                                    <span class="compliance-badge <?php echo $comp_class; ?>">
                                        <?php echo $comp_label; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 2rem; color: #6c757d;">
                                    No instructor compliance data available.
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
            window.location.href = 'ExportInstructorCompliance.php';
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
