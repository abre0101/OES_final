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
$pageTitle = "Department Exams";
$deptId = $_SESSION['DeptId'] ?? null;

$message = '';
$messageType = '';

// Handle publish/unpublish
if(isset($_GET['toggle_publish'])) {
    $exam_id = intval($_GET['toggle_publish']);
    $con->query("UPDATE exams SET is_active = NOT is_active WHERE exam_id = $exam_id");
    header("Location: DepartmentExams.php?published=1");
    exit();
}

if(isset($_GET['published'])) {
    $message = "Exam visibility updated successfully!";
    $messageType = "success";
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$course_filter = $_GET['course'] ?? 'all';
$year_filter = $_GET['year'] ?? 'all';

// Build status condition
$status_condition = "";
switch($status_filter) {
    case 'upcoming':
        $status_condition = "AND es.exam_date >= CURDATE()";
        break;
    case 'past':
        $status_condition = "AND es.exam_date < CURDATE()";
        break;
    case 'today':
        $status_condition = "AND es.exam_date = CURDATE()";
        break;
    case 'pending':
        $status_condition = "AND es.approval_status = 'pending'";
        break;
    case 'approved':
        $status_condition = "AND es.approval_status = 'approved'";
        break;
}

// Build course condition
$course_condition = "";
if($course_filter != 'all') {
    $course_condition = "AND c.course_id = " . intval($course_filter);
}

// Build year condition
$year_condition = "";
if($year_filter != 'all') {
    $year_condition = "AND YEAR(es.exam_date) = " . intval($year_filter);
}

// Get all exams in this department
$exams_query = "SELECT es.*, c.course_name, c.course_code, ec.category_name,
                i.full_name as instructor_name,
                COUNT(DISTINCT sc.student_id) as enrolled_count,
                COUNT(DISTINCT er.result_id) as attempts_count,
                (SELECT COUNT(*) FROM exam_questions eq WHERE eq.exam_id = es.exam_id) as question_count
                FROM exams es
                LEFT JOIN courses c ON es.course_id = c.course_id
                LEFT JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
                LEFT JOIN instructors i ON es.created_by = i.instructor_id
                LEFT JOIN exam_results er ON es.exam_id = er.exam_id
                LEFT JOIN student_courses sc ON c.course_id = sc.course_id
                WHERE c.department_id = ?
                $status_condition $course_condition $year_condition
                GROUP BY es.exam_id
                ORDER BY es.exam_date DESC, es.start_time DESC";
$stmt = $con->prepare($exams_query);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$exams = $stmt->get_result();

// Get courses for filter
$courses_query = "SELECT * FROM courses WHERE department_id = ? AND is_active = 1 ORDER BY course_code";
$stmt = $con->prepare($courses_query);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$courses = $stmt->get_result();

// Get statistics
$stats_query = "SELECT 
                COUNT(DISTINCT es.exam_id) as total_exams,
                COUNT(DISTINCT CASE WHEN es.exam_date >= CURDATE() THEN es.exam_id END) as upcoming_exams,
                COUNT(DISTINCT CASE WHEN es.exam_date < CURDATE() THEN es.exam_id END) as past_exams,
                COUNT(DISTINCT CASE WHEN es.exam_date = CURDATE() THEN es.exam_id END) as today_exams,
                COUNT(DISTINCT CASE WHEN es.approval_status = 'pending' THEN es.exam_id END) as pending_exams
                FROM exams es
                LEFT JOIN courses c ON es.course_id = c.course_id
                WHERE c.department_id = ?";
$stmt = $con->prepare($stats_query);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get available years for filter
$years_query = "SELECT DISTINCT YEAR(exam_date) as exam_year 
                FROM exams es
                LEFT JOIN courses c ON es.course_id = c.course_id
                WHERE c.department_id = ? AND exam_date IS NOT NULL
                ORDER BY exam_year DESC";
$stmt = $con->prepare($years_query);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$years = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Exams - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <style>
        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .filter-tab {
            padding: 0.5rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #495057;
            font-size: 0.9rem;
        }
        .filter-tab:hover {
            border-color: #667eea;
            color: #667eea;
        }
        .filter-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }
        .exam-card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
            background: white;
        }
        .exam-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>
    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>
        <div class="admin-content">
            <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: var(--primary-color); font-weight: 700;">🏛️ Department Exams</h1>
                    <p style="margin: 0; color: var(--text-secondary); font-size: 1.05rem;">
                        All exams in <?php echo $_SESSION['Dept']; ?> Department (past, present, future)
                    </p>
                </div>
                <button onclick="exportExams()" class="btn btn-success" style="display: flex; align-items: center; gap: 0.5rem;">
                    <span>📥</span> Export Data
                </button>
            </div>

            <?php if($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 1.5rem;">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1.25rem; border-radius: 12px; color: white; text-align: center;">
                    <h3 style="margin: 0; font-size: 2rem; font-weight: 700; color: white;"><?php echo $stats['total_exams']; ?></h3>
                    <p style="margin: 0.5rem 0 0 0; color: white; font-weight: 500;">Total Exams</p>
                </div>
                <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 1.25rem; border-radius: 12px; color: white; text-align: center;">
                    <h3 style="margin: 0; font-size: 2rem; font-weight: 700; color: white;"><?php echo $stats['upcoming_exams']; ?></h3>
                    <p style="margin: 0.5rem 0 0 0; color: white; font-weight: 500;">Upcoming</p>
                </div>
                <div style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); padding: 1.25rem; border-radius: 12px; color: white; text-align: center;">
                    <h3 style="margin: 0; font-size: 2rem; font-weight: 700; color: white;"><?php echo $stats['today_exams']; ?></h3>
                    <p style="margin: 0.5rem 0 0 0; color: white; font-weight: 500;">Today</p>
                </div>
                <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 1.25rem; border-radius: 12px; color: white; text-align: center;">
                    <h3 style="margin: 0; font-size: 2rem; font-weight: 700; color: white;"><?php echo $stats['past_exams']; ?></h3>
                    <p style="margin: 0.5rem 0 0 0; color: white; font-weight: 500;">Past</p>
                </div>
                <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 1.25rem; border-radius: 12px; color: white; text-align: center;">
                    <h3 style="margin: 0; font-size: 2rem; font-weight: 700; color: white;"><?php echo $stats['pending_exams']; ?></h3>
                    <p style="margin: 0.5rem 0 0 0; color: white; font-weight: 500;">Pending</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-body">
                    <h4 style="margin: 0 0 1rem 0;">🔍 Filter Exams</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Status:</label>
                            <div class="filter-tabs">
                                <a href="?status=all&course=<?php echo $course_filter; ?>&year=<?php echo $year_filter; ?>" class="filter-tab <?php echo $status_filter == 'all' ? 'active' : ''; ?>">All</a>
                                <a href="?status=upcoming&course=<?php echo $course_filter; ?>&year=<?php echo $year_filter; ?>" class="filter-tab <?php echo $status_filter == 'upcoming' ? 'active' : ''; ?>">Upcoming</a>
                                <a href="?status=today&course=<?php echo $course_filter; ?>&year=<?php echo $year_filter; ?>" class="filter-tab <?php echo $status_filter == 'today' ? 'active' : ''; ?>">Today</a>
                                <a href="?status=past&course=<?php echo $course_filter; ?>&year=<?php echo $year_filter; ?>" class="filter-tab <?php echo $status_filter == 'past' ? 'active' : ''; ?>">Past</a>
                                <a href="?status=pending&course=<?php echo $course_filter; ?>&year=<?php echo $year_filter; ?>" class="filter-tab <?php echo $status_filter == 'pending' ? 'active' : ''; ?>">Pending</a>
                            </div>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Course:</label>
                            <select onchange="window.location.href='?status=<?php echo $status_filter; ?>&course=' + this.value + '&year=<?php echo $year_filter; ?>'" class="form-control">
                                <option value="all">All Courses</option>
                                <?php 
                                $courses->data_seek(0);
                                while($course = $courses->fetch_assoc()): ?>
                                <option value="<?php echo $course['course_id']; ?>" <?php echo $course_filter == $course['course_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Year:</label>
                            <select onchange="window.location.href='?status=<?php echo $status_filter; ?>&course=<?php echo $course_filter; ?>&year=' + this.value" class="form-control">
                                <option value="all">All Years</option>
                                <?php while($year = $years->fetch_assoc()): ?>
                                <option value="<?php echo $year['exam_year']; ?>" <?php echo $year_filter == $year['exam_year'] ? 'selected' : ''; ?>>
                                    <?php echo $year['exam_year']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exams List -->
            <?php if($exams->num_rows > 0): ?>
                <?php while($exam = $exams->fetch_assoc()): 
                    $is_scheduled = $exam['exam_date'] && strtotime($exam['exam_date']) >= strtotime('today');
                    $is_completed = $exam['exam_date'] && strtotime($exam['exam_date']) < strtotime('today');
                    $is_pending = $exam['approval_status'] == 'pending';
                ?>
                <div class="exam-card">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <div style="flex: 1;">
                            <h3 style="margin: 0 0 0.5rem 0; color: var(--primary-color);">
                                <?php echo htmlspecialchars($exam['exam_name']); ?>
                            </h3>
                            <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; color: #6c757d; font-size: 0.9rem;">
                                <span><strong>Course:</strong> <?php echo htmlspecialchars($exam['course_code']); ?></span>
                                <span><strong>Category:</strong> <?php echo htmlspecialchars($exam['category_name']); ?></span>
                                <span><strong>Instructor:</strong> <?php echo htmlspecialchars($exam['instructor_name'] ?? 'N/A'); ?></span>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <?php if($is_pending): ?>
                                <span class="status-badge" style="background: #fff3cd; color: #856404;">⏳ Pending Approval</span>
                            <?php elseif($is_scheduled): ?>
                                <span class="status-badge" style="background: #d1ecf1; color: #0c5460;">📅 Scheduled</span>
                            <?php else: ?>
                                <span class="status-badge" style="background: #d4edda; color: #155724;">✅ Completed</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px; margin-bottom: 1rem;">
                        <div>
                            <small style="color: #495057; font-weight: 600;">Date</small>
                            <div style="font-weight: 700; color: #212529;">
                                <?php echo $exam['exam_date'] ? date('M d, Y', strtotime($exam['exam_date'])) : 'Not scheduled'; ?>
                            </div>
                        </div>
                        <div>
                            <small style="color: #495057; font-weight: 600;">Time</small>
                            <div style="font-weight: 700; color: #212529;">
                                <?php echo $exam['start_time'] ? date('h:i A', strtotime($exam['start_time'])) : 'N/A'; ?>
                            </div>
                        </div>
                        <div>
                            <small style="color: #495057; font-weight: 600;">Duration</small>
                            <div style="font-weight: 700; color: #212529;"><?php echo $exam['duration_minutes']; ?> min</div>
                        </div>
                        <div>
                            <small style="color: #495057; font-weight: 600;">Questions</small>
                            <div style="font-weight: 700; color: #212529;"><?php echo $exam['question_count']; ?></div>
                        </div>
                        <div>
                            <small style="color: #495057; font-weight: 600;">Total Marks</small>
                            <div style="font-weight: 700; color: #212529;"><?php echo $exam['total_marks']; ?></div>
                        </div>
                        <div>
                            <small style="color: #495057; font-weight: 600;">Enrolled</small>
                            <div style="font-weight: 700; color: #212529;"><?php echo $exam['enrolled_count']; ?></div>
                        </div>
                        <div>
                            <small style="color: #495057; font-weight: 600;">Attempts</small>
                            <div style="font-weight: 700; color: #212529;"><?php echo $exam['attempts_count']; ?></div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <a href="ViewExamDetails.php?id=<?php echo $exam['exam_id']; ?>" class="btn btn-sm btn-primary">📊 View Details</a>
                        <?php if($is_pending): ?>
                            <a href="PendingApprovals.php" class="btn btn-sm btn-warning">⏳ Review</a>
                        <?php endif; ?>
                        <?php if($exam['attempts_count'] > 0): ?>
                            <a href="ExamResults.php?id=<?php echo $exam['exam_id']; ?>" class="btn btn-sm btn-info">📈 View Results</a>
                        <?php endif; ?>
                        <?php if($exam['approval_status'] == 'approved' && $exam['attempts_count'] == 0): ?>
                            <a href="?toggle_publish=<?php echo $exam['exam_id']; ?>&status=<?php echo $status_filter; ?>&course=<?php echo $course_filter; ?>" 
                               class="btn btn-sm btn-warning" 
                               onclick="return confirm('Are you sure you want to <?php echo $exam['is_active'] ? 'unpublish' : 'publish'; ?> this exam?')">
                                <?php echo $exam['is_active'] ? '👁️ Unpublish' : '📢 Publish'; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <p><strong>No exams found.</strong></p>
                    <p>Try adjusting your filters or <a href="ScheduleExam.php">schedule a new exam</a>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function exportExams() {
            // Get current filters
            const status = '<?php echo $status_filter; ?>';
            const course = '<?php echo $course_filter; ?>';
            const year = '<?php echo $year_filter; ?>';
            
            // Redirect to export script
            window.location.href = 'ExportExams.php?status=' + status + '&course=' + course + '&year=' + year;
        }
    </script>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>

