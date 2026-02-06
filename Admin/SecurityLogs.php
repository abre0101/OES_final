<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Administrator session
SessionManager::startSession('Administrator');

if(!isset($_SESSION['username'])){
    header("Location:../auth/staff-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;

// Get filter parameters
$filterType = $_GET['type'] ?? 'all';
$filterStatus = $_GET['is_active'] ?? 'all';
$filterDate = $_GET['date'] ?? '';
$searchUser = $_GET['search'] ?? '';

// Build query - using audit_logs table
$whereConditions = array();
if($filterType != 'all') {
    $whereConditions[] = "user_type = '" . $con->real_escape_string($filterType) . "'";
}
if($filterDate) {
    $whereConditions[] = "DATE(created_at) = '" . $con->real_escape_string($filterDate) . "'";
}
if($searchUser) {
    $whereConditions[] = "(user_id LIKE '%" . $con->real_escape_string($searchUser) . "%' OR action LIKE '%" . $con->real_escape_string($searchUser) . "%')";
}

$whereClause = count($whereConditions) > 0 ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Get logs with pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

$logsQuery = "SELECT * FROM audit_logs $whereClause ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
$logsResult = $con->query($logsQuery);
$logsData = [];
if($logsResult && $logsResult->num_rows > 0) {
    while($row = $logsResult->fetch_assoc()) {
        $logsData[] = $row;
    }
}

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM audit_logs $whereClause";
$totalResult = $con->query($countQuery);
$totalLogs = $totalResult ? $totalResult->fetch_assoc()['total'] : 0;
$totalPages = ceil($totalLogs / $perPage);

// Get statistics
$stats = array(
    'total' => 0,
    'today' => 0,
    'failed' => 0,
    'success' => 0
);

$totalQuery = $con->query("SELECT COUNT(*) as count FROM audit_logs");
if($totalQuery) {
    $stats['total'] = $totalQuery->fetch_assoc()['count'];
}

$todayQuery = $con->query("SELECT COUNT(*) as count FROM audit_logs WHERE DATE(created_at) = CURDATE()");
if($todayQuery) {
    $stats['today'] = $todayQuery->fetch_assoc()['count'];
}

$failedQuery = $con->query("SELECT COUNT(*) as count FROM audit_logs WHERE action LIKE '%failed%' OR action LIKE '%error%'");
if($failedQuery) {
    $stats['failed'] = $failedQuery->fetch_assoc()['count'];
}

$successQuery = $con->query("SELECT COUNT(*) as count FROM audit_logs WHERE action LIKE '%success%' OR action LIKE '%login%' OR action LIKE '%created%' OR action LIKE '%updated%'");
if($successQuery) {
    $stats['success'] = $successQuery->fetch_assoc()['count'];
}

// Get recent suspicious activities (failed logins, errors, etc.)
$suspiciousQuery = "SELECT * FROM audit_logs 
    WHERE action LIKE '%failed%' OR action LIKE '%error%' OR action LIKE '%denied%'
    ORDER BY created_at DESC LIMIT 10";
$suspiciousResult = $con->query($suspiciousQuery);
$suspiciousData = [];
if($suspiciousResult && $suspiciousResult->num_rows > 0) {
    while($row = $suspiciousResult->fetch_assoc()) {
        $suspiciousData[] = $row;
    }
}

$con->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Logs & Monitoring - Admin</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
        
        .log-entry {
            padding: 1.5rem;
            border-left: 4px solid #e0e0e0;
            margin-bottom: 1rem;
            background: white;
            border-radius: var(--radius-md);
            transition: all 0.3s ease;
        }
        
        .log-entry:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transform: translateX(2px);
        }
        
        .log-entry.success {
            border-left-color: var(--success-color);
        }
        
        .log-entry.failed {
            border-left-color: #dc3545;
            background: rgba(220, 53, 69, 0.05);
        }
        
        .log-entry.warning {
            border-left-color: #ffc107;
        }
        
        .log-entry strong {
            font-size: 1.1rem;
            font-weight: 700;
        }
        
        .log-meta {
            display: flex;
            gap: 1.5rem;
            font-size: 1rem;
            color: var(--text-secondary);
            margin-top: 0.75rem;
            flex-wrap: wrap;
        }
        
        .log-meta span {
            font-size: 1rem;
        }
        
        .log-entry .details-text {
            margin-top: 0.75rem;
            font-size: 1rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .status-success {
            background: var(--success-color);
            color: white;
        }
        
        .status-failed {
            background: #dc3545;
            color: white;
        }
        
        .status-warning {
            background: #ffc107;
            color: var(--primary-dark);
        }
        
        .status-info {
            background: #17a2b8;
            color: white;
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header-actions">
                <div class="page-title-section">
                    <h1><span>🔒</span> Security Logs & Monitoring</h1>
                    <p class="page-subtitle">Monitor system security and user activities</p>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">📊</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
                        <div class="stat-label">Total Logs</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon">📅</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo number_format($stats['today']); ?></div>
                        <div class="stat-label">Today's Activities</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo number_format($stats['success']); ?></div>
                        <div class="stat-label">Successful Actions</div>
                    </div>
                </div>

                <div class="stat-card stat-danger">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo number_format($stats['failed']); ?></div>
                        <div class="stat-label">Failed Attempts</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">🔍 Filter Logs</h3>
                </div>
                <div class="card-body">
                    <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>User Type</label>
                            <select name="type" class="form-control">
                                <option value="all" <?php echo $filterType == 'all' ? 'selected' : ''; ?>>All Types</option>
                                <option value="admin" <?php echo $filterType == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="instructor" <?php echo $filterType == 'instructor' ? 'selected' : ''; ?>>Instructor</option>
                                <option value="student" <?php echo $filterType == 'student' ? 'selected' : ''; ?>>Student</option>
                                <option value="department_head" <?php echo $filterType == 'department_head' ? 'selected' : ''; ?>>Department Head</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Status</label>
                            <select name="is_active" class="form-control">
                                <option value="all" <?php echo $filterStatus == 'all' ? 'selected' : ''; ?>>All Actions</option>
                                <option value="login" <?php echo $filterStatus == 'login' ? 'selected' : ''; ?>>Login</option>
                                <option value="logout" <?php echo $filterStatus == 'logout' ? 'selected' : ''; ?>>Logout</option>
                                <option value="create" <?php echo $filterStatus == 'create' ? 'selected' : ''; ?>>Create</option>
                                <option value="update" <?php echo $filterStatus == 'update' ? 'selected' : ''; ?>>Update</option>
                                <option value="delete" <?php echo $filterStatus == 'delete' ? 'selected' : ''; ?>>Delete</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($filterDate); ?>">
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Search User</label>
                            <input type="text" name="search" class="form-control" placeholder="User ID..." value="<?php echo htmlspecialchars($searchUser); ?>">
                        </div>
                        
                        <div style="display: flex; gap: 0.5rem;">
                            <button type="submit" class="btn btn-primary">🔍 Filter</button>
                            <a href="SecurityLogs.php" class="btn btn-secondary">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="grid grid-2 mt-4">
                <!-- Security Logs -->
                <div class="card" style="grid-column: 1 / -1;">
                    <div class="card-header">
                        <h3 class="card-title">📋 Security Logs</h3>
                    </div>
                    <div class="card-body">
                        <?php if(count($logsData) > 0): ?>
                            <?php foreach($logsData as $log): ?>
                            <?php 
                                // Determine log type based on action
                                $logClass = 'success';
                                $statusBadge = 'info';
                                if(stripos($log['action'], 'failed') !== false || stripos($log['action'], 'error') !== false) {
                                    $logClass = 'failed';
                                    $statusBadge = 'failed';
                                } elseif(stripos($log['action'], 'delete') !== false) {
                                    $logClass = 'warning';
                                    $statusBadge = 'warning';
                                } elseif(stripos($log['action'], 'login') !== false || stripos($log['action'], 'create') !== false) {
                                    $statusBadge = 'success';
                                }
                            ?>
                            <div class="log-entry <?php echo $logClass; ?>">
                                <div style="display: flex; justify-content: space-between; align-items: start; gap: 1rem;">
                                    <div style="flex: 1;">
                                        <strong style="color: var(--primary-color); font-size: 1.15rem;">
                                            <?php echo htmlspecialchars($log['action']); ?>
                                        </strong>
                                        <div class="log-meta">
                                            <span><strong>👤 User:</strong> <?php echo htmlspecialchars($log['user_id'] ?? 'System'); ?></span>
                                            <span><strong>🏷️ Type:</strong> <?php echo ucfirst(htmlspecialchars($log['user_type'] ?? 'N/A')); ?></span>
                                            <span><strong>🕐 Time:</strong> <?php echo date('M j, Y - g:i A', strtotime($log['created_at'])); ?></span>
                                        </div>
                                        
                                        <?php if(!empty($log['table_name'])): ?>
                                        <div class="details-text" style="margin-top: 1rem; padding: 1rem; background: rgba(0,0,0,0.02); border-radius: 8px;">
                                            <div style="display: grid; grid-template-columns: auto 1fr; gap: 0.75rem 1.5rem;">
                                                <strong style="color: var(--primary-color);">📊 Table:</strong>
                                                <span><?php echo htmlspecialchars($log['table_name']); ?></span>
                                                
                                                <?php if($log['record_id']): ?>
                                                <strong style="color: var(--primary-color);">🔑 Record ID:</strong>
                                                <span><?php echo htmlspecialchars($log['record_id']); ?></span>
                                                <?php endif; ?>
                                                
                                                <?php if(!empty($log['ip_address'])): ?>
                                                <strong style="color: var(--primary-color);">🌐 IP Address:</strong>
                                                <span><?php echo htmlspecialchars($log['ip_address']); ?></span>
                                                <?php endif; ?>
                                                
                                                <?php if(!empty($log['metadata'])): ?>
                                                <?php 
                                                $metadata = json_decode($log['metadata'], true);
                                                if($metadata && isset($metadata['changed_fields'])): 
                                                ?>
                                                <strong style="color: var(--primary-color);">📝 Changed Fields:</strong>
                                                <span>
                                                    <?php 
                                                    $fields = array_keys($metadata['changed_fields']);
                                                    echo implode(', ', array_map('htmlspecialchars', $fields)); 
                                                    ?>
                                                </span>
                                                <?php endif; ?>
                                                <?php endif; ?>
                                                
                                                <?php if(!empty($log['old_value']) && !empty($log['new_value'])): ?>
                                                <strong style="color: var(--primary-color);">🔄 Changes:</strong>
                                                <div>
                                                    <div style="margin-bottom: 0.5rem;">
                                                        <span style="color: #dc3545; font-weight: 600;">Old:</span> 
                                                        <code style="background: rgba(220,53,69,0.1); padding: 0.25rem 0.5rem; border-radius: 4px;">
                                                            <?php echo htmlspecialchars(substr($log['old_value'], 0, 100)); ?>
                                                            <?php if(strlen($log['old_value']) > 100) echo '...'; ?>
                                                        </code>
                                                    </div>
                                                    <div>
                                                        <span style="color: var(--success-color); font-weight: 600;">New:</span> 
                                                        <code style="background: rgba(40,167,69,0.1); padding: 0.25rem 0.5rem; border-radius: 4px;">
                                                            <?php echo htmlspecialchars(substr($log['new_value'], 0, 100)); ?>
                                                            <?php if(strlen($log['new_value']) > 100) echo '...'; ?>
                                                        </code>
                                                    </div>
                                                </div>
                                                <?php elseif(!empty($log['new_value'])): ?>
                                                <strong style="color: var(--primary-color);">ℹ️ Details:</strong>
                                                <span><?php echo htmlspecialchars($log['new_value']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="status-badge status-<?php echo $statusBadge; ?>">
                                        <?php 
                                        // Show operation type
                                        if(stripos($log['action'], 'login') !== false) echo '🔐 Login';
                                        elseif(stripos($log['action'], 'logout') !== false) echo '🚪 Logout';
                                        elseif(stripos($log['action'], 'created') !== false) echo '➕ Create';
                                        elseif(stripos($log['action'], 'updated') !== false) echo '✏️ Update';
                                        elseif(stripos($log['action'], 'deleted') !== false) echo '🗑️ Delete';
                                        elseif(stripos($log['action'], 'failed') !== false) echo '❌ Failed';
                                        else echo '📋 Action';
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <!-- Pagination -->
                            <?php if($totalPages > 1): ?>
                            <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                                <?php if($page > 1): ?>
                                <a href="?page=<?php echo $page-1; ?>&type=<?php echo $filterType; ?>&is_active=<?php echo $filterStatus; ?>&date=<?php echo $filterDate; ?>&search=<?php echo $searchUser; ?>" class="btn btn-secondary">← Previous</a>
                                <?php endif; ?>
                                
                                <span style="padding: 0.5rem 1rem; background: var(--bg-light); border-radius: var(--radius-md);">
                                    Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                                </span>
                                
                                <?php if($page < $totalPages): ?>
                                <a href="?page=<?php echo $page+1; ?>&type=<?php echo $filterType; ?>&is_active=<?php echo $filterStatus; ?>&date=<?php echo $filterDate; ?>&search=<?php echo $searchUser; ?>" class="btn btn-secondary">Next →</a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 4rem 2rem;">
                                <div style="font-size: 4rem; margin-bottom: 1rem;">📋</div>
                                <h3 style="color: var(--text-secondary);">No Logs Found</h3>
                                <p>No security logs match your filters</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Suspicious Activities -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">⚠️ Recent Suspicious Activities</h3>
                    </div>
                    <div class="card-body">
                        <?php if(count($suspiciousData) > 0): ?>
                            <?php foreach($suspiciousData as $sus): ?>
                            <div style="padding: 1.25rem; background: rgba(220, 53, 69, 0.05); border-left: 3px solid #dc3545; border-radius: var(--radius-md); margin-bottom: 0.75rem;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <strong style="color: #dc3545; font-size: 1.05rem;">⚠️ <?php echo htmlspecialchars($sus['action']); ?></strong>
                                    <span style="font-size: 0.85rem; color: var(--text-secondary);">
                                        <?php echo date('M j, g:i A', strtotime($sus['created_at'])); ?>
                                    </span>
                                </div>
                                <div style="font-size: 0.95rem; color: var(--text-secondary); display: grid; grid-template-columns: auto 1fr; gap: 0.5rem 1rem;">
                                    <span><strong>User:</strong></span>
                                    <span><?php echo htmlspecialchars($sus['user_id'] ?? 'Unknown'); ?></span>
                                    
                                    <span><strong>Type:</strong></span>
                                    <span><?php echo ucfirst(htmlspecialchars($sus['user_type'] ?? 'N/A')); ?></span>
                                    
                                    <?php if(!empty($sus['ip_address'])): ?>
                                    <span><strong>IP:</strong></span>
                                    <span><?php echo htmlspecialchars($sus['ip_address']); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if(!empty($sus['table_name'])): ?>
                                    <span><strong>Target:</strong></span>
                                    <span><?php echo htmlspecialchars($sus['table_name']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem;">
                                <div style="font-size: 3rem; margin-bottom: 0.5rem;">✅</div>
                                <p style="color: var(--text-secondary); margin: 0;">No suspicious activities detected</p>
                                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem;">System is secure</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Security Tips -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">💡 Security Tips</h3>
                    </div>
                    <div class="card-body">
                        <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-secondary); line-height: 2; font-size: 1rem;">
                            <li>Monitor failed login attempts regularly</li>
                            <li>Review suspicious IP addresses</li>
                            <li>Check for unusual activity patterns</li>
                            <li>Block accounts with multiple failed attempts</li>
                            <li>Keep security logs for audit purposes</li>
                            <li>Export logs periodically for backup</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
