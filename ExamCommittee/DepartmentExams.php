<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$con = require_once(__DIR__ . "/../Connections/OES.php");

// Get selected department
$selectedDept = $_GET['dept'] ?? '';
$statusFilter = $_GET['status'] ?? 'all';

// Get all departments with exam counts (only submitted exams, not drafts)
$departments = $con->query("SELECT d.department_id, d.department_name, d.department_code,
    COUNT(DISTINCT es.schedule_id) as total_exams,
    SUM(CASE WHEN es.approval_status = 'pending' AND es.submitted_for_approval = TRUE THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN es.approval_status = 'approved' THEN 1 ELSE 0 END) as approved_count,
    SUM(CASE WHEN es.approval_status = 'revision' THEN 1 ELSE 0 END) as revision_count,
    SUM(CASE WHEN es.approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
    FROM departments d
    LEFT JOIN courses c ON d.department_id = c.department_id
    LEFT JOIN exam_schedules es ON c.course_id = es.course_id AND es.approval_status != 'draft'
    GROUP BY d.department_id, d.department_name, d.department_code
    HAVING total_exams > 0
    ORDER BY d.department_name");

// Get exams for selected department
$exams = null;
$deptInfo = null;
$deptStats = null;
if($selectedDept) {
    $deptInfo = $con->query("SELECT * FROM departments WHERE department_id = " . intval($selectedDept))->fetch_assoc();
    
    // Get department statistics (exclude drafts)
    $deptStats = $con->query("SELECT 
        COUNT(*) as total_exams,
        SUM(CASE WHEN es.approval_status = 'pending' AND es.submitted_for_approval = TRUE THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN es.approval_status = 'approved' THEN 1 ELSE 0 END) as approved_count,
        SUM(CASE WHEN es.approval_status = 'revision' THEN 1 ELSE 0 END) as revision_count,
        SUM(CASE WHEN es.approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
        FROM exam_schedules es
        INNER JOIN courses c ON es.course_id = c.course_id
        WHERE c.department_id = " . intval($selectedDept) . " AND es.approval_status != 'draft'")->fetch_assoc();
    
    $query = "SELECT es.*, c.course_name, c.course_code, ec.category_name,
        (SELECT COUNT(*) FROM exam_questions eq WHERE eq.schedule_id = es.schedule_id) as question_count
        FROM exam_schedules es
        INNER JOIN courses c ON es.course_id = c.course_id
        INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
        WHERE c.department_id = " . intval($selectedDept) . " AND es.approval_status != 'draft'";
    
    if($statusFilter != 'all') {
        $query .= " AND es.approval_status = '" . $con->real_escape_string($statusFilter) . "'";
    }
    
    $query .= " ORDER BY es.created_at DESC";
    $exams = $con->query($query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Exams</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .dept-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.2s;
            cursor: pointer;
            border-left: 4px solid var(--primary-color);
        }
        .dept-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        .dept-card.selected {
            border-left-color: var(--success-color);
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.05), rgba(40, 167, 69, 0.02));
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.6rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 1.75rem; margin: 0 0 0.25rem 0; color: var(--primary-color);">🏛️ Department Exams</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.95rem;">Browse exams by department</p>
            </div>

            <?php if(!$selectedDept): ?>
            <!-- Department Selection -->
            <?php if($departments && $departments->num_rows > 0): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.25rem;">
                <?php while($dept = $departments->fetch_assoc()): ?>
                <a href="?dept=<?php echo $dept['department_id']; ?>&t=<?php echo time(); ?>" class="dept-card" style="text-decoration: none; color: inherit;">
                    <h3 style="margin: 0 0 1rem 0; color: var(--primary-color); font-size: 1.1rem;">
                        <?php echo htmlspecialchars($dept['department_name']); ?>
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div style="text-align: center; padding: 0.5rem; background: var(--bg-light); border-radius: var(--radius-md);">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);"><?php echo $dept['total_exams'] ?? 0; ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-secondary);">Total Exams</div>
                        </div>
                        <div style="text-align: center; padding: 0.5rem; background: rgba(255, 152, 0, 0.1); border-radius: var(--radius-md);">
                            <div style="font-size: 1.5rem; font-weight: 700; color: #ff9800;"><?php echo $dept['pending_count'] ?? 0; ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-secondary);">Pending</div>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; font-size: 0.8rem; text-align: center;">
                        <div style="padding: 0.35rem; background: rgba(40, 167, 69, 0.1); border-radius: var(--radius-sm);">
                            <div style="font-weight: 700; color: var(--success-color);"><?php echo $dept['approved_count'] ?? 0; ?></div>
                            <div style="font-size: 0.7rem; color: var(--text-secondary);">Approved</div>
                        </div>
                        <div style="padding: 0.35rem; background: rgba(255, 152, 0, 0.1); border-radius: var(--radius-sm);">
                            <div style="font-weight: 700; color: #ff9800;"><?php echo $dept['revision_count'] ?? 0; ?></div>
                            <div style="font-size: 0.7rem; color: var(--text-secondary);">Revision</div>
                        </div>
                        <div style="padding: 0.35rem; background: rgba(220, 53, 69, 0.1); border-radius: var(--radius-sm);">
                            <div style="font-weight: 700; color: #dc3545;"><?php echo $dept['rejected_count'] ?? 0; ?></div>
                            <div style="font-size: 0.7rem; color: var(--text-secondary);">Rejected</div>
                        </div>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="card" style="padding: 3rem; text-align: center;">
                <div style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;">🏛️</div>
                <h3 style="color: var(--text-primary); margin-bottom: 0.5rem;">No Departments with Exams</h3>
                <p style="color: var(--text-secondary); margin: 0;">
                    Departments will appear here once instructors create and submit exams.
                </p>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <!-- Department Exams -->
            <?php if($deptInfo): ?>
            <div style="margin-bottom: 1.5rem;">
                <a href="DepartmentExams.php" class="btn btn-secondary" style="margin-bottom: 1rem;">← Back to Departments</a>
                
                <!-- Department Stats -->
                <?php if($deptStats): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="background: white; padding: 1rem; border-radius: var(--radius-lg); text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        <div style="font-size: 1.75rem; font-weight: 700; color: var(--primary-color);"><?php echo $deptStats['total_exams']; ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">Total</div>
                    </div>
                    <div style="background: white; padding: 1rem; border-radius: var(--radius-lg); text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        <div style="font-size: 1.75rem; font-weight: 700; color: #ff9800;"><?php echo $deptStats['pending_count']; ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">Pending</div>
                    </div>
                    <div style="background: white; padding: 1rem; border-radius: var(--radius-lg); text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        <div style="font-size: 1.75rem; font-weight: 700; color: var(--success-color);"><?php echo $deptStats['approved_count']; ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">Approved</div>
                    </div>
                    <div style="background: white; padding: 1rem; border-radius: var(--radius-lg); text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        <div style="font-size: 1.75rem; font-weight: 700; color: #ff9800;"><?php echo $deptStats['revision_count']; ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">Revision</div>
                    </div>
                    <div style="background: white; padding: 1rem; border-radius: var(--radius-lg); text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        <div style="font-size: 1.75rem; font-weight: 700; color: #dc3545;"><?php echo $deptStats['rejected_count']; ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">Rejected</div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="card" style="padding: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h2 style="margin: 0; color: var(--primary-color); font-size: 1.5rem;">
                            🏛️ <?php echo htmlspecialchars($deptInfo['department_name']); ?>
                        </h2>
                        <?php if($exams): ?>
                        <span style="background: var(--bg-light); padding: 0.5rem 1rem; border-radius: var(--radius-md); font-weight: 600; color: var(--text-secondary);">
                            <?php echo $exams->num_rows; ?> Exam<?php echo $exams->num_rows != 1 ? 's' : ''; ?> 
                            <?php if($statusFilter != 'all'): ?>
                            (<?php echo ucfirst($statusFilter); ?>)
                            <?php endif; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <form method="GET" style="display: flex; gap: 1rem; align-items: end;">
                        <input type="hidden" name="dept" value="<?php echo $selectedDept; ?>">
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem;">Filter by Status</label>
                            <select name="status" class="form-control">
                                <option value="all" <?php echo $statusFilter == 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="pending" <?php echo $statusFilter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $statusFilter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="revision" <?php echo $statusFilter == 'revision' ? 'selected' : ''; ?>>Revision</option>
                                <option value="rejected" <?php echo $statusFilter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">🔍 Filter</button>
                        <?php if($statusFilter != 'all'): ?>
                        <a href="?dept=<?php echo $selectedDept; ?>" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <?php if($exams && $exams->num_rows > 0): ?>
            <div style="display: grid; gap: 1rem;">
                <?php while($exam = $exams->fetch_assoc()): 
                    $statusColors = [
                        'pending' => ['bg' => '#fff3cd', 'color' => '#856404', 'icon' => '⏳'],
                        'approved' => ['bg' => '#d4edda', 'color' => '#155724', 'icon' => '✓'],
                        'revision' => ['bg' => 'rgba(255, 152, 0, 0.1)', 'color' => '#ff9800', 'icon' => '✏️'],
                        'rejected' => ['bg' => '#f8d7da', 'color' => '#721c24', 'icon' => '✗']
                    ];
                    $status = $statusColors[$exam['approval_status']] ?? $statusColors['pending'];
                ?>
                <div class="card" style="padding: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <div style="flex: 1;">
                            <h3 style="margin: 0 0 0.5rem 0; color: var(--primary-color); font-size: 1.1rem;">
                                <?php echo htmlspecialchars($exam['exam_name']); ?>
                            </h3>
                            <div style="font-size: 0.9rem; color: var(--text-secondary);">
                                <?php echo htmlspecialchars($exam['course_code']); ?> - <?php echo htmlspecialchars($exam['course_name']); ?>
                            </div>
                        </div>
                        <span class="status-badge" style="background: <?php echo $status['bg']; ?>; color: <?php echo $status['color']; ?>; padding: 0.35rem 0.75rem; font-size: 0.85rem;">
                            <?php echo $status['icon']; ?> <?php echo ucfirst($exam['approval_status']); ?>
                        </span>
                    </div>
                    <div style="display: flex; gap: 2rem; font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1rem; flex-wrap: wrap;">
                        <span>📚 <?php echo htmlspecialchars($exam['category_name']); ?></span>
                        <span>📝 <?php echo $exam['question_count']; ?> Questions</span>
                        <span>⏱️ <?php echo $exam['duration_minutes']; ?> min</span>
                        <span>📅 <?php echo date('M d, Y', strtotime($exam['exam_date'])); ?></span>
                    </div>
                    <a href="ViewExamDetails.php?schedule_id=<?php echo $exam['schedule_id']; ?>" class="btn btn-primary btn-sm">
                        👁️ View Details
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="card" style="padding: 3rem; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;">📋</div>
                <h3 style="color: var(--text-primary); margin-bottom: 0.5rem;">No Exams Found</h3>
                <p style="color: var(--text-secondary); margin: 0;">
                    <?php if($statusFilter != 'all'): ?>
                        No exams with status "<?php echo ucfirst($statusFilter); ?>" in this department.
                    <?php else: ?>
                        This department doesn't have any exams yet.
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="card" style="padding: 3rem; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;">❌</div>
                <h3 style="color: var(--text-primary);">Department Not Found</h3>
                <a href="DepartmentExams.php" class="btn btn-primary" style="margin-top: 1rem;">← Back to Departments</a>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php $con->close(); ?>
    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
