<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Exams Management";
$instructor_id = $_SESSION['ID'];

// Get filter parameters
$course_filter = $_GET['course'] ?? null;
$status_filter = $_GET['status'] ?? null;
$category_filter = $_GET['category'] ?? null;

// Build query for exams
$query = "SELECT 
    es.exam_id,
    es.exam_name,
    es.exam_date,
    es.start_time,
    es.end_time,
    es.duration_minutes,
    es.total_marks,
    es.pass_marks,
    es.is_active,
    c.course_id,
    c.course_name,
    c.course_code,
    ec.category_name,
    d.department_name,
    COUNT(DISTINCT eq.question_id) as question_count,
    COUNT(DISTINCT er.student_id) as students_taken,
    AVG(er.percentage_score) as avg_score
    FROM exams es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    INNER JOIN departments d ON c.department_id = d.department_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    LEFT JOIN exam_questions eq ON es.exam_id = eq.exam_id
    LEFT JOIN exam_results er ON es.exam_id = er.exam_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE";

$params = [$instructor_id];
$types = "i";

if($course_filter) {
    $query .= " AND c.course_id = ?";
    $params[] = $course_filter;
    $types .= "i";
}

if($status_filter !== null && $status_filter !== '') {
    $query .= " AND es.is_active = ?";
    $params[] = $status_filter;
    $types .= "i";
}

if($category_filter) {
    $query .= " AND ec.exam_category_id = ?";
    $params[] = $category_filter;
    $types .= "i";
}

$query .= " GROUP BY es.exam_id ORDER BY es.exam_date DESC, es.start_time DESC";

$stmt = $con->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$exams = $stmt->get_result();

// Get courses for filter
$coursesQuery = $con->prepare("SELECT DISTINCT c.course_id, c.course_name, c.course_code
    FROM instructor_courses ic
    INNER JOIN courses c ON ic.course_id = c.course_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE
    ORDER BY c.course_name");
$coursesQuery->bind_param("i", $instructor_id);
$coursesQuery->execute();
$courses = $coursesQuery->get_result();

// Get categories for filter
$categories = $con->query("SELECT * FROM exam_categories WHERE is_active = TRUE ORDER BY category_name");

// Get statistics
$statsQuery = $con->prepare("SELECT 
    COUNT(DISTINCT es.exam_id) as total_exams,
    COUNT(DISTINCT c.course_id) as courses_with_exams,
    SUM(CASE WHEN es.exam_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming_exams,
    SUM(CASE WHEN es.exam_date < CURDATE() THEN 1 ELSE 0 END) as past_exams,
    COUNT(DISTINCT er.student_id) as total_students_examined
    FROM exams es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    LEFT JOIN exam_results er ON es.exam_id = er.exam_id
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
    <title>Exams Management - Instructor</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .exam-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 5px solid var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .exam-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .exam-card.past {
            border-left-color: #6c757d;
            opacity: 0.85;
        }
        
        .exam-card.today {
            border-left-color: #ff9800;
            background: linear-gradient(to right, rgba(255, 152, 0, 0.05), white);
        }
        
        .exam-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        
        .exam-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-upcoming { background: #e3f2fd; color: #1976d2; }
        .badge-today { background: #fff3e0; color: #f57c00; }
        .badge-past { background: #f5f5f5; color: #757575; }
        .badge-active { background: #e8f5e9; color: #388e3c; }
        .badge-inactive { background: #ffebee; color: #d32f2f; }
        
        .exam-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
            padding: 1rem;
            background: var(--bg-light);
            border-radius: var(--radius-md);
        }
        
        .exam-stat {
            text-align: center;
        }
        
        .exam-stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-color);
        }
        
        .exam-stat-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            margin-top: 0.25rem;
        }
        
        .exam-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .timeline-marker {
            position: absolute;
            left: -8px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: var(--primary-color);
            border: 3px solid white;
            box-shadow: 0 0 0 2px var(--primary-color);
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header">
                <h1>📋 Exams Management</h1>
                <p>Schedule, manage, and monitor all your course examinations</p>
            </div>

            <!-- Key Statistics -->
            <div class="stats-grid" style="margin-bottom: 2rem;">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">📋</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $stats['total_exams']; ?></div>
                        <div class="stat-label">Total Exams</div>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">📅</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $stats['upcoming_exams']; ?></div>
                        <div class="stat-label">Upcoming Exams</div>
                    </div>
                </div>
                
                <div class="stat-card stat-info">
                    <div class="stat-icon">📚</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $stats['courses_with_exams']; ?></div>
                        <div class="stat-label">Courses</div>
                    </div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $stats['total_students_examined']; ?></div>
                        <div class="stat-label">Students Examined</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3 class="card-title">🔍 Filter Exams</h3>
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
                                <label>Category</label>
                                <select name="category" class="form-control">
                                    <option value="">All Categories</option>
                                    <?php 
                                    while($cat = $categories->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $cat['exam_category_id']; ?>" <?php echo ($category_filter == $cat['exam_category_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group" style="margin: 0;">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="1" <?php echo ($status_filter === '1') ? 'selected' : ''; ?>>Active</option>
                                    <option value="0" <?php echo ($status_filter === '0') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <div style="display: flex; gap: 0.5rem; align-items: end;">
                                <button type="submit" class="btn btn-primary">Apply</button>
                                <a href="ExamsManagement.php" class="btn btn-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Exams List -->
            <h3 style="margin-bottom: 1.5rem;">📋 Scheduled Exams (<?php echo $exams->num_rows; ?>)</h3>
            
            <?php if($exams->num_rows > 0): ?>
                <?php while($exam = $exams->fetch_assoc()): 
                    $exam_date = strtotime($exam['exam_date']);
                    $today = strtotime('today');
                    $is_today = date('Y-m-d', $exam_date) == date('Y-m-d');
                    $is_past = $exam_date < $today;
                    $is_upcoming = $exam_date > $today;
                    
                    $card_class = '';
                    $time_badge = '';
                    if($is_today) {
                        $card_class = 'today';
                        $time_badge = '<span class="exam-badge badge-today">📍 TODAY</span>';
                    } elseif($is_past) {
                        $card_class = 'past';
                        $time_badge = '<span class="exam-badge badge-past">✓ COMPLETED</span>';
                    } else {
                        $time_badge = '<span class="exam-badge badge-upcoming">📅 UPCOMING</span>';
                    }
                    
                    $status_badge = $exam['is_active'] 
                        ? '<span class="exam-badge badge-active">● ACTIVE</span>' 
                        : '<span class="exam-badge badge-inactive">● INACTIVE</span>';
                ?>
                <div class="exam-card <?php echo $card_class; ?>">
                    <div class="exam-header">
                        <div style="flex: 1;">
                            <h3 style="margin: 0 0 0.5rem 0; color: var(--primary-color); font-size: 1.3rem;">
                                <?php echo htmlspecialchars($exam['exam_name']); ?>
                            </h3>
                            <div style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 0.5rem;">
                                <strong><?php echo htmlspecialchars($exam['course_name']); ?></strong> (<?php echo $exam['course_code']; ?>)
                                • <?php echo htmlspecialchars($exam['category_name']); ?>
                                • <?php echo htmlspecialchars($exam['department_name']); ?>
                            </div>
                            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                                <?php echo $time_badge; ?>
                                <?php echo $status_badge; ?>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1.1rem; font-weight: 600; color: var(--primary-color);">
                                <?php echo date('M d, Y', $exam_date); ?>
                            </div>
                            <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                <?php echo date('g:i A', strtotime($exam['start_time'])); ?> - <?php echo date('g:i A', strtotime($exam['end_time'])); ?>
                            </div>
                        </div>
                    </div>

                    <div class="exam-stats">
                        <div class="exam-stat">
                            <div class="exam-stat-value"><?php echo $exam['question_count']; ?></div>
                            <div class="exam-stat-label">Questions</div>
                        </div>
                        <div class="exam-stat">
                            <div class="exam-stat-value"><?php echo $exam['duration_minutes']; ?></div>
                            <div class="exam-stat-label">Minutes</div>
                        </div>
                        <div class="exam-stat">
                            <div class="exam-stat-value"><?php echo $exam['total_marks']; ?></div>
                            <div class="exam-stat-label">Total Marks</div>
                        </div>
                        <div class="exam-stat">
                            <div class="exam-stat-value"><?php echo $exam['pass_marks']; ?></div>
                            <div class="exam-stat-label">Pass Marks</div>
                        </div>
                        <div class="exam-stat">
                            <div class="exam-stat-value"><?php echo $exam['students_taken']; ?></div>
                            <div class="exam-stat-label">Students</div>
                        </div>
                        <?php if($exam['students_taken'] > 0): ?>
                        <div class="exam-stat">
                            <div class="exam-stat-value"><?php echo round($exam['avg_score'], 1); ?>%</div>
                            <div class="exam-stat-label">Avg Score</div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="exam-actions">
                        <a href="ViewExam.php?id=<?php echo $exam['exam_id']; ?>" class="btn btn-primary btn-sm">
                            👁️ View Questions (<?php echo $exam['question_count']; ?>)
                        </a>
                        <a href="ResultsOverview.php?exam=<?php echo $exam['exam_id']; ?>" class="btn btn-success btn-sm">
                            📊 Results (<?php echo $exam['students_taken']; ?>)
                        </a>
                        <a href="EditSchedule.php?id=<?php echo $exam['exam_id']; ?>" class="btn btn-secondary btn-sm">
                            ⚙️ Settings
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 4rem; background: white; border-radius: var(--radius-lg); box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📋</div>
                    <h3 style="color: var(--text-secondary);">No Exams Found</h3>
                    <p style="color: var(--text-secondary); margin: 1rem 0 2rem 0;">
                        <?php if($course_filter || $status_filter || $category_filter): ?>
                            No exams match your current filters. Try adjusting the filters above.
                        <?php else: ?>
                            You haven't scheduled any exams yet. Start by creating your first exam.
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
