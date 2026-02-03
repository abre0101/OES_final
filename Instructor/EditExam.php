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
$pageTitle = "Edit Exam";
$instructor_id = $_SESSION['ID'];

$exam_id = $_GET['id'] ?? 0;
$message = '';
$messageType = '';

// Get exam details with validation
$examQuery = $con->prepare("SELECT 
    es.*,
    c.course_name,
    c.course_code,
    ec.category_name,
    (SELECT COUNT(*) FROM exam_results WHERE exam_id = es.exam_id) as result_count
    FROM exams es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    WHERE es.exam_id = ? AND es.created_by = ?");
$examQuery->bind_param("ii", $exam_id, $instructor_id);
$examQuery->execute();
$exam = $examQuery->get_result()->fetch_assoc();

if(!$exam) {
    header("Location: MyExams.php");
    exit();
}

// Check if exam has been taken
if($exam['result_count'] > 0) {
    header("Location: MyExams.php?error=exam_taken");
    exit();
}

// Get courses for this instructor
$coursesQuery = $con->prepare("SELECT course_id, course_name, course_code FROM courses WHERE instructor_id = ?");
$coursesQuery->bind_param("i", $instructor_id);
$coursesQuery->execute();
$courses = $coursesQuery->get_result();

// Get exam categories
$categories = $con->query("SELECT * FROM exam_categories ORDER BY category_name");

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $exam_name = mysqli_real_escape_string($con, $_POST['exam_name']);
    $course_id = $_POST['course_id'];
    $category_id = $_POST['exam_category_id'];
    $duration = $_POST['duration_minutes'];
    $total_marks = $_POST['total_marks'];
    $passing_marks = $_POST['passing_marks'];
    $instructions = mysqli_real_escape_string($con, $_POST['instructions']);
    $randomize_questions = isset($_POST['randomize_questions']) ? 1 : 0;
    $show_results_immediately = isset($_POST['show_results_immediately']) ? 1 : 0;
    
    // If exam was approved or pending, reset to pending for re-approval
    // If it was draft, keep it as draft
    $new_status = ($exam['approval_status'] == 'approved' || $exam['approval_status'] == 'pending') ? 'pending' : 'draft';
    
    // Track revision count if it was approved
    $revision_increment = ($exam['approval_status'] == 'approved') ? ", revision_count = revision_count + 1" : "";
    
    // If changing from approved to pending, clear approval details and unpublish
    $clear_approval = ($exam['approval_status'] == 'approved') ? ", approval_comments = NULL, approved_by = NULL, approved_at = NULL, is_active = 0, exam_date = NULL, start_time = NULL, end_time = NULL" : "";
    
    $updateQuery = "UPDATE exams 
                    SET exam_name = ?,
                        course_id = ?,
                        exam_category_id = ?,
                        duration_minutes = ?,
                        total_marks = ?,
                        passing_marks = ?,
                        instructions = ?,
                        randomize_questions = ?,
                        show_results_immediately = ?,
                        approval_status = ?,
                        updated_at = NOW()
                        $revision_increment
                        $clear_approval
                    WHERE exam_id = ? AND created_by = ?";
    
    $stmt = $con->prepare($updateQuery);
    $stmt->bind_param("siiiissiisii", 
        $exam_name, $course_id, $category_id, $duration, $total_marks, 
        $passing_marks, $instructions, $randomize_questions, 
        $show_results_immediately, $new_status, $exam_id, $instructor_id);
    
    if($stmt->execute()) {
        // Log the edit in exam history
        $action = ($exam['approval_status'] == 'approved') ? 'edited_after_approval' : 'edited';
        $historyQuery = $con->prepare("INSERT INTO exam_history (exam_id, action, performed_by, performed_at, notes) 
                                       VALUES (?, ?, ?, NOW(), 'Exam details updated')");
        $historyQuery->bind_param("isi", $exam_id, $action, $instructor_id);
        $historyQuery->execute();
        
        if($exam['approval_status'] == 'approved') {
            header("Location: MyExams.php?success=edited_reapproval");
        } else {
            header("Location: MyExams.php?success=edited");
        }
        exit();
    } else {
        $message = "Error updating exam: " . $con->error;
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Exam - Instructor</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .alert {
            padding: 1.25rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header">
                <h1>✏️ Edit Exam</h1>
                <p>Update exam details for: <strong><?php echo htmlspecialchars($exam['exam_name']); ?></strong></p>
            </div>

            <?php if($message): ?>
            <div class="alert alert-<?php echo $messageType == 'success' ? 'success' : 'danger'; ?>">
                <span style="font-size: 1.5rem;"><?php echo $messageType == 'success' ? '✅' : '❌'; ?></span>
                <span><?php echo $message; ?></span>
            </div>
            <?php endif; ?>

            <?php if($exam['approval_status'] == 'approved'): ?>
            <div class="alert alert-warning">
                <span style="font-size: 1.5rem;">⚠️</span>
                <div>
                    <strong>Re-Approval Required</strong><br>
                    This exam is currently approved. Any changes will reset its status to "Pending" and require re-approval from the department head. The exam will also be unpublished and unscheduled.
                </div>
            </div>
            <?php elseif($exam['approval_status'] == 'pending'): ?>
            <div class="alert alert-warning">
                <span style="font-size: 1.5rem;">⏳</span>
                <div>
                    <strong>Pending Approval</strong><br>
                    This exam is awaiting approval. You can still make changes, but it will remain in pending status.
                </div>
            </div>
            <?php endif; ?>

            <div class="form-wrapper">
                <form method="POST">
                    <div class="form-section">
                        <h3 class="form-section-title">Basic Information</h3>
                        
                        <div class="form-group">
                            <label>Exam Name *</label>
                            <input type="text" name="exam_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($exam['exam_name']); ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Course *</label>
                                <select name="course_id" class="form-control" required>
                                    <?php 
                                    $courses->data_seek(0);
                                    while($course = $courses->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $course['course_id']; ?>" 
                                            <?php echo ($course['course_id'] == $exam['course_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Exam Category *</label>
                                <select name="exam_category_id" class="form-control" required>
                                    <?php 
                                    $categories->data_seek(0);
                                    while($category = $categories->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $category['exam_category_id']; ?>"
                                            <?php echo ($category['exam_category_id'] == $exam['exam_category_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Exam Settings</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Duration (minutes) *</label>
                                <input type="number" name="duration_minutes" class="form-control" 
                                       value="<?php echo $exam['duration_minutes']; ?>" required min="1">
                            </div>
                            
                            <div class="form-group">
                                <label>Total Marks *</label>
                                <input type="number" name="total_marks" class="form-control" 
                                       value="<?php echo $exam['total_marks']; ?>" required min="1">
                            </div>
                            
                            <div class="form-group">
                                <label>Passing Marks *</label>
                                <input type="number" name="passing_marks" class="form-control" 
                                       value="<?php echo $exam['passing_marks']; ?>" required min="1">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Instructions</label>
                            <textarea name="instructions" class="form-control" rows="4" 
                                      placeholder="Enter exam instructions..."><?php echo htmlspecialchars($exam['instructions']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="checkbox" name="randomize_questions" 
                                       <?php echo $exam['randomize_questions'] ? 'checked' : ''; ?>>
                                <span>Randomize question order for each student</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="checkbox" name="show_results_immediately" 
                                       <?php echo $exam['show_results_immediately'] ? 'checked' : ''; ?>>
                                <span>Show results immediately after submission</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            💾 Save Changes
                        </button>
                        <a href="MyExams.php" class="btn btn-secondary">
                            ← Back to My Exams
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
