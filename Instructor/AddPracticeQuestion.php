<?php
session_start();
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$instructor_id = $_SESSION['ID'];

// Get instructor's courses only
$coursesQuery = $con->prepare("SELECT DISTINCT c.course_id, c.course_name, c.course_code
    FROM courses c
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE
    ORDER BY c.course_name");
$coursesQuery->bind_param("i", $instructor_id);
$coursesQuery->execute();
$instructorCourses = $coursesQuery->get_result();
$coursesQuery->close();

$message = '';
$messageType = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_practice_question'])) {
    $course_id = $_POST['course_id'];
    $question_text = $_POST['question_text'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = $_POST['option_c'];
    $option_d = $_POST['option_d'];
    $correct_answer = $_POST['correct_answer'];
    $explanation = $_POST['explanation'];
    $difficulty_level = $_POST['difficulty_level'];
    $topic = $_POST['topic'];
    $points = $_POST['points'];
    
    // Verify instructor teaches this course
    $verifyStmt = $con->prepare("SELECT COUNT(*) as count FROM instructor_courses 
                                  WHERE instructor_id = ? AND course_id = ? AND is_active = TRUE");
    $verifyStmt->bind_param("ii", $instructor_id, $course_id);
    $verifyStmt->execute();
    $canAdd = $verifyStmt->get_result()->fetch_assoc()['count'] > 0;
    $verifyStmt->close();
    
    if($canAdd) {
        $stmt = $con->prepare("INSERT INTO practice_questions 
                              (course_id, question_text, question_type, option_a, option_b, option_c, option_d, 
                               correct_answer, explanation, difficulty_level, topic, points, created_by)
                              VALUES (?, ?, 'multiple_choice', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssssssii", $course_id, $question_text, $option_a, $option_b, $option_c, $option_d,
                         $correct_answer, $explanation, $difficulty_level, $topic, $points, $instructor_id);
        
        if($stmt->execute()) {
            $message = 'Practice question added successfully! Students can now practice with this question.';
            $messageType = 'success';
            // Clear form
            $_POST = array();
        } else {
            $message = 'Error adding practice question: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    } else {
        $message = 'Error: You can only add practice questions for courses you teach!';
        $messageType = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Practice Question - Instructor Dashboard</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <!-- Page Header -->
            <div class="page-header-actions">
                <div class="page-title-section">
                    <h1><span>➕</span> Add Practice Question</h1>
                    <p>Create a new practice question for your students</p>
                </div>
                <div class="header-actions-group">
                    <a href="ManagePracticeQuestions.php" class="btn btn-secondary">
                        <span>← Back to Practice Questions</span>
                    </a>
                </div>
            </div>

            <?php if($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 2rem;">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <!-- Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📝 Question Details</h3>
                </div>
                <div style="padding: 2rem;">
                    <form method="POST">
                        <div class="form-group">
                            <label>Course *</label>
                            <select name="course_id" class="form-control" required>
                                <option value="">-- Select Your Course --</option>
                                <?php while($course = $instructorCourses->fetch_assoc()): ?>
                                <option value="<?php echo $course['course_id']; ?>">
                                    <?php echo $course['course_name']; ?> (<?php echo $course['course_code']; ?>)
                                </option>
                                <?php endwhile; ?>
                            </select>
                            <small>You can only add practice questions for courses you teach</small>
                        </div>

                        <div class="form-group">
                            <label>Question Text *</label>
                            <textarea name="question_text" class="form-control" rows="4" required 
                                      placeholder="Enter the question text..."></textarea>
                        </div>

                        <div class="grid grid-2">
                            <div class="form-group">
                                <label>Option A *</label>
                                <input type="text" name="option_a" class="form-control" required 
                                       placeholder="First option">
                            </div>
                            <div class="form-group">
                                <label>Option B *</label>
                                <input type="text" name="option_b" class="form-control" required 
                                       placeholder="Second option">
                            </div>
                            <div class="form-group">
                                <label>Option C *</label>
                                <input type="text" name="option_c" class="form-control" required 
                                       placeholder="Third option">
                            </div>
                            <div class="form-group">
                                <label>Option D *</label>
                                <input type="text" name="option_d" class="form-control" required 
                                       placeholder="Fourth option">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Correct Answer *</label>
                            <select name="correct_answer" class="form-control" required>
                                <option value="">-- Select Correct Answer --</option>
                                <option value="option_a">Option A</option>
                                <option value="option_b">Option B</option>
                                <option value="option_c">Option C</option>
                                <option value="option_d">Option D</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Explanation (Optional but Recommended)</label>
                            <textarea name="explanation" class="form-control" rows="3" 
                                      placeholder="Explain why this is the correct answer. This helps students learn!"></textarea>
                            <small>Adding an explanation helps students understand the concept better</small>
                        </div>

                        <div class="grid grid-3">
                            <div class="form-group">
                                <label>Difficulty Level *</label>
                                <select name="difficulty_level" class="form-control" required>
                                    <option value="easy">Easy</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Topic (Optional)</label>
                                <input type="text" name="topic" class="form-control" 
                                       placeholder="e.g., Vital Signs, Anatomy">
                            </div>
                            <div class="form-group">
                                <label>Points *</label>
                                <input type="number" name="points" class="form-control" value="1" min="1" required>
                            </div>
                        </div>

                        <div style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 197, 253, 0.1) 100%); 
                                    padding: 1.5rem; border-radius: 15px; margin: 2rem 0; border-left: 4px solid #3b82f6;">
                            <strong style="color: #1a2b4a;">💡 Tips for Good Practice Questions:</strong>
                            <ul style="margin: 0.75rem 0 0 1.5rem; color: #6c757d; line-height: 1.8;">
                                <li>Make questions clear and unambiguous</li>
                                <li>Ensure all options are plausible</li>
                                <li>Add explanations to help students learn</li>
                                <li>Cover important concepts from your course</li>
                                <li>Start with easier questions and progress to harder ones</li>
                            </ul>
                        </div>

                        <div class="form-actions" style="display: flex; gap: 1rem;">
                            <button type="submit" name="add_practice_question" class="btn btn-primary" style="flex: 1;">
                                ✅ Add Practice Question
                            </button>
                            <a href="ManagePracticeQuestions.php" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>
