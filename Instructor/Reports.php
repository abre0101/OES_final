<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$instructorName = $_SESSION['Name'];

// Get instructor ID
$instructorQuery = $con->prepare("SELECT instructor_id, department_id FROM instructors WHERE full_name = ?");
if (!$instructorQuery) {
    die("Prepare failed: " . $con->error);
}
$instructorQuery->bind_param("s", $instructorName);
$instructorQuery->execute();
$instructorData = $instructorQuery->get_result()->fetch_assoc();
$instructorId = $instructorData['instructor_id'] ?? 0;
$instructorDeptId = $instructorData['department_id'] ?? 0;

if ($instructorId == 0) {
    die("Instructor not found. Logged in as: " . htmlspecialchars($instructorName));
}

// Get report filters
$reportType = $_GET['type'] ?? 'overview';
$studentFilter = $_GET['student'] ?? '';
$courseFilter = $_GET['course'] ?? '';
$examFilter = $_GET['exam'] ?? '';
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Get instructor's courses
$coursesQuery = $con->prepare("
    SELECT DISTINCT c.course_id, c.course_code, c.course_name, c.semester, d.department_name
    FROM courses c
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    INNER JOIN departments d ON c.department_id = d.department_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE
    ORDER BY c.course_name
");
$coursesQuery->bind_param("i", $instructorId);
$coursesQuery->execute();
$coursesResult = $coursesQuery->get_result();
$courses = [];
while($row = $coursesResult->fetch_assoc()) {
    $courses[] = $row;
}

// Get students enrolled in instructor's courses
$studentsQuery = $con->prepare("
    SELECT DISTINCT s.student_id, s.student_code, s.full_name, s.email, d.department_name
    FROM students s
    INNER JOIN student_courses sc ON s.student_id = sc.student_id
    INNER JOIN instructor_courses ic ON sc.course_id = ic.course_id
    INNER JOIN departments d ON s.department_id = d.department_id
    WHERE ic.instructor_id = ? AND sc.is_active = TRUE
    ORDER BY s.full_name
");
$studentsQuery->bind_param("i", $instructorId);
$studentsQuery->execute();
$studentsResult = $studentsQuery->get_result();
$students = [];
while($row = $studentsResult->fetch_assoc()) {
    $students[] = $row;
}

// Get exams for instructor's courses
$examsQuery = $con->prepare("
    SELECT DISTINCT es.schedule_id, es.exam_name, es.exam_date, c.course_name, ec.category_name
    FROM exam_schedules es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    WHERE ic.instructor_id = ? AND es.is_active = TRUE
    ORDER BY es.exam_date DESC
");
$examsQuery->bind_param("i", $instructorId);
$examsQuery->execute();
$examsResult = $examsQuery->get_result();
$exams = [];
while($row = $examsResult->fetch_assoc()) {
    $exams[] = $row;
}

// OVERVIEW STATISTICS
$statsQuery = $con->prepare("
    SELECT 
        COUNT(DISTINCT er.student_id) as total_students,
        COUNT(DISTINCT er.result_id) as total_exams,
        AVG(er.percentage_score) as avg_score,
        SUM(CASE WHEN er.pass_status = 'Pass' THEN 1 ELSE 0 END) as total_passed,
        SUM(CASE WHEN er.pass_status = 'Fail' THEN 1 ELSE 0 END) as total_failed
    FROM exam_results er
    INNER JOIN exam_schedules es ON er.schedule_id = es.schedule_id
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ?
");
$statsQuery->bind_param("i", $instructorId);
$statsQuery->execute();
$stats = $statsQuery->get_result()->fetch_assoc();

$totalStudents = $stats['total_students'] ?? 0;
$totalExams = $stats['total_exams'] ?? 0;
$avgScore = $stats['avg_score'] ?? 0;
$passRate = $totalExams > 0 ? (($stats['total_passed'] ?? 0) / $totalExams * 100) : 0;

// GRADE DISTRIBUTION
$gradeDistQuery = $con->prepare("
    SELECT 
        er.letter_grade,
        COUNT(*) as count,
        AVG(er.percentage_score) as avg_percentage
    FROM exam_results er
    INNER JOIN exam_schedules es ON er.schedule_id = es.schedule_id
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ?
    GROUP BY er.letter_grade
    ORDER BY MIN(er.percentage_score) DESC
");
$gradeDistQuery->bind_param("i", $instructorId);
$gradeDistQuery->execute();
$gradeDistResult = $gradeDistQuery->get_result();
$gradeDistribution = [];
while($row = $gradeDistResult->fetch_assoc()) {
    $gradeDistribution[] = $row;
}

// COURSE PERFORMANCE
$coursePerformanceQuery = $con->prepare("
    SELECT 
        c.course_code,
        c.course_name,
        COUNT(DISTINCT er.student_id) as enrolled_students,
        COUNT(er.result_id) as total_attempts,
        AVG(er.percentage_score) as avg_score,
        MAX(er.percentage_score) as highest_score,
        MIN(er.percentage_score) as lowest_score,
        SUM(CASE WHEN er.pass_status = 'Pass' THEN 1 ELSE 0 END) as passed,
        SUM(CASE WHEN er.pass_status = 'Fail' THEN 1 ELSE 0 END) as failed
    FROM courses c
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    LEFT JOIN exam_schedules es ON c.course_id = es.course_id
    LEFT JOIN exam_results er ON es.schedule_id = er.schedule_id
    WHERE ic.instructor_id = ?
    GROUP BY c.course_id, c.course_code, c.course_name
    ORDER BY c.course_name
");
$coursePerformanceQuery->bind_param("i", $instructorId);
$coursePerformanceQuery->execute();
$coursePerformanceResult = $coursePerformanceQuery->get_result();

// INDIVIDUAL STUDENT PERFORMANCE (if student filter is applied)
$studentPerformance = null;
if($studentFilter) {
    $studentPerfQuery = $con->prepare("
        SELECT 
            s.student_code,
            s.full_name as student_name,
            s.email,
            c.course_code,
            c.course_name,
            es.exam_name,
            ec.category_name,
            er.percentage_score,
            er.letter_grade,
            er.gpa,
            er.pass_status,
            er.correct_answers,
            er.total_questions,
            er.exam_submitted_at,
            er.time_taken_minutes,
            er.result_id
        FROM exam_results er
        INNER JOIN students s ON er.student_id = s.student_id
        INNER JOIN exam_schedules es ON er.schedule_id = es.schedule_id
        INNER JOIN courses c ON es.course_id = c.course_id
        INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
        INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
        WHERE ic.instructor_id = ? AND s.student_id = ?
        ORDER BY er.exam_submitted_at DESC
    ");
    $studentPerfQuery->bind_param("ii", $instructorId, $studentFilter);
    $studentPerfQuery->execute();
    $studentPerformance = $studentPerfQuery->get_result();
}

// EXAM-SPECIFIC PERFORMANCE (if exam filter is applied)
$examPerformance = null;
if($examFilter) {
    $examPerfQuery = $con->prepare("
        SELECT 
            s.student_code,
            s.full_name as student_name,
            er.percentage_score,
            er.letter_grade,
            er.pass_status,
            er.correct_answers,
            er.wrong_answers,
            er.unanswered,
            er.total_questions,
            er.time_taken_minutes,
            er.exam_submitted_at
        FROM exam_results er
        INNER JOIN students s ON er.student_id = s.student_id
        WHERE er.schedule_id = ?
        ORDER BY er.percentage_score DESC
    ");
    $examPerfQuery->bind_param("i", $examFilter);
    $examPerfQuery->execute();
    $examPerformance = $examPerfQuery->get_result();
}

// TOP PERFORMERS
$topPerformersQuery = $con->prepare("
    SELECT 
        s.student_code,
        s.full_name,
        AVG(er.percentage_score) as avg_score,
        AVG(er.gpa) as avg_gpa,
        COUNT(er.result_id) as exams_taken
    FROM exam_results er
    INNER JOIN students s ON er.student_id = s.student_id
    INNER JOIN exam_schedules es ON er.schedule_id = es.schedule_id
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ?
    GROUP BY s.student_id, s.student_code, s.full_name
    HAVING COUNT(er.result_id) > 0
    ORDER BY avg_score DESC
    LIMIT 10
");
$topPerformersQuery->bind_param("i", $instructorId);
$topPerformersQuery->execute();
$topPerformersResult = $topPerformersQuery->get_result();

// QUESTION ANALYSIS
$questionAnalysisQuery = $con->prepare("
    SELECT 
        q.question_id,
        LEFT(q.question_text, 100) as question_preview,
        qt.topic_name,
        q.difficulty_level,
        COUNT(sa.answer_id) as times_answered,
        SUM(CASE WHEN sa.is_correct = TRUE THEN 1 ELSE 0 END) as correct_count,
        (SUM(CASE WHEN sa.is_correct = TRUE THEN 1 ELSE 0 END) * 100.0 / COUNT(sa.answer_id)) as success_rate
    FROM questions q
    INNER JOIN courses c ON q.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    LEFT JOIN question_topics qt ON q.topic_id = qt.topic_id
    LEFT JOIN student_answers sa ON q.question_id = sa.question_id
    WHERE ic.instructor_id = ?
    GROUP BY q.question_id, q.question_text, qt.topic_name, q.difficulty_level
    HAVING COUNT(sa.answer_id) > 0
    ORDER BY success_rate ASC
    LIMIT 20
");
$questionAnalysisQuery->bind_param("i", $instructorId);
$questionAnalysisQuery->execute();
$questionAnalysisResult = $questionAnalysisQuery->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - OES</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        .reports-header { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2); margin-bottom: 2rem; }
        .reports-header h1 { margin: 0 0 0.5rem 0; font-size: 2.2rem; font-weight: 800; display: flex; align-items: center; gap: 1rem; color: white; }
        .reports-header h1 span { color: white; }
        .reports-header p { margin: 0; opacity: 0.95; font-size: 1.05rem; color: white; }
        .filter-section { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); margin-bottom: 2rem; }
        .filter-section h3 { margin: 0 0 1.5rem 0; font-size: 1.5rem; font-weight: 700; color: #003366; }
        .filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem; }
        .filter-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #003366; font-size: 0.95rem; }
        .filter-group select, .filter-group input { width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 0.95rem; transition: all 0.3s ease; font-family: 'Poppins', sans-serif; }
        .filter-group select:focus, .filter-group input:focus { outline: none; border-color: #003366; box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1); }
        .filter-actions { display: flex; gap: 1rem; flex-wrap: wrap; }
        .btn-filter { padding: 0.85rem 1.75rem; border-radius: 8px; font-weight: 600; font-size: 0.95rem; border: none; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-primary { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3); }
        .btn-export { background: linear-gradient(135deg, #28a745 0%, #218838 100%); color: white; }
        .btn-export:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3); }
        .btn-print { background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); color: white; }
        .btn-print:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(111, 66, 193, 0.3); }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; border-radius: 12px; padding: 1.75rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); border-left: 5px solid; transition: transform 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card.primary { border-left-color: #007bff; }
        .stat-card.success { border-left-color: #28a745; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.info { border-left-color: #17a2b8; }
        .stat-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
        .stat-value { font-size: 2.5rem; font-weight: 900; color: #003366; margin-bottom: 0.5rem; }
        .stat-label { font-size: 0.95rem; color: #6c757d; font-weight: 500; }
        .report-section { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); margin-bottom: 2rem; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 3px solid #f0f0f0; }
        .section-title { font-size: 1.4rem; font-weight: 700; color: #003366; display: flex; align-items: center; gap: 0.75rem; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); }
        .data-table th { padding: 1rem; text-align: left; color: white; font-weight: 600; font-size: 0.9rem; white-space: nowrap; }
        .data-table td { padding: 0.85rem 1rem; border-bottom: 1px solid #e8eef3; font-size: 0.9rem; }
        .data-table tbody tr:hover { background: #f8f9fa; }
        .score-badge { padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 600; font-size: 0.85rem; display: inline-block; }
        .badge-excellent { background: #d4edda; color: #155724; }
        .badge-good { background: #d1ecf1; color: #0c5460; }
        .badge-average { background: #fff3cd; color: #856404; }
        .badge-poor { background: #f8d7da; color: #721c24; }
        .pass-badge { background: #d4edda; color: #155724; padding: 0.25rem 0.75rem; border-radius: 6px; font-weight: 600; }
        .fail-badge { background: #f8d7da; color: #721c24; padding: 0.25rem 0.75rem; border-radius: 6px; font-weight: 600; }
        .chart-container { margin: 1.5rem 0; padding: 1.5rem; background: #f8f9fa; border-radius: 8px; }
        .no-data { text-align: center; padding: 3rem; color: #6c757d; font-size: 1.1rem; }
        .no-data-icon { font-size: 4rem; margin-bottom: 1rem; opacity: 0.5; }
        @media print { .filter-section, .admin-sidebar, .admin-header, .filter-actions { display: none; } .admin-main-content { margin-left: 0; } .stat-card { break-inside: avoid; } }
        @media (max-width: 768px) { .stats-grid { grid-template-columns: 1fr; } .filter-grid { grid-template-columns: 1fr; } .data-table { font-size: 0.8rem; } .data-table th, .data-table td { padding: 0.5rem; } }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>
    <div class="admin-main-content">
        <?php $pageTitle = 'Reports & Analytics'; include 'header-component.php'; ?>
        <div class="admin-content">
            <div class="reports-header">
                <h1><span>📊</span> Reports & Analytics</h1>
                <p>Comprehensive performance analysis and insights for your courses</p>
            </div>
            <div class="filter-section">
                <h3>🔍 Filter Reports</h3>
                <form method="GET" action="">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label>Report Type</label>
                            <select name="type">
                                <option value="overview" <?php echo $reportType == 'overview' ? 'selected' : ''; ?>>Overview Dashboard</option>
                                <option value="individual" <?php echo $reportType == 'individual' ? 'selected' : ''; ?>>Individual Student</option>
                                <option value="exam" <?php echo $reportType == 'exam' ? 'selected' : ''; ?>>Exam Analysis</option>
                                <option value="question" <?php echo $reportType == 'question' ? 'selected' : ''; ?>>Question Analysis</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Select Student</label>
                            <select name="student">
                                <option value="">All Students</option>
                                <?php foreach($students as $student): ?>
                                <option value="<?php echo $student['student_id']; ?>" <?php echo $studentFilter == $student['student_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($student['full_name']); ?> (<?php echo $student['student_code']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Select Course</label>
                            <select name="course">
                                <option value="">All Courses</option>
                                <?php foreach($courses as $course): ?>
                                <option value="<?php echo $course['course_id']; ?>" <?php echo $courseFilter == $course['course_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_code']); ?> - <?php echo htmlspecialchars($course['course_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Select Exam</label>
                            <select name="exam">
                                <option value="">All Exams</option>
                                <?php foreach($exams as $exam): ?>
                                <option value="<?php echo $exam['schedule_id']; ?>" <?php echo $examFilter == $exam['schedule_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($exam['exam_name']); ?> (<?php echo date('M d, Y', strtotime($exam['exam_date'])); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn-filter btn-primary"><span>🔍</span> Generate Report</button>
                        <button type="button" class="btn-filter btn-export" onclick="exportToCSV()"><span>📥</span> Export to CSV</button>
                        <button type="button" class="btn-filter btn-print" onclick="window.print()"><span>🖨️</span> Print Report</button>
                    </div>
                </form>
            </div>
            <div class="stats-grid">
                <div class="stat-card primary"><div class="stat-icon">👨‍🎓</div><div class="stat-value"><?php echo number_format($totalStudents); ?></div><div class="stat-label">Total Students</div></div>
                <div class="stat-card success"><div class="stat-icon">📝</div><div class="stat-value"><?php echo number_format($totalExams); ?></div><div class="stat-label">Total Exam Attempts</div></div>
                <div class="stat-card warning"><div class="stat-icon">📊</div><div class="stat-value"><?php echo number_format($avgScore, 1); ?>%</div><div class="stat-label">Average Score</div></div>
                <div class="stat-card info"><div class="stat-icon">✅</div><div class="stat-value"><?php echo number_format($passRate, 1); ?>%</div><div class="stat-label">Pass Rate</div></div>
            </div>
            <?php if($studentFilter && $studentPerformance && $studentPerformance->num_rows > 0): ?>
            <div class="report-section">
                <div class="section-header"><h2 class="section-title"><span>👤</span> Individual Student Performance</h2></div>
                <table class="data-table">
                    <thead><tr><th>Course</th><th>Exam Name</th><th>Category</th><th>Score</th><th>Grade</th><th>GPA</th><th>Status</th><th>Correct/Total</th><th>Time Taken</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php while($row = $studentPerformance->fetch_assoc()): 
                            $score = $row['percentage_score'];
                            $badgeClass = $score >= 85 ? 'badge-excellent' : ($score >= 70 ? 'badge-good' : ($score >= 50 ? 'badge-average' : 'badge-poor'));
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['course_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['exam_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td><span class="score-badge <?php echo $badgeClass; ?>"><?php echo number_format($score, 1); ?>%</span></td>
                            <td><strong><?php echo $row['letter_grade']; ?></strong></td>
                            <td><?php echo number_format($row['gpa'], 2); ?></td>
                            <td><span class="<?php echo $row['pass_status'] == 'Pass' ? 'pass-badge' : 'fail-badge'; ?>"><?php echo $row['pass_status']; ?></span></td>
                            <td><?php echo $row['correct_answers']; ?> / <?php echo $row['total_questions']; ?></td>
                            <td><?php echo $row['time_taken_minutes']; ?> min</td>
                            <td><?php echo date('M d, Y', strtotime($row['exam_submitted_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            <?php if($examFilter && $examPerformance && $examPerformance->num_rows > 0): ?>
            <div class="report-section">
                <div class="section-header"><h2 class="section-title"><span>📋</span> Exam Performance Analysis</h2></div>
                <table class="data-table">
                    <thead><tr><th>Student Code</th><th>Student Name</th><th>Score</th><th>Grade</th><th>Status</th><th>Correct</th><th>Wrong</th><th>Unanswered</th><th>Time Taken</th><th>Submitted</th></tr></thead>
                    <tbody>
                        <?php while($row = $examPerformance->fetch_assoc()): 
                            $score = $row['percentage_score'];
                            $badgeClass = $score >= 85 ? 'badge-excellent' : ($score >= 70 ? 'badge-good' : ($score >= 50 ? 'badge-average' : 'badge-poor'));
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['student_code']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['student_name']); ?></strong></td>
                            <td><span class="score-badge <?php echo $badgeClass; ?>"><?php echo number_format($score, 1); ?>%</span></td>
                            <td><strong><?php echo $row['letter_grade']; ?></strong></td>
                            <td><span class="<?php echo $row['pass_status'] == 'Pass' ? 'pass-badge' : 'fail-badge'; ?>"><?php echo $row['pass_status']; ?></span></td>
                            <td style="color: #28a745; font-weight: 600;"><?php echo $row['correct_answers']; ?></td>
                            <td style="color: #dc3545; font-weight: 600;"><?php echo $row['wrong_answers']; ?></td>
                            <td style="color: #ffc107; font-weight: 600;"><?php echo $row['unanswered']; ?></td>
                            <td><?php echo $row['time_taken_minutes']; ?> min</td>
                            <td><?php echo date('M d, H:i', strtotime($row['exam_submitted_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            <div class="report-section">
                <div class="section-header"><h2 class="section-title"><span>📚</span> Course Performance Summary</h2></div>
                <?php if($coursePerformanceResult->num_rows > 0): ?>
                <table class="data-table">
                    <thead><tr><th>Course Code</th><th>Course Name</th><th>Students</th><th>Attempts</th><th>Avg Score</th><th>Highest</th><th>Lowest</th><th>Passed</th><th>Failed</th><th>Pass Rate</th></tr></thead>
                    <tbody>
                        <?php while($row = $coursePerformanceResult->fetch_assoc()): 
                            $coursePassRate = $row['total_attempts'] > 0 ? ($row['passed'] / $row['total_attempts'] * 100) : 0;
                            $avgScore = $row['avg_score'] ?? 0;
                            $badgeClass = $avgScore >= 85 ? 'badge-excellent' : ($avgScore >= 70 ? 'badge-good' : ($avgScore >= 50 ? 'badge-average' : 'badge-poor'));
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['course_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                            <td><?php echo $row['enrolled_students'] ?? 0; ?></td>
                            <td><?php echo $row['total_attempts'] ?? 0; ?></td>
                            <td><span class="score-badge <?php echo $badgeClass; ?>"><?php echo number_format($avgScore, 1); ?>%</span></td>
                            <td><?php echo number_format($row['highest_score'] ?? 0, 1); ?>%</td>
                            <td><?php echo number_format($row['lowest_score'] ?? 0, 1); ?>%</td>
                            <td style="color: #28a745; font-weight: 700;"><?php echo $row['passed'] ?? 0; ?></td>
                            <td style="color: #dc3545; font-weight: 700;"><?php echo $row['failed'] ?? 0; ?></td>
                            <td><span class="score-badge <?php echo $coursePassRate >= 70 ? 'badge-excellent' : ($coursePassRate >= 50 ? 'badge-good' : 'badge-poor'); ?>"><?php echo number_format($coursePassRate, 1); ?>%</span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-data"><div class="no-data-icon">📭</div><p>No course performance data available yet.</p></div>
                <?php endif; ?>
            </div>
            <?php if(count($gradeDistribution) > 0): ?>
            <div class="report-section">
                <div class="section-header"><h2 class="section-title"><span>📊</span> Grade Distribution</h2></div>
                <div class="chart-container"><canvas id="gradeChart" style="max-height: 350px;"></canvas></div>
                <table class="data-table" style="margin-top: 2rem;">
                    <thead><tr><th>Grade</th><th>Count</th><th>Avg Percentage</th><th>Distribution</th></tr></thead>
                    <tbody>
                        <?php 
                        $totalGrades = array_sum(array_column($gradeDistribution, 'count'));
                        foreach($gradeDistribution as $grade): 
                            $percentage = $totalGrades > 0 ? ($grade['count'] / $totalGrades * 100) : 0;
                            $barWidth = $percentage;
                        ?>
                        <tr>
                            <td><strong style="font-size: 1.2rem;"><?php echo $grade['letter_grade']; ?></strong></td>
                            <td><?php echo $grade['count']; ?></td>
                            <td><?php echo number_format($grade['avg_percentage'], 1); ?>%</td>
                            <td><div style="background: linear-gradient(90deg, #003366 0%, #0055aa 100%); height: 28px; width: <?php echo $barWidth; ?>%; border-radius: 6px; display: flex; align-items: center; padding-left: 10px; color: white; font-weight: 700; font-size: 0.85rem; min-width: 40px;"><?php echo number_format($percentage, 1); ?>%</div></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            <?php if($topPerformersResult->num_rows > 0): ?>
            <div class="report-section">
                <div class="section-header"><h2 class="section-title"><span>🏆</span> Top Performers</h2></div>
                <table class="data-table">
                    <thead><tr><th>Rank</th><th>Student Code</th><th>Student Name</th><th>Average Score</th><th>Average GPA</th><th>Exams Taken</th></tr></thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        while($row = $topPerformersResult->fetch_assoc()): 
                            $avgScore = $row['avg_score'];
                            $badgeClass = $avgScore >= 90 ? 'badge-excellent' : ($avgScore >= 80 ? 'badge-good' : 'badge-average');
                        ?>
                        <tr>
                            <td><strong><?php echo $rank++; ?></strong></td>
                            <td><?php echo htmlspecialchars($row['student_code']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                            <td><span class="score-badge <?php echo $badgeClass; ?>"><?php echo number_format($avgScore, 1); ?>%</span></td>
                            <td><?php echo number_format($row['avg_gpa'], 2); ?></td>
                            <td><?php echo $row['exams_taken']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            <?php if($reportType == 'question' && $questionAnalysisResult->num_rows > 0): ?>
            <div class="report-section">
                <div class="section-header"><h2 class="section-title"><span>❓</span> Question Difficulty Analysis</h2><p style="margin: 0; color: #6c757d; font-size: 0.9rem;">Questions with lowest success rates (most difficult)</p></div>
                <table class="data-table">
                    <thead><tr><th style="width: 40%;">Question Preview</th><th>Topic</th><th>Difficulty</th><th>Times Answered</th><th>Correct</th><th>Success Rate</th></tr></thead>
                    <tbody>
                        <?php while($row = $questionAnalysisResult->fetch_assoc()): 
                            $successRate = $row['success_rate'] ?? 0;
                            $badgeClass = $successRate >= 70 ? 'badge-excellent' : ($successRate >= 50 ? 'badge-average' : 'badge-poor');
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['question_preview']); ?>...</td>
                            <td><?php echo htmlspecialchars($row['topic_name'] ?? 'N/A'); ?></td>
                            <td><span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; <?php if($row['difficulty_level'] == 'Easy') echo 'background: #d4edda; color: #155724;'; elseif($row['difficulty_level'] == 'Medium') echo 'background: #fff3cd; color: #856404;'; else echo 'background: #f8d7da; color: #721c24;'; ?>"><?php echo $row['difficulty_level']; ?></span></td>
                            <td><?php echo $row['times_answered']; ?></td>
                            <td style="color: #28a745; font-weight: 600;"><?php echo $row['correct_count']; ?></td>
                            <td><span class="score-badge <?php echo $badgeClass; ?>"><?php echo number_format($successRate, 1); ?>%</span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        <?php if(count($gradeDistribution) > 0): ?>
        const gradeData = <?php echo json_encode($gradeDistribution); ?>;
        const gradeLabels = gradeData.map(g => g.letter_grade);
        const gradeCounts = gradeData.map(g => parseInt(g.count));
        const gradeCtx = document.getElementById('gradeChart').getContext('2d');
        new Chart(gradeCtx, {
            type: 'bar',
            data: { labels: gradeLabels, datasets: [{ label: 'Number of Students', data: gradeCounts, backgroundColor: ['rgba(40, 167, 69, 0.8)','rgba(40, 167, 69, 0.7)','rgba(23, 162, 184, 0.8)','rgba(23, 162, 184, 0.7)','rgba(255, 193, 7, 0.8)','rgba(255, 193, 7, 0.7)','rgba(253, 126, 20, 0.8)','rgba(220, 53, 69, 0.8)','rgba(220, 53, 69, 0.7)','rgba(108, 117, 125, 0.8)'], borderColor: ['rgba(40, 167, 69, 1)','rgba(40, 167, 69, 1)','rgba(23, 162, 184, 1)','rgba(23, 162, 184, 1)','rgba(255, 193, 7, 1)','rgba(255, 193, 7, 1)','rgba(253, 126, 20, 1)','rgba(220, 53, 69, 1)','rgba(220, 53, 69, 1)','rgba(108, 117, 125, 1)'], borderWidth: 2 }] },
            options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false }, title: { display: true, text: 'Grade Distribution Across All Courses', font: { size: 16, weight: 'bold', family: 'Poppins' } } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, font: { family: 'Poppins' } } }, x: { ticks: { font: { family: 'Poppins', weight: 'bold' } } } } }
        });
        <?php endif; ?>
        function exportToCSV() {
            let csv = 'Course Performance Report\nGenerated: ' + new Date().toLocaleString() + '\n\nCourse Code,Course Name,Students,Attempts,Avg Score,Highest,Lowest,Passed,Failed,Pass Rate\n';
            const table = document.querySelector('.data-table tbody');
            if(table) {
                const rows = table.querySelectorAll('tr');
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    const rowData = [];
                    cells.forEach((cell, index) => {
                        let text = cell.textContent.trim().replace(/,/g, ';').replace(/\n/g, ' ');
                        rowData.push(text);
                    });
                    csv += rowData.join(',') + '\n';
                });
            }
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'instructor_report_' + new Date().toISOString().split('T')[0] + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
