<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
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
    es.schedule_id,
    c.course_name,
    c.course_code,
    c.course_id
    FROM exam_results er
    INNER JOIN students s ON er.student_id = s.student_id
    INNER JOIN exam_schedules es ON er.schedule_id = es.schedule_id
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE";

$params = [$instructor_id];
$types = "i";

if($course_filter) {
    $query .= " AND c.course_id = ?";
    $params[] = $course_filter;
    $types .= "i";
}

if($exam_filter) {
    $query .= " AND es.schedule_id = ?";
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
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE
    ORDER BY c.course_name");
$coursesQuery->bind_param("i", $instructor_id);
$coursesQuery->execute();
$courses = $coursesQuery->get_result();

// Get exams for filter
$examsQuery = $con->prepare("SELECT DISTINCT es.schedule_id, es.exam_name, c.course_name
    FROM exam_schedules es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE AND es.is_active = TRUE
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
    SUM(CASE WHEN er.letter_grade = 'D' THEN 1 ELSE 0 END) as grade_d,
    SUM(CASE WHEN er.letter_grade = 'F' THEN 1 ELSE 0 END) as grade_f
    FROM exam_results er
    INNER JOIN exam_schedules es ON er.schedule_id = es.schedule_id
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE";

$statsParams = [$instructor_id];
$statsTypes = "i";

// Apply same filters to statistics
if($course_filter) {
    $statsQueryStr .= " AND c.course_id = ?";
    $statsParams[] = $course_filter;
    $statsTypes .= "i";
}

if($exam_filter) {
    $statsQueryStr .= " AND es.schedule_id = ?";
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
        .page-header-modern { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2); margin-bottom: 2rem; }
        .page-header-modern h1 { margin: 0 0 0.5rem 0; font-size: 2.2rem; font-weight: 800; display: flex; align-items: center; gap: 1rem; color: white; }
        .page-header-modern h1 span { color: white; }
        .page-header-modern p { margin: 0; opacity: 0.95; font-size: 1.05rem; color: white; }
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
        .filter-section { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); margin-bottom: 2rem; }
        .filter-section h3 { margin: 0 0 1.5rem 0; font-size: 1.5rem; font-weight: 700; color: #003366; }
        .filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem; }
        .filter-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #003366; font-size: 0.95rem; }
        .filter-group select { width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 0.95rem; transition: all 0.3s ease; font-family: 'Poppins', sans-serif; }
        .filter-group select:focus { outline: none; border-color: #003366; box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1); }
        .btn-filter { padding: 0.85rem 1.75rem; border-radius: 8px; font-weight: 600; font-size: 0.95rem; border: none; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; }
        .btn-filter:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3); }
        .data-table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); }
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
        .grade-badge { display: inline-block; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 700; font-size: 1.1rem; }
        .grade-a-badge { background: #28a745; color: white; }
        .grade-b-badge { background: #17a2b8; color: white; }
        .grade-c-badge { background: #ffc107; color: #333; }
        .grade-d-badge { background: #fd7e14; color: white; }
        .grade-f-badge { background: #dc3545; color: white; }
        @media (max-width: 768px) { .stats-grid { grid-template-columns: 1fr; } .filter-grid { grid-template-columns: 1fr; } .data-table { font-size: 0.8rem; } .data-table th, .data-table td { padding: 0.5rem; } }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php $pageTitle = 'Results Overview'; include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header-modern">
                <h1><span>📊</span> Results Overview</h1>
                <p>Comprehensive view of student performance across all your courses</p>
            </div>

            <!-- Key Statistics -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">📝</div>
                    <div class="stat-value"><?php echo number_format($stats['total_results'] ?? 0); ?></div>
                    <div class="stat-label">Total Exam Results</div>
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

            <!-- Filters -->
            <div class="filter-section">
                <h3>🔍 Filter Results</h3>
                <form method="GET" action="">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label>Course</label>
                            <select name="course">
                                <option value="">All Courses</option>
                                <?php 
                                $courses->data_seek(0);
                                while($course = $courses->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $course['course_id']; ?>" <?php echo $course_filter == $course['course_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_code']); ?> - <?php echo htmlspecialchars($course['course_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Exam</label>
                            <select name="exam">
                                <option value="">All Exams</option>
                                <?php 
                                $exams->data_seek(0);
                                while($exam = $exams->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $exam['schedule_id']; ?>" <?php echo $exam_filter == $exam['schedule_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($exam['exam_name']); ?> - <?php echo htmlspecialchars($exam['course_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Grade</label>
                            <select name="grade">
                                <option value="">All Grades</option>
                                <option value="A" <?php echo $grade_filter == 'A' ? 'selected' : ''; ?>>A</option>
                                <option value="A-" <?php echo $grade_filter == 'A-' ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo $grade_filter == 'B+' ? 'selected' : ''; ?>>B+</option>
                                <option value="B" <?php echo $grade_filter == 'B' ? 'selected' : ''; ?>>B</option>
                                <option value="B-" <?php echo $grade_filter == 'B-' ? 'selected' : ''; ?>>B-</option>
                                <option value="C+" <?php echo $grade_filter == 'C+' ? 'selected' : ''; ?>>C+</option>
                                <option value="C" <?php echo $grade_filter == 'C' ? 'selected' : ''; ?>>C</option>
                                <option value="C-" <?php echo $grade_filter == 'C-' ? 'selected' : ''; ?>>C-</option>
                                <option value="D" <?php echo $grade_filter == 'D' ? 'selected' : ''; ?>>D</option>
                                <option value="F" <?php echo $grade_filter == 'F' ? 'selected' : ''; ?>>F</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Pass Status</label>
                            <select name="pass">
                                <option value="">All Status</option>
                                <option value="Pass" <?php echo $pass_filter == 'Pass' ? 'selected' : ''; ?>>Pass</option>
                                <option value="Fail" <?php echo $pass_filter == 'Fail' ? 'selected' : ''; ?>>Fail</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-filter"><span>🔍</span> Apply Filters</button>
                </form>
            </div>

            <!-- Results Table -->
            <?php if($results->num_rows > 0): ?>
            <div style="background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);">
                <h3 style="margin: 0 0 1.5rem 0; font-size: 1.4rem; font-weight: 700; color: #003366;">📋 Exam Results</h3>
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
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result['student_code']); ?></td>
                            <td><strong><?php echo htmlspecialchars($result['student_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($result['course_code']); ?></td>
                            <td><?php echo htmlspecialchars($result['exam_name']); ?></td>
                            <td><span class="score-badge <?php echo $badgeClass; ?>"><?php echo number_format($score, 1); ?>%</span></td>
                            <td><strong><?php echo $result['letter_grade']; ?></strong></td>
                            <td><span class="<?php echo $result['pass_status'] == 'Pass' ? 'pass-badge' : 'fail-badge'; ?>"><?php echo $result['pass_status']; ?></span></td>
                            <td><?php echo $result['correct_answers']; ?> / <?php echo $result['total_questions']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($result['exam_submitted_at'])); ?></td>
                            <td><a href="ViewStudentResult.php?result_id=<?php echo $result['result_id']; ?>" class="btn btn-sm btn-primary">View Details</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div style="background: white; border-radius: 12px; padding: 3rem; text-align: center; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);">
                <div style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;">📭</div>
                <p style="color: #6c757d; font-size: 1.1rem;">No results found matching your filters.</p>
            </div>
            <?php endif; ?>
                        </div>
                        <div class="grade-box grade-b">
                            <div class="grade-letter" style="color: #17a2b8;">B</div>
                            <div class="grade-count"><?php echo $stats['grade_b']; ?> students</div>
                        </div>
                        <div class="grade-box grade-c">
                            <div class="grade-letter" style="color: #ffc107;">C</div>
                            <div class="grade-count"><?php echo $stats['grade_c']; ?> students</div>
                        </div>
                        <div class="grade-box grade-d">
                            <div class="grade-letter" style="color: #fd7e14;">D</div>
                            <div class="grade-count"><?php echo $stats['grade_d']; ?> students</div>
                        </div>
                        <div class="grade-box grade-f">
                            <div class="grade-letter" style="color: #dc3545;">F</div>
                            <div class="grade-count"><?php echo $stats['grade_f']; ?> students</div>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-top: 2rem; padding-top: 2rem; border-top: 2px solid var(--border-color);">
                        <div style="text-align: center;">
                            <div style="font-size: 2rem; font-weight: 800; color: var(--success-color);">
                                <?php echo $stats['passed']; ?>
                            </div>
                            <div style="color: var(--text-secondary);">✅ Passed</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 2rem; font-weight: 800; color: #dc3545;">
                                <?php echo $stats['failed']; ?>
                            </div>
                            <div style="color: var(--text-secondary);">❌ Failed</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3 class="card-title">🔍 Filter Results</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <form method="GET" action="">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <div class="form-group" style="margin: 0;">
                                <label>Course</label>
                                <select name="course" class="form-control">
                                    <option value="">All Courses</option>
                                    <?php 
                                    while($course = $courses->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $course['course_id']; ?>" <?php echo ($course_filter == $course['course_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['course_name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group" style="margin: 0;">
                                <label>Exam</label>
                                <select name="exam" class="form-control">
                                    <option value="">All Exams</option>
                                    <?php 
                                    while($exam = $exams->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $exam['schedule_id']; ?>" <?php echo ($exam_filter == $exam['schedule_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($exam['exam_name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group" style="margin: 0;">
                                <label>Grade</label>
                                <select name="grade" class="form-control">
                                    <option value="">All Grades</option>
                                    <?php 
                                    $grades = ['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'F'];
                                    foreach($grades as $g): 
                                    ?>
                                    <option value="<?php echo $g; ?>" <?php echo ($grade_filter == $g) ? 'selected' : ''; ?>><?php echo $g; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group" style="margin: 0;">
                                <label>Status</label>
                                <select name="pass" class="form-control">
                                    <option value="">All</option>
                                    <option value="Pass" <?php echo ($pass_filter == 'Pass') ? 'selected' : ''; ?>>Passed</option>
                                    <option value="Fail" <?php echo ($pass_filter == 'Fail') ? 'selected' : ''; ?>>Failed</option>
                                </select>
                            </div>
                            
                            <div style="display: flex; gap: 0.5rem; align-items: end;">
                                <button type="submit" class="btn btn-primary">Apply</button>
                                <a href="ResultsOverview.php" class="btn btn-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📋 Student Results (<?php echo $results->num_rows; ?>)</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <?php if($results->num_rows > 0): ?>
                        <div style="display: grid; grid-template-columns: 120px 1fr 200px 100px 80px auto; gap: 1rem; padding: 0.75rem 1rem; font-weight: 600; color: var(--text-secondary); font-size: 0.9rem; border-bottom: 2px solid var(--border-color); margin-bottom: 1rem;">
                            <div>STUDENT ID</div>
                            <div>STUDENT NAME</div>
                            <div>EXAM</div>
                            <div>SCORE</div>
                            <div>GRADE</div>
                            <div>ACTION</div>
                        </div>
                        
                        <?php while($result = $results->fetch_assoc()): 
                            $grade_class = '';
                            if(in_array($result['letter_grade'], ['A+', 'A', 'A-'])) $grade_class = 'grade-a-badge';
                            elseif(in_array($result['letter_grade'], ['B+', 'B', 'B-'])) $grade_class = 'grade-b-badge';
                            elseif(in_array($result['letter_grade'], ['C+', 'C', 'C-'])) $grade_class = 'grade-c-badge';
                            elseif($result['letter_grade'] == 'D') $grade_class = 'grade-d-badge';
                            else $grade_class = 'grade-f-badge';
                        ?>
                        <div class="result-row">
                            <div style="font-weight: 600; color: var(--primary-color);">
                                <?php echo htmlspecialchars($result['student_code']); ?>
                            </div>
                            <div>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($result['student_name']); ?></div>
                                <div style="font-size: 0.85rem; color: var(--text-secondary);">
                                    <?php echo htmlspecialchars($result['course_name']); ?>
                                </div>
                            </div>
                            <div>
                                <div style="font-weight: 500;"><?php echo htmlspecialchars($result['exam_name']); ?></div>
                                <div style="font-size: 0.85rem; color: var(--text-secondary);">
                                    <?php echo date('M d, Y', strtotime($result['exam_submitted_at'])); ?>
                                </div>
                            </div>
                            <div style="font-size: 1.2rem; font-weight: 700; color: var(--primary-color);">
                                <?php echo round($result['percentage_score'], 1); ?>%
                            </div>
                            <div>
                                <span class="grade-badge <?php echo $grade_class; ?>">
                                    <?php echo $result['letter_grade']; ?>
                                </span>
                            </div>
                            <div>
                                <a href="ViewStudentResult.php?result_id=<?php echo $result['result_id']; ?>" class="btn btn-primary btn-sm">
                                    👁️ View
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 4rem; color: var(--text-secondary);">
                            <div style="font-size: 4rem; margin-bottom: 1rem;">📊</div>
                            <h3>No Results Found</h3>
                            <p>No exam results match your current filters.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>
