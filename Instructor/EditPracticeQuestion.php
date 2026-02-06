<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Instructor session
SessionManager::startSession('Instructor');

// Check if user is logged in
if(!isset($_SESSION['ID'])){
    header("Location: ../auth/staff-login.php");
    exit();
}

// Validate instructor role
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Instructor'){
    SessionManager::destroySession();
    header("Location: ../auth/staff-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Edit Practice Question";

$practice_id = $_GET['id'] ?? 0;

// Get practice question details
$question = $con->query("SELECT pq.*, c.course_code, c.course_name 
                         FROM practice_questions pq
                         LEFT JOIN courses c ON pq.course_id = c.course_id
                         WHERE pq.practice_id = '$practice_id'")->fetch_assoc();

if(!$question) {
    header("Location: ManagePracticeQuestions.php");
    exit();
}

// Determine question type
$question_type = $question['question_type'] ?? 'multiple_choice';

// If question_type is not set, try to detect from options
if(empty($question_type) || $question_type == 'multiple_choice') {
    if(($question['option_a'] == 'True' && $question['option_b'] == 'False') || 
       ($question['correct_answer'] == 'True' || $question['correct_answer'] == 'False')) {
        $question_type = 'true_false';
    }
}

$is_true_false = ($question_type == 'true_false');

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'];
    $question_text = mysqli_real_escape_string($con, $_POST['question']);
    $question_type = $_POST['question_type'];
    $difficulty = $_POST['difficulty'];
    $correct_answer = $_POST['answer'];
    
    if($question_type == 'true_false') {
        $option_a = 'True';
        $option_b = 'False';
        $option_c = null;
        $option_d = null;
    } else {
        $option_a = mysqli_real_escape_string($con, $_POST['option1']);
        $option_b = mysqli_real_escape_string($con, $_POST['option2']);
        $option_c = mysqli_real_escape_string($con, $_POST['option3']);
        $option_d = mysqli_real_escape_string($con, $_POST['option4']);
    }
    
    $update = $con->prepare("UPDATE practice_questions 
                             SET course_id = ?, 
                                 question_text = ?,
                                 question_type = ?,
                                 option_a = ?, 
                                 option_b = ?, 
                                 option_c = ?, 
                                 option_d = ?, 
                                 correct_answer = ?,
                                 difficulty_level = ?,
                                 updated_at = NOW()
                             WHERE practice_id = ?");
    $update->bind_param("issssssssi", $course_id, $question_text, $question_type, $option_a, $option_b, $option_c, $option_d, $correct_answer, $difficulty, $practice_id);
    
    if($update->execute()) {
        header("Location: ManagePracticeQuestions.php?success=1");
        exit();
    }
    $update->close();
}

// Get courses assigned to this instructor
$instructor_id = $_SESSION['ID'];
$courses = $con->query("SELECT DISTINCT c.course_id, c.course_code, c.course_name
                        FROM courses c
                        INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
                        WHERE ic.instructor_id = $instructor_id
                        ORDER BY c.course_code");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Practice Question - Instructor</title>
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
                <h1>✏️ Edit Practice Question</h1>
                <p>UPDATE practice question details</p>
            </div>

            <div class="form-wrapper">
                <form method="POST">
                    <div class="form-section">
                        <h3 class="form-section-title">Question Details</h3>
                        
                        <div class="form-group">
                            <label>Practice Question ID</label>
                            <input type="text" class="form-control" value="<?php echo $question['practice_id']; ?>" disabled>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Course *</label>
                                <select name="course_id" class="form-control" required>
                                    <?php 
                                    if($courses && $courses->num_rows > 0) {
                                        while($course = $courses->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $course['course_id']; ?>" <?php echo (isset($question['course_id']) && $question['course_id'] == $course['course_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                    </option>
                                    <?php 
                                        endwhile;
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Question Type *</label>
                                <select name="question_type" id="questionType" class="form-control" required onchange="toggleQuestionType()">
                                    <option value="multiple_choice" <?php echo !$is_true_false ? 'selected' : ''; ?>>Multiple Choice (A, B, C, D)</option>
                                    <option value="true_false" <?php echo $is_true_false ? 'selected' : ''; ?>>True/False</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Difficulty Level *</label>
                            <select name="difficulty" class="form-control" required>
                                <option value="Easy" <?php echo ($question['difficulty_level'] ?? '') == 'Easy' ? 'selected' : ''; ?>>Easy</option>
                                <option value="Medium" <?php echo ($question['difficulty_level'] ?? '') == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="Hard" <?php echo ($question['difficulty_level'] ?? '') == 'Hard' ? 'selected' : ''; ?>>Hard</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Question Text *</label>
                            <textarea name="question" class="form-control" rows="4" required><?php echo htmlspecialchars($question['question_text'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="form-section" id="multipleChoiceOptions" style="display: <?php echo $is_true_false ? 'none' : 'block'; ?>;">
                        <h3 class="form-section-title">Answer Options (Multiple Choice)</h3>
                        
                        <div class="form-group">
                            <label>Option A *</label>
                            <input type="text" name="option1" class="form-control" value="<?php echo htmlspecialchars($question['option_a'] ?? ''); ?>" <?php echo $is_true_false ? '' : 'required'; ?>>
                        </div>
                        
                        <div class="form-group">
                            <label>Option B *</label>
                            <input type="text" name="option2" class="form-control" value="<?php echo htmlspecialchars($question['option_b'] ?? ''); ?>" <?php echo $is_true_false ? '' : 'required'; ?>>
                        </div>
                        
                        <div class="form-group">
                            <label>Option C *</label>
                            <input type="text" name="option3" class="form-control" value="<?php echo htmlspecialchars($question['option_c'] ?? ''); ?>" <?php echo $is_true_false ? '' : 'required'; ?>>
                        </div>
                        
                        <div class="form-group">
                            <label>Option D *</label>
                            <input type="text" name="option4" class="form-control" value="<?php echo htmlspecialchars($question['option_d'] ?? ''); ?>" <?php echo $is_true_false ? '' : 'required'; ?>>
                        </div>
                        
                        <div class="form-group">
                            <label>Correct Answer *</label>
                            <select name="answer" id="mcAnswer" class="form-control" <?php echo $is_true_false ? '' : 'required'; ?>>
                                <option value="">Select Correct Answer</option>
                                <option value="A" <?php echo (($question['correct_answer'] ?? '') == 'A') ? 'selected' : ''; ?>>Option A</option>
                                <option value="B" <?php echo (($question['correct_answer'] ?? '') == 'B') ? 'selected' : ''; ?>>Option B</option>
                                <option value="C" <?php echo (($question['correct_answer'] ?? '') == 'C') ? 'selected' : ''; ?>>Option C</option>
                                <option value="D" <?php echo (($question['correct_answer'] ?? '') == 'D') ? 'selected' : ''; ?>>Option D</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-section" id="trueFalseOptions" style="display: <?php echo $is_true_false ? 'block' : 'none'; ?>;">
                        <h3 class="form-section-title">Answer Options (True/False)</h3>
                        
                        <div style="background: var(--bg-light); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                            <p style="margin: 0 0 1rem 0; color: var(--text-secondary);">
                                <strong>ℹ️ Note:</strong> For True/False questions, only two options are available: True and False
                            </p>
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <span style="padding: 0.75rem 1.5rem; background: white; border: 2px solid var(--border-color); border-radius: var(--radius-md); font-weight: 600;">✓ True</span>
                                <span style="padding: 0.75rem 1.5rem; background: white; border: 2px solid var(--border-color); border-radius: var(--radius-md); font-weight: 600;">✗ False</span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Correct Answer *</label>
                            <select name="answer" id="tfAnswer" class="form-control" <?php echo $is_true_false ? 'required' : ''; ?>>
                                <option value="">Select Correct Answer</option>
                                <option value="True" <?php echo (($question['correct_answer'] ?? '') == 'True') ? 'selected' : ''; ?>>✓ True</option>
                                <option value="False" <?php echo (($question['correct_answer'] ?? '') == 'False') ? 'selected' : ''; ?>>✗ False</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            💾 Save Changes
                        </button>
                        <a href="ManagePracticeQuestions.php" class="btn btn-secondary">
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
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <p style="font-size: 1.1rem; font-weight: 600; margin: 0; color: var(--primary-color); flex: 1;">
                                <?php echo htmlspecialchars($question['question_text'] ?? ''); ?>
                            </p>
                            <div style="display: flex; gap: 0.5rem; margin-left: 1rem;">
                                <span style="background: var(--primary-color); color: white; padding: 0.25rem 0.75rem; border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 700; white-space: nowrap;">
                                    <?php echo $is_true_false ? 'TRUE/FALSE' : 'MULTIPLE CHOICE'; ?>
                                </span>
                                <span style="background: <?php 
                                    echo ($question['difficulty_level'] ?? 'Medium') == 'Easy' ? '#28a745' : 
                                        (($question['difficulty_level'] ?? 'Medium') == 'Hard' ? '#dc3545' : '#ffc107'); 
                                ?>; color: white; padding: 0.25rem 0.75rem; border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 700; white-space: nowrap;">
                                    <?php echo strtoupper($question['difficulty_level'] ?? 'MEDIUM'); ?>
                                </span>
                            </div>
                        </div>
                        <div style="margin-left: 1rem;">
                            <?php if($is_true_false): ?>
                                <p style="margin: 0.5rem 0;"><strong>✓</strong> True <?php if(($question['correct_answer'] ?? '') == 'True') echo '<span style="color: var(--success-color); font-weight: 700;"> ← Correct Answer</span>'; ?></p>
                                <p style="margin: 0.5rem 0;"><strong>✗</strong> False <?php if(($question['correct_answer'] ?? '') == 'False') echo '<span style="color: var(--success-color); font-weight: 700;"> ← Correct Answer</span>'; ?></p>
                            <?php else: ?>
                                <p style="margin: 0.5rem 0;"><strong>A.</strong> <?php echo htmlspecialchars($question['option_a'] ?? ''); ?> <?php if(($question['correct_answer'] ?? '') == 'A') echo '<span style="color: var(--success-color); font-weight: 700;">✓ Correct</span>'; ?></p>
                                <p style="margin: 0.5rem 0;"><strong>B.</strong> <?php echo htmlspecialchars($question['option_b'] ?? ''); ?> <?php if(($question['correct_answer'] ?? '') == 'B') echo '<span style="color: var(--success-color); font-weight: 700;">✓ Correct</span>'; ?></p>
                                <p style="margin: 0.5rem 0;"><strong>C.</strong> <?php echo htmlspecialchars($question['option_c'] ?? ''); ?> <?php if(($question['correct_answer'] ?? '') == 'C') echo '<span style="color: var(--success-color); font-weight: 700;">✓ Correct</span>'; ?></p>
                                <p style="margin: 0.5rem 0;"><strong>D.</strong> <?php echo htmlspecialchars($question['option_d'] ?? ''); ?> <?php if(($question['correct_answer'] ?? '') == 'D') echo '<span style="color: var(--success-color); font-weight: 700;">✓ Correct</span>'; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function toggleQuestionType() {
            const questionType = document.getElementById('questionType').value;
            const mcOptions = document.getElementById('multipleChoiceOptions');
            const tfOptions = document.getElementById('trueFalseOptions');
            const mcAnswer = document.getElementById('mcAnswer');
            const tfAnswer = document.getElementById('tfAnswer');
            
            if(questionType === 'true_false') {
                mcOptions.style.display = 'none';
                tfOptions.style.display = 'block';
                
                // Remove required from MC fields
                mcOptions.querySelectorAll('input, select').forEach(el => {
                    el.removeAttribute('required');
                });
                
                // Add required to TF fields
                tfAnswer.setAttribute('required', 'required');
            } else {
                mcOptions.style.display = 'block';
                tfOptions.style.display = 'none';
                
                // Add required to MC fields
                mcOptions.querySelectorAll('input[name^="option"], #mcAnswer').forEach(el => {
                    el.setAttribute('required', 'required');
                });
                
                // Remove required from TF fields
                tfAnswer.removeAttribute('required');
            }
        }
        
        function deleteQuestion() {
            if(confirm('Are you sure you want to delete this practice question?')) {
                window.location.href = 'DeletePracticeQuestion.php?id=<?php echo $practice_id; ?>';
            }
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
