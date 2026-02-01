<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
$pageTitle = "Add New Question";
$instructor_id = $_SESSION['ID'];

// Get URL parameters for pre-filling
$preselect_schedule_id = $_GET['schedule_id'] ?? null;
$preselect_course_id = $_GET['course_id'] ?? null;

// Get pre-selected exam details if schedule_id is provided
$preselectedExam = null;
if($preselect_schedule_id) {
    $examQuery = $con->prepare("SELECT es.*, c.course_id, c.course_name, c.course_code, ec.exam_category_id, ec.category_name
        FROM exam_schedules es
        INNER JOIN courses c ON es.course_id = c.course_id
        INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
        WHERE es.schedule_id = ?");
    $examQuery->bind_param("i", $preselect_schedule_id);
    $examQuery->execute();
    $preselectedExam = $examQuery->get_result()->fetch_assoc();
    $examQuery->close();
    
    // Check if exam is locked (submitted for approval)
    if($preselectedExam && $preselectedExam['approval_status'] != 'draft' && $preselectedExam['approval_status'] != 'revision') {
        $_SESSION['error'] = "Cannot add questions to an exam that has been submitted for approval.";
        header("Location: ViewExam.php?id=" . $preselect_schedule_id);
        exit();
    }
    
    // Set course_id from exam if not already set
    if($preselectedExam && !$preselect_course_id) {
        $preselect_course_id = $preselectedExam['course_id'];
    }
}

// Get instructor's courses
$coursesQuery = $con->prepare("SELECT DISTINCT c.course_id, c.course_code, c.course_name, c.semester
    FROM instructor_courses ic
    INNER JOIN courses c ON ic.course_id = c.course_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE
    ORDER BY c.course_name");
$coursesQuery->bind_param("i", $instructor_id);
$coursesQuery->execute();
$courses = $coursesQuery->get_result();

// Get exam categories
$examCategories = $con->query("SELECT * FROM exam_categories WHERE is_active = TRUE ORDER BY category_name");

// Get exam schedules for instructor's courses
$examsQuery = $con->prepare("SELECT es.schedule_id, es.exam_name, c.course_name, c.course_code, ec.category_name
    FROM exam_schedules es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE AND es.is_active = TRUE
    ORDER BY es.exam_date DESC");
$examsQuery->bind_param("i", $instructor_id);
$examsQuery->execute();
$exams = $examsQuery->get_result();

// Get topics for instructor's courses
$topicsQuery = $con->prepare("SELECT qt.topic_id, qt.topic_name, qt.chapter_number, c.course_id, c.course_name, c.course_code
    FROM question_topics qt
    INNER JOIN courses c ON qt.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE
    ORDER BY c.course_name, qt.chapter_number, qt.topic_name");
$topicsQuery->bind_param("i", $instructor_id);
$topicsQuery->execute();
$topics = $topicsQuery->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Question - Instructor</title>
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
                <h1>➕ Add New Question</h1>
                <p>
                    <?php if($preselectedExam): ?>
                        Adding question to: <strong><?php echo htmlspecialchars($preselectedExam['exam_name']); ?></strong> - 
                        <?php echo htmlspecialchars($preselectedExam['course_name']); ?>
                    <?php else: ?>
                        Create a new exam question
                    <?php endif; ?>
                </p>
            </div>

            <?php if($preselectedExam): ?>
            <div style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); color: white; padding: 1.5rem; border-radius: var(--radius-lg); margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="font-size: 2.5rem;">📝</div>
                    <div>
                        <h3 style="margin: 0; color: white;">
                            <?php echo htmlspecialchars($preselectedExam['exam_name']); ?>
                        </h3>
                        <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">
                            <?php echo htmlspecialchars($preselectedExam['course_name']); ?> (<?php echo $preselectedExam['course_code']; ?>) - 
                            <?php echo htmlspecialchars($preselectedExam['category_name']); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-wrapper">
                <form method="POST" action="InsertQuestion.php">
                    <input type="hidden" name="instructor_id" value="<?php echo $instructor_id; ?>">
                    <?php if($preselect_schedule_id): ?>
                    <input type="hidden" name="schedule_id" value="<?php echo $preselect_schedule_id; ?>">
                    <?php endif; ?>
                    
                    <div class="form-section">
                        <h3 class="form-section-title">Question Details</h3>
                        
                        <?php if(!$preselectedExam): ?>
                        <!-- Show full form if no exam is preselected -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Course *</label>
                                <select name="course_id" id="courseSelect" class="form-control" required>
                                    <option value="">Select Course</option>
                                    <?php 
                                    $courses->data_seek(0);
                                    while($course = $courses->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $course['course_id']; ?>" 
                                        data-semester="<?php echo $course['semester']; ?>"
                                        <?php echo ($preselect_course_id == $course['course_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['course_name']); ?> (<?php echo $course['course_code']; ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Exam Category *</label>
                                <select name="exam_category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php 
                                    if($examCategories && $examCategories->num_rows > 0) {
                                        while($cat = $examCategories->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $cat['exam_category_id']; ?>">
                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                    </option>
                                    <?php endwhile; } ?>
                                </select>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Show read-only info if exam is preselected -->
                        <input type="hidden" name="course_id" value="<?php echo $preselectedExam['course_id']; ?>">
                        <input type="hidden" name="exam_category_id" value="<?php echo $preselectedExam['exam_category_id']; ?>">
                        
                        <div style="background: var(--bg-light); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border-left: 4px solid var(--primary-color);">
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                                <div>
                                    <strong style="color: var(--text-secondary); font-size: 0.9rem;">Course:</strong>
                                    <p style="margin: 0.25rem 0 0 0; color: var(--text-primary); font-weight: 600;">
                                        <?php echo htmlspecialchars($preselectedExam['course_name']); ?> (<?php echo $preselectedExam['course_code']; ?>)
                                    </p>
                                </div>
                                <div>
                                    <strong style="color: var(--text-secondary); font-size: 0.9rem;">Exam Category:</strong>
                                    <p style="margin: 0.25rem 0 0 0; color: var(--text-primary); font-weight: 600;">
                                        <?php echo htmlspecialchars($preselectedExam['category_name']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Topic/Chapter (Optional)</label>
                                <select name="topic_id" class="form-control" id="topicSelect">
                                    <option value="">-- No Topic --</option>
                                    <?php 
                                    if($topics->num_rows > 0) {
                                        while($topic = $topics->fetch_assoc()): 
                                            // Only show topics for the selected course
                                            $showTopic = !$preselect_course_id || ($topic['course_id'] == $preselect_course_id);
                                    ?>
                                    <option value="<?php echo $topic['topic_id']; ?>" 
                                        data-course-id="<?php echo $topic['course_id']; ?>"
                                        <?php echo !$showTopic ? 'style="display:none;"' : ''; ?>>
                                        <?php echo htmlspecialchars($topic['course_name']); ?> - 
                                        <?php if($topic['chapter_number']): ?>
                                            Ch.<?php echo $topic['chapter_number']; ?>: 
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($topic['topic_name']); ?>
                                    </option>
                                    <?php endwhile; } ?>
                                </select>
                                <small style="color: var(--text-secondary);">Organize questions by chapter/topic</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Question Text *</label>
                            <textarea name="question_text" class="form-control" rows="4" required placeholder="Enter your question here..." autofocus></textarea>
                        </div>
                        
                        <!-- Hidden field with default difficulty -->
                        <input type="hidden" name="difficulty_level" value="Medium">
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Answer Options</h3>
                        
                        <div class="form-group">
                            <label>Option A *</label>
                            <input type="text" name="option_a" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Option B *</label>
                            <input type="text" name="option_b" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Option C</label>
                            <input type="text" name="option_c" class="form-control">
                            <small style="color: var(--text-secondary);">Optional - leave blank if not needed</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Option D</label>
                            <input type="text" name="option_d" class="form-control">
                            <small style="color: var(--text-secondary);">Optional - leave blank if not needed</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Correct Answer *</label>
                            <select name="correct_answer" class="form-control" required>
                                <option value="">Select Correct Answer</option>
                                <option value="A">Option A</option>
                                <option value="B">Option B</option>
                                <option value="C">Option C</option>
                                <option value="D">Option D</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Point Value *</label>
                            <input type="number" name="point_value" class="form-control" min="1" max="100" value="1" required>
                            <small style="color: var(--text-secondary);">Points awarded for correct answer (1-100)</small>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            💾 Save Question
                        </button>
                        <button type="submit" name="save_and_add_another" value="1" class="btn btn-success">
                            💾 Save & Add Another
                        </button>
                        <a href="<?php echo $preselect_schedule_id ? 'ManageQuestions.php' : 'ManageQuestions.php'; ?>" class="btn btn-secondary">
                            Cancel
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
