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
$pageTitle = "Results Overview";
$instructor_id = $_SESSION['ID'];

// Get filter parameters
$course_filter = $_GET['course'] ?? null;
$exam_filter = $_GET['exam'] ?? null;
$grade_filter = $_GET['grade'] ?? null;
$pass_filter = $_GET['pass'] ?? null;

// Build query
$query = "SELECT 
    er.result_id,
    er.percentage_score,
    er.letter_grade,
    er.pass_status,
    er.correct_answers,
    er.wrong_answers,
    er.total_questions,
    er.exam_submitted_at,
    s.student_code,
    s.full_name as student_name,
    es.exam_name,
    es.exam_id,
    c.course_name,
    c.course_code,
    c.course_id
    FROM exam_results er
    INNER JOIN students s ON er.student_id = s.student_id
    INNER JOIN exams es ON er.exam_id = es.exam_id
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ?";

$params = [$instructor_id];
$types = "i";

if($course_filter) {
    $query .= " AND c.course_id = ?";
    $params[] = $course_filter;
    $types .= "i";
}

if($exam_filter) {
    $query .= " AND es.exam_id = ?";
    $params[] = $exam_filter;
    $types .= "i";
}

if($grade_filter) {
    $query .= " AND er.letter_grade = ?";
    $params[] = $grade_filter;
    $types .= "s";
}

if($pass_filter) {
    $query .= " AND er.pass_status = ?";
    $params[] = $pass_filter;
    $types .= "s";
}

$query .= " ORDER BY er.exam_submitted_at DESC LIMIT 100";

$stmt = $con->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$results = $stmt->get_result();

// Get courses for filter
$coursesQuery = $con->prepare("SELECT DISTINCT c.course_id, c.course_name, c.course_code
    FROM instructor_courses ic
    INNER JOIN courses c ON ic.course_id = c.course_id
    WHERE ic.instructor_id = ?
    ORDER BY c.course_name");
$coursesQuery->bind_param("i", $instructor_id);
$coursesQuery->execute();
$courses = $coursesQuery->get_result();

// Get exams for filter (with exam_date in SELECT for ORDER BY compatibility)
$examsQuery = $con->prepare("SELECT DISTINCT es.exam_id, es.exam_name, c.course_name, es.exam_date
    FROM exams es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ?
    ORDER BY es.exam_date DESC");
$examsQuery->bind_param("i", $instructor_id);
$examsQuery->execute();
$exams = $examsQuery->get_result();

// Get statistics (with same filters as main query)
$statsQueryStr = "SELECT 
    COUNT(DISTINCT er.result_id) as total_results,
    COUNT(DISTINCT er.student_id) as unique_students,
    AVG(er.percentage_score) as avg_score,
    MAX(er.percentage_score) as highest_score,
    MIN(er.percentage_score) as lowest_score,
    SUM(CASE WHEN er.pass_status = 'Pass' THEN 1 ELSE 0 END) as passed,
    SUM(CASE WHEN er.pass_status = 'Fail' THEN 1 ELSE 0 END) as failed,
    SUM(CASE WHEN er.letter_grade IN ('A+', 'A', 'A-') THEN 1 ELSE 0 END) as grade_a,
    SUM(CASE WHEN er.letter_grade IN ('B+', 'B', 'B-') THEN 1 ELSE 0 END) as grade_b,
    SUM(CASE WHEN er.letter_grade IN ('C+', 'C', 'C-') THEN 1 ELSE 0 END) as grade_c,
    SUM(CASE WHEN er.letter_grade IN ('D+', 'D', 'D-') THEN 1 ELSE 0 END) as grade_d,
    SUM(CASE WHEN er.letter_grade = 'F' THEN 1 ELSE 0 END) as grade_f
    FROM exam_results er
    INNER JOIN exams es ON er.exam_id = es.exam_id
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ?";

$statsParams = [$instructor_id];
$statsTypes = "i";

// Apply same filters to statistics
if($course_filter) {
    $statsQueryStr .= " AND c.course_id = ?";
    $statsParams[] = $course_filter;
    $statsTypes .= "i";
}

if($exam_filter) {
    $statsQueryStr .= " AND es.exam_id = ?";
    $statsParams[] = $exam_filter;
    $statsTypes .= "i";
}

if($grade_filter) {
    $statsQueryStr .= " AND er.letter_grade = ?";
    $statsParams[] = $grade_filter;
    $statsTypes .= "s";
}

if($pass_filter) {
    $statsQueryStr .= " AND er.pass_status = ?";
    $statsParams[] = $pass_filter;
    $statsTypes .= "s";
}

$statsQuery = $con->prepare($statsQueryStr);
$statsQuery->bind_param($statsTypes, ...$statsParams);
$statsQuery->execute();
$stats = $statsQuery->get_result()->fetch_assoc();
$statsQuery->close();

// Get actual grade distribution
$gradeDistQuery = "SELECT 
    er.letter_grade,
    COUNT(*) as count
    FROM exam_results er
    INNER JOIN exams es ON er.exam_id = es.exam_id
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ?
    AND er.letter_grade IS NOT NULL
    AND er.letter_grade != ''";

$gradeDistParams = [$instructor_id];
$gradeDistTypes = "i";

if($course_filter) {
    $gradeDistQuery .= " AND c.course_id = ?";
    $gradeDistParams[] = $course_filter;
    $gradeDistTypes .= "i";
}

if($exam_filter) {
    $gradeDistQuery .= " AND es.exam_id = ?";
    $gradeDistParams[] = $exam_filter;
    $gradeDistTypes .= "i";
}

$gradeDistQuery .= " GROUP BY er.letter_grade
    ORDER BY FIELD(SUBSTRING(er.letter_grade, 1, 1), 'A', 'B', 'C', 'D', 'F'), er.letter_grade";

$gradeDistStmt = $con->prepare($gradeDistQuery);
$gradeDistStmt->bind_param($gradeDistTypes, ...$gradeDistParams);
$gradeDistStmt->execute();
$gradeDistResult = $gradeDistStmt->get_result();
$gradeDistribution = [];
while($row = $gradeDistResult->fetch_assoc()) {
    $gradeDistribution[] = $row;
}
$gradeDistStmt->close();

$pass_rate = $stats['total_results'] > 0 ? round(($stats['passed'] / $stats['total_results']) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results Overview - Instructor</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        
        .page-header-modern {
            background: linear-gradient(135deg, #003366 0%, #0055aa 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2);
            margin-bottom: 2rem;
        }
        
        .page-header-modern h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2.2rem;
            font-weight: 800;
            color: white;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .page-header-modern p { margin: 0; opacity: 0.95; font-size: 1.05rem; color: white; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: all 0.3s ease;
            border-top: 4px solid;
        }
        
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12); }
        .stat-card.primary { border-top-color: #003366; }
        .stat-card.success { border-top-color: #28a745; }
        .stat-card.warning { border-top-color: #ffc107; }
        .stat-card.info { border-top-color: #17a2b8; }
        
        .stat-icon { font-size: 3rem; margin-bottom: 1rem; }
        .stat-value { font-size: 2.5rem; font-weight: 900; color: #003366; margin-bottom: 0.5rem; }
        .stat-label { font-size: 0.95rem; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        
        .filter-card h3 {
            margin: 0 0 1.5rem 0;
            font-size: 1.3rem;
            font-weight: 700;
            color: #003366;
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #003366;
            font-size: 0.95rem;
        }
        
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-group select:focus {
            outline: none;
            border-color: #003366;
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
        }
        
        .btn {
            padding: 0.85rem 1.75rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #003366 0%, #0055aa 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3);
        }
        
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; transform: translateY(-2px); }
        .btn-info { background: #17a2b8; color: white; }
        .btn-info:hover { background: #138496; transform: translateY(-2px); }
        .btn-sm { padding: 0.6rem 1.2rem; font-size: 0.875rem; }
        
        .results-table-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }
        
        .results-table-container h3 {
            margin: 0 0 1.5rem 0;
            font-size: 1.3rem;
            font-weight: 700;
            color: #003366;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .data-table thead {
            background: linear-gradient(135deg, #003366 0%, #0055aa 100%);
        }
        
        .data-table th {
            padding: 1rem;
            text-align: left;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            white-space: nowrap;
        }
        
        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #e8eef3;
            font-size: 0.9rem;
        }
        
        .data-table tbody tr {
            transition: all 0.3s ease;
        }
        
        .data-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .score-badge {
            padding: 0.4rem 0.85rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-block;
        }
        
        .badge-excellent { background: #d4edda; color: #155724; }
        .badge-good { background: #d1ecf1; color: #0c5460; }
        .badge-average { background: #fff3cd; color: #856404; }
        .badge-poor { background: #f8d7da; color: #721c24; }
        
        .status-badge {
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pass { background: #d4edda; color: #155724; }
        .status-fail { background: #f8d7da; color: #721c24; }
        
        .grade-badge {
            display: inline-block;
            padding: 0.4rem 0.85rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
        }
        
        .grade-a { background: #28a745; color: white; }
        .grade-b { background: #17a2b8; color: white; }
        .grade-c { background: #ffc107; color: #212529; }
        .grade-d { background: #fd7e14; color: white; }
        .grade-f { background: #dc3545; color: white; }
        
        /* Stat card grade colors */
        .stat-card .stat-icon { filter: grayscale(0); }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }
        
        .empty-state-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            opacity: 0.3;
        }
        
        .empty-state h3 {
            color: #495057;
            margin-bottom: 0.75rem;
        }
        
        .empty-state p {
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr; }
            .filter-grid { grid-template-columns: 1fr; }
            .data-table { font-size: 0.8rem; }
            .data-table th, .data-table td { padding: 0.6rem; }
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <!-- Page Header -->
            <div class="page-header-modern">
                <h1>📊 Results Overview</h1>
                <p>Comprehensive view of student performance across all your courses</p>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">📝</div>
                    <div class="stat-value"><?php echo number_format($stats['total_results'] ?? 0); ?></div>
                    <div class="stat-label">Total Results</div>
                </div>
                <div class="stat-card info">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-value"><?php echo number_format($stats['unique_students'] ?? 0); ?></div>
                    <div class="stat-label">Unique Students</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon">📈</div>
                    <div class="stat-value"><?php echo number_format($stats['avg_score'] ?? 0, 1); ?>%</div>
                    <div class="stat-label">Average Score</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value"><?php echo number_format($pass_rate, 1); ?>%</div>
                    <div class="stat-label">Pass Rate</div>
                </div>
            </div>

            <!-- Grade Distribution -->
  <?php if($stats['total_results'] > 0 && count($gradeDistribution) > 0): ?>
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); margin-bottom: 2rem;">
        <?php 
        // Calculate total for percentage
        $totalGrades = array_sum(array_column($gradeDistribution, 'count'));
        
        foreach($gradeDistribution as $grade):
            $letter = $grade['letter_grade'];
            $count = $grade['count'];
            $percentage = $totalGrades > 0 ? round(($count / $totalGrades) * 100, 0) : 0;
            $baseGrade = substr($letter, 0, 1);
            
            // Determine color based on grade
            $colors = [
                'A' => '#28a745',
                'B' => '#17a2b8', 
                'C' => '#ffc107',
                'D' => '#fd7e14',
                'F' => '#dc3545'
            ];
            $color = $colors[$baseGrade] ?? '#6c757d';
            
            // Background colors (lighter versions)
            $bgColors = [
                'A' => 'rgba(40, 167, 69, 0.1)',
                'B' => 'rgba(23, 162, 184, 0.1)', 
                'C' => 'rgba(255, 193, 7, 0.1)',
                'D' => 'rgba(253, 126, 20, 0.1)',
                'F' => 'rgba(220, 53, 69, 0.1)'
            ];
            $bgColor = $bgColors[$baseGrade] ?? 'rgba(108, 117, 125, 0.1)';
        ?>
        <div class="stat-card" style="border-top-color: <?php echo $color; ?>; background: <?php echo $bgColor; ?>; border-left: 4px solid <?php echo $color; ?>;">
            <div class="stat-icon" style="color: <?php echo $color; ?>; font-size: 3rem; font-weight: 900;"><?php echo htmlspecialchars($letter); ?></div>
            <div class="stat-value" style="color: <?php echo $color; ?>;"><?php echo $count; ?></div>
            <div class="stat-label" style="color: <?php echo $color; ?>; font-weight: 600;"><?php echo $percentage; ?>% of total</div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

            <!-- Filters -->
            <div class="filter-card">
                <h3>🔍 Filter Results</h3>
                <form method="GET" action="">
                    <div class="filter-grid">
                        <div class="form-group">
                            <label>Course</label>
                            <select name="course">
                                <option value="">All Courses</option>
                                <?php 
                                $courses->data_seek(0);
                                while($course = $courses->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $course['course_id']; ?>" <?php echo $course_filter == $course['course_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Exam</label>
                            <select name="exam">
                                <option value="">All Exams</option>
                                <?php 
                                $exams->data_seek(0);
                                while($exam = $exams->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $exam['exam_id']; ?>" <?php echo $exam_filter == $exam['exam_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($exam['exam_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Grade</label>
                            <select name="grade">
                                <option value="">All Grades</option>
                                <?php 
                                $grades = ['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'F'];
                                foreach($grades as $g): 
                                ?>
                                <option value="<?php echo $g; ?>" <?php echo $grade_filter == $g ? 'selected' : ''; ?>><?php echo $g; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Pass Status</label>
                            <select name="pass">
                                <option value="">All Status</option>
                                <option value="Pass" <?php echo $pass_filter == 'Pass' ? 'selected' : ''; ?>>Pass</option>
                                <option value="Fail" <?php echo $pass_filter == 'Fail' ? 'selected' : ''; ?>>Fail</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary">
                            <span>🔍</span> Apply Filters
                        </button>
                        <?php if($course_filter || $exam_filter || $grade_filter || $pass_filter): ?>
                        <a href="ResultsOverview.php" class="btn btn-secondary">
                            <span>🔄</span> Clear Filters
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Results Table -->
            <div class="results-table-container">
                <h3>📋 Exam Results (<?php echo $results->num_rows; ?>)</h3>
                
                <?php if($results->num_rows > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student Code</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Exam</th>
                            <th>Score</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th>Correct/Total</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($result = $results->fetch_assoc()): 
                            $score = $result['percentage_score'];
                            $badgeClass = $score >= 85 ? 'badge-excellent' : ($score >= 70 ? 'badge-good' : ($score >= 50 ? 'badge-average' : 'badge-poor'));
                            
                            // Determine grade badge class
                            $grade = $result['letter_grade'];
                            if(in_array($grade, ['A+', 'A', 'A-'])) $gradeClass = 'grade-a';
                            elseif(in_array($grade, ['B+', 'B', 'B-'])) $gradeClass = 'grade-b';
                            elseif(in_array($grade, ['C+', 'C', 'C-'])) $gradeClass = 'grade-c';
                            elseif($grade == 'D') $gradeClass = 'grade-d';
                            else $gradeClass = 'grade-f';
                        ?>
                        <tr>
                            <td><strong style="color: #003366;"><?php echo htmlspecialchars($result['student_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($result['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($result['course_code']); ?></td>
                            <td><?php echo htmlspecialchars($result['exam_name']); ?></td>
                            <td><span class="score-badge <?php echo $badgeClass; ?>"><?php echo number_format($score, 1); ?>%</span></td>
                            <td><span class="grade-badge <?php echo $gradeClass; ?>"><?php echo $grade; ?></span></td>
                            <td><span class="status-badge status-<?php echo strtolower($result['pass_status']); ?>"><?php echo $result['pass_status']; ?></span></td>
                            <td><?php echo $result['correct_answers']; ?> / <?php echo $result['total_questions']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($result['exam_submitted_at'])); ?></td>
                            <td>
                                <a href="ViewStudentResult.php?result_id=<?php echo $result['result_id']; ?>" class="btn btn-info btn-sm">
                                    <span>👁️</span> View
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📊</div>
                    <h3>No Results Found</h3>
                    <p>
                        <?php if($course_filter || $exam_filter || $grade_filter || $pass_filter): ?>
                            No results match your filter criteria. Try adjusting the filters.
                        <?php else: ?>
                            No exam results available yet.
                        <?php endif; ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>

