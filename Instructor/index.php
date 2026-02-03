<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Instructor session
SessionManager::startSession('Instructor');

// Check if user is logged in
if(!isset($_SESSION['ID'])){
    header("Location: ../auth/institute-login.php");
    exit();
}

// Validate instructor role - only instructors can access this page
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Instructor'){
    SessionManager::destroySession();
    header("Location: ../auth/institute-login.php");
    exit();
}

require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Dashboard";
$instructor_id = $_SESSION['ID'];
$instructor_name = $_SESSION['Name'];

// Get statistics
$statsQuery = $con->prepare("SELECT 
    COUNT(DISTINCT ic.course_id) as total_courses,
    COUNT(DISTINCT sc.student_id) as total_students,
    COUNT(DISTINCT q.question_id) as total_questions,
    COUNT(DISTINCT es.exam_id) as total_exams,
    COUNT(DISTINCT CASE WHEN es.approval_status = 'draft' THEN es.exam_id END) as draft_exams,
    COUNT(DISTINCT CASE WHEN es.approval_status = 'pending' THEN es.exam_id END) as pending_exams,
    COUNT(DISTINCT CASE WHEN es.approval_status = 'approved' THEN es.exam_id END) as approved_exams
    FROM instructor_courses ic
    LEFT JOIN student_courses sc ON ic.course_id = sc.course_id
    LEFT JOIN questions q ON ic.course_id = q.course_id AND q.created_by = ?
    LEFT JOIN exams es ON ic.course_id = es.course_id AND es.created_by = ?
    WHERE ic.instructor_id = ?");
$statsQuery->bind_param("iii", $instructor_id, $instructor_id, $instructor_id);
$statsQuery->execute();
$stats = $statsQuery->get_result()->fetch_assoc();

// Get recent courses
$recentCoursesQuery = $con->prepare("SELECT 
    c.course_id, c.course_code, c.course_name, c.semester,
    d.department_name,
    COUNT(DISTINCT sc.student_id) as student_count
    FROM instructor_courses ic
    INNER JOIN courses c ON ic.course_id = c.course_id
    INNER JOIN departments d ON c.department_id = d.department_id
    LEFT JOIN student_courses sc ON c.course_id = sc.course_id
    WHERE ic.instructor_id = ?
    GROUP BY c.course_id
    ORDER BY c.course_name
    LIMIT 4");
$recentCoursesQuery->bind_param("i", $instructor_id);
$recentCoursesQuery->execute();
$recentCourses = $recentCoursesQuery->get_result();

// Get recent exams
$recentExamsQuery = $con->prepare("SELECT 
    es.exam_id, es.exam_name, es.approval_status, es.exam_date, es.created_at,
    c.course_name, c.course_code,
    (SELECT COUNT(*) FROM exam_questions WHERE exam_id = es.exam_id) as question_count
    FROM exams es
    INNER JOIN courses c ON es.course_id = c.course_id
    WHERE es.created_by = ?
    ORDER BY es.created_at DESC
    LIMIT 5");
$recentExamsQuery->bind_param("i", $instructor_id);
$recentExamsQuery->execute();
$recentExams = $recentExamsQuery->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Instructor</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        
        .welcome-banner {
            background: linear-gradient(135deg, #003366 0%, #0055aa 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .welcome-content {
            position: relative;
            z-index: 1;
        }
        
        .welcome-title {
            font-size: 2rem;
            font-weight: 800;
            margin: 0 0 0.5rem 0;
            color: white;
        }
        
        .welcome-subtitle {
            font-size: 1.1rem;
            opacity: 0.95;
            margin: 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(0, 51, 102, 0.05), transparent);
            border-radius: 0 0 0 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 900;
            color: #003366;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
        }
        
        .stat-card.primary { border-top: 4px solid #003366; }
        .stat-card.success { border-top: 4px solid #28a745; }
        .stat-card.warning { border-top: 4px solid #ffc107; }
        .stat-card.info { border-top: 4px solid #17a2b8; }
        .stat-card.danger { border-top: 4px solid #dc3545; }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .action-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .action-icon {
            font-size: 3rem;
            flex-shrink: 0;
        }
        
        .action-content h3 {
            margin: 0 0 0.5rem 0;
            color: #003366;
            font-size: 1.2rem;
            font-weight: 700;
        }
        
        .action-content p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #003366;
            margin: 0 0 1.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .card-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #003366;
            margin: 0;
        }
        
        .course-item {
            padding: 1.25rem;
            border-radius: 8px;
            background: #f8f9fa;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .course-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .course-item:last-child {
            margin-bottom: 0;
        }
        
        .course-name {
            font-weight: 700;
            color: #003366;
            margin-bottom: 0.5rem;
            font-size: 1.05rem;
        }
        
        .course-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .exam-item {
            padding: 1.25rem;
            border-left: 4px solid #003366;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .exam-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .exam-item:last-child {
            margin-bottom: 0;
        }
        
        .exam-item.draft { border-left-color: #6c757d; }
        .exam-item.pending { border-left-color: #ffc107; }
        .exam-item.approved { border-left-color: #28a745; }
        
        .exam-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 0.75rem;
        }
        
        .exam-name {
            font-weight: 700;
            color: #003366;
            font-size: 1.05rem;
        }
        
        .status-badge {
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-draft { background: #e9ecef; color: #495057; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        
        .exam-meta {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
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
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }
        
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }
        
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr; }
            .quick-actions { grid-template-columns: 1fr; }
            .content-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <div class="welcome-content">
                    <h1 class="welcome-title">👋 Welcome back, <?php echo htmlspecialchars($instructor_name); ?>!</h1>
                    <p class="welcome-subtitle">Here's what's happening with your courses today</p>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">📚</div>
                    <div class="stat-value"><?php echo number_format($stats['total_courses']); ?></div>
                    <div class="stat-label">My Courses</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-value"><?php echo number_format($stats['total_students']); ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon">❓</div>
                    <div class="stat-value"><?php echo number_format($stats['total_questions']); ?></div>
                    <div class="stat-label">Questions</div>
                </div>
                <div class="stat-card info">
                    <div class="stat-icon">📝</div>
                    <div class="stat-value"><?php echo number_format($stats['total_exams']); ?></div>
                    <div class="stat-label">Total Exams</div>
                </div>
            </div>

            <!-- Exam Status Overview -->
            <?php if($stats['total_exams'] > 0): ?>
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                <div class="stat-card" style="border-top-color: #6c757d;">
                    <div class="stat-icon">📝</div>
                    <div class="stat-value"><?php echo number_format($stats['draft_exams']); ?></div>
                    <div class="stat-label">Draft Exams</div>
                </div>
                <div class="stat-card" style="border-top-color: #ffc107;">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-value"><?php echo number_format($stats['pending_exams']); ?></div>
                    <div class="stat-label">Pending Approval</div>
                </div>
                <div class="stat-card" style="border-top-color: #28a745;">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value"><?php echo number_format($stats['approved_exams']); ?></div>
                    <div class="stat-label">Approved Exams</div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <h2 class="section-title">⚡ Quick Actions</h2>
            <div class="quick-actions">
                <a href="CreateExam.php" class="action-card">
                    <div class="action-icon">📝</div>
                    <div class="action-content">
                        <h3>Create Exam</h3>
                        <p>Start creating a new exam</p>
                    </div>
                </a>
                <a href="AddQuestion.php" class="action-card">
                    <div class="action-icon">➕</div>
                    <div class="action-content">
                        <h3>Add Question</h3>
                        <p>Add to your question bank</p>
                    </div>
                </a>
                <a href="ManageQuestions.php" class="action-card">
                    <div class="action-icon">📋</div>
                    <div class="action-content">
                        <h3>Question Bank</h3>
                        <p>Manage your questions</p>
                    </div>
                </a>
                <a href="ViewStudents.php" class="action-card">
                    <div class="action-icon">👥</div>
                    <div class="action-content">
                        <h3>View Students</h3>
                        <p>See enrolled students</p>
                    </div>
                </a>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- My Courses -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📚 My Courses</h3>
                        <a href="MyCourses.php" class="btn btn-primary btn-sm">View All</a>
                    </div>
                    <?php if($recentCourses->num_rows > 0): ?>
                        <?php while($course = $recentCourses->fetch_assoc()): ?>
                        <div class="course-item">
                            <div class="course-name"><?php echo htmlspecialchars($course['course_name']); ?></div>
                            <div class="course-meta">
                                <span><strong>📖</strong> <?php echo htmlspecialchars($course['course_code']); ?></span>
                                <span><strong>🏛️</strong> <?php echo htmlspecialchars($course['department_name']); ?></span>
                                <span><strong>👨‍🎓</strong> <?php echo $course['student_count']; ?> students</span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📚</div>
                            <p>No courses assigned yet</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Exams -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📝 Recent Exams</h3>
                        <a href="MyExams.php" class="btn btn-primary btn-sm">View All</a>
                    </div>
                    <?php if($recentExams->num_rows > 0): ?>
                        <?php while($exam = $recentExams->fetch_assoc()): ?>
                        <div class="exam-item <?php echo $exam['approval_status']; ?>">
                            <div class="exam-header">
                                <div class="exam-name"><?php echo htmlspecialchars($exam['exam_name']); ?></div>
                                <span class="status-badge status-<?php echo $exam['approval_status']; ?>">
                                    <?php echo strtoupper($exam['approval_status']); ?>
                                </span>
                            </div>
                            <div class="exam-meta">
                                <div><strong>📚</strong> <?php echo htmlspecialchars($exam['course_code']); ?></div>
                                <div><strong>❓</strong> <?php echo $exam['question_count']; ?> questions</div>
                                <?php if($exam['exam_date']): ?>
                                <div><strong>📅</strong> <?php echo date('M d, Y', strtotime($exam['exam_date'])); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📝</div>
                            <p>No exams created yet</p>
                            <a href="CreateExam.php" class="btn btn-primary btn-sm" style="margin-top: 1rem;">Create Your First Exam</a>
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