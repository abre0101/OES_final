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
$pageTitle = "Exam Results";
$exam_id = $_GET['id'] ?? 0;

// Get exam details
$examQuery = $con->prepare("SELECT e.*, c.course_name, c.course_code, ec.category_name,
    i.full_name as instructor_name
    FROM exams e
    INNER JOIN courses c ON e.course_id = c.course_id
    INNER JOIN exam_categories ec ON e.exam_category_id = ec.exam_category_id
    LEFT JOIN instructors i ON e.created_by = i.instructor_id
    WHERE e.exam_id = ?");
$examQuery->bind_param("i", $exam_id);
$examQuery->execute();
$exam = $examQuery->get_result()->fetch_assoc();

if(!$exam) {
    die("Exam not found.");
}

// Get exam results/attempts
$resultsQuery = $con->prepare("SELECT er.*, s.student_code, s.full_name as student_name, s.email,
    er.total_points_earned as score, er.exam_submitted_at as submitted_at
    FROM exam_results er
    INNER JOIN students s ON er.student_id = s.student_id
    WHERE er.exam_id = ?
    ORDER BY er.total_points_earned DESC, er.exam_submitted_at DESC");
$resultsQuery->bind_param("i", $exam_id);
$resultsQuery->execute();
$results = $resultsQuery->get_result();

// Calculate statistics
$total_attempts = $results->num_rows;
$passed = 0;
$failed = 0;
$total_score = 0;
$highest_score = 0;
$lowest_score = $exam['total_marks'];

$results->data_seek(0);
while($result = $results->fetch_assoc()) {
    $score = $result['score'];
    $total_score += $score;
    if($score >= $exam['pass_marks']) {
        $passed++;
    } else {
        $failed++;
    }
    if($score > $highest_score) {
        $highest_score = $score;
    }
    if($score < $lowest_score) {
        $lowest_score = $score;
    }
}

$average_score = $total_attempts > 0 ? round($total_score / $total_attempts, 2) : 0;
$pass_rate = $total_attempts > 0 ? round(($passed / $total_attempts) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); }
        .stat-label { font-size: 0.85rem; color: #6c757d; margin-bottom: 0.5rem; }
        .stat-value { font-size: 2rem; font-weight: 700; color: #003366; }
        .exam-info-card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); margin-bottom: 2rem; }
        .results-table { width: 100%; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); }
        .results-table table { width: 100%; border-collapse: collapse; }
        .results-table th { background: #003366; color: white; padding: 1rem; text-align: left; font-weight: 600; }
        .results-table td { padding: 1rem; border-bottom: 1px solid #e0e0e0; }
        .results-table tr:hover { background: #f8f9fa; }
        .status-badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .status-passed { background: #d4edda; color: #155724; }
        .status-failed { background: #f8d7da; color: #721c24; }
        .btn { padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; display: inline-block; font-weight: 600; }
        .btn-secondary { background: #6c757d; color: white; }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header">
                <h1>📈 Exam Results</h1>
                <p>View detailed results and statistics for this exam</p>
            </div>

            <div class="exam-info-card">
                <h2 style="margin: 0 0 1rem 0; color: #003366;"><?php echo htmlspecialchars($exam['exam_name']); ?></h2>
                <p style="margin: 0; color: #6c757d;">
                    <strong>Course:</strong> <?php echo htmlspecialchars($exam['course_code'] . ' - ' . $exam['course_name']); ?> | 
                    <strong>Category:</strong> <?php echo htmlspecialchars($exam['category_name']); ?> | 
                    <strong>Instructor:</strong> <?php echo htmlspecialchars($exam['instructor_name']); ?>
                </p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Attempts</div>
                    <div class="stat-value"><?php echo $total_attempts; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Passed</div>
                    <div class="stat-value" style="color: #28a745;"><?php echo $passed; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Failed</div>
                    <div class="stat-value" style="color: #dc3545;"><?php echo $failed; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Pass Rate</div>
                    <div class="stat-value"><?php echo $pass_rate; ?>%</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Average Score</div>
                    <div class="stat-value"><?php echo $average_score; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Highest Score</div>
                    <div class="stat-value" style="color: #28a745;"><?php echo $highest_score; ?></div>
                </div>
            </div>

            <div class="results-table">
                <table>
                    <thead>
                        <tr>
                            <th>Student Code</th>
                            <th>Student Name</th>
                            <th>Score</th>
                            <th>Percentage</th>
                            <th>Status</th>
                            <th>Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $results->data_seek(0);
                        if($total_attempts > 0):
                            while($result = $results->fetch_assoc()): 
                                $score = $result['score'];
                                $percentage = round(($score / $exam['total_marks']) * 100, 2);
                                $status = $score >= $exam['pass_marks'] ? 'passed' : 'failed';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result['student_code']); ?></td>
                            <td><?php echo htmlspecialchars($result['student_name']); ?></td>
                            <td><strong><?php echo $score; ?> / <?php echo $exam['total_marks']; ?></strong></td>
                            <td><?php echo $percentage; ?>%</td>
                            <td>
                                <span class="status-badge status-<?php echo $status; ?>">
                                    <?php echo strtoupper($status); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y h:i A', strtotime($result['submitted_at'])); ?></td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: #6c757d;">
                                No results available yet.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 2rem;">
                <a href="DepartmentExams.php" class="btn btn-secondary">← Back to Exams</a>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>
