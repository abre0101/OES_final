<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
$pageTitle = "Edit Question";

$question_id = $_GET['id'] ?? 0;

// Get question details
$question = $con->query("SELECT * FROM question_page WHERE question_id = '$question_id'")->fetch_assoc();

if(!$question) {
    header("Location: ManageQuestions.php");
    exit();
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $exam_id = $_POST['exam_id'];
    $semester = $_POST['semester'];
    $course_name = $_POST['course_name'];
    $question_text = $_POST['question'];
    $option1 = $_POST['option1'];
    $option2 = $_POST['option2'];
    $option3 = $_POST['option3'];
    $option4 = $_POST['option4'];
    $answer = $_POST['answer'];
    
    $update = $con->prepare("UPDATE question_page SET exam_id=?, semester=?, course_name=?, question=?, Option1=?, Option2=?, Option3=?, Option4=?, Answer=? WHERE question_id=?");
    $update->bind_param("iisssssssi", $exam_id, $semester, $course_name, $question_text, $option1, $option2, $option3, $option4, $answer, $question_id);
    
    if($update->execute()) {
        header("Location: ManageQuestions.php?success=1");
        exit();
    }
    $update->close();
}

// Get exam categories
$exams = null;
$tableCheck = $con->query("SHOW TABLES LIKE 'exam_category'");
if($tableCheck && $tableCheck->num_rows > 0) {
    $exams = $con->query("SELECT * FROM exam_category");
}

// Get only courses assigned to this instructor
$instructor_id = $_SESSION['ID'];
$courses = null;
$tableCheck = $con->query("SHOW TABLES LIKE 'course'");
if($tableCheck && $tableCheck->num_rows > 0) {
    $icTableCheck = $con->query("SHOW TABLES LIKE 'instructor_courses'");
    if($icTableCheck && $icTableCheck->num_rows > 0) {
        $courses = $con->query("SELECT c.* FROM course c 
            INNER JOIN instructor_courses ic ON c.course_id = ic.course_id 
            WHERE ic.instructor_id = $instructor_id 
            ORDER BY c.course_name");
    } else {
        $courses = $con->query("SELECT * FROM course ORDER BY course_name");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Question - Instructor</title>
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
                <h1>✏️ Edit Question</h1>
                <p>UPDATE questions details</p>
            </div>

            <div class="form-wrapper">
                <form method="POST">
                    <div class="form-section">
                        <h3 class="form-section-title">Question Details</h3>
                        
                        <div class="form-group">
                            <label>Question ID</label>
                            <input type="text" class="form-control" value="<?php echo $question['question_id']; ?>" disabled>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Exam Type *</label>
                                <select name="exam_id" class="form-control" required>
                                    <option value="">Select Exam</option>
                                    <?php 
                                    if($exams && $exams->num_rows > 0) {
                                        while($exam = $exams->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $exam['exam_id']; ?>" <?php echo ($question['exam_id'] == $exam['exam_id']) ? 'selected' : ''; ?>>
                                        <?php echo $exam['exam_name']; ?>
                                    </option>
                                    <?php 
                                        endwhile;
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Course Code *</label>
                                <select name="course_name" class="form-control" required>
                                    <option value="">Select Course</option>
                                    <?php 
                                    if($courses && $courses->num_rows > 0) {
                                        while($course = $courses->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $course['course_name']; ?>" <?php echo ($question['course_name'] == $course['course_name']) ? 'selected' : ''; ?>>
                                        <?php echo $course['course_name']; ?> - <?php echo $course['course_id']; ?>
                                    </option>
                                    <?php 
                                        endwhile;
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Semester *</label>
                                <select name="semester" class="form-control" required>
                                    <option value="1" <?php echo ($question['semester'] == 1) ? 'selected' : ''; ?>>Semester 1</option>
                                    <option value="2" <?php echo ($question['semester'] == 2) ? 'selected' : ''; ?>>Semester 2</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Question Text *</label>
                            <textarea name="question" class="form-control" rows="4" required><?php echo htmlspecialchars($question['question']); ?></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Answer Options</h3>
                        
                        <div class="form-group">
                            <label>Option A *</label>
                            <input type="text" name="option1" class="form-control" value="<?php echo htmlspecialchars($question['Option1']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Option B *</label>
                            <input type="text" name="option2" class="form-control" value="<?php echo htmlspecialchars($question['Option2']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Option C *</label>
                            <input type="text" name="option3" class="form-control" value="<?php echo htmlspecialchars($question['Option3']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Option D *</label>
                            <input type="text" name="option4" class="form-control" value="<?php echo htmlspecialchars($question['Option4']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Correct Answer *</label>
                            <select name="answer" class="form-control" required>
                                <option value="">Select Correct Answer</option>
                                <option value="A" <?php echo ($question['Answer'] == 'A') ? 'selected' : ''; ?>>Option A</option>
                                <option value="B" <?php echo ($question['Answer'] == 'B') ? 'selected' : ''; ?>>Option B</option>
                                <option value="C" <?php echo ($question['Answer'] == 'C') ? 'selected' : ''; ?>>Option C</option>
                                <option value="D" <?php echo ($question['Answer'] == 'D') ? 'selected' : ''; ?>>Option D</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            💾 Save Changes
                        </button>
                        <a href="ManageQuestions.php" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="button" class="btn btn-danger" onclick="deleteQuestion()" style="margin-left: auto;">
                            🗑️ Delete Question
                        </button>
                    </div>
                </form>
            </div>

            <!-- Preview Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">👁️ Question Preview</h3>
                </div>
                <div style="padding: 2rem;">
                    <div style="background: var(--bg-light); padding: 1.5rem; border-radius: var(--radius-md);">
                        <p style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem; color: var(--primary-color);">
                            <?php echo htmlspecialchars($question['question']); ?>
                        </p>
                        <div style="margin-left: 1rem;">
                            <p style="margin: 0.5rem 0;"><strong>A.</strong> <?php echo htmlspecialchars($question['Option1']); ?> <?php if($question['Answer'] == 'A') echo '<span style="color: var(--success-color); font-weight: 700;">✓</span>'; ?></p>
                            <p style="margin: 0.5rem 0;"><strong>B.</strong> <?php echo htmlspecialchars($question['Option2']); ?> <?php if($question['Answer'] == 'B') echo '<span style="color: var(--success-color); font-weight: 700;">✓</span>'; ?></p>
                            <p style="margin: 0.5rem 0;"><strong>C.</strong> <?php echo htmlspecialchars($question['Option3']); ?> <?php if($question['Answer'] == 'C') echo '<span style="color: var(--success-color); font-weight: 700;">✓</span>'; ?></p>
                            <p style="margin: 0.5rem 0;"><strong>D.</strong> <?php echo htmlspecialchars($question['Option4']); ?> <?php if($question['Answer'] == 'D') echo '<span style="color: var(--success-color); font-weight: 700;">✓</span>'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function deleteQuestion() {
            if(confirm('Are you sure you want to delete this question?')) {
                window.location.href = 'DeleteQuestion.php?id=<?php echo $question_id; ?>';
            }
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
