<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");

// Get filter parameters
$actionFilter = $_GET['action'] ?? 'all';
$dateFilter = $_GET['date'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';

// Build query - exclude draft exams
$query = "SELECT eah.*, es.exam_name, c.course_name, c.course_code, ec.category_name,
    ecm.full_name as reviewer_name
    FROM exam_approval_history eah
    INNER JOIN exam_schedules es ON eah.schedule_id = es.schedule_id
    LEFT JOIN courses c ON es.course_id = c.course_id
    LEFT JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    LEFT JOIN exam_committee_members ecm ON eah.performed_by = ecm.committee_member_id AND eah.performed_by_type = 'committee'
    WHERE es.approval_status != 'draft'";

if($actionFilter != 'all') {
    $query .= " AND eah.action = '" . $con->real_escape_string($actionFilter) . "'";
}

if($dateFilter == 'today') {
    $query .= " AND DATE(eah.created_at) = CURDATE()";
} elseif($dateFilter == 'week') {
    $query .= " AND eah.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif($dateFilter == 'month') {
    $query .= " AND eah.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
}

if($searchQuery) {
    $query .= " AND (es.exam_name LIKE '%" . $con->real_escape_string($searchQuery) . "%' 
                OR c.course_name LIKE '%" . $con->real_escape_string($searchQuery) . "%'
                OR c.course_code LIKE '%" . $con->real_escape_string($searchQuery) . "%')";
}

$query .= " ORDER BY eah.created_at DESC LIMIT 100";
$history = $con->query($query);

// Debug: Check if query executed successfully
if(!$history) {
    error_log("Approval History Query Error: " . $con->error);
}

// Get statistics
$stats = $con->query("SELECT 
    COUNT(*) as total_reviews,
    SUM(CASE WHEN eah.action = 'approved' THEN 1 ELSE 0 END) as approved_count,
    SUM(CASE WHEN eah.action = 'revision_requested' THEN 1 ELSE 0 END) as revision_count,
    SUM(CASE WHEN eah.action = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
    SUM(CASE WHEN DATE(eah.created_at) = CURDATE() THEN 1 ELSE 0 END) as today_count
    FROM exam_approval_history eah");

// Ensure stats has default values if query fails
if(!$stats) {
    $stats = ['total_reviews' => 0, 'approved_count' => 0, 'revision_count' => 0, 'rejected_count' => 0, 'today_count' => 0];
} else {
    $stats = $stats->fetch_assoc();
    // Ensure all values are set
    $stats['total_reviews'] = $stats['total_reviews'] ?? 0;
    $stats['approved_count'] = $stats['approved_count'] ?? 0;
    $stats['revision_count'] = $stats['revision_count'] ?? 0;
    $stats['rejected_count'] = $stats['rejected_count'] ?? 0;
    $stats['today_count'] = $stats['today_count'] ?? 0;
}

// Get top reviewers
$topReviewers = $con->query("SELECT 
    ecm.full_name,
    COUNT(*) as review_count,
    SUM(CASE WHEN eah.action = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN eah.action = 'revision_requested' THEN 1 ELSE 0 END) as revisions,
    SUM(CASE WHEN eah.action = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM exam_approval_history eah
    LEFT JOIN exam_committee_members ecm ON eah.performed_by = ecm.committee_member_id
    WHERE eah.performed_by_type = 'committee'
    GROUP BY eah.performed_by, ecm.full_name
    ORDER BY review_count DESC
    LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval History</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .timeline {
            position: relative;
            padding-left: 2.5rem;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 0.75rem;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(to bottom, var(--primary-color), transparent);
        }
        .timeline-item {
            position: relative;
            padding-bottom: 2rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.85rem;
            top: 0.5rem;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 3px var(--primary-color);
            z-index: 1;
        }
        .timeline-item.approved::before { box-shadow: 0 0 0 3px var(--success-color); background: var(--success-color); }
        .timeline-item.revision_requested::before { box-shadow: 0 0 0 3px #ff9800; background: #ff9800; }
        .timeline-item.rejected::before { box-shadow: 0 0 0 3px #dc3545; background: #dc3545; }
        
        .history-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.2s;
        }
        .history-card:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .badge-approved { background: #d4edda; color: #155724; }
        .badge-revision { background: #fff3cd; color: #856404; }
        .badge-rejected { background: #f8d7da; color: #721c24; }
        .badge-submitted { background: #d1ecf1; color: #0c5460; }
        
        .stat-card-mini {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .reviewer-card {
            background: white;
            border-radius: var(--radius-md);
            padding: 1rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }
        .filter-chip {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            border: 2px solid var(--border-color);
            background: white;
            color: var(--text-primary);
        }
        .filter-chip:hover {
            border-color: var(--primary-color);
            background: var(--primary-color);
            color: white;
        }
        .filter-chip.active {
            border-color: var(--primary-color);
            background: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <!-- Header -->
            <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 1.75rem; margin: 0 0 0.25rem 0; color: var(--primary-color);">📜 Approval History</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.95rem;">Complete audit trail of all exam reviews</p>
            </div>

            <?php if($stats['total_reviews'] == 0): ?>
            <!-- Info Box for Empty State -->
            <div style="background: linear-gradient(135deg, rgba(0, 123, 255, 0.1), rgba(0, 123, 255, 0.05)); border-left: 4px solid var(--primary-color); padding: 1.25rem; border-radius: var(--radius-lg); margin-bottom: 2rem;">
                <div style="display: flex; align-items: start; gap: 1rem;">
                    <div style="font-size: 2rem;">ℹ️</div>
                    <div>
                        <h3 style="margin: 0 0 0.5rem 0; color: var(--primary-color); font-size: 1.05rem;">Getting Started</h3>
                        <p style="margin: 0 0 0.75rem 0; color: var(--text-primary); font-size: 0.9rem;">
                            This page tracks all exam approval activities. Once you start reviewing exams from the Pending Approvals page, 
                            you'll see a complete timeline of all actions here including approvals, revision requests, and rejections.
                        </p>
                        <a href="PendingApprovals.php" class="btn btn-primary btn-sm">
                            Go to Pending Approvals →
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Statistics Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <div class="stat-card-mini">
                    <div style="font-size: 2rem; font-weight: 800; color: var(--primary-color);"><?php echo $stats['total_reviews']; ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem;">Total Reviews</div>
                </div>
                <div class="stat-card-mini">
                    <div style="font-size: 2rem; font-weight: 800; color: var(--success-color);"><?php echo $stats['approved_count']; ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem;">Approved</div>
                </div>
                <div class="stat-card-mini">
                    <div style="font-size: 2rem; font-weight: 800; color: #ff9800;"><?php echo $stats['revision_count']; ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem;">Revisions</div>
                </div>
                <div class="stat-card-mini">
                    <div style="font-size: 2rem; font-weight: 800; color: #dc3545;"><?php echo $stats['rejected_count']; ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem;">Rejected</div>
                </div>
                <div class="stat-card-mini">
                    <div style="font-size: 2rem; font-weight: 800; color: var(--primary-color);"><?php echo $stats['today_count']; ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem;">Today</div>
                </div>
            </div>

            <div class="grid grid-2" style="gap: 1.5rem;">
                <!-- Main Timeline -->
                <div style="grid-column: span 2;">
                    <!-- Filters -->
                    <div class="card" style="margin-bottom: 1.5rem;">
                        <div style="padding: 1.25rem;">
                            <h3 style="margin: 0 0 1rem 0; font-size: 1.05rem; color: var(--primary-color);">🔍 Filters</h3>
                            
                            <!-- Action Filter -->
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.85rem; color: var(--text-secondary);">Action Type</label>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <a href="?action=all&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" class="filter-chip <?php echo $actionFilter == 'all' ? 'active' : ''; ?>">All</a>
                                    <a href="?action=approved&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" class="filter-chip <?php echo $actionFilter == 'approved' ? 'active' : ''; ?>">✓ Approved</a>
                                    <a href="?action=revision_requested&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" class="filter-chip <?php echo $actionFilter == 'revision_requested' ? 'active' : ''; ?>">✏️ Revision</a>
                                    <a href="?action=rejected&date=<?php echo $dateFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" class="filter-chip <?php echo $actionFilter == 'rejected' ? 'active' : ''; ?>">✗ Rejected</a>
                                </div>
                            </div>

                            <!-- Date Filter -->
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.85rem; color: var(--text-secondary);">Time Period</label>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <a href="?action=<?php echo $actionFilter; ?>&date=all&search=<?php echo urlencode($searchQuery); ?>" class="filter-chip <?php echo $dateFilter == 'all' ? 'active' : ''; ?>">All Time</a>
                                    <a href="?action=<?php echo $actionFilter; ?>&date=today&search=<?php echo urlencode($searchQuery); ?>" class="filter-chip <?php echo $dateFilter == 'today' ? 'active' : ''; ?>">Today</a>
                                    <a href="?action=<?php echo $actionFilter; ?>&date=week&search=<?php echo urlencode($searchQuery); ?>" class="filter-chip <?php echo $dateFilter == 'week' ? 'active' : ''; ?>">Last 7 Days</a>
                                    <a href="?action=<?php echo $actionFilter; ?>&date=month&search=<?php echo urlencode($searchQuery); ?>" class="filter-chip <?php echo $dateFilter == 'month' ? 'active' : ''; ?>">Last 30 Days</a>
                                </div>
                            </div>

                            <!-- Search -->
                            <form method="GET" style="display: flex; gap: 0.5rem;">
                                <input type="hidden" name="action" value="<?php echo $actionFilter; ?>">
                                <input type="hidden" name="date" value="<?php echo $dateFilter; ?>">
                                <input type="text" name="search" class="form-control" placeholder="Search exams, courses..." value="<?php echo htmlspecialchars($searchQuery); ?>" style="flex: 1;">
                                <button type="submit" class="btn btn-primary">Search</button>
                                <?php if($searchQuery): ?>
                                <a href="?action=<?php echo $actionFilter; ?>&date=<?php echo $dateFilter; ?>" class="btn btn-secondary">Clear</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title" style="margin: 0;">📋 Review Timeline</h3>
                        </div>
                        <div style="padding: 2rem 1.5rem;">
                            <?php if($history && $history->num_rows > 0): ?>
                            <div class="timeline">
                                <?php while($item = $history->fetch_assoc()): 
                                    $actionLabels = [
                                        'approved' => ['text' => 'Approved', 'class' => 'badge-approved', 'icon' => '✓'],
                                        'revision_requested' => ['text' => 'Revision Requested', 'class' => 'badge-revision', 'icon' => '✏️'],
                                        'rejected' => ['text' => 'Rejected', 'class' => 'badge-rejected', 'icon' => '✗'],
                                        'submitted' => ['text' => 'Submitted', 'class' => 'badge-submitted', 'icon' => '📤'],
                                        'resubmitted' => ['text' => 'Resubmitted', 'class' => 'badge-submitted', 'icon' => '🔄']
                                    ];
                                    $action = $actionLabels[$item['action']] ?? ['text' => ucfirst($item['action']), 'class' => 'badge-submitted', 'icon' => '•'];
                                ?>
                                <div class="timeline-item <?php echo $item['action']; ?>">
                                    <div class="history-card">
                                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                                            <div style="flex: 1;">
                                                <h4 style="margin: 0 0 0.35rem 0; color: var(--primary-color); font-size: 1.05rem;">
                                                    <?php echo htmlspecialchars($item['exam_name']); ?>
                                                </h4>
                                                <div style="font-size: 0.85rem; color: var(--text-secondary);">
                                                    <?php echo htmlspecialchars($item['course_code']); ?> - <?php echo htmlspecialchars($item['course_name']); ?>
                                                </div>
                                            </div>
                                            <span class="status-badge <?php echo $action['class']; ?>">
                                                <?php echo $action['icon']; ?> <?php echo $action['text']; ?>
                                            </span>
                                        </div>

                                        <?php if($item['comments']): ?>
                                        <div style="background: var(--bg-light); padding: 0.75rem; border-radius: var(--radius-md); margin-bottom: 0.75rem; border-left: 3px solid var(--primary-color);">
                                            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem; font-weight: 600;">Comments:</div>
                                            <div style="font-size: 0.9rem; color: var(--text-primary);">
                                                <?php echo nl2br(htmlspecialchars($item['comments'])); ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; color: var(--text-secondary);">
                                            <span>👤 <?php echo htmlspecialchars($item['reviewer_name'] ?? 'Committee Member'); ?></span>
                                            <span>🕒 <?php echo date('M d, Y - h:i A', strtotime($item['created_at'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                            <?php else: ?>
                            <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                <div style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;">📜</div>
                                <h3 style="margin: 0 0 0.75rem 0; color: var(--text-primary);">No Approval History Yet</h3>
                                <p style="margin: 0 0 0.5rem 0; font-size: 0.95rem;">
                                    <?php if($actionFilter != 'all' || $dateFilter != 'all' || $searchQuery): ?>
                                        No records match your current filters.
                                    <?php else: ?>
                                        Approval history will appear here once you start reviewing exams.
                                    <?php endif; ?>
                                </p>
                                <?php if($actionFilter != 'all' || $dateFilter != 'all' || $searchQuery): ?>
                                <a href="ApprovalHistory.php" class="btn btn-primary" style="margin-top: 1rem;">Clear All Filters</a>
                                <?php else: ?>
                                <a href="PendingApprovals.php" class="btn btn-primary" style="margin-top: 1rem;">Go to Pending Approvals</a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Top Reviewers Sidebar -->
                <?php if($topReviewers && $topReviewers->num_rows > 0): ?>
                <div style="grid-column: span 2;">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title" style="margin: 0;">🏆 Top Reviewers</h3>
                        </div>
                        <div style="padding: 1.25rem;">
                            <?php $rank = 1; while($reviewer = $topReviewers->fetch_assoc()): ?>
                            <div class="reviewer-card">
                                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem;">
                                        #<?php echo $rank++; ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600; color: var(--primary-color); font-size: 0.95rem;">
                                            <?php echo htmlspecialchars($reviewer['full_name'] ?? 'Committee Member'); ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                            <?php echo $reviewer['review_count']; ?> total reviews
                                        </div>
                                    </div>
                                </div>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; text-align: center; font-size: 0.8rem;">
                                    <div>
                                        <div style="font-weight: 700; color: var(--success-color);"><?php echo $reviewer['approved']; ?></div>
                                        <div style="color: var(--text-secondary); font-size: 0.7rem;">Approved</div>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: #ff9800;"><?php echo $reviewer['revisions']; ?></div>
                                        <div style="color: var(--text-secondary); font-size: 0.7rem;">Revisions</div>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: #dc3545;"><?php echo $reviewer['rejected']; ?></div>
                                        <div style="color: var(--text-secondary); font-size: 0.7rem;">Rejected</div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Export Options -->
            <div class="card" style="margin-top: 1.5rem;">
                <div style="padding: 1.25rem;">
                    <h3 style="margin: 0 0 0.75rem 0; font-size: 1.05rem; color: var(--primary-color);">📥 Export Options</h3>
                    <p style="margin: 0 0 1rem 0; color: var(--text-secondary); font-size: 0.9rem;">
                        Download approval history for record keeping and analysis
                    </p>
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <button class="btn btn-success" onclick="alert('CSV export feature - Coming soon!')">
                            📊 Export to CSV
                        </button>
                        <button class="btn btn-primary" onclick="window.print()">
                            🖨️ Print Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $con->close(); ?>
    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
