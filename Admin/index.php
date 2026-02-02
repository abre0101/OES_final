<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");

// Fetch statistics
$stats = [
    'students' => $con->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'] ?? 0,
    'active_students' => $con->query("SELECT COUNT(*) as count FROM students WHERE is_active=1")->fetch_assoc()['count'] ?? 0,
    'instructors' => $con->query("SELECT COUNT(*) as count FROM instructors")->fetch_assoc()['count'] ?? 0,
    'active_instructors' => $con->query("SELECT COUNT(*) as count FROM instructors WHERE is_active=1")->fetch_assoc()['count'] ?? 0,
    'courses' => $con->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'] ?? 0,
    'departments' => $con->query("SELECT COUNT(*) as count FROM departments")->fetch_assoc()['count'] ?? 0,
    'pending_exams' => $con->query("SELECT COUNT(*) as count FROM exam_schedules WHERE approval_status='pending' AND submitted_for_approval=1")->fetch_assoc()['count'] ?? 0,
    'total_exams' => $con->query("SELECT COUNT(*) as count FROM exam_schedules")->fetch_assoc()['count'] ?? 0,
];

// Recent activities - Combined registrations
$recent_registrations_query = "
    (SELECT 'student' as type, full_name as name, email, created_at FROM students ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT 'instructor' as type, full_name as name, email, created_at FROM instructors ORDER BY created_at DESC LIMIT 2)
    ORDER BY created_at DESC
    LIMIT 5
";
$recent_registrations = $con->query($recent_registrations_query);

// Recent exam creations
$recent_exams = $con->query("
    SELECT es.exam_name, c.course_code, es.created_at, 
           COALESCE(i.full_name, 'System') as instructor_name
    FROM exam_schedules es 
    JOIN courses c ON es.course_id = c.course_id 
    LEFT JOIN instructors i ON es.created_by = i.instructor_id
    ORDER BY es.created_at DESC 
    LIMIT 5
");

// Recent approvals/rejections
$recent_approvals = $con->query("
    SELECT es.exam_name, c.course_code, es.approval_status, 
           COALESCE(es.reviewed_at, es.updated_at) as updated_at
    FROM exam_schedules es 
    JOIN courses c ON es.course_id = c.course_id 
    WHERE es.approval_status IN ('approved', 'rejected')
    ORDER BY COALESCE(es.reviewed_at, es.updated_at) DESC 
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(168, 85, 247, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        
        .admin-main-content {
            position: relative;
            z-index: 1;
        }
        
        .dashboard-container {
            padding: 2.5rem;
            max-width: 1600px;
            position: relative;
        }
        
        .page-header {
            margin-bottom: 2.5rem;
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(99, 102, 241, 0.1);
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.75rem;
        }
        
        .page-subtitle {
            color: #64748b;
            font-size: 1.125rem;
            font-weight: 500;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(226, 232, 240, 0.8);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--card-color-1), var(--card-color-2));
        }
        
        .stat-card::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, var(--card-color-1) 0%, transparent 70%);
            opacity: 0.03;
            pointer-events: none;
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.12);
            border-color: var(--card-color-1);
        }
        
        .stat-card.blue { --card-color-1: #3b82f6; --card-color-2: #2563eb; }
        .stat-card.green { --card-color-1: #10b981; --card-color-2: #059669; }
        .stat-card.orange { --card-color-1: #f59e0b; --card-color-2: #d97706; }
        .stat-card.red { --card-color-1: #ef4444; --card-color-2: #dc2626; }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.25rem;
        }
        
        .stat-icon {
            width: 64px;
            height: 64px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            background: linear-gradient(135deg, var(--card-color-1), var(--card-color-2));
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .stat-value {
            font-size: 3rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
            letter-spacing: -0.02em;
        }
        
        .stat-label {
            font-size: 1rem;
            color: #64748b;
            margin-top: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .stat-footer {
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 2px solid #e2e8f0;
            font-size: 0.95rem;
            color: #64748b;
            font-weight: 500;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2.5rem;
        }
        
        .action-btn {
            background: white;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 14px;
            padding: 1.75rem 1.25rem;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            position: relative;
            overflow: hidden;
        }
        
        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .action-btn:hover::before {
            opacity: 1;
        }
        
        .action-btn:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);
            border-color: #6366f1;
        }
        
        .action-btn:hover .action-icon {
            transform: scale(1.1);
        }
        
        .action-btn:hover .action-label {
            color: white;
        }
        
        .action-icon {
            font-size: 2.25rem;
            transition: transform 0.3s;
            position: relative;
            z-index: 1;
        }
        
        .action-label {
            font-size: 1rem;
            font-weight: 700;
            color: #334155;
            transition: color 0.3s;
            position: relative;
            z-index: 1;
        }
        
        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(480px, 1fr));
            gap: 2rem;
        }
        
        .content-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .card-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
        }
        
        .card-link {
            font-size: 1rem;
            color: #6366f1;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.2s;
        }
        
        .card-link:hover {
            color: #4f46e5;
            transform: translateX(4px);
        }
        
        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table thead {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
        }
        
        .data-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        
        .data-table td {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 1rem;
        }
        
        .data-table tbody tr {
            transition: all 0.2s;
        }
        
        .data-table tbody tr:hover {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.05) 0%, transparent 100%);
            transform: scale(1.005);
        }
        
        .data-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .empty-state {
            text-align: center;
            padding: 3.5rem 1rem;
            color: #94a3b8;
            font-size: 1.05rem;
            font-weight: 500;
        }
        
        .badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 700;
        }
        
        .badge.warning {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(251, 191, 36, 0.3);
        }
        
        .badge.blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }
        
        .badge.green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }
        
        .badge.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }
        
        .badge.danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }
        
        @media (max-width: 768px) {
            .dashboard-container { padding: 1.25rem; }
            .page-title { font-size: 2rem; }
            .page-subtitle { font-size: 1rem; }
            .stats-grid { grid-template-columns: 1fr; }
            .content-grid { grid-template-columns: 1fr; }
            .quick-actions { grid-template-columns: repeat(2, 1fr); }
            .stat-value { font-size: 2.5rem; }
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php 
        $pageTitle = 'Dashboard';
        include 'header-component.php'; 
        ?>

        <div class="admin-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title"><span style="-webkit-text-fill-color: initial;">📊</span> Dashboard</h1>
                <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! Here's what's happening today.</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo number_format($stats['students']); ?></div>
                            <div class="stat-label">Total Students</div>
                        </div>
                        <div class="stat-icon">👨‍🎓</div>
                    </div>
                    <div class="stat-footer">
                        <?php echo $stats['active_students']; ?> active
                    </div>
                </div>

                <div class="stat-card green">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo number_format($stats['instructors']); ?></div>
                            <div class="stat-label">Total Instructors</div>
                        </div>
                        <div class="stat-icon">👨‍🏫</div>
                    </div>
                    <div class="stat-footer">
                        <?php echo $stats['active_instructors']; ?> active
                    </div>
                </div>

                <div class="stat-card orange">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo number_format($stats['courses']); ?></div>
                            <div class="stat-label">Total Courses</div>
                        </div>
                        <div class="stat-icon">📚</div>
                    </div>
                    <div class="stat-footer">
                        <?php echo $stats['departments']; ?> departments
                    </div>
                </div>

                <div class="stat-card red">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo number_format($stats['pending_exams']); ?></div>
                            <div class="stat-label">Pending Approvals</div>
                        </div>
                        <div class="stat-icon">⏳</div>
                    </div>
                    <div class="stat-footer">
                        <?php echo $stats['total_exams']; ?> total exams
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="InsertStudent.php" class="action-btn">
                    <span class="action-icon">➕</span>
                    <span class="action-label">Add Student</span>
                </a>
                <a href="InsertInstructor.php" class="action-btn">
                    <span class="action-icon">👨‍🏫</span>
                    <span class="action-label">Add Instructor</span>
                </a>
                <a href="InsertCourse.php" class="action-btn">
                    <span class="action-icon">📚</span>
                    <span class="action-label">Add Course</span>
                </a>
                <a href="InsertDepartment.php" class="action-btn">
                    <span class="action-icon">🏢</span>
                    <span class="action-label">Add Department</span>
                </a>
                <a href="BulkImport.php" class="action-btn">
                    <span class="action-icon">📥</span>
                    <span class="action-label">Bulk Import</span>
                </a>
                <a href="Reports.php" class="action-btn">
                    <span class="action-icon">📊</span>
                    <span class="action-label">Reports</span>
                </a>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Recent Registrations -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">📝 Recent Registrations</h2>
                        <a href="Student.php" class="card-link">View All →</a>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($recent_registrations && $recent_registrations->num_rows > 0): ?>
                                <?php while($reg = $recent_registrations->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <span class="badge <?php echo $reg['type'] == 'student' ? 'blue' : 'green'; ?>">
                                            <?php echo ucfirst($reg['type']); ?>
                                        </span>
                                    </td>
                                    <td style="font-weight: 600;"><?php echo htmlspecialchars($reg['name']); ?></td>
                                    <td style="color: #64748b;"><?php echo htmlspecialchars($reg['email']); ?></td>
                                    <td style="color: #64748b; font-size: 0.9rem;">
                                        <?php echo date('M d, Y', strtotime($reg['created_at'])); ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="empty-state">No recent registrations</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Recent Exam Creations -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">📋 Recent Exam Creations</h2>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Exam Name</th>
                                <th>Course</th>
                                <th>Instructor</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($recent_exams && $recent_exams->num_rows > 0): ?>
                                <?php while($exam = $recent_exams->fetch_assoc()): ?>
                                <tr>
                                    <td style="font-weight: 600;"><?php echo htmlspecialchars($exam['exam_name']); ?></td>
                                    <td><span class="badge warning"><?php echo htmlspecialchars($exam['course_code']); ?></span></td>
                                    <td style="color: #64748b;"><?php echo htmlspecialchars($exam['instructor_name'] ?? 'N/A'); ?></td>
                                    <td style="color: #64748b; font-size: 0.9rem;">
                                        <?php echo date('M d, Y', strtotime($exam['created_at'])); ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="empty-state">No recent exams</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Recent Approvals/Rejections -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">✅ Recent Approvals/Rejections</h2>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Exam Name</th>
                                <th>Course</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($recent_approvals && $recent_approvals->num_rows > 0): ?>
                                <?php while($approval = $recent_approvals->fetch_assoc()): ?>
                                <tr>
                                    <td style="font-weight: 600;"><?php echo htmlspecialchars($approval['exam_name']); ?></td>
                                    <td><span class="badge warning"><?php echo htmlspecialchars($approval['course_code']); ?></span></td>
                                    <td>
                                        <span class="badge <?php echo $approval['approval_status'] == 'approved' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($approval['approval_status']); ?>
                                        </span>
                                    </td>
                                    <td style="color: #64748b; font-size: 0.9rem;">
                                        <?php echo date('M d, Y', strtotime($approval['updated_at'])); ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="empty-state">No recent approvals/rejections</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>
