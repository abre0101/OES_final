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
$pageTitle = "Report Technical Issue";

$message = '';
$messageType = '';

// Handle issue submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_issue'])) {
    $issue_type = mysqli_real_escape_string($con, $_POST['issue_type']);
    $exam_id = !empty($_POST['exam_id']) ? intval($_POST['exam_id']) : null;
    $priority = mysqli_real_escape_string($con, $_POST['priority']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $reported_by = $_SESSION['Name'];
    $reporter_role = 'DepartmentHead';
    
    // Handle screenshot upload
    $screenshot_path = null;
    if(isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] == 0) {
        $upload_dir = '../uploads/issue_screenshots/';
        if(!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['screenshot']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if(in_array($file_ext, $allowed_ext) && $_FILES['screenshot']['size'] <= 5000000) {
            $filename = 'issue_' . time() . '_' . uniqid() . '.' . $file_ext;
            $screenshot_path = $upload_dir . $filename;
            move_uploaded_file($_FILES['screenshot']['tmp_name'], $screenshot_path);
        }
    }
    
    $insert_query = "INSERT INTO technical_issues (issue_type, exam_id, priority, description, screenshot_path, 
                     reported_by, reporter_role, status, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'open', NOW())";
    $stmt = $con->prepare($insert_query);
    $stmt->bind_param("sisssss", $issue_type, $exam_id, $priority, $description, $screenshot_path, 
                      $reported_by, $reporter_role);
    
    if($stmt->execute()) {
        $message = "Issue reported successfully! Our technical team will review it shortly.";
        $messageType = "success";
    } else {
        $message = "Error reporting issue: " . $con->error;
        $messageType = "error";
    }
}

// Get recent exams for dropdown
$deptId = $_SESSION['DeptId'] ?? null;
$exams_query = "SELECT es.exam_id, es.exam_name, c.course_code 
                FROM exams es
                LEFT JOIN courses c ON es.course_id = c.course_id
                WHERE c.department_id = ? AND es.is_active = 1
                ORDER BY es.exam_date DESC LIMIT 50";
$stmt = $con->prepare($exams_query);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$exams = $stmt->get_result();

// Get recent issues
$issues_query = "SELECT * FROM technical_issues 
                 WHERE reporter_role = 'DepartmentHead' AND reported_by = ?
                 ORDER BY created_at DESC LIMIT 10";
$stmt = $con->prepare($issues_query);
$stmt->bind_param("s", $_SESSION['Name']);
$stmt->execute();
$recent_issues = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Technical Issue - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>
    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>
        <div class="admin-content">
            <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: var(--primary-color); font-weight: 700;">Report Technical Issue</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 1.05rem;">
                    Report technical problems or system issues for quick resolution
                </p>
            </div>

            <?php if($message): ?>
            <div class="alert alert-<?php echo $messageType == 'success' ? 'success' : 'danger'; ?>" style="margin-bottom: 1.5rem;">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Report Form -->
                <div class="card">
                    <div class="card-header">
                        <h3>🐛 Submit New Issue</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Issue Type *</label>
                                <select name="issue_type" class="form-control" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="exam_access">Exam Access Problem</option>
                                    <option value="scheduling">Scheduling Issue</option>
                                    <option value="results">Results/Grading Issue</option>
                                    <option value="student_account">Student Account Issue</option>
                                    <option value="instructor_account">Instructor Account Issue</option>
                                    <option value="system_error">System Error</option>
                                    <option value="performance">Performance/Speed Issue</option>
                                    <option value="data_loss">Data Loss/Corruption</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Related Exam (Optional)</label>
                                <select name="exam_id" class="form-control">
                                    <option value="">-- Not related to specific exam --</option>
                                    <?php while($exam = $exams->fetch_assoc()): ?>
                                    <option value="<?php echo $exam['exam_id']; ?>">
                                        <?php echo htmlspecialchars($exam['course_code'] . ' - ' . $exam['exam_name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Priority *</label>
                                <select name="priority" class="form-control" required>
                                    <option value="low">🟢 Low - Minor inconvenience</option>
                                    <option value="medium" selected>🟡 Medium - Affects workflow</option>
                                    <option value="high">🟠 High - Significant impact</option>
                                    <option value="critical">🔴 Critical - System down/data loss</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Description *</label>
                                <textarea name="description" class="form-control" rows="6" required 
                                          placeholder="Please describe the issue in detail:&#10;- What were you trying to do?&#10;- What happened?&#10;- What did you expect to happen?&#10;- Any error messages?"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Screenshot (Optional)</label>
                                <input type="file" name="screenshot" class="form-control" accept="image/*">
                                <small class="form-text text-muted">Max 5MB. Formats: JPG, PNG, GIF</small>
                            </div>

                            <button type="submit" name="submit_issue" class="btn btn-primary btn-block">
                                📤 Submit Issue Report
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Recent Issues -->
                <div class="card">
                    <div class="card-header">
                        <h3>📋 Your Recent Issues</h3>
                    </div>
                    <div class="card-body">
                        <?php if($recent_issues->num_rows > 0): ?>
                        <div style="max-height: 600px; overflow-y: auto;">
                            <?php while($issue = $recent_issues->fetch_assoc()): 
                                $status_colors = [
                                    'open' => '#ffc107',
                                    'in_progress' => '#17a2b8',
                                    'resolved' => '#28a745',
                                    'closed' => '#6c757d'
                                ];
                                $status_color = $status_colors[$issue['status']] ?? '#6c757d';
                            ?>
                            <div style="border: 1px solid #e9ecef; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <strong style="color: var(--primary-color);"><?php echo htmlspecialchars($issue['issue_type']); ?></strong>
                                    <span style="background: <?php echo $status_color; ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">
                                        <?php echo strtoupper($issue['status']); ?>
                                    </span>
                                </div>
                                <p style="margin: 0.5rem 0; font-size: 0.9rem; color: #495057;">
                                    <?php echo htmlspecialchars(substr($issue['description'], 0, 100)) . (strlen($issue['description']) > 100 ? '...' : ''); ?>
                                </p>
                                <small style="color: #6c757d;">
                                    Reported: <?php echo date('M d, Y h:i A', strtotime($issue['created_at'])); ?>
                                </small>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <p>No issues reported yet.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>

