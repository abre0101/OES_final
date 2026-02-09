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
$pageTitle = "Reports & Analytics";
$instructor_id = $_SESSION['ID'];
$instructor_name = $_SESSION['Name'];

// Get active tab
$activeTab = $_GET['tab'] ?? 'overview';

// Get report filters
$timeRange = $_GET['time_range'] ?? 'month';
$courseFilter = $_GET['course'] ?? 'all';
$studentFilter = $_GET['student'] ?? 'all';
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Set time range dynamically
if ($timeRange === 'week') {
    $startDate = date('Y-m-d', strtotime('-7 days'));
    $endDate = date('Y-m-d');
} elseif ($timeRange === 'month') {
    $startDate = date('Y-m-01');
    $endDate = date('Y-m-d');
} elseif ($timeRange === 'quarter') {
    $startDate = date('Y-m-01', strtotime('-3 months'));
    $endDate = date('Y-m-d');
} elseif ($timeRange === 'year') {
    $startDate = date('Y-01-01');
    $endDate = date('Y-m-d');
}

// Get instructor's courses for dropdown
$coursesQuery = $con->prepare("SELECT DISTINCT c.course_id, c.course_code, c.course_name 
                               FROM courses c 
                               INNER JOIN instructor_courses ic ON c.course_id = ic.course_id 
                               WHERE ic.instructor_id = ? 
                               ORDER BY c.course_name");
$coursesQuery->bind_param("i", $instructor_id);
$coursesQuery->execute();
$courses = $coursesQuery->get_result();

// Get students for dropdown
$studentsQuery = $con->prepare("SELECT DISTINCT s.student_id, s.student_code, s.full_name 
                                FROM students s
                                INNER JOIN student_courses sc ON s.student_id = sc.student_id
                                INNER JOIN instructor_courses ic ON sc.course_id = ic.course_id
                                WHERE ic.instructor_id = ?
                                ORDER BY s.full_name");
$studentsQuery->bind_param("i", $instructor_id);
$studentsQuery->execute();
$students = $studentsQuery->get_result();

// OVERVIEW STATISTICS
$statsQuery = $con->prepare("
    SELECT 
        COUNT(DISTINCT ic.course_id) as total_courses,
        COUNT(DISTINCT sc.student_id) as total_students,
        COUNT(DISTINCT q.question_id) as total_questions,
        COUNT(DISTINCT es.exam_id) as total_exams,
        COUNT(DISTINCT CASE WHEN es.approval_status = 'approved' THEN es.exam_id END) as approved_exams,
        COALESCE(AVG(er.percentage_score), 0) as avg_score,
        COALESCE(SUM(CASE WHEN er.pass_status = 'Pass' THEN 1 ELSE 0 END), 0) as total_passed,
        COALESCE(SUM(CASE WHEN er.pass_status = 'Fail' THEN 1 ELSE 0 END), 0) as total_failed,
        COALESCE(SUM(CASE WHEN er.letter_grade IN ('A+', 'A', 'A-') THEN 1 ELSE 0 END), 0) as grade_a,
        COALESCE(SUM(CASE WHEN er.letter_grade IN ('B+', 'B', 'B-') THEN 1 ELSE 0 END), 0) as grade_b,
        COALESCE(SUM(CASE WHEN er.letter_grade IN ('C+', 'C', 'C-') THEN 1 ELSE 0 END), 0) as grade_c,
        COALESCE(SUM(CASE WHEN er.letter_grade IN ('D+', 'D', 'D-') THEN 1 ELSE 0 END), 0) as grade_d,
        COALESCE(SUM(CASE WHEN er.letter_grade = 'F' THEN 1 ELSE 0 END), 0) as grade_f
    FROM instructor_courses ic
    LEFT JOIN student_courses sc ON ic.course_id = sc.course_id
    LEFT JOIN questions q ON ic.course_id = q.course_id
    LEFT JOIN exams es ON ic.course_id = es.course_id
    LEFT JOIN exam_results er ON es.exam_id = er.exam_id
    WHERE ic.instructor_id = ?
");
$statsQuery->bind_param("i", $instructor_id);
$statsQuery->execute();
$stats = $statsQuery->get_result()->fetch_assoc();

// COURSE PERFORMANCE DATA
$coursePerformanceQuery = $con->prepare("
    SELECT 
        c.course_id,
        c.course_code,
        c.course_name,
        COUNT(DISTINCT sc.student_id) as enrolled_students,
        COUNT(DISTINCT es.exam_id) as total_exams,
        COALESCE(AVG(er.percentage_score), 0) as avg_score,
        COALESCE(MIN(er.percentage_score), 0) as min_score,
        COALESCE(MAX(er.percentage_score), 0) as max_score,
        COALESCE(SUM(CASE WHEN er.pass_status = 'Pass' THEN 1 ELSE 0 END), 0) as passed,
        COALESCE(SUM(CASE WHEN er.pass_status = 'Fail' THEN 1 ELSE 0 END), 0) as failed
    FROM courses c
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    LEFT JOIN student_courses sc ON c.course_id = sc.course_id
    LEFT JOIN exams es ON c.course_id = es.course_id
    LEFT JOIN exam_results er ON es.exam_id = er.exam_id
    WHERE ic.instructor_id = ?
    GROUP BY c.course_id, c.course_code, c.course_name
    ORDER BY avg_score DESC
");
$coursePerformanceQuery->bind_param("i", $instructor_id);
$coursePerformanceQuery->execute();
$coursePerformance = $coursePerformanceQuery->get_result();

// MONTHLY PERFORMANCE TREND
$monthlyTrendQuery = $con->prepare("
    SELECT 
        DATE_FORMAT(er.exam_submitted_at, '%Y-%m') as month,
        COUNT(DISTINCT er.result_id) as exam_count,
        COALESCE(AVG(er.percentage_score), 0) as avg_score,
        COALESCE(SUM(CASE WHEN er.pass_status = 'Pass' THEN 1 ELSE 0 END), 0) as passed_count
    FROM exam_results er
    INNER JOIN exams es ON er.exam_id = es.exam_id
    INNER JOIN instructor_courses ic ON es.course_id = ic.course_id
    WHERE ic.instructor_id = ? 
    AND er.exam_submitted_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(er.exam_submitted_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 6
");
$monthlyTrendQuery->bind_param("i", $instructor_id);
$monthlyTrendQuery->execute();
$monthlyTrend = $monthlyTrendQuery->get_result();
$trendData = [];
while($row = $monthlyTrend->fetch_assoc()) {
    $trendData[] = $row;
}
$trendData = array_reverse($trendData);

// GRADE DISTRIBUTION
$gradeDistributionQuery = $con->prepare("
    SELECT 
        er.letter_grade,
        COUNT(*) as count
    FROM exam_results er
    INNER JOIN exams es ON er.exam_id = es.exam_id
    INNER JOIN instructor_courses ic ON es.course_id = ic.course_id
    WHERE ic.instructor_id = ?
    AND er.letter_grade IS NOT NULL
    AND er.letter_grade != ''
    GROUP BY er.letter_grade
    ORDER BY FIELD(SUBSTRING(er.letter_grade, 1, 1), 'A', 'B', 'C', 'D', 'F'), er.letter_grade
");
$gradeDistributionQuery->bind_param("i", $instructor_id);
$gradeDistributionQuery->execute();
$gradeDistribution = $gradeDistributionQuery->get_result();
$gradeDistributionData = [];
while($row = $gradeDistribution->fetch_assoc()) {
    $gradeDistributionData[] = $row;
}

// TOP PERFORMING STUDENTS
$topStudentsQuery = $con->prepare("
    SELECT 
        s.student_id,
        s.student_code,
        s.full_name,
        COUNT(DISTINCT er.exam_id) as exams_taken,
        COALESCE(AVG(er.percentage_score), 0) as avg_score,
        COALESCE(AVG(er.gpa), 0) as avg_gpa,
        RANK() OVER (ORDER BY AVG(er.percentage_score) DESC) as rank_position
    FROM students s
    INNER JOIN exam_results er ON s.student_id = er.student_id
    INNER JOIN exams es ON er.exam_id = es.exam_id
    INNER JOIN instructor_courses ic ON es.course_id = ic.course_id
    WHERE ic.instructor_id = ?
    GROUP BY s.student_id, s.student_code, s.full_name
    HAVING COUNT(DISTINCT er.exam_id) >= 2
    ORDER BY avg_score DESC
    LIMIT 10
");
$topStudentsQuery->bind_param("i", $instructor_id);
$topStudentsQuery->execute();
$topStudents = $topStudentsQuery->get_result();

// QUESTION ANALYSIS
$questionAnalysisQuery = $con->prepare("
    SELECT 
        q.question_id,
        LEFT(q.question_text, 150) as question_text,
        qt.topic_name,
        COUNT(DISTINCT sa.answer_id) as times_attempted,
        SUM(CASE WHEN sa.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers,
        (SUM(CASE WHEN sa.is_correct = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(DISTINCT sa.answer_id)) as success_rate
    FROM questions q
    LEFT JOIN question_topics qt ON q.topic_id = qt.topic_id
    LEFT JOIN student_answers sa ON q.question_id = sa.question_id
    INNER JOIN instructor_courses ic ON q.course_id = ic.course_id
    WHERE ic.instructor_id = ?
    AND sa.answer_id IS NOT NULL
    GROUP BY q.question_id, q.question_text, qt.topic_name
    HAVING COUNT(DISTINCT sa.answer_id) > 0
    ORDER BY success_rate ASC
    LIMIT 15
");
$questionAnalysisQuery->bind_param("i", $instructor_id);
$questionAnalysisQuery->execute();
$questionAnalysis = $questionAnalysisQuery->get_result();

// EXAM PERFORMANCE COMPARISON
$examComparisonQuery = $con->prepare("
    SELECT 
        es.exam_id,
        es.exam_name,
        c.course_code,
        COUNT(DISTINCT er.result_id) as total_attempts,
        COALESCE(AVG(er.percentage_score), 0) as avg_score,
        COALESCE(MIN(er.percentage_score), 0) as min_score,
        COALESCE(MAX(er.percentage_score), 0) as max_score,
        COALESCE(SUM(CASE WHEN er.pass_status = 'Pass' THEN 1 ELSE 0 END), 0) as passed_count
    FROM exams es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    LEFT JOIN exam_results er ON es.exam_id = er.exam_id
    WHERE ic.instructor_id = ?
    AND es.exam_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
    GROUP BY es.exam_id, es.exam_name, c.course_code
    ORDER BY es.exam_date DESC
    LIMIT 8
");
$examComparisonQuery->bind_param("i", $instructor_id);
$examComparisonQuery->execute();
$examComparison = $examComparisonQuery->get_result();

// QUESTION DIFFICULTY ANALYSIS (for Analytics tab)
$questionDifficultyQuery = $con->prepare("
    SELECT 
        q.question_id,
        q.question_text,
        c.course_name,
        COUNT(DISTINCT sa.answer_id) as attempt_count,
        SUM(CASE WHEN sa.is_correct = 1 THEN 1 ELSE 0 END) as correct_count,
        ROUND((SUM(CASE WHEN sa.is_correct = 1 THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(sa.answer_id), 0)), 2) as success_rate
    FROM questions q
    INNER JOIN courses c ON q.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    LEFT JOIN student_answers sa ON q.question_id = sa.question_id
    WHERE ic.instructor_id = ?
    GROUP BY q.question_id, q.question_text, c.course_name
    HAVING attempt_count > 0
    ORDER BY success_rate ASC
    LIMIT 20
");
$questionDifficultyQuery->bind_param("i", $instructor_id);
$questionDifficultyQuery->execute();
$questionDifficulty = $questionDifficultyQuery->get_result();

// TOPIC PERFORMANCE
$topicPerformanceQuery = $con->prepare("
    SELECT 
        qt.topic_name,
        c.course_name,
        COUNT(DISTINCT q.question_id) as question_count,
        COUNT(DISTINCT sa.answer_id) as attempt_count,
        ROUND(AVG(CASE WHEN sa.is_correct = 1 THEN 100 ELSE 0 END), 2) as avg_accuracy
    FROM question_topics qt
    LEFT JOIN questions q ON qt.topic_id = q.topic_id
    LEFT JOIN courses c ON q.course_id = c.course_id
    LEFT JOIN student_answers sa ON q.question_id = sa.question_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ?
    GROUP BY qt.topic_id, qt.topic_name, c.course_name
    HAVING question_count > 0 AND attempt_count > 0
    ORDER BY avg_accuracy ASC
    LIMIT 10
");
$topicPerformanceQuery->bind_param("i", $instructor_id);
$topicPerformanceQuery->execute();
$topicPerformance = $topicPerformanceQuery->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - Instructor Dashboard</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        :root {
            --primary-color: #003366;
            --primary-light: #0055aa;
            --primary-dark: #002244;
            --secondary-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --dark-color: #343a40;
            --light-color: #f8f9fa;
            --success-gradient: linear-gradient(135deg, #28a745 0%, #218838 100%);
            --primary-gradient: linear-gradient(135deg, #003366 0%, #0055aa 100%);
            --warning-gradient: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            --danger-gradient: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
        }
        
        body.admin-layout { 
            background: #f5f7fa; 
            font-family: 'Poppins', sans-serif; 
            color: #333;
        }
        
        .page-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2.5rem;
            border-radius: var(--radius-lg);
            box-shadow: 0 8px 32px rgba(0, 51, 102, 0.15);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .header-content {
            position: relative;
            z-index: 2;
        }
        
        .header-content h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2.5rem;
            font-weight: 800;
            color: white;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .header-content p {
            margin: 0;
            opacity: 0.95;
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            max-width: 800px;
        }
        
        /* Tab Navigation */
        .tab-navigation {
            background: white;
            border-radius: var(--radius-lg);
            padding: 0.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .tab-btn {
            flex: 1;
            min-width: 150px;
            padding: 1rem 1.5rem;
            border: none;
            background: transparent;
            color: var(--dark-color);
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: 'Poppins', sans-serif;
        }
        
        .tab-btn:hover {
            background: rgba(0, 51, 102, 0.05);
        }
        
        .tab-btn.active {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .filters-section {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-width: 200px;
        }
        
        .filter-group label {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .filter-group select, .filter-group input {
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: var(--radius-md);
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            background: white;
        }
        
        .filter-group select:focus, .filter-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
        }
        
        .filter-actions {
            display: flex;
            gap: 1rem;
            margin-left: auto;
        }
        
        .btn {
            padding: 0.85rem 1.75rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 51, 102, 0.3);
        }
        
        .btn-success {
            background: var(--success-gradient);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
        }
        
        .btn-warning {
            background: var(--warning-gradient);
            color: white;
        }
        
        .btn-warning:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 193, 7, 0.3);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.75rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            border-top: 5px solid;
            display: flex;
            flex-direction: column;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(0, 51, 102, 0.05), transparent);
            border-radius: 0 0 0 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            line-height: 1;
        }
        
        .stat-trend {
            font-size: 0.85rem;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .trend-up {
            background: rgba(40, 167, 69, 0.15);
            color: var(--secondary-color);
        }
        
        .trend-down {
            background: rgba(220, 53, 69, 0.15);
            color: var(--danger-color);
        }
        
        .trend-neutral {
            background: rgba(108, 117, 125, 0.15);
            color: #6c757d;
        }
        
        .stat-value {
            font-size: 2.8rem;
            font-weight: 900;
            color: var(--primary-color);
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.95rem;
            color: #6c757d;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .stat-subtext {
            font-size: 0.85rem;
            color: #adb5bd;
            margin-top: auto;
        }
        
        .stat-card.primary { border-top-color: var(--primary-color); }
        .stat-card.success { border-top-color: var(--secondary-color); }
        .stat-card.warning { border-top-color: var(--warning-color); }
        .stat-card.info { border-top-color: var(--info-color); }
        .stat-card.danger { border-top-color: var(--danger-color); }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 1200px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .chart-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .chart-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .chart-container {
            height: 300px;
            position: relative;
        }
        
        .data-section {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid #f0f0f0;
        }
        
        .section-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table thead {
            background: var(--primary-gradient);
        }
        
        .data-table th {
            padding: 1rem 1.25rem;
            text-align: left;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            white-space: nowrap;
        }
        
        .data-table th:first-child {
            border-top-left-radius: var(--radius-md);
        }
        
        .data-table th:last-child {
            border-top-right-radius: var(--radius-md);
        }
        
        .data-table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e8eef3;
            font-size: 0.9rem;
        }
        
        .data-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .data-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .badge {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .badge-success {
            background: rgba(40, 167, 69, 0.15);
            color: #155724;
        }
        
        .badge-warning {
            background: rgba(255, 193, 7, 0.15);
            color: #856404;
        }
        
        .badge-danger {
            background: rgba(220, 53, 69, 0.15);
            color: #721c24;
        }
        
        .badge-info {
            background: rgba(23, 162, 184, 0.15);
            color: #0c5460;
        }
        
        .score-bar {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }
        
        .score-fill {
            height: 100%;
            border-radius: 4px;
            background: var(--primary-gradient);
            transition: width 1s ease;
        }
        
        .rank-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            font-size: 0.9rem;
        }
        
        .rank-1 { background: linear-gradient(135deg, #ffd700 0%, #ffa500 100%); }
        .rank-2 { background: linear-gradient(135deg, #c0c0c0 0%, #a9a9a9 100%); }
        .rank-3 { background: linear-gradient(135deg, #cd7f32 0%, #a0522d 100%); }
        .rank-other { background: var(--primary-gradient); }
        
        .grade-distribution {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        .grade-item {
            flex: 1;
            min-width: 120px;
            text-align: center;
            padding: 1rem;
            border-radius: var(--radius-lg);
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .grade-letter {
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
        }
        
        .grade-count {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
        }
        
        .grade-percentage {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .grade-a { border-top: 4px solid #28a745; }
        .grade-b { border-top: 4px solid #17a2b8; }
        .grade-c { border-top: 4px solid #ffc107; }
        .grade-d { border-top: 4px solid #fd7e14; }
        .grade-f { border-top: 4px solid #dc3545; }
        
        .question-item {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1rem;
            border-left: 4px solid;
            transition: all 0.3s ease;
        }
        
        .question-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .question-item.hard { border-left-color: #dc3545; }
        .question-item.medium { border-left-color: #ffc107; }
        .question-item.easy { border-left-color: #28a745; }
        
        .course-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .course-score {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .course-score.excellent { color: #28a745; }
        .course-score.good { color: #17a2b8; }
        .course-score.average { color: #ffc107; }
        .course-score.poor { color: #dc3545; }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
        
        .empty-state p {
            font-size: 1rem;
            margin-bottom: 1.5rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .insight-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
        }
        
        .insight-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #ffffff;
        }
        
        .insight-text {
            font-size: 0.95rem;
            margin: 0;
            color: #ffffff;
            line-height: 1.6;
        }
        
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr; }
            .filters-section { flex-direction: column; align-items: stretch; }
            .filter-actions { margin-left: 0; justify-content: center; }
            .charts-grid { grid-template-columns: 1fr; }
            .data-table { font-size: 0.8rem; }
            .data-table th, .data-table td { padding: 0.75rem; }
            .grade-distribution { flex-wrap: wrap; }
            .grade-item { flex: 0 0 calc(50% - 0.5rem); }
            .tab-navigation { flex-direction: column; }
            .tab-btn { min-width: 100%; }
        }
        
        @media print {
            .filters-section, .admin-sidebar, .admin-header, .btn, .filter-actions, .tab-navigation { display: none; }
            .admin-main-content { margin-left: 0; }
            .stat-card, .chart-card, .data-section { break-inside: avoid; }
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <h1>📊 Reports & Analytics Dashboard</h1>
                    <p>Comprehensive performance insights, analytics, and data-driven reports for <?php echo htmlspecialchars($instructor_name); ?>'s courses</p>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="tab-navigation">
                <button class="tab-btn <?php echo $activeTab === 'overview' ? 'active' : ''; ?>" onclick="switchTab('overview')">
                    <span>📈</span> Overview
                </button>
                <button class="tab-btn <?php echo $activeTab === 'performance' ? 'active' : ''; ?>" onclick="switchTab('performance')">
                    <span>🎯</span> Performance
                </button>
                <button class="tab-btn <?php echo $activeTab === 'analytics' ? 'active' : ''; ?>" onclick="switchTab('analytics')">
                    <span>📊</span> Analytics
                </button>
                <button class="tab-btn <?php echo $activeTab === 'students' ? 'active' : ''; ?>" onclick="switchTab('students')">
                    <span>👨‍🎓</span> Students
                </button>
            </div>

            <!-- Filters Section -->
            <form method="GET" action="" class="filters-section">
                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab); ?>">
                
                <div class="filter-group">
                    <label><span>📅</span> Time Range</label>
                    <select name="time_range">
                        <option value="week" <?php echo $timeRange === 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="month" <?php echo $timeRange === 'month' ? 'selected' : ''; ?>>This Month</option>
                        <option value="quarter" <?php echo $timeRange === 'quarter' ? 'selected' : ''; ?>>Last 3 Months</option>
                        <option value="year" <?php echo $timeRange === 'year' ? 'selected' : ''; ?>>This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label><span>📚</span> Course</label>
                    <select name="course">
                        <option value="all" <?php echo $courseFilter === 'all' ? 'selected' : ''; ?>>All Courses</option>
                        <?php 
                        $courses->data_seek(0);
                        while($course = $courses->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $course['course_id']; ?>" <?php echo $courseFilter == $course['course_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <span>🔍</span> Apply Filters
                    </button>
                    <button type="button" class="btn btn-success" onclick="exportToExcel()">
                        <span>📥</span> Export Excel
                    </button>
                    <button type="button" class="btn btn-warning" onclick="window.print()">
                        <span>🖨️</span> Print
                    </button>
                </div>
            </form>

            <!-- OVERVIEW TAB -->
            <div id="overview-tab" class="tab-content <?php echo $activeTab === 'overview' ? 'active' : ''; ?>">
                <!-- Key Statistics -->
                <div class="stats-grid">
                    <div class="stat-card primary">
                        <div class="stat-header">
                            <div class="stat-icon">📚</div>
                            <div class="stat-trend trend-up">+12%</div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['total_courses']); ?></div>
                        <div class="stat-label">Total Courses</div>
                        <div class="stat-subtext">Active courses you're teaching</div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-header">
                            <div class="stat-icon">👨‍🎓</div>
                            <div class="stat-trend trend-up">+8%</div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['total_students']); ?></div>
                        <div class="stat-label">Total Students</div>
                        <div class="stat-subtext">Students enrolled in your courses</div>
                    </div>
                    
                    <div class="stat-card warning">
                        <div class="stat-header">
                            <div class="stat-icon">📝</div>
                            <div class="stat-trend trend-neutral">0%</div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['total_exams']); ?></div>
                        <div class="stat-label">Exams Conducted</div>
                        <div class="stat-subtext">Total exams created and conducted</div>
                    </div>
                    
                    <div class="stat-card info">
                        <div class="stat-header">
                            <div class="stat-icon">📊</div>
                            <div class="stat-trend trend-up">+5%</div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['avg_score'] ?? 0, 1); ?>%</div>
                        <div class="stat-label">Average Score</div>
                        <div class="stat-subtext">Overall student performance</div>
                    </div>
                    
                    <div class="stat-card danger">
                        <div class="stat-header">
                            <div class="stat-icon">✅</div>
                            <div class="stat-trend trend-up">+3%</div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['total_passed']); ?></div>
                        <div class="stat-label">Students Passed</div>
                        <div class="stat-subtext">Successfully completed exams</div>
                    </div>
                    
                    <div class="stat-card primary">
                        <div class="stat-header">
                            <div class="stat-icon">❓</div>
                            <div class="stat-trend trend-neutral">0%</div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['total_questions']); ?></div>
                        <div class="stat-label">Total Questions</div>
                        <div class="stat-subtext">Questions in question bank</div>
                    </div>
                </div>

                <!-- Grade Distribution -->
                <!-- Charts Grid -->
                <div class="charts-grid">
                    <!-- Performance Trend Chart -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">📈 Performance Trends (6 Months)</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="performanceTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Insight Card -->
                <div class="insight-card">
                    <div class="insight-title">
                        <span>💡</span> Key Insight
                    </div>
                    <p class="insight-text">
                        <?php 
                        $avgScore = $stats['avg_score'] ?? 0;
                        $passRate = ($stats['total_passed'] + $stats['total_failed']) > 0 
                            ? round(($stats['total_passed'] / ($stats['total_passed'] + $stats['total_failed'])) * 100, 1) 
                            : 0;
                        
                        if ($avgScore >= 80) {
                            echo "Excellent overall performance! Your students are achieving high scores with a {$passRate}% pass rate. Consider introducing more challenging material to further enhance learning outcomes.";
                        } elseif ($avgScore >= 70) {
                            echo "Good performance with room for improvement. Focus on areas where students scored below 60% to boost the {$passRate}% pass rate. Consider additional practice materials for struggling topics.";
                        } elseif ($avgScore >= 60) {
                            echo "Average performance detected. Review the most challenging topics and consider providing additional resources, study guides, or remedial sessions to improve student outcomes.";
                        } else {
                            echo "Performance needs attention. Review course materials, exam difficulty, and consider implementing remedial sessions for struggling topics. One-on-one consultations may help identify specific challenges.";
                        }
                        ?>
                    </p>
                </div>
            </div>

            <!-- PERFORMANCE TAB -->
            <div id="performance-tab" class="tab-content <?php echo $activeTab === 'performance' ? 'active' : ''; ?>">
                <!-- Course Performance Table -->
                <div class="data-section">
                    <div class="section-header">
                        <h2 class="section-title">📚 Course Performance Analysis</h2>
                    </div>
                    
                    <?php if($coursePerformance->num_rows > 0): ?>
                    <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Enrolled</th>
                                <th>Exams</th>
                                <th>Average Score</th>
                                <th>Score Range</th>
                                <th>Pass Rate</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $coursePerformance->data_seek(0);
                            while($course = $coursePerformance->fetch_assoc()): 
                                $passRate = ($course['passed'] + $course['failed']) > 0 
                                    ? round(($course['passed'] / ($course['passed'] + $course['failed'])) * 100, 1) 
                                    : 0;
                                $scoreWidth = $course['avg_score'] > 0 ? $course['avg_score'] : 0;
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($course['course_code']); ?></strong><br>
                                    <small style="color: #6c757d;"><?php echo htmlspecialchars($course['course_name']); ?></small>
                                </td>
                                <td><?php echo number_format($course['enrolled_students']); ?></td>
                                <td><?php echo number_format($course['total_exams']); ?></td>
                                <td>
                                    <strong><?php echo number_format($course['avg_score'], 1); ?>%</strong>
                                </td>
                                <td>
                                    <small><?php echo number_format($course['min_score'], 1); ?>% - <?php echo number_format($course['max_score'], 1); ?>%</small>
                                </td>
                                <td>
                                    <span class="badge <?php echo $passRate >= 70 ? 'badge-success' : ($passRate >= 50 ? 'badge-warning' : 'badge-danger'); ?>">
                                        <?php echo $passRate; ?>%
                                    </span>
                                </td>
                                <td style="width: 150px;">
                                    <div class="score-bar">
                                        <div class="score-fill" style="width: <?php echo $scoreWidth; ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <h3>No Data Available</h3>
                        <p>No course performance data available yet. Start creating exams to track performance.</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Exam Performance Comparison -->
                <?php if($examComparison->num_rows > 0): ?>
                <div class="data-section">
                    <div class="section-header">
                        <h2 class="section-title">📊 Recent Exam Performance (Last 3 Months)</h2>
                    </div>
                    
                    <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Exam Name</th>
                                <th>Course</th>
                                <th>Attempts</th>
                                <th>Average Score</th>
                                <th>Score Range</th>
                                <th>Pass Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $examComparison->data_seek(0);
                            while($exam = $examComparison->fetch_assoc()): 
                                $passRate = $exam['total_attempts'] > 0 
                                    ? round(($exam['passed_count'] / $exam['total_attempts']) * 100, 1) 
                                    : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($exam['exam_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($exam['course_code']); ?></td>
                                <td><?php echo number_format($exam['total_attempts']); ?></td>
                                <td>
                                    <strong><?php echo number_format($exam['avg_score'], 1); ?>%</strong>
                                </td>
                                <td>
                                    <small><?php echo number_format($exam['min_score'], 1); ?>% - <?php echo number_format($exam['max_score'], 1); ?>%</small>
                                </td>
                                <td>
                                    <span class="badge <?php echo $passRate >= 70 ? 'badge-success' : ($passRate >= 50 ? 'badge-warning' : 'badge-danger'); ?>">
                                        <?php echo $passRate; ?>%
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ANALYTICS TAB -->
            <div id="analytics-tab" class="tab-content <?php echo $activeTab === 'analytics' ? 'active' : ''; ?>">
                <!-- Question Difficulty Analysis -->
                <div class="data-section">
                    <div class="section-header">
                        <h3 class="section-title">🎯 Most Difficult Questions (Needs Review)</h3>
                    </div>
                    <div style="max-height: 600px; overflow-y: auto;">
                        <?php if($questionDifficulty && $questionDifficulty->num_rows > 0): ?>
                        <?php while($q = $questionDifficulty->fetch_assoc()): 
                            $difficultyClass = $q['success_rate'] < 40 ? 'hard' : ($q['success_rate'] < 70 ? 'medium' : 'easy');
                            $badgeColor = $q['success_rate'] < 40 ? '#dc3545' : ($q['success_rate'] < 70 ? '#ffc107' : '#28a745');
                        ?>
                        <div class="question-item <?php echo $difficultyClass; ?>">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                                <div style="flex: 1;">
                                    <strong style="color: #003366;">Question #<?php echo $q['question_id']; ?></strong>
                                    <div style="font-size: 0.85rem; color: #6c757d; margin-top: 0.25rem;">
                                        <?php echo htmlspecialchars($q['course_name']); ?>
                                    </div>
                                </div>
                                <span class="badge" style="background: <?php echo $badgeColor; ?>; color: white;">
                                    <?php echo number_format($q['success_rate'], 1); ?>% Success
                                </span>
                            </div>
                            <p style="margin: 0 0 0.75rem 0; color: #6c757d; font-size: 0.9rem;">
                                <?php echo htmlspecialchars(substr($q['question_text'], 0, 150)); ?><?php echo strlen($q['question_text']) > 150 ? '...' : ''; ?>
                            </p>
                            <div style="display: flex; gap: 2rem; font-size: 0.85rem; color: #6c757d;">
                                <div>
                                    <strong><?php echo $q['attempt_count']; ?></strong> attempts
                                </div>
                                <div>
                                    <strong><?php echo $q['correct_count']; ?></strong> correct
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📊</div>
                            <h3>No Data Available</h3>
                            <p>No question attempt data available yet. Students need to complete exams first.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Topic Performance -->
                <?php if($topicPerformance && $topicPerformance->num_rows > 0): ?>
                <div class="data-section">
                    <div class="section-header">
                        <h3 class="section-title">📖 Weakest Topics (Need Attention)</h3>
                    </div>
                    <div>
                        <?php while($topic = $topicPerformance->fetch_assoc()): 
                            $topicColor = $topic['avg_accuracy'] < 50 ? '#dc3545' : ($topic['avg_accuracy'] < 70 ? '#ffc107' : '#28a745');
                        ?>
                        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 12px; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="color: #003366;"><?php echo htmlspecialchars($topic['topic_name']); ?></strong>
                                <div style="font-size: 0.85rem; color: #6c757d;">
                                    <?php echo htmlspecialchars($topic['course_name']); ?> • <?php echo $topic['question_count']; ?> questions • <?php echo $topic['attempt_count']; ?> attempts
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 1.5rem; font-weight: 800; color: <?php echo $topicColor; ?>;">
                                    <?php echo number_format($topic['avg_accuracy'], 1); ?>%
                                </div>
                                <div style="font-size: 0.75rem; color: #6c757d;">Success Rate</div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Insights & Recommendations -->
                <div class="charts-grid">
                    <div class="insight-card">
                        <h4 class="insight-title">📊 Question Quality Insights</h4>
                        <p class="insight-text">
                            <?php 
                            $hardQuestions = 0;
                            if($questionDifficulty) {
                                $questionDifficulty->data_seek(0);
                                while($q = $questionDifficulty->fetch_assoc()) {
                                    if($q['success_rate'] < 50) $hardQuestions++;
                                }
                            }
                            
                            if($hardQuestions > 10) {
                                echo "You have {$hardQuestions} questions with <50% success rate. Consider reviewing these for clarity, difficulty level, or potential ambiguity in wording.";
                            } elseif($hardQuestions > 0) {
                                echo "You have {$hardQuestions} challenging questions. This is good for assessment variety, but ensure they're fair and clearly worded.";
                            } else {
                                echo "Your questions have good difficulty balance. Keep monitoring student performance and adjust as needed.";
                            }
                            ?>
                        </p>
                    </div>
                    <div class="insight-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                        <h4 class="insight-title">✅ Best Practices</h4>
                        <ul style="margin: 0.5rem 0 0 1.5rem; padding: 0;">
                            <li>Review questions with <40% success rate for clarity</li>
                            <li>Balance easy (70%+), medium (50-70%), and hard (<50%) questions</li>
                            <li>Use topics to organize questions by subject area</li>
                            <li>Regularly update questions based on student feedback</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- STUDENTS TAB -->
            <div id="students-tab" class="tab-content <?php echo $activeTab === 'students' ? 'active' : ''; ?>">
                <!-- Top Performing Students -->
                <?php if($topStudents->num_rows > 0): ?>
                <div class="data-section">
                    <div class="section-header">
                        <h2 class="section-title">🏆 Top Performing Students</h2>
                    </div>
                    
                    <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Student</th>
                                <th>Exams Taken</th>
                                <th>Average Score</th>
                                <th>Average GPA</th>
                                <th>Performance Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $topStudents->data_seek(0);
                            while($student = $topStudents->fetch_assoc()): 
                                $rank = $student['rank_position'];
                                $performance = $student['avg_score'] >= 85 ? 'Excellent' : ($student['avg_score'] >= 70 ? 'Good' : 'Average');
                                $performanceClass = $student['avg_score'] >= 85 ? 'badge-success' : ($student['avg_score'] >= 70 ? 'badge-info' : 'badge-warning');
                            ?>
                            <tr>
                                <td>
                                    <div class="rank-badge <?php echo $rank <= 3 ? 'rank-' . $rank : 'rank-other'; ?>">
                                        <?php echo $rank; ?>
                                    </div>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($student['full_name']); ?></strong><br>
                                    <small style="color: #6c757d;"><?php echo htmlspecialchars($student['student_code']); ?></small>
                                </td>
                                <td><?php echo number_format($student['exams_taken']); ?></td>
                                <td>
                                    <strong><?php echo number_format($student['avg_score'], 1); ?>%</strong>
                                </td>
                                <td><?php echo number_format($student['avg_gpa'], 2); ?></td>
                                <td>
                                    <span class="badge <?php echo $performanceClass; ?>">
                                        <?php echo $performance; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                <?php else: ?>
                <div class="data-section">
                    <div class="empty-state">
                        <div class="empty-state-icon">👨‍🎓</div>
                        <h3>No Student Data Available</h3>
                        <p>Student performance data will appear here once they complete at least 2 exams.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        // Tab Switching Function
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked button
            event.target.closest('.tab-btn').classList.add('active');
            
            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.pushState({}, '', url);
        }

        // Performance Trend Chart
        const trendCtx = document.getElementById('performanceTrendChart');
        if(trendCtx) {
            <?php if(!empty($trendData)): ?>
            const trendMonths = <?php echo json_encode(array_column($trendData, 'month')); ?>;
            const trendScores = <?php echo json_encode(array_map(function($v) { return round($v, 1); }, array_column($trendData, 'avg_score'))); ?>;
            const trendExams = <?php echo json_encode(array_column($trendData, 'exam_count')); ?>;
            
            new Chart(trendCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: trendMonths.map(m => {
                        const date = new Date(m + '-01');
                        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                    }),
                    datasets: [
                        {
                            label: 'Average Score (%)',
                            data: trendScores,
                            borderColor: '#003366',
                            backgroundColor: 'rgba(0, 51, 102, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Exams Conducted',
                            data: trendExams,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    family: 'Poppins',
                                    size: 12
                                },
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: {
                                family: 'Poppins',
                                size: 12
                            },
                            bodyFont: {
                                family: 'Poppins',
                                size: 11
                            },
                            padding: 12,
                            cornerRadius: 6
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: 'Poppins',
                                    size: 11
                                }
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Average Score (%)',
                                font: {
                                    family: 'Poppins',
                                    size: 12,
                                    weight: 'bold'
                                }
                            },
                            min: 0,
                            max: 100,
                            ticks: {
                                font: {
                                    family: 'Poppins',
                                    size: 11
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Exams Conducted',
                                font: {
                                    family: 'Poppins',
                                    size: 12,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                            ticks: {
                                font: {
                                    family: 'Poppins',
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
            <?php else: ?>
            trendCtx.parentElement.innerHTML = `
                <div class="empty-state" style="padding: 2rem;">
                    <div class="empty-state-icon">📈</div>
                    <p>No trend data available yet. Exam results will appear here.</p>
                </div>
            `;
            <?php endif; ?>
        }

        // Export to Excel Function
        function exportToExcel() {
            const wb = XLSX.utils.book_new();
            
            // Get all visible tables in the active tab
            const activeTab = document.querySelector('.tab-content.active');
            const tables = activeTab.querySelectorAll('.data-table');
            
            if(tables.length === 0) {
                alert('No data tables found to export in the current tab!');
                return;
            }
            
            tables.forEach((table, index) => {
                const sectionTitle = table.closest('.data-section')?.querySelector('.section-title')?.textContent.trim() || 'Sheet' + (index + 1);
                const ws = XLSX.utils.table_to_sheet(table);
                const sheetName = sectionTitle.replace(/[^a-zA-Z0-9]/g, '_').substring(0, 31);
                XLSX.utils.book_append_sheet(wb, ws, sheetName);
            });
            
            const filename = 'Instructor_Report_' + new Date().toISOString().split('T')[0] + '.xlsx';
            XLSX.writeFile(wb, filename);
            
            setTimeout(() => {
                alert('✅ Excel file exported successfully!\n\nFilename: ' + filename);
            }, 500);
        }

        // Print optimization
        window.addEventListener('beforeprint', () => {
            document.querySelectorAll('.stat-card, .chart-card, .data-section').forEach(el => {
                el.style.marginBottom = '20px';
                el.style.pageBreakInside = 'avoid';
            });
        });

        // Time range selector change handler
        const timeRangeSelect = document.querySelector('select[name="time_range"]');
        if(timeRangeSelect) {
            timeRangeSelect.addEventListener('change', function(e) {
                if (e.target.value === 'custom') {
                    alert('Custom date range feature coming soon! For now, please use the predefined ranges.');
                }
            });
        }
    </script>
</body>
</html>
<?php 
$con->close();
?>
