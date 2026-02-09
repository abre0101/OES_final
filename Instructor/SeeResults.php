<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Instructor session
SessionManager::startSession('Instructor');

// Check if user is logged in
if(!isset($_SESSION['ID'])){
    header("Location: ../auth/staff-login.php");
    exit();
}

// Validate instructor role
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Instructor'){
    SessionManager::destroySession();
    header("Location: ../auth/staff-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "See Results";
$instructor_id = $_SESSION['ID'];

// Get filter parameters
$exam_id = $_GET['exam_id'] ?? null;
$course_id = $_GET['course_id'] ?? null;
$grade_filter = $_GET['grade'] ?? null;
$pass_status = $_GET['pass_status'] ?? null;

// Build query for results from instructor's courses
$query = "SELECT 
    er.result_id,
    er.total_questions,
    er.correct_answers,
    er.wrong_answers,
    er.percentage_score,
    er.letter_grade,
    er.pass_status,
    er.exam_submitted_at,
    s.student_code,
    s.full_name as student_name,
    es.exam_name,
    c.course_name,
    c.course_code
    FROM exam_results er
    INNER JOIN students s ON er.student_id = s.student_id
    INNER JOIN exams es ON er.exam_id = es.exam_id
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ?";

$params = [$instructor_id];
$types = "i";

if($exam_id) {
    $query .= " AND er.exam_id = ?";
    $params[] = $exam_id;
    $types .= "i";
}

if($course_id) {
    $query .= " AND c.course_id = ?";
    $params[] = $course_id;
    $types .= "i";
}

if($grade_filter) {
    $query .= " AND er.letter_grade = ?";
    $params[] = $grade_filter;
    $types .= "s";
}

if($pass_status) {
    $query .= " AND er.pass_status = ?";
    $params[] = $pass_status;
    $types .= "s";
}

$query .= " ORDER BY er.exam_submitted_at DESC LIMIT 100";

$stmt = $con->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$results = $stmt->get_result();

// Get instructor's courses for filter
$coursesQuery = $con->prepare("SELECT DISTINCT c.course_id, c.course_name, c.course_code
    FROM instructor_courses ic
    INNER JOIN courses c ON ic.course_id = c.course_id
    WHERE ic.instructor_id = ?
    ORDER BY c.course_name");
$coursesQuery->bind_param("i", $instructor_id);
$coursesQuery->execute();
$courses = $coursesQuery->get_result();

// Get statistics
$statsQuery = $con->prepare("SELECT 
    COUNT(DISTINCT er.result_id) as total_results,
    COUNT(DISTINCT er.student_id) as total_students,
    AVG(er.percentage_score) as avg_score,
    SUM(CASE WHEN er.pass_status = 'Pass' THEN 1 ELSE 0 END) as passed_count
    FROM exam_results er
    INNER JOIN exams es ON er.exam_id = es.exam_id
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ?");
$statsQuery->bind_param("i", $instructor_id);
$statsQuery->execute();
$stats = $statsQuery->get_result()->fetch_assoc();
$statsQuery->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results Overview - Instructor</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header">
                <h1>📊 Results Overview</h1>
                <p>View and analyze student performance across all exams</p>
            </div>

            <!-- Stats -->
            <div class="stats-grid" style="margin-bottom: 2rem;">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">📝</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $stats['total_results']; ?></div>
                        <div class="stat-label">Total Results</div>
                    </div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $stats['total_students']; ?></div>
                        <div class="stat-label">Students</div>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">📈</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo round($stats['avg_score'], 1); ?>%</div>
                        <div class="stat-label">Average Score</div>
                    </div>
                </div>
                
                <div class="stat-card stat-info">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $stats['passed_count']; ?></div>
                        <div class="stat-label">Passed</div>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <form method="GET" action="">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                        <div class="form-group" style="margin: 0;">
                            <label>Filter by Grade</label>
                            <select name="grade" class="form-control">
                                <option value="">All Grades</option>
                                <option value="A+" <?php echo ($grade_filter == 'A+') ? 'selected' : ''; ?>>A+</option>
                                <option value="A" <?php echo ($grade_filter == 'A') ? 'selected' : ''; ?>>A</option>
                                <option value="A-" <?php echo ($grade_filter == 'A-') ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo ($grade_filter == 'B+') ? 'selected' : ''; ?>>B+</option>
                                <option value="B" <?php echo ($grade_filter == 'B') ? 'selected' : ''; ?>>B</option>
                                <option value="B-" <?php echo ($grade_filter == 'B-') ? 'selected' : ''; ?>>B-</option>
                                <option value="C+" <?php echo ($grade_filter == 'C+') ? 'selected' : ''; ?>>C+</option>
                                <option value="C" <?php echo ($grade_filter == 'C') ? 'selected' : ''; ?>>C</option>
                                <option value="C-" <?php echo ($grade_filter == 'C-') ? 'selected' : ''; ?>>C-</option>
                                <option value="D" <?php echo ($grade_filter == 'D') ? 'selected' : ''; ?>>D</option>
                                <option value="F" <?php echo ($grade_filter == 'F') ? 'selected' : ''; ?>>F</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="margin: 0;">
                            <label>Filter by Status</label>
                            <select name="pass_status" class="form-control">
                                <option value="">All Status</option>
                                <option value="Pass" <?php echo ($pass_status == 'Pass') ? 'selected' : ''; ?>>✅ Passed</option>
                                <option value="Fail" <?php echo ($pass_status == 'Fail') ? 'selected' : ''; ?>>❌ Failed</option>
                            </select>
                        </div>
                        
                        <div style="display: flex; gap: 0.5rem;">
                            <button type="submit" class="btn btn-primary">🔍 Filter</button>
                            <a href="SeeResults.php" class="btn btn-secondary">Clear</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Results Table -->
            <div class="data-table-wrapper">
                <div class="table-header">
                    <h3 class="table-title">Student Results</h3>
                    <div class="table-actions">
                        <button class="btn btn-success btn-sm" onclick="exportResults()">
                            📤 Export Excel
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="window.print()">
                            🖨️ Print
                        </button>
                    </div>
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Exam</th>
                                <th>Score</th>
                                <th>Grade</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($results && $results->num_rows > 0): ?>
                                <?php while($result = $results->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($result['student_code']); ?></td>
                                    <td><?php echo htmlspecialchars($result['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($result['exam_name']); ?><br>
                                        <small style="color: var(--text-secondary);"><?php echo htmlspecialchars($result['course_name']); ?></small>
                                    </td>
                                    <td><strong><?php echo round($result['percentage_score'], 1); ?>%</strong></td>
                                    <td>
                                        <span class="badge <?php echo $result['pass_status'] == 'Pass' ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo $result['letter_grade']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($result['exam_submitted_at'])); ?></td>
                                    <td>
                                        <a href="ViewStudentResult.php?result_id=<?php echo $result['result_id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 2rem;">
                                        No results available
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function exportResults() {
            alert('Export functionality will be implemented');
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
