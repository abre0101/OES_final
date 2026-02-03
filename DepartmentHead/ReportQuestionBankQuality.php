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
$pageTitle = "Question Bank Quality Report";
$deptId = $_SESSION['DeptId'] ?? null;

// Get question bank quality data by course
$qualityQuery = "SELECT c.course_code, c.course_name,
    COUNT(DISTINCT q.question_id) as total_questions,
    COUNT(DISTINCT e.exam_id) as total_exams,
    AVG(q.point_value) as avg_points,
    COUNT(DISTINCT q.question_id) as mcq_count,
    0 as tf_count,
    0 as sa_count,
    COUNT(DISTINCT CASE WHEN e.approval_status = 'approved' THEN e.exam_id END) as approved_exams
    FROM courses c
    LEFT JOIN exams e ON c.course_id = e.course_id
    LEFT JOIN exam_questions eq ON e.exam_id = eq.exam_id
    LEFT JOIN questions q ON eq.question_id = q.question_id
    WHERE c.department_id = ? AND c.is_active = 1
    GROUP BY c.course_id
    ORDER BY c.course_code";
$stmt = $con->prepare($qualityQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$courses = $stmt->get_result();

// Get overall statistics
$statsQuery = "SELECT 
    COUNT(DISTINCT q.question_id) as total_questions,
    COUNT(DISTINCT e.exam_id) as total_exams,
    AVG(q.point_value) as avg_question_points,
    COUNT(DISTINCT CASE WHEN e.approval_status = 'approved' THEN e.exam_id END) as quality_approved
    FROM courses c
    LEFT JOIN exams e ON c.course_id = e.course_id
    LEFT JOIN exam_questions eq ON e.exam_id = eq.exam_id
    LEFT JOIN questions q ON eq.question_id = q.question_id
    WHERE c.department_id = ?";
$stmt = $con->prepare($statsQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

$quality_rate = $stats['total_exams'] > 0 ? round(($stats['quality_approved'] / $stats['total_exams']) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Bank Quality Report - Department Head</title>
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
        .quality-badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .quality-excellent { background: #d4edda; color: #155724; }
        .quality-good { background: #d1ecf1; color: #0c5460; }
        .quality-average { background: #fff3cd; color: #856404; }
        .quality-poor { background: #f8d7da; color: #721c24; }
        .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-block; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .question-type-chart { display: flex; gap: 0.5rem; align-items: center; font-size: 0.85rem; }
        .type-bar { height: 20px; border-radius: 4px; display: inline-block; }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: #003366; font-weight: 700;">❓ Question Bank & Exam Quality Report</h1>
                    <p style="margin: 0; color: #6c757d; font-size: 1.05rem;">
                        Quality control for exams - prevents errors and ensures integrity
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
                    <div style="font-size: 2rem;">❓</div>
                    <div class="stat-value"><?php echo $stats['total_questions']; ?></div>
                    <div class="stat-label">Total Questions</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem;">📝</div>
                    <div class="stat-value"><?php echo $stats['total_exams']; ?></div>
                    <div class="stat-label">Total Exams</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem;">📊</div>
                    <div class="stat-value"><?php echo round($stats['avg_question_points'], 2); ?></div>
                    <div class="stat-label">Avg Points/Question</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 2rem;">✅</div>
                    <div class="stat-value" style="color: #28a745;"><?php echo $quality_rate; ?>%</div>
                    <div class="stat-label">Quality Approval Rate</div>
                </div>
            </div>

            <!-- Quality Data Table -->
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Total Questions</th>
                            <th>Total Exams</th>
                            <th>MCQ</th>
                            <th>True/False</th>
                            <th>Short Answer</th>
                            <th>Avg Points</th>
                            <th>Quality Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($courses->num_rows > 0): ?>
                            <?php 
                            $courses->data_seek(0);
                            while($course = $courses->fetch_assoc()): 
                                $questions_per_exam = $course['total_exams'] > 0 ? 
                                    round($course['total_questions'] / $course['total_exams'], 1) : 0;
                                
                                // Determine quality level
                                if($course['total_questions'] >= 20 && $course['approved_exams'] == $course['total_exams']) {
                                    $quality_class = 'quality-excellent';
                                    $quality_label = 'Excellent';
                                } elseif($course['total_questions'] >= 10 && $course['approved_exams'] >= $course['total_exams'] * 0.8) {
                                    $quality_class = 'quality-good';
                                    $quality_label = 'Good';
                                } elseif($course['total_questions'] >= 5) {
                                    $quality_class = 'quality-average';
                                    $quality_label = 'Average';
                                } else {
                                    $quality_class = 'quality-poor';
                                    $quality_label = 'Needs Improvement';
                                }
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($course['course_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><strong><?php echo $course['total_questions']; ?></strong></td>
                                <td><?php echo $course['total_exams']; ?></td>
                                <td><?php echo $course['mcq_count']; ?></td>
                                <td><?php echo $course['tf_count']; ?></td>
                                <td><?php echo $course['sa_count']; ?></td>
                                <td><?php echo $course['avg_points'] ? round($course['avg_points'], 2) : 'N/A'; ?></td>
                                <td>
                                    <span class="quality-badge <?php echo $quality_class; ?>">
                                        <?php echo $quality_label; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 2rem; color: #6c757d;">
                                    No question bank data available.
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
            window.location.href = 'ExportQuestionBankQuality.php';
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
