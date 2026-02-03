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

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
$pageTitle = "Check Questions";

// Check if question_page table exists, if not use questions table
$table_check = $con->query("SHOW TABLES LIKE 'question_page'");
$use_question_page = ($table_check && $table_check->num_rows > 0);

if($use_question_page) {
    // Add approval_status column if it doesn't exist
    $con->query("ALTER TABLE question_page ADD COLUMN IF NOT EXISTS approval_status ENUM('pending', 'approved', 'revision', 'rejected') DEFAULT 'pending'");
    
    // Filter by status
    $statusFilter = isset($_GET['is_active']) ? $_GET['is_active'] : 'all';
    $whereClause = $statusFilter != 'all' ? "WHERE qp.approval_status = '$statusFilter'" : "";
    
    // Get questions from question_page table
    $questions = $con->query("SELECT qp.*, ec.exam_name 
        FROM question_page qp 
        LEFT JOIN exam_categories ec ON qp.exam_id = ec.exam_id 
        $whereClause
        ORDER BY qp.question_id DESC 
        LIMIT 50");
    
    // Get counts for each status
    $pendingCount = $con->query("SELECT COUNT(*) as count FROM question_page WHERE approval_status = 'pending' OR approval_status IS NULL")->fetch_assoc()['count'];
} else {
    // Use questions table instead
    // Add approval_status column if it doesn't exist
    $con->query("ALTER TABLE questions ADD COLUMN IF NOT EXISTS approval_status ENUM('pending', 'approved', 'revision', 'rejected') DEFAULT 'pending'");
    
    // Filter by status
    $statusFilter = isset($_GET['is_active']) ? $_GET['is_active'] : 'all';
    $whereClause = $statusFilter != 'all' ? "WHERE q.approval_status = '$statusFilter'" : "";
    
    // Get questions from questions table
    $questions = $con->query("SELECT q.*, c.course_name, c.course_code
        FROM questions q 
        LEFT JOIN courses c ON q.course_id = c.course_id 
        $whereClause
        ORDER BY q.question_id DESC 
        LIMIT 50");
    
    // Get counts for each status
    $pendingCount = $con->query("SELECT COUNT(*) as count FROM questions WHERE approval_status = 'pending' OR approval_status IS NULL")->fetch_assoc()['count'];
}

$table_name = $use_question_page ? 'question_page' : 'questions';
$approvedCount = $con->query("SELECT COUNT(*) as count FROM $table_name WHERE approval_status = 'approved'")->fetch_assoc()['count'];
$revisionCount = $con->query("SELECT COUNT(*) as count FROM $table_name WHERE approval_status = 'revision'")->fetch_assoc()['count'];
$rejectedCount = $con->query("SELECT COUNT(*) as count FROM $table_name WHERE approval_status = 'rejected'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Questions - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header">
                <h1>🔍 Check Questions</h1>
                <p>Review and approve examination questions</p>
            </div>

            <!-- Status Filter Tabs -->
            <div style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="?is_active=all" class="filter-tab <?php echo $statusFilter == 'all' ? 'active' : ''; ?>" style="flex: 1; min-width: 150px; padding: 1rem; text-align: center; border-radius: var(--radius-md); text-decoration: none; transition: all 0.3s; <?php echo $statusFilter == 'all' ? 'background: var(--primary-color); color: white;' : 'background: var(--bg-light); color: var(--text-primary);'; ?>">
                        <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $pendingCount + $approvedCount + $revisionCount + $rejectedCount; ?></div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">All Questions</div>
                    </a>
                    <a href="?is_active=pending" class="filter-tab <?php echo $statusFilter == 'pending' ? 'active' : ''; ?>" style="flex: 1; min-width: 150px; padding: 1rem; text-align: center; border-radius: var(--radius-md); text-decoration: none; transition: all 0.3s; <?php echo $statusFilter == 'pending' ? 'background: var(--warning-color); color: white;' : 'background: var(--bg-light); color: var(--text-primary);'; ?>">
                        <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $pendingCount; ?></div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">⏳ Pending</div>
                    </a>
                    <a href="?is_active=approved" class="filter-tab <?php echo $statusFilter == 'approved' ? 'active' : ''; ?>" style="flex: 1; min-width: 150px; padding: 1rem; text-align: center; border-radius: var(--radius-md); text-decoration: none; transition: all 0.3s; <?php echo $statusFilter == 'approved' ? 'background: var(--success-color); color: white;' : 'background: var(--bg-light); color: var(--text-primary);'; ?>">
                        <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $approvedCount; ?></div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">✓ Approved</div>
                    </a>
                    <a href="?is_active=revision" class="filter-tab <?php echo $statusFilter == 'revision' ? 'active' : ''; ?>" style="flex: 1; min-width: 150px; padding: 1rem; text-align: center; border-radius: var(--radius-md); text-decoration: none; transition: all 0.3s; <?php echo $statusFilter == 'revision' ? 'background: #ff9800; color: white;' : 'background: var(--bg-light); color: var(--text-primary);'; ?>">
                        <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $revisionCount; ?></div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">✏️ Revision</div>
                    </a>
                    <a href="?is_active=rejected" class="filter-tab <?php echo $statusFilter == 'rejected' ? 'active' : ''; ?>" style="flex: 1; min-width: 150px; padding: 1rem; text-align: center; border-radius: var(--radius-md); text-decoration: none; transition: all 0.3s; <?php echo $statusFilter == 'rejected' ? 'background: #dc3545; color: white;' : 'background: var(--bg-light); color: var(--text-primary);'; ?>">
                        <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $rejectedCount; ?></div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">✗ Rejected</div>
                    </a>
                </div>
            </div>

            <!-- Questions List -->
            <div>
                <?php if($questions && $questions->num_rows > 0): ?>
                    <?php while($q = $questions->fetch_assoc()): ?>
                    <div style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); border-left: 4px solid var(--primary-color);">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <div>
                                <h3 style="margin: 0 0 0.5rem 0; color: var(--primary-color);">
                                    <?php echo $q['exam_name'] ?? 'Exam Question'; ?>
                                </h3>
                                <div style="font-size: 0.9rem; color: var(--text-secondary);">
                                    <strong>Course:</strong> <?php echo $q['course_name'] ?? 'N/A'; ?> | 
                                    <strong>Question ID:</strong> <?php echo $q['question_id']; ?>
                                </div>
                            </div>
                            <?php 
                            $status = $q['approval_status'] ?? 'pending';
                            $statusColors = [
                                'pending' => ['bg' => '#ffc107', 'text' => '#000', 'icon' => '⏳'],
                                'approved' => ['bg' => 'var(--success-color)', 'text' => 'white', 'icon' => '✓'],
                                'revision' => ['bg' => '#ff9800', 'text' => 'white', 'icon' => '✏️'],
                                'rejected' => ['bg' => '#dc3545', 'text' => 'white', 'icon' => '✗']
                            ];
                            $statusInfo = $statusColors[$status];
                            ?>
                            <span style="padding: 0.35rem 0.75rem; border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 700; background: <?php echo $statusInfo['bg']; ?>; color: <?php echo $statusInfo['text']; ?>;">
                                <?php echo $statusInfo['icon']; ?> <?php echo ucfirst($status); ?>
                            </span>
                        </div>
                        
                        <div style="padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md); margin: 1rem 0;">
                            <strong>Course:</strong> <?php echo htmlspecialchars($q['course_name'] ?? $q['course_code'] ?? 'N/A'); ?> | 
                            <strong>Question ID:</strong> <?php echo $q['question_id']; ?>
                            <?php if(isset($q['exam_id'])): ?>
                            | <strong>Exam ID:</strong> <?php echo $q['exam_id']; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                            <a href="ViewQuestion.php?id=<?php echo $q['question_id']; ?>" class="btn btn-primary btn-sm">
                                👁️ View Details
                            </a>
                            <button class="btn btn-success btn-sm" onclick="alert('Approval feature coming soon')">
                                ✓ Approve
                            </button>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 4rem; background: white; border-radius: var(--radius-lg);">
                        <h3 style="color: var(--text-secondary);">No questions found</h3>
                        <p>Questions will appear here when instructors submit them.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>
