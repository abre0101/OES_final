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
$pageTitle = "Reports Center";
$deptId = $_SESSION['DeptId'] ?? null;

// Get selected report type
$reportType = $_GET['report'] ?? 'overview';

// Get date range filters
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Get department overview statistics
$overviewQuery = "SELECT 
    COUNT(DISTINCT s.student_id) as total_students,
    COUNT(DISTINCT CASE WHEN s.is_active = 1 THEN s.student_id END) as active_students,
    COUNT(DISTINCT i.instructor_id) as total_instructors,
    COUNT(DISTINCT c.course_id) as total_courses,
    COUNT(DISTINCT e.exam_id) as total_exams,
    COUNT(DISTINCT er.result_id) as total_attempts
    FROM departments d
    LEFT JOIN students s ON d.department_id = s.department_id
    LEFT JOIN instructors i ON d.department_id = i.department_id AND i.is_active = 1
    LEFT JOIN courses c ON d.department_id = c.department_id AND c.is_active = 1
    LEFT JOIN exams e ON c.course_id = e.course_id
    LEFT JOIN exam_results er ON e.exam_id = er.exam_id
    WHERE d.department_id = ?";
$stmt = $con->prepare($overviewQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$overview = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Center - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        
        .report-categories {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 1200px) {
            .report-categories {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .report-categories {
                grid-template-columns: 1fr;
            }
        }
        
        .report-category-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .report-category-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            border-color: #667eea;
        }
        
        .report-category-card {
            border-left: 4px solid #667eea;
        }
        
        .category-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .category-icon {
            font-size: 2.5rem;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
        }
        
        .category-title {
            flex: 1;
        }
        
        .category-title h3 {
            margin: 0 0 0.25rem 0;
            color: #003366;
            font-size: 1.1rem;
            font-weight: 700;
        }
        

        
        .category-description {
            color: #6c757d;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }
        
        .category-features {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .category-features li {
            padding: 0.4rem 0;
            color: #495057;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .category-features li:before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
        }
        
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #003366;
            margin: 0.5rem 0;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            align-items: end;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #495057;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #003366;
            margin: 0;
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <!-- Page Header -->
            <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 2.5rem; margin: 0 0 0.5rem 0; color: #003366; font-weight: 700;">
                    📊 Reports Center
                </h1>
                <p style="margin: 0; color: #6c757d; font-size: 1.1rem;">
                    Comprehensive reporting and analytics for <?php echo htmlspecialchars($_SESSION['Dept']); ?> Department
                </p>
            </div>

            <!-- Department Overview Statistics -->
            <div class="stats-overview">
                <div class="stat-card">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-value"><?php echo $overview['active_students']; ?></div>
                    <div class="stat-label">Active Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👨‍🏫</div>
                    <div class="stat-value"><?php echo $overview['total_instructors']; ?></div>
                    <div class="stat-label">Instructors</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-value"><?php echo $overview['total_courses']; ?></div>
                    <div class="stat-label">Active Courses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-value"><?php echo $overview['total_exams']; ?></div>
                    <div class="stat-label">Total Exams</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✍️</div>
                    <div class="stat-value"><?php echo $overview['total_attempts']; ?></div>
                    <div class="stat-label">Exam Attempts</div>
                </div>
            </div>

            <!-- Date Range Filter -->
            <div class="filter-section">
                <form method="GET" action="">
                    <div class="filter-grid">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?php echo $startDate; ?>">
                        </div>
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?php echo $endDate; ?>">
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">Apply Filter</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Available Reports -->
            <div class="report-categories">
                <!-- Report 1: Student Management -->
                <div class="report-category-card" onclick="window.location.href='ReportStudentManagement.php'">
                    <div class="category-header">
                        <div class="category-icon">👨‍🎓</div>
                        <div class="category-title">
                            <h3>Student Management Report</h3>
                        </div>
                    </div>
                    <div class="category-description">
                        Basic student oversight - the primary responsibility of department heads. Track student status, enrollment, and key metrics.
                    </div>
                    <ul class="category-features">
                        <li>Complete student list with status</li>
                        <li>Enrollment details by course</li>
                        <li>Student performance metrics</li>
                        <li>Active vs inactive students</li>
                    </ul>
                </div>

                <!-- Report 2: Course Performance -->
                <div class="report-category-card" onclick="window.location.href='ReportCoursePerformance.php'">
                    <div class="category-header">
                        <div class="category-icon">📈</div>
                        <div class="category-title">
                            <h3>Course Performance & Results</h3>
                        </div>
                    </div>
                    <div class="category-description">
                        Directly from "See Result" use case - shows exam outcomes and course effectiveness.
                    </div>
                    <ul class="category-features">
                        <li>Pass/fail rates per course</li>
                        <li>Instructor performance analysis</li>
                        <li>Student achievement trends</li>
                        <li>Grade distribution charts</li>
                    </ul>
                </div>

                <!-- Report 3: Exam Participation -->
                <div class="report-category-card" onclick="window.location.href='ReportExamParticipation.php'">
                    <div class="category-header">
                        <div class="category-icon">✅</div>
                        <div class="category-title">
                            <h3>Exam Participation & Attendance</h3>
                        </div>
                    </div>
                    <div class="category-description">
                        Replaces manual attendance forms - automated tracking eliminates paperwork.
                    </div>
                    <ul class="category-features">
                        <li>Automated attendance tracking</li>
                        <li>Participation rates by exam</li>
                        <li>No-show analysis</li>
                        <li>Eliminates manual paperwork</li>
                    </ul>
                </div>

                <!-- Report 4: Instructor Compliance -->
                <div class="report-category-card" onclick="window.location.href='ReportInstructorCompliance.php'">
                    <div class="category-header">
                        <div class="category-icon">👨‍🏫</div>
                        <div class="category-title">
                            <h3>Instructor Compliance Report</h3>
                        </div>
                    </div>
                    <div class="category-description">
                        Ensures exam procedures are followed according to Exam Committee workflow.
                    </div>
                    <ul class="category-features">
                        <li>Question submission timeliness</li>
                        <li>Exam Committee approval status</li>
                        <li>Grading compliance tracking</li>
                        <li>Deadline adherence metrics</li>
                    </ul>
                </div>

                <!-- Report 5: Examination Schedule -->
                <div class="report-category-card" onclick="window.location.href='ReportExaminationSchedule.php'">
                    <div class="category-header">
                        <div class="category-icon">📅</div>
                        <div class="category-title">
                            <h3>Examination Schedule Report</h3>
                        </div>
                    </div>
                    <div class="category-description">
                        Centralized exam planning - prevents scheduling conflicts and overlaps.
                    </div>
                    <ul class="category-features">
                        <li>Upcoming exams calendar</li>
                        <li>Approval status tracking</li>
                        <li>Schedule conflict detection</li>
                        <li>Resource allocation view</li>
                    </ul>
                </div>

                <!-- Report 6: Question Bank Quality -->
                <div class="report-category-card" onclick="window.location.href='ReportQuestionBankQuality.php'">
                    <div class="category-header">
                        <div class="category-icon">❓</div>
                        <div class="category-title">
                            <h3>Question Bank & Exam Quality</h3>
                        </div>
                    </div>
                    <div class="category-description">
                        Quality control for exams - prevents errors and ensures exam integrity.
                    </div>
                    <ul class="category-features">
                        <li>Question usage statistics</li>
                        <li>Review status tracking</li>
                        <li>Quality indicators</li>
                        <li>Difficulty distribution</li>
                    </ul>
                </div>
            </div>

            <!-- Quick Actions -->
            <div style="margin-top: 3rem; padding: 2rem; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
                <h3 style="margin: 0 0 1.5rem 0; color: #003366;">Quick Export Options</h3>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <button onclick="exportAllReports()" class="btn btn-success">
                        📥 Export All Reports
                    </button>
                    <button onclick="window.print()" class="btn btn-primary">
                        🖨️ Print Overview
                    </button>
                    <a href="DepartmentExams.php" class="btn" style="background: #6c757d; color: white;">
                        ← Back to Exams
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function exportAllReports() {
            if(confirm('This will generate and download all available reports. Continue?')) {
                window.location.href = 'ExportAllReports.php?start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>';
            }
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
