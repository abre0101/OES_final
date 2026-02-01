<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "My Courses";
$instructor_id = $_SESSION['ID'];

// Get filter parameters
$department_filter = $_GET['department'] ?? null;
$semester_filter = $_GET['semester'] ?? null;

// Get instructor's courses with enhanced details
$query = "SELECT 
    c.course_id,
    c.course_code,
    c.course_name,
    c.credit_hours,
    c.semester,
    d.department_id,
    d.department_name,
    f.faculty_name,
    COUNT(DISTINCT sc.student_id) as student_count,
    COUNT(DISTINCT es.schedule_id) as total_exams,
    SUM(CASE WHEN es.exam_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming_exams,
    SUM(CASE WHEN es.exam_date < CURDATE() THEN 1 ELSE 0 END) as past_exams,
    COUNT(DISTINCT eq.question_id) as total_questions,
    COUNT(DISTINCT er.result_id) as total_results,
    AVG(er.percentage_score) as avg_score
    FROM instructor_courses ic
    INNER JOIN courses c ON ic.course_id = c.course_id
    INNER JOIN departments d ON c.department_id = d.department_id
    INNER JOIN faculties f ON d.faculty_id = f.faculty_id
    LEFT JOIN student_courses sc ON c.course_id = sc.course_id AND sc.is_active = TRUE
    LEFT JOIN exam_schedules es ON c.course_id = es.course_id AND es.is_active = TRUE
    LEFT JOIN exam_questions eq ON es.schedule_id = eq.schedule_id
    LEFT JOIN exam_results er ON es.schedule_id = er.schedule_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE";

$params = [$instructor_id];
$types = "i";

if($department_filter) {
    $query .= " AND d.department_id = ?";
    $params[] = $department_filter;
    $types .= "i";
}

if($semester_filter) {
    $query .= " AND c.semester = ?";
    $params[] = $semester_filter;
    $types .= "s";
}

$query .= " GROUP BY c.course_id ORDER BY c.course_name";

$stmt = $con->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$courses = $stmt->get_result();

// Get departments for filter
$deptQuery = $con->prepare("SELECT DISTINCT d.department_id, d.department_name
    FROM instructor_courses ic
    INNER JOIN courses c ON ic.course_id = c.course_id
    INNER JOIN departments d ON c.department_id = d.department_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE
    ORDER BY d.department_name");
$deptQuery->bind_param("i", $instructor_id);
$deptQuery->execute();
$departments = $deptQuery->get_result();

// Get overall statistics
$statsQuery = $con->prepare("SELECT 
    COUNT(DISTINCT c.course_id) as total_courses,
    COUNT(DISTINCT sc.student_id) as total_students,
    COUNT(DISTINCT es.schedule_id) as total_exams,
    COUNT(DISTINCT eq.question_id) as total_questions
    FROM instructor_courses ic
    INNER JOIN courses c ON ic.course_id = c.course_id
    LEFT JOIN student_courses sc ON c.course_id = sc.course_id AND sc.is_active = TRUE
    LEFT JOIN exam_schedules es ON c.course_id = es.course_id AND es.is_active = TRUE
    LEFT JOIN exam_questions eq ON es.schedule_id = eq.schedule_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE");
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
    <title>My Courses - Instructor</title>
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
        .course-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 5px solid var(--primary-color);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .course-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.05), transparent);
            border-radius: 0 0 0 100%;
        }
        
        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }
        
        .course-badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-semester {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .badge-credits {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .course-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.05), rgba(74, 144, 226, 0.02));
            border-radius: var(--radius-md);
            position: relative;
            z-index: 1;
        }
        
        .course-stat {
            text-align: center;
            padding: 0.5rem;
        }
        
        .course-stat-value {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color), #667eea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .course-stat-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            margin-top: 0.25rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .course-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }
        
        .course-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 0.75rem;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .meta-icon {
            font-size: 1.1rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .empty-state-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            opacity: 0.5;
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header-modern">
                <h1><span>📚</span> My Courses</h1>
                <p>Manage and monitor all your assigned courses</p>
            </div>

            <!-- Statistics Overview -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">📚</div>
                    <div class="stat-value"><?php echo number_format($stats['total_courses'] ?? 0); ?></div>
                    <div class="stat-label">Total Courses</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-value"><?php echo number_format($stats['total_students'] ?? 0); ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon">📝</div>
                    <div class="stat-value"><?php echo number_format($stats['total_exams'] ?? 0); ?></div>
                    <div class="stat-label">Total Exams</div>
                </div>
                <div class="stat-card info">
                    <div class="stat-icon">❓</div>
                    <div class="stat-value"><?php echo number_format($stats['total_questions'] ?? 0); ?></div>
                    <div class="stat-label">Total Questions</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-section">
                <h3>🔍 Filter Courses</h3>
                <form method="GET" action="">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label>Department</label>
                            <select name="department">
                                <option value="">All Departments</option>
                                <?php 
                                $departments->data_seek(0);
                                while($dept = $departments->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $dept['department_id']; ?>" <?php echo $department_filter == $dept['department_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Semester</label>
                            <select name="semester">
                                <option value="">All Semesters</option>
                                <option value="1" <?php echo $semester_filter == '1' ? 'selected' : ''; ?>>Semester 1</option>
                                <option value="2" <?php echo $semester_filter == '2' ? 'selected' : ''; ?>>Semester 2</option>
                                <option value="3" <?php echo $semester_filter == '3' ? 'selected' : ''; ?>>Semester 3</option>
                                <option value="4" <?php echo $semester_filter == '4' ? 'selected' : ''; ?>>Semester 4</option>
                                <option value="5" <?php echo $semester_filter == '5' ? 'selected' : ''; ?>>Semester 5</option>
                                <option value="6" <?php echo $semester_filter == '6' ? 'selected' : ''; ?>>Semester 6</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-filter"><span>🔍</span> Apply Filters</button>
                </form>
            </div>

            <!-- Key Statistics -->
            <div class="stats-grid" style="margin-bottom: 2rem;">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">📚</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $stats['total_courses']; ?></div>
                        <div class="stat-label">Total Courses</div>
                    </div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $stats['total_students']; ?></div>
                        <div class="stat-label">Total Students</div>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">📋</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $stats['total_exams']; ?></div>
                        <div class="stat-label">Scheduled Exams</div>
                    </div>
                </div>
                
                <div class="stat-card stat-info">
                    <div class="stat-icon">❓</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $stats['total_questions']; ?></div>
                        <div class="stat-label">Question Bank</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3 class="card-title">🔍 Filter Courses</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <form method="GET" action="">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                            <div class="form-group" style="margin: 0;">
                                <label>Department</label>
                                <select name="department" class="form-control">
                                    <option value="">All Departments</option>
                                    <?php 
                                    while($dept = $departments->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $dept['department_id']; ?>" <?php echo ($department_filter == $dept['department_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['department_name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group" style="margin: 0;">
                                <label>Semester</label>
                                <select name="semester" class="form-control">
                                    <option value="">All Semesters</option>
                                    <?php 
                                    $semesters = ['Fall 2024', 'Spring 2025', 'Summer 2025', 'Fall 2025'];
                                    foreach($semesters as $sem): 
                                    ?>
                                    <option value="<?php echo $sem; ?>" <?php echo ($semester_filter == $sem) ? 'selected' : ''; ?>><?php echo $sem; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div style="display: flex; gap: 0.5rem; align-items: end;">
                                <button type="submit" class="btn btn-primary">Apply</button>
                                <a href="MyCourses.php" class="btn btn-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Courses List -->
            <h3 style="margin-bottom: 1.5rem;">📖 Your Courses (<?php echo $courses->num_rows; ?>)</h3>
            
            <?php if($courses->num_rows > 0): ?>
                <?php while($course = $courses->fetch_assoc()): ?>
                <div class="course-card">
                    <div class="course-header">
                        <div style="flex: 1;">
                            <h2 style="margin: 0 0 0.75rem 0; color: var(--primary-color); font-size: 1.5rem; font-weight: 700;">
                                <?php echo htmlspecialchars($course['course_name']); ?>
                            </h2>
                            <div style="font-size: 1.1rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">
                                <?php echo htmlspecialchars($course['course_code']); ?>
                            </div>
                            <div class="course-meta">
                                <div class="meta-item">
                                    <span class="meta-icon">🏛️</span>
                                    <span><?php echo htmlspecialchars($course['department_name']); ?></span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-icon">🎓</span>
                                    <span><?php echo htmlspecialchars($course['faculty_name']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div style="text-align: right; display: flex; flex-direction: column; gap: 0.5rem;">
                            <span class="course-badge badge-semester">
                                📅 <?php echo htmlspecialchars($course['semester']); ?>
                            </span>
                            <span class="course-badge badge-credits">
                                ⭐ <?php echo $course['credit_hours']; ?> Credits
                            </span>
                        </div>
                    </div>

                    <div class="course-stats">
                        <div class="course-stat">
                            <div class="course-stat-value"><?php echo $course['student_count']; ?></div>
                            <div class="course-stat-label">👨‍🎓 Students</div>
                        </div>
                        <div class="course-stat">
                            <div class="course-stat-value"><?php echo $course['total_exams']; ?></div>
                            <div class="course-stat-label">📋 Total Exams</div>
                        </div>
                        <div class="course-stat">
                            <div class="course-stat-value"><?php echo $course['total_questions']; ?></div>
                            <div class="course-stat-label">❓ Questions</div>
                        </div>
                        <div class="course-stat">
                            <div class="course-stat-value"><?php echo $course['total_results']; ?></div>
                            <div class="course-stat-label">📊 Results</div>
                        </div>
                        <?php if($course['total_results'] > 0): ?>
                        <div class="course-stat">
                            <div class="course-stat-value"><?php echo round($course['avg_score'], 1); ?>%</div>
                            <div class="course-stat-label">📈 Avg Score</div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="course-actions">
                        <a href="AddQuestion.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-primary btn-sm">
                            ➕ Add Questions
                        </a>
                        <a href="ManageQuestions.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-secondary btn-sm">
                            📝 Manage Questions
                        </a>
                        <a href="ExamsManagement.php?course=<?php echo $course['course_id']; ?>" class="btn btn-warning btn-sm">
                            📋 View Exams (<?php echo $course['total_exams']; ?>)
                        </a>
                        <a href="ViewStudents.php?course=<?php echo $course['course_id']; ?>" class="btn btn-info btn-sm">
                            👥 Students (<?php echo $course['student_count']; ?>)
                        </a>
                        <a href="ResultsOverview.php?course=<?php echo $course['course_id']; ?>" class="btn btn-success btn-sm">
                            📊 Results (<?php echo $course['total_results']; ?>)
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📚</div>
                    <h3 style="color: var(--text-secondary); margin-bottom: 1rem;">No Courses Found</h3>
                    <p style="color: var(--text-secondary); margin: 0 0 2rem 0;">
                        <?php if($department_filter || $semester_filter): ?>
                            No courses match your current filters. Try adjusting the filters above.
                        <?php else: ?>
                            You don't have any courses assigned yet. Please contact the administrator.
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>
