<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;

// Get instructor info
$instructor_id = $_SESSION['ID'];
$instructor_name = $_SESSION['Name'];

// Get instructor's courses count
$coursesQuery = $con->prepare("SELECT COUNT(DISTINCT course_id) as count 
    FROM instructor_courses 
    WHERE instructor_id = ? AND is_active = TRUE");
$coursesQuery->bind_param("i", $instructor_id);
$coursesQuery->execute();
$total_courses = $coursesQuery->get_result()->fetch_assoc()['count'];
$coursesQuery->close();

// Get total questions created by this instructor
$questionsQuery = $con->prepare("SELECT COUNT(*) as count 
    FROM questions 
    WHERE instructor_id = ?");
$questionsQuery->bind_param("i", $instructor_id);
$questionsQuery->execute();
$total_questions = $questionsQuery->get_result()->fetch_assoc()['count'];
$questionsQuery->close();

// Get total exams created for instructor's courses
$examsQuery = $con->prepare("SELECT COUNT(DISTINCT es.schedule_id) as count 
    FROM exam_schedules es
    INNER JOIN instructor_courses ic ON es.course_id = ic.course_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE");
$examsQuery->bind_param("i", $instructor_id);
$examsQuery->execute();
$total_exams = $examsQuery->get_result()->fetch_assoc()['count'];
$examsQuery->close();

// Get total students enrolled in instructor's courses
$studentsQuery = $con->prepare("SELECT COUNT(DISTINCT sc.student_id) as count 
    FROM student_courses sc
    INNER JOIN instructor_courses ic ON sc.course_id = ic.course_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE AND sc.is_active = TRUE");
$studentsQuery->bind_param("i", $instructor_id);
$studentsQuery->execute();
$total_students = $studentsQuery->get_result()->fetch_assoc()['count'];
$studentsQuery->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard - Debre Markos University</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <!-- Main Content -->
    <div class="admin-main-content">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-left">
                <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
                <div class="header-breadcrumb">
                    <span class="breadcrumb-item">Instructor</span>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active">Dashboard</span>
                </div>
            </div>
            
            <div class="header-center">
                <div class="header-search">
                    <span class="search-icon">🔍</span>
                    <input type="text" placeholder="Search questions, exams, students..." class="search-input">
                </div>
            </div>
            
            <div class="header-right">
                <div class="header-datetime">
                    <div class="header-time" id="currentTime"></div>
                    <div class="header-date"><?php echo date('D, M d, Y'); ?></div>
                </div>
                
                <div class="header-notifications">
                    <button class="header-icon-btn" title="Notifications">
                        <span class="notification-icon">🔔</span>
                        <span class="notification-badge">3</span>
                    </button>
                </div>
                
                <div class="header-profile" onclick="toggleProfileDropdown(event)">
                    <div class="header-profile-avatar">
                        <?php echo strtoupper(substr($_SESSION['Name'], 0, 1)); ?>
                    </div>
                    <div class="header-profile-info">
                        <div class="header-profile-name"><?php echo $_SESSION['Name']; ?></div>
                        <div class="header-profile-role">Instructor</div>
                    </div>
                    <button class="header-dropdown-btn">▼</button>
                    
                    <div class="profile-dropdown">
                        <a href="Profile.php" class="dropdown-item">
                            <span class="dropdown-icon">👤</span>
                            <span>My Profile</span>
                        </a>
                        <a href="Settings.php" class="dropdown-item">
                            <span class="dropdown-icon">⚙️</span>
                            <span>Settings</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="Logout.php" class="dropdown-item logout">
                            <span class="dropdown-icon">🚪</span>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="admin-content">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <div class="welcome-content">
                    <h1>👋 Welcome, <?php echo $_SESSION['Name']; ?>!</h1>
                    <p>Instructor Dashboard - Debre Markos University</p>
                    <p style="font-size: 0.95rem; margin-top: 0.5rem; opacity: 0.9;">
                        Manage your exams, questions, and student assessments
                    </p>
                </div>
                <div class="welcome-image">
                    <img src="images/instructor.png" alt="Instructor" onerror="this.style.display='none'">
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">📚</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $total_courses; ?></div>
                        <div class="stat-label">My Courses</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">📝</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $total_questions; ?></div>
                        <div class="stat-label">Questions Created</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon">📋</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $total_exams; ?></div>
                        <div class="stat-label">Exams Created</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $total_students; ?></div>
                        <div class="stat-label">Students</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="content-wrapper mt-4">
                <h2>⚡ Quick Actions</h2>
                <div class="quick-actions-grid">
                    <a href="AddQuestion.php" class="action-card">
                        <div class="action-icon">➕</div>
                        <div class="action-title">Create New Exam</div>
                        <div class="action-desc">Add questions and create exam</div>
                    </a>
                    <a href="ManageExams.php" class="action-card">
                        <div class="action-icon">📅</div>
                        <div class="action-title">View Exams</div>
                        <div class="action-desc">Check scheduled exams</div>
                    </a>
                    <a href="ManageQuestions.php" class="action-card">
                        <div class="action-icon">📋</div>
                        <div class="action-title">Manage Questions</div>
                        <div class="action-desc">Edit existing questions</div>
                    </a>
                    <a href="SeeResults.php" class="action-card">
                        <div class="action-icon">📊</div>
                        <div class="action-title">View Results</div>
                        <div class="action-desc">Check student performance</div>
                    </a>
                </div>
            </div>

            <!-- Recent Activity & Notifications -->
            <div class="grid grid-2 mt-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📋 Recent Activity</h3>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon" style="background: var(--primary-color);">👤</div>
                            <div class="activity-content">
                                <div class="activity-title">You logged in</div>
                                <div class="activity-time">Just now</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon" style="background: var(--success-color);">✓</div>
                            <div class="activity-content">
                                <div class="activity-title">System is_active</div>
                                <div class="activity-time">All systems operational</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🔔 Notifications</h3>
                    </div>
                    <div style="padding: 1.5rem;">
                        <div style="padding: 1rem; background: rgba(40, 167, 69, 0.1); border-radius: var(--radius-md); margin-bottom: 1rem; border-left: 4px solid var(--success-color);">
                            <strong style="color: var(--success-color);">✓ Exam Approved</strong>
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem;">Your exam has been approved by the committee</p>
                        </div>
                        <div style="padding: 1rem; background: rgba(255, 193, 7, 0.1); border-radius: var(--radius-md); border-left: 4px solid var(--warning-color);">
                            <strong style="color: var(--warning-color);">⏰ Upcoming Deadline</strong>
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem;">Exam scheduled for tomorrow</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>
