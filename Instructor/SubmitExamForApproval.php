<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Instructor session
SessionManager::startSession('Instructor');

// Check if user is logged in
if(!isset($_SESSION['ID'])){
    header("Location: ../auth/institute-login.php");
    exit();
}

// Validate instructor role
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Instructor'){
    SessionManager::destroySession();
    header("Location: ../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$instructor_id = $_SESSION['ID'];
$exam_id = $_GET['exam_id'] ?? 0;

// Get exam details
$examQuery = $con->prepare("SELECT es.*, c.course_name,
    (SELECT COUNT(*) FROM exam_questions WHERE exam_id = es.exam_id) as question_count
    FROM exams es
    INNER JOIN courses c ON es.course_id = c.course_id
    WHERE es.exam_id = ? AND es.created_by = ?");
$examQuery->bind_param("ii", $exam_id, $instructor_id);
$examQuery->execute();
$exam = $examQuery->get_result()->fetch_assoc();

if(!$exam) {
    die("Exam not found or you don't have permission.");
}

if($exam['approval_status'] != 'draft') {
    die("This exam has already been submitted.");
}

if($exam['question_count'] == 0) {
    header("Location: ManageExamQuestions.php?exam_id=" . $exam_id . "&error=no_questions");
    exit();
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $comments = trim($_POST['comments'] ?? '');
    
    // Update exam status to pending
    $updateQuery = $con->prepare("UPDATE exams 
        SET approval_status = 'pending', submitted_at = NOW() 
        WHERE exam_id = ? AND created_by = ?");
    $updateQuery->bind_param("ii", $exam_id, $instructor_id);
    
    if($updateQuery->execute()) {
        // Log in approval history
        $historyQuery = $con->prepare("INSERT INTO exam_approval_history 
            (exam_id, action, performed_by, performed_by_type, comments, previous_status, new_status, created_at) 
            VALUES (?, 'submitted', ?, 'instructor', ?, 'draft', 'pending', NOW())");
        $historyQuery->bind_param("iis", $exam_id, $instructor_id, $comments);
        $historyQuery->execute();
        
        header("Location: MyExams.php?success=submitted");
        exit();
    } else {
        $error = "Failed to submit exam: " . $con->error;
    }
}

$pageTitle = "Submit Exam for Approval";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit for Approval - Instructor</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        .page-header-modern { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2); margin-bottom: 2rem; }
        .page-header-modern h1 { margin: 0 0 0.5rem 0; font-size: 2.2rem; font-weight: 800; color: white; }
        .page-header-modern p { margin: 0; opacity: 0.95; font-size: 1.05rem; color: white; }
        .card { background: white; border-radius: 12px; padding: 2.5rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); margin-bottom: 2rem; }
        .card h2 { margin: 0 0 1.5rem 0; color: #003366; font-size: 1.5rem; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin: 1.5rem 0; }
        .info-item { padding: 1rem; background: #f8f9fa; border-radius: 8px; }
        .info-label { font-size: 0.85rem; color: #6c757d; margin-bottom: 0.25rem; }
        .info-value { font-size: 1.1rem; font-weight: 700; color: #003366; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #003366; }
        .form-group textarea { width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 0.95rem; min-height: 120px; font-family: 'Poppins', sans-serif; }
        .btn { padding: 0.85rem 1.75rem; border-radius: 8px; font-weight: 600; font-size: 0.95rem; border: none; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-block; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-secondary { background: #6c757d; color: white; }
        .alert { padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header-modern">
                <h1>✅ Submit Exam for Approval</h1>
                <p>Review and submit your exam to the department head</p>
            </div>

            <?php if(isset($error)): ?>
            <div class="alert alert-danger">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="alert alert-info">
                ℹ️ Once submitted, your exam will be reviewed by the department head. You will be notified of the approval decision.
            </div>

            <div class="card">
                <h2>📋 Exam Summary</h2>
                
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="color: #003366; margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($exam['exam_name']); ?></h3>
                    <p style="color: #6c757d; margin: 0;"><?php echo htmlspecialchars($exam['course_name']); ?></p>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Duration</div>
                        <div class="info-value"><?php echo $exam['duration_minutes']; ?> minutes</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Total Marks</div>
                        <div class="info-value"><?php echo $exam['total_marks']; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Pass Marks</div>
                        <div class="info-value"><?php echo $exam['pass_marks']; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Questions</div>
                        <div class="info-value"><?php echo $exam['question_count']; ?></div>
                    </div>
                </div>

                <?php if($exam['instructions']): ?>
                <div style="margin-top: 1.5rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <div style="font-weight: 600; color: #003366; margin-bottom: 0.5rem;">Instructions:</div>
                    <div style="color: #555;"><?php echo nl2br(htmlspecialchars($exam['instructions'])); ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2>💬 Submit for Approval</h2>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Comments for Reviewer (Optional)</label>
                        <textarea name="comments" placeholder="Add any notes or comments for the department head..."></textarea>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-success">
                            ✅ Submit for Approval
                        </button>
                        <a href="ManageExamQuestions.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-secondary">
                            ← Back to Edit
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>
