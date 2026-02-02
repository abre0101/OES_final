<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../index.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;

// Get selected report type
$reportType = $_GET['report'] ?? 'overview';
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// System Overview Stats
$totalStudents = $con->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$totalInstructors = $con->query("SELECT COUNT(*) as count FROM instructors")->fetch_assoc()['count'];
$totalDepartments = $con->query("SELECT COUNT(*) as count FROM departments")->fetch_assoc()['count'];
$totalExams = $con->query("SELECT COUNT(*) as count FROM exam_categories")->fetch_assoc()['count'];
$totalResults = $con->query("SELECT COUNT(*) as count FROM exam_results")->fetch_assoc()['count'];

// Academic Analytics - Department Performance
$deptPerformance = $con->query("SELECT 
    d.department_name,
    COUNT(DISTINCT s.student_id) as total_students,
    COUNT(DISTINCT i.instructor_id) as total_instructors,
    COUNT(DISTINCT c.course_id) as total_courses,
    COUNT(DISTINCT r.result_id) as total_exams,
    AVG(r.percentage_score) as avg_score,
    SUM(CASE WHEN r.percentage_score >= 50 THEN 1 ELSE 0 END) as passed,
    SUM(CASE WHEN r.percentage_score < 50 THEN 1 ELSE 0 END) as failed
    FROM departments d
    LEFT JOIN students s ON d.department_id = s.department_id
    LEFT JOIN instructors i ON d.department_id = i.department_id
    LEFT JOIN courses c ON d.department_id = c.department_id
    LEFT JOIN exam_results r ON s.student_id = r.student_id
    GROUP BY d.department_id, d.department_name
    ORDER BY avg_score DESC");

// Instructor Effectiveness
$instructorMetrics = $con->query("SELECT 
    i.full_name,
    d.department_name,
    COUNT(DISTINCT ic.course_id) as courses_taught,
    COUNT(DISTINCT r.result_id) as total_exams,
    AVG(r.percentage_score) as avg_student_score,
    SUM(CASE WHEN r.percentage_score >= 50 THEN 1 ELSE 0 END) as students_passed,
    COUNT(DISTINCT r.student_id) as unique_students
    FROM instructors i
    LEFT JOIN departments d ON i.department_id = d.department_id
    LEFT JOIN instructor_courses ic ON i.instructor_id = ic.instructor_id
    LEFT JOIN exam_schedules es ON ic.course_id = es.course_id
    LEFT JOIN exam_results r ON es.schedule_id = r.schedule_id
    GROUP BY i.instructor_id, i.full_name, d.department_name
    ORDER BY avg_student_score DESC
    LIMIT 20");

// Program Success Rates
$programSuccess = $con->query("SELECT 
    d.department_name as program,
    COUNT(DISTINCT s.student_id) as enrolled_students,
    COUNT(DISTINCT CASE WHEN s.is_active = 1 THEN s.student_id END) as active_students,
    AVG(r.percentage_score) as avg_performance,
    (SUM(CASE WHEN r.percentage_score >= 50 THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(r.result_id), 0)) as success_rate
    FROM departments d
    LEFT JOIN students s ON d.department_id = s.department_id
    LEFT JOIN exam_results r ON s.student_id = r.student_id
    GROUP BY d.department_id, d.department_name
    ORDER BY success_rate DESC");

// System Usage Statistics
$usageStats = [
    'active_users_today' => $con->query("SELECT COUNT(DISTINCT username) as count FROM students WHERE is_active=1")->fetch_assoc()['count'],
    'exams_completed_today' => $con->query("SELECT COUNT(*) as count FROM exam_results WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'],
    'avg_exam_duration' => '45 mins', // Placeholder
    'peak_usage_time' => '10:00 AM - 12:00 PM' // Placeholder
];

// Exam Completion Rates
$examCompletion = $con->query("SELECT 
    es.exam_name,
    c.course_name,
    COUNT(DISTINCT r.student_id) as students_attempted,
    COUNT(r.result_id) as total_attempts,
    AVG(r.percentage_score) as avg_score,
    (SUM(CASE WHEN r.percentage_score >= 50 THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(r.result_id), 0)) as completion_rate
    FROM exam_schedules es
    LEFT JOIN courses c ON es.course_id = c.course_id
    LEFT JOIN exam_results r ON es.schedule_id = r.schedule_id
    GROUP BY es.schedule_id, es.exam_name, c.course_name
    HAVING total_attempts > 0
    ORDER BY total_attempts DESC
    LIMIT 15");

$con->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Reports - OES</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        .page-header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 2rem;
            background: linear-gradient(135deg, rgba(0, 51, 102, 0.05) 0%, rgba(0, 85, 170, 0.05) 100%);
            padding: 2rem;
            border-radius: var(--radius-lg);
            border: 2px solid rgba(0, 51, 102, 0.1);
        }
        
        .page-title-section h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .page-title-section h1 span {
            -webkit-text-fill-color: initial;
            background: none;
        }
        
        .page-subtitle {
            margin: 0;
            color: var(--text-secondary);
            font-size: 1.05rem;
            font-weight: 500;
        }
        
        .report-categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .report-category-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 2px solid #e8eef3;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .report-category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
        }
        
        .report-category-card.academic::before { background: linear-gradient(90deg, #007bff 0%, #0056b3 100%); }
        .report-category-card.operational::before { background: linear-gradient(90deg, #28a745 0%, #218838 100%); }
        .report-category-card.compliance::before { background: linear-gradient(90deg, #dc3545 0%, #c82333 100%); }
        
        .report-category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            border-color: var(--primary-color);
        }
        
        .report-category-card.active {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, rgba(0, 51, 102, 0.05) 0%, rgba(0, 51, 102, 0.02) 100%);
        }
        
        .category-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .category-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .category-desc {
            font-size: 0.9rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }
        
        .report-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-generate {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
        }
        
        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3);
        }
        
        .btn-export {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            color: white;
        }
        
        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }
        
        .btn-print {
            background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
            color: white;
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.3);
        }
        
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-box {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.95rem;
            color: var(--text-secondary);
            font-weight: 600;
        }
        
        .report-section {
            margin-bottom: 2rem;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid var(--secondary-color);
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .data-table thead {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        }
        
        .data-table th {
            padding: 1rem;
            text-align: left;
            color: white;
            font-weight: 700;
            font-size: 0.95rem;
        }
        
        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #e8eef3;
            font-size: 0.95rem;
        }
        
        .data-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .performance-badge {
            padding: 0.35rem 0.75rem;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        
        .badge-excellent { background: #d4edda; color: #155724; }
        .badge-good { background: #d1ecf1; color: #0c5460; }
        .badge-average { background: #fff3cd; color: #856404; }
        .badge-poor { background: #f8d7da; color: #721c24; }
        
        .chart-container {
            margin: 2rem 0;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: var(--radius-md);
        }
        
        @media print {
            .report-actions, .admin-sidebar, .admin-header, .report-categories { display: none; }
            .admin-main-content { margin-left: 0; }
        }
        
        @media (max-width: 768px) {
            .report-actions {
                flex-direction: column;
            }
            
            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php 
        $pageTitle = 'Administrator Reports';
        include 'header-component.php'; 
        ?>

        <div class="admin-content">
            <div class="page-header-actions">
                <div class="page-title-section">
                    <h1><span>📊</span> Administrator Reports & Analytics</h1>
                    <p class="page-subtitle">Comprehensive system-wide reports for academic, operational, and compliance analysis</p>
                </div>
            </div>

            <!-- Report Type Selection -->
            <div class="report-categories">
                <div class="report-category-card academic <?php echo in_array($reportType, ['overview', 'department', 'instructor', 'program']) ? 'active' : ''; ?>" 
                     onclick="window.location.href='?report=academic'">
                    <div class="category-icon">📈</div>
                    <div class="category-title">Academic Analytics</div>
                    <div class="category-desc">
                        Department performance, instructor effectiveness, and program success rates
                    </div>
                </div>
                
                <div class="report-category-card operational <?php echo in_array($reportType, ['usage', 'completion', 'technical']) ? 'active' : ''; ?>" 
                     onclick="window.location.href='?report=operational'">
                    <div class="category-icon">⚙️</div>
                    <div class="category-title">Operational Reports</div>
                    <div class="category-desc">
                        System usage statistics, exam completion rates, and technical metrics
                    </div>
                </div>
                
                <div class="report-category-card compliance <?php echo in_array($reportType, ['audit', 'security', 'integrity']) ? 'active' : ''; ?>" 
                     onclick="window.location.href='?report=compliance'">
                    <div class="category-icon">🔒</div>
                    <div class="category-title">Compliance Reports</div>
                    <div class="category-desc">
                        Audit trails, security logs, and data integrity checks
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="report-actions">
                <button class="btn-action btn-generate" onclick="generateReport()">
                    <span>📊</span> Generate Report
                </button>
                <button class="btn-action btn-export" onclick="exportToExcel()">
                    <span>📥</span> Export to Excel
                </button>
                <button class="btn-action btn-print" onclick="window.print()">
                    <span>🖨️</span> Print Report
                </button>
            </div>

            <!-- System Overview Stats -->
            <div class="stats-overview">
                <div class="stat-box">
                    <div class="stat-value"><?php echo number_format($totalStudents); ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?php echo number_format($totalInstructors); ?></div>
                    <div class="stat-label">Total Instructors</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?php echo number_format($totalDepartments); ?></div>
                    <div class="stat-label">Departments</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?php echo number_format($totalExams); ?></div>
                    <div class="stat-label">Total Exams</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?php echo number_format($totalResults); ?></div>
                    <div class="stat-label">Exam Results</div>
                </div>
            </div>

            <!-- Academic Analytics Section -->
            <?php if($reportType == 'overview' || $reportType == 'academic'): ?>
            
            <!-- Department Performance Comparison -->
            <div class="card report-section">
                <div class="card-header">
                    <h3 class="card-title"><span>🏛️</span> Department Performance Comparison</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Students</th>
                                <th>Instructors</th>
                                <th>Courses</th>
                                <th>Exams Taken</th>
                                <th>Avg Score</th>
                                <th>Pass Rate</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $deptPerformance->data_seek(0);
                            while($dept = $deptPerformance->fetch_assoc()): 
                                $passRate = $dept['total_exams'] > 0 ? round(($dept['passed'] / $dept['total_exams']) * 100, 1) : 0;
                                $performance = 'Excellent';
                                $badgeClass = 'badge-excellent';
                                if($dept['avg_score'] < 50) {
                                    $performance = 'Poor';
                                    $badgeClass = 'badge-poor';
                                } elseif($dept['avg_score'] < 70) {
                                    $performance = 'Average';
                                    $badgeClass = 'badge-average';
                                } elseif($dept['avg_score'] < 85) {
                                    $performance = 'Good';
                                    $badgeClass = 'badge-good';
                                }
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($dept['department_name']); ?></strong></td>
                                <td><?php echo number_format($dept['total_students']); ?></td>
                                <td><?php echo number_format($dept['total_instructors']); ?></td>
                                <td><?php echo number_format($dept['total_courses']); ?></td>
                                <td><?php echo number_format($dept['total_exams']); ?></td>
                                <td><strong><?php echo number_format($dept['avg_score'], 1); ?>%</strong></td>
                                <td><strong><?php echo $passRate; ?>%</strong></td>
                                <td><span class="performance-badge <?php echo $badgeClass; ?>"><?php echo $performance; ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="chart-container">
                    <canvas id="deptPerformanceChart" style="max-height: 400px;"></canvas>
                </div>
                </div>
            </div>

            <!-- Instructor Effectiveness Metrics -->
            <div class="card report-section">
                <div class="card-header">
                    <h3 class="card-title"><span>👨‍🏫</span> Instructor Effectiveness Metrics</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Instructor Name</th>
                                <th>Department</th>
                                <th>Courses Taught</th>
                                <th>Total Exams</th>
                                <th>Unique Students</th>
                                <th>Avg Student Score</th>
                                <th>Pass Rate</th>
                                <th>Effectiveness</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($inst = $instructorMetrics->fetch_assoc()): 
                                $passRate = $inst['total_exams'] > 0 ? round(($inst['students_passed'] / $inst['total_exams']) * 100, 1) : 0;
                                $effectiveness = 'Excellent';
                                $badgeClass = 'badge-excellent';
                                if($inst['avg_student_score'] < 50) {
                                    $effectiveness = 'Needs Improvement';
                                    $badgeClass = 'badge-poor';
                                } elseif($inst['avg_student_score'] < 70) {
                                    $effectiveness = 'Average';
                                    $badgeClass = 'badge-average';
                                } elseif($inst['avg_student_score'] < 85) {
                                    $effectiveness = 'Good';
                                    $badgeClass = 'badge-good';
                                }
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($inst['full_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($inst['department_name']); ?></td>
                                <td><?php echo $inst['courses_taught']; ?></td>
                                <td><?php echo number_format($inst['total_exams']); ?></td>
                                <td><?php echo number_format($inst['unique_students']); ?></td>
                                <td><strong><?php echo number_format($inst['avg_student_score'], 1); ?>%</strong></td>
                                <td><strong><?php echo $passRate; ?>%</strong></td>
                                <td><span class="performance-badge <?php echo $badgeClass; ?>"><?php echo $effectiveness; ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                </div>
            </div>

            <!-- Program Success Rates -->
            <div class="card report-section">
                <div class="card-header">
                    <h3 class="card-title"><span>🎓</span> Program Success Rates</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Program/Department</th>
                                <th>Enrolled Students</th>
                                <th>Active Students</th>
                                <th>Avg Performance</th>
                                <th>Success Rate</th>
                                <th>is_active</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($prog = $programSuccess->fetch_assoc()): 
                                $is_active = 'Excellent';
                                $badgeClass = 'badge-excellent';
                                if($prog['success_rate'] < 50) {
                                    $is_active = 'Critical';
                                    $badgeClass = 'badge-poor';
                                } elseif($prog['success_rate'] < 70) {
                                    $is_active = 'Needs Attention';
                                    $badgeClass = 'badge-average';
                                } elseif($prog['success_rate'] < 85) {
                                    $is_active = 'Good';
                                    $badgeClass = 'badge-good';
                                }
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($prog['program']); ?></strong></td>
                                <td><?php echo number_format($prog['enrolled_students']); ?></td>
                                <td><?php echo number_format($prog['active_students']); ?></td>
                                <td><strong><?php echo number_format($prog['avg_performance'], 1); ?>%</strong></td>
                                <td><strong><?php echo number_format($prog['success_rate'], 1); ?>%</strong></td>
                                <td><span class="performance-badge <?php echo $badgeClass; ?>"><?php echo $is_active; ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="chart-container">
                    <canvas id="programSuccessChart" style="max-height: 400px;"></canvas>
                </div>
                </div>
            </div>

            <?php endif; ?>

            <!-- Operational Reports Section -->
            <?php if($reportType == 'operational'): ?>
            
            <!-- System Usage Statistics -->
            <div class="card report-section">
                <div class="card-header">
                    <h3 class="card-title"><span>📊</span> System Usage Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="stats-overview">
                    <div class="stat-box">
                        <div class="stat-value"><?php echo $usageStats['active_users_today']; ?></div>
                        <div class="stat-label">Active Users Today</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?php echo $usageStats['exams_completed_today']; ?></div>
                        <div class="stat-label">Exams Completed Today</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?php echo $usageStats['avg_exam_duration']; ?></div>
                        <div class="stat-label">Avg Exam Duration</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value" style="font-size: 1.5rem;"><?php echo $usageStats['peak_usage_time']; ?></div>
                        <div class="stat-label">Peak Usage Time</div>
                    </div>
                </div>
                </div>
            </div>

            <!-- Exam Completion Rates -->
            <div class="card report-section">
                <div class="card-header">
                    <h3 class="card-title"><span>✅</span> Exam Completion Rates</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Exam Name</th>
                                <th>Course</th>
                                <th>Students Attempted</th>
                                <th>Total Attempts</th>
                                <th>Avg Score</th>
                                <th>Completion Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($exam = $examCompletion->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($exam['exam_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($exam['course_name']); ?></td>
                                <td><?php echo number_format($exam['students_attempted']); ?></td>
                                <td><?php echo number_format($exam['total_attempts']); ?></td>
                                <td><strong><?php echo number_format($exam['avg_score'], 1); ?>%</strong></td>
                                <td><strong><?php echo number_format($exam['completion_rate'], 1); ?>%</strong></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                </div>
            </div>

            <?php endif; ?>

            <!-- Compliance Reports Section -->
            <?php if($reportType == 'compliance'): ?>
            
            <div class="card report-section">
                <div class="card-header">
                    <h3 class="card-title"><span>🔒</span> Compliance & Security Overview</h3>
                </div>
                <div class="card-body" style="text-align: center; padding: 3rem 2rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🔐</div>
                    <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Security & Audit Logs</h3>
                    <p style="color: var(--text-secondary); font-size: 1.05rem; max-width: 600px; margin: 0 auto;">
                        Detailed audit trails, security logs, and data integrity checks are available.
                        Contact system administrator for access to sensitive compliance reports.
                    </p>
                    <div style="margin-top: 2rem;">
                        <a href="SecurityLogs.php" class="btn-action btn-generate">
                            <span>🔒</span> View Security Logs
                        </a>
                    </div>
                </div>
            </div>

            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js?v=<?php echo time(); ?>"></script>
    <script>
        // Department Performance Chart
        <?php if($reportType == 'overview' || $reportType == 'academic'): ?>
        const deptCtx = document.getElementById('deptPerformanceChart');
        if(deptCtx) {
            new Chart(deptCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: [
                        <?php 
                        $deptPerformance->data_seek(0);
                        $labels = [];
                        while($d = $deptPerformance->fetch_assoc()) {
                            $labels[] = "'" . addslashes($d['department_name']) . "'";
                        }
                        echo implode(',', $labels);
                        ?>
                    ],
                    datasets: [{
                        label: 'Average Score (%)',
                        data: [
                            <?php 
                            $deptPerformance->data_seek(0);
                            $scores = [];
                            while($d = $deptPerformance->fetch_assoc()) {
                                $scores[] = round($d['avg_score'], 1);
                            }
                            echo implode(',', $scores);
                            ?>
                        ],
                        backgroundColor: '#003366',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        // Program Success Chart
        const progCtx = document.getElementById('programSuccessChart');
        if(progCtx) {
            new Chart(progCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: [
                        <?php 
                        $programSuccess->data_seek(0);
                        $progLabels = [];
                        while($p = $programSuccess->fetch_assoc()) {
                            $progLabels[] = "'" . addslashes($p['program']) . "'";
                        }
                        echo implode(',', $progLabels);
                        ?>
                    ],
                    datasets: [{
                        label: 'Success Rate (%)',
                        data: [
                            <?php 
                            $programSuccess->data_seek(0);
                            $progRates = [];
                            while($p = $programSuccess->fetch_assoc()) {
                                $progRates[] = round($p['success_rate'], 1);
                            }
                            echo implode(',', $progRates);
                            ?>
                        ],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }
        <?php endif; ?>

        function generateReport() {
            // Show loading message
            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span>⏳</span> Generating...';
            btn.disabled = true;
            
            // Get current report type from URL
            const urlParams = new URLSearchParams(window.location.search);
            const reportType = urlParams.get('report') || 'overview';
            
            // Simulate report generation (in production, this would call a backend endpoint)
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert('✅ Report generated successfully!\n\nReport Type: ' + reportType.toUpperCase() + '\nGenerated: ' + new Date().toLocaleString());
            }, 1500);
        }

        function exportToExcel() {
            // Get current report type from URL
            const urlParams = new URLSearchParams(window.location.search);
            const reportType = urlParams.get('report') || 'overview';
            
            // Create a new workbook
            const wb = XLSX.utils.book_new();
            
            // Get all tables and convert to sheets
            const tables = document.querySelectorAll('.data-table');
            
            if(tables.length === 0) {
                alert('No data tables found to export!');
                return;
            }
            
            tables.forEach((table, index) => {
                // Get the section title
                const sectionTitle = table.closest('.report-section')?.querySelector('.card-title')?.textContent.trim() || 'Sheet' + (index + 1);
                
                // Convert table to worksheet
                const ws = XLSX.utils.table_to_sheet(table);
                
                // Add worksheet to workbook with a clean name
                const sheetName = sectionTitle.replace(/[^a-zA-Z0-9]/g, '_').substring(0, 31);
                XLSX.utils.book_append_sheet(wb, ws, sheetName);
            });
            
            // Generate filename with date
            const filename = 'OES_Report_' + reportType + '_' + new Date().toISOString().split('T')[0] + '.xlsx';
            
            // Save the file
            XLSX.writeFile(wb, filename);
            
            // Show success message
            setTimeout(() => {
                alert('✅ Excel file exported successfully!\n\nFilename: ' + filename);
            }, 500);
        }
    </script>
</body>
</html>
