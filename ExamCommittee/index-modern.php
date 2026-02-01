<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");

// Get statistics
$pending_count = $con->query("SELECT COUNT(*) as count FROM exam_schedules WHERE approval_status = 'pending' AND submitted_for_approval = TRUE")->fetch_assoc()['count'] ?? 0;
$approved_today = $con->query("SELECT COUNT(*) as count FROM exam_schedules WHERE approval_status = 'approved' AND DATE(approval_date) = CURDATE()")->fetch_assoc()['count'] ?? 0;
$approved_month = $con->query("SELECT COUNT(*) as count FROM exam_schedules WHERE approval_status = 'approved' AND MONTH(approval_date) = MONTH(CURDATE()) AND YEAR(approval_date) = YEAR(CURDATE())")->fetch_assoc()['count'] ?? 0;
$revision_count = $con->query("SELECT COUNT(*) as count FROM exam_schedules WHERE approval_status = 'revision'")->fetch_assoc()['count'] ?? 0;
$total_submitted = $con->query("SELECT COUNT(*) as count FROM exam_schedules WHERE submitted_for_approval = TRUE")->fetch_assoc()['count'] ?? 0;

// Get recent pending exams
$recent_pending = $con->query("SELECT es.*, c.course_name, c.course_code, ec.category_name
    FROM exam_schedules es
    LEFT JOIN courses c ON es.course_id = c.course_id
    LEFT JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    WHERE es.approval_status = 'pending' AND es.submitted_for_approval = TRUE
    ORDER BY es.submitted_at DESC
    LIMIT 5");

// Get recent approvals
$recent_approvals = $con->query("SELECT eah.*, es.exam_name, c.course_code, c.course_name
    FROM exam_approval_history eah
    INNER JOIN exam_schedules es ON eah.schedule_id = es.schedule_id
    LEFT JOIN courses c ON es.course_id = c.course_id
    WHERE eah.performed_by_type = 'committee'
    ORDER BY eah.created_at DESC
    LIMIT 5");

// Get upcoming exam dates
$upcoming_exams = $con->query("SELECT es.*, c.course_name, c.course_code, ec.category_name
    FROM exam_schedules es
    LEFT JOIN courses c ON es.course_id = c.course_id
    LEFT JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    WHERE es.approval_status = 'approved' AND es.exam_date >= CURDATE()
    ORDER BY es.exam_date ASC
    LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Committee Dashboard</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .compact-stat {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.75rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .compact-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        .compact-stat-icon {
            width: 60px;
            height: 60px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            flex-shrink: 0;
        }
        .compact-stat-content {
            flex: 1;
        }
        .compact-stat-value {
            font-size: 2.25rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.35rem;
        }
        .compact-stat-label {
            font-size: 0.95rem;
            color: var(--text-secondary);
            font-weight: 500;
        }
        .exam-item {
            padding: 1.25rem;
            border-bottom: 1px solid var(--border-color);
            transition: background 0.2s;
        }
        .exam-item:last-child {
            border-bottom: none;
        }
        .exam-item:hover {
            background: var(--bg-light);
        }
        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-revision { background: #fff3cd; color: #856404; }
        .status-rejected { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <!-- Compact Header -->
            <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: var(--primary-color); font-weight: 700;">Exam Committee Dashboard</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 1.05rem;">
                    <?php echo $_SESSION['Dept']; ?> Department • <?php echo date('l, F j, Y'); ?>
                </p>
            </div>

            <!-- Urgent Alert -->
            <?php if($pending_count > 0): ?>
            <div style="background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: white; padding: 1.5rem 2rem; border-radius: var(--radius-lg); margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4); border: 2px solid rgba(255, 255, 255, 0.2);">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                        <span style="font-size: 1.75rem; animation: pulse 2s infinite;">⚠️</span>
                        <strong style="font-size: 1.25rem; font-weight: 700; color: #ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <?php echo $pending_count; ?> Exam<?php echo $pending_count > 1 ? 's' : ''; ?> Awaiting Review
                        </strong>
                    </div>
                    <p style="margin: 0; font-size: 1rem; color: #ffffff; opacity: 0.95; font-weight: 500;">
                        Action required to keep exam schedule on track
                    </p>
                </div>
                <a href="PendingApprovals.php" class="btn" style="background: white; color: #ff6b35; font-weight: 700; white-space: nowrap; padding: 0.75rem 1.75rem; font-size: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border: none; transition: all 0.3s;">
                    Review Now →
                </a>
            </div>
            <style>
                @keyframes pulse {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                }
            </style>
            <?php endif; ?>

            <!-- Compact Stats Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <div class="compact-stat">
                    <div class="compact-stat-icon" style="background: rgba(255, 152, 0, 0.1); color: #ff9800;">⏳</div>
                    <div class="compact-stat-content">
                        <div class="compact-stat-value" style="color: #ff9800;"><?php echo $pending_count; ?></div>
                        <div class="compact-stat-label">Pending Review</div>
                    </div>
                </div>
                
                <div class="compact-stat">
                    <div class="compact-stat-icon" style="background: rgba(40, 167, 69, 0.1); color: var(--success-color);">✅</div>
                    <div class="compact-stat-content">
                        <div class="compact-stat-value" style="color: var(--success-color);"><?php echo $approved_today; ?></div>
                        <div class="compact-stat-label">Approved Today</div>
                    </div>
                </div>
                
                <div class="compact-stat">
                    <div class="compact-stat-icon" style="background: rgba(0, 123, 255, 0.1); color: var(--primary-color);">📊</div>
                    <div class="compact-stat-content">
                        <div class="compact-stat-value" style="color: var(--primary-color);"><?php echo $approved_month; ?></div>
                        <div class="compact-stat-label">Approved This Month</div>
                    </div>
                </div>
                
                <div class="compact-stat">
                    <div class="compact-stat-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">✏️</div>
                    <div class="compact-stat-content">
                        <div class="compact-stat-value" style="color: #ffc107;"><?php echo $revision_count; ?></div>
                        <div class="compact-stat-label">Needs Revision</div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-2" style="gap: 1.5rem;">
                <!-- Pending Exams -->
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="card-title" style="margin: 0;">⏳ Pending Approvals</h3>
                        <a href="PendingApprovals.php" style="font-size: 0.85rem; color: var(--primary-color); text-decoration: none; font-weight: 600;">View All →</a>
                    </div>
                    <div>
                        <?php if($recent_pending && $recent_pending->num_rows > 0): ?>
                            <?php while($exam = $recent_pending->fetch_assoc()): ?>
                            <div class="exam-item">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <div style="flex: 1;">
                                        <strong style="color: var(--primary-color); font-size: 1.05rem;">
                                            <?php echo htmlspecialchars($exam['exam_name']); ?>
                                        </strong>
                                        <div style="font-size: 0.9rem; color: var(--text-secondary); margin-top: 0.35rem;">
                                            <?php echo htmlspecialchars($exam['course_code']); ?> - <?php echo htmlspecialchars($exam['course_name']); ?>
                                        </div>
                                    </div>
                                    <span class="status-badge status-pending">Pending</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: var(--text-secondary);">
                                    <span>� <?php echo htmlspecialchars($exam['category_name'] ?? 'Exam'); ?></span>
                                    <span><?php echo date('M d, Y', strtotime($exam['submitted_at'])); ?></span>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">✓</div>
                                <p style="margin: 0;">No pending approvals</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="card-title" style="margin: 0;">📋 Recent Activity</h3>
                        <a href="ApprovalHistory.php" style="font-size: 0.85rem; color: var(--primary-color); text-decoration: none; font-weight: 600;">View All →</a>
                    </div>
                    <div>
                        <?php if($recent_approvals && $recent_approvals->num_rows > 0): ?>
                            <?php while($activity = $recent_approvals->fetch_assoc()): ?>
                            <div class="exam-item">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <div style="flex: 1;">
                                        <strong style="font-size: 1.05rem;">
                                            <?php echo htmlspecialchars($activity['exam_name']); ?>
                                        </strong>
                                        <div style="font-size: 0.9rem; color: var(--text-secondary); margin-top: 0.35rem;">
                                            <?php echo htmlspecialchars($activity['course_code']); ?>
                                        </div>
                                    </div>
                                    <span class="status-badge status-<?php echo $activity['action']; ?>">
                                        <?php echo ucfirst($activity['action']); ?>
                                    </span>
                                </div>
                                <div style="font-size: 0.9rem; color: var(--text-secondary);">
                                    <?php echo date('M d, Y - h:i A', strtotime($activity['created_at'])); ?>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📋</div>
                                <p style="margin: 0;">No recent activity</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Upcoming Exams -->
            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="card-title" style="margin: 0;">📅 Upcoming Approved Exams</h3>
                    <a href="ApprovedExams.php" style="font-size: 0.85rem; color: var(--primary-color); text-decoration: none; font-weight: 600;">View All →</a>
                </div>
                <div>
                    <?php if($upcoming_exams && $upcoming_exams->num_rows > 0): ?>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem; padding: 1.25rem;">
                            <?php while($exam = $upcoming_exams->fetch_assoc()): ?>
                            <div style="padding: 1.25rem; background: var(--bg-light); border-radius: var(--radius-md); border-left: 3px solid var(--success-color);">
                                <div style="font-weight: 600; color: var(--primary-color); margin-bottom: 0.5rem; font-size: 1.05rem;">
                                    <?php echo htmlspecialchars($exam['exam_name']); ?>
                                </div>
                                <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars($exam['course_code']); ?> - <?php echo htmlspecialchars($exam['course_name']); ?>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.75rem;">
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">
                                        📅 <?php echo date('M d, Y', strtotime($exam['exam_date'])); ?>
                                    </span>
                                    <span class="status-badge status-approved">Approved</span>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📅</div>
                            <p style="margin: 0;">No upcoming exams scheduled</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div style="margin-top: 1.5rem;">
                <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--text-primary); font-weight: 600;">⚡ Quick Actions</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                    <a href="PendingApprovals.php" style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); text-decoration: none; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.2s; display: block;">
                        <div style="font-size: 2.25rem; margin-bottom: 0.5rem;">⏳</div>
                        <div style="font-weight: 600; color: var(--primary-color); margin-bottom: 0.35rem; font-size: 1.05rem;">Pending Approvals</div>
                        <div style="font-size: 0.9rem; color: var(--text-secondary);">Review submitted exams</div>
                    </a>
                    <a href="ApprovedExams.php" style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); text-decoration: none; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.2s; display: block;">
                        <div style="font-size: 2.25rem; margin-bottom: 0.5rem;">✅</div>
                        <div style="font-weight: 600; color: var(--primary-color); margin-bottom: 0.35rem; font-size: 1.05rem;">Approved Exams</div>
                        <div style="font-size: 0.9rem; color: var(--text-secondary);">View approved exams</div>
                    </a>
                    <a href="DepartmentExams.php" style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); text-decoration: none; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.2s; display: block;">
                        <div style="font-size: 2.25rem; margin-bottom: 0.5rem;">🏛️</div>
                        <div style="font-weight: 600; color: var(--primary-color); margin-bottom: 0.35rem; font-size: 1.05rem;">Department Exams</div>
                        <div style="font-size: 0.9rem; color: var(--text-secondary);">Browse by department</div>
                    </a>
                    <a href="ApprovalHistory.php" style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); text-decoration: none; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.2s; display: block;">
                        <div style="font-size: 2.25rem; margin-bottom: 0.5rem;">📜</div>
                        <div style="font-weight: 600; color: var(--primary-color); margin-bottom: 0.35rem; font-size: 1.05rem;">Approval History</div>
                        <div style="font-size: 0.9rem; color: var(--text-secondary);">View audit trail</div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php $con->close(); ?>
    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
