<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
$pageTitle = "View Exam";
$instructor_id = $_SESSION['ID'];

$exam_id = $_GET['id'] ?? 0;

// Get exam schedule details
$examQuery = $con->prepare("SELECT 
    es.*,
    c.course_name,
    c.course_code,
    ec.category_name,
    d.department_name
    FROM exams es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    INNER JOIN departments d ON c.department_id = d.department_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE es.exam_id = ? AND ic.instructor_id = ? AND ic.is_active = TRUE");
$examQuery->bind_param("ii", $exam_id, $instructor_id);
$examQuery->execute();
$exam = $examQuery->get_result()->fetch_assoc();
$examQuery->close();

if(!$exam) {
    header("Location: ManageQuestions.php");
    exit();
}

// Get questions for this exam
$questionsQuery = $con->prepare("SELECT 
    q.*,
    qt.topic_name,
    eq.question_order
    FROM exam_questions eq
    INNER JOIN questions q ON eq.question_id = q.question_id
    LEFT JOIN question_topics qt ON q.topic_id = qt.topic_id
    WHERE eq.exam_id = ?
    ORDER BY eq.question_order");
$questionsQuery->bind_param("i", $exam_id);
$questionsQuery->execute();
$questions = $questionsQuery->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Exam - Instructor</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .question-preview {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--primary-color);
        }
        
        .option-preview {
            padding: 0.75rem;
            margin: 0.5rem 0;
            background: var(--bg-light);
            border-radius: var(--radius-md);
            border-left: 3px solid #e0e0e0;
        }
        
        .option-preview.correct {
            border-left-color: var(--success-color);
            background: rgba(40, 167, 69, 0.1);
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header">
                <h1>👁️ View Exam</h1>
                <p>Preview exam details and questions</p>
            </div>

            <!-- Exam Header -->
            <div style="background: white; border: 2px solid var(--primary-color); padding: 2rem; border-radius: var(--radius-lg); margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
                <h2 style="margin: 0 0 0.5rem 0; color: var(--primary-color);"><?php echo htmlspecialchars($exam['exam_name']); ?></h2>
                <p style="margin: 0; color: var(--text-secondary); font-size: 1.1rem;">
                    <?php echo htmlspecialchars($exam['course_name']); ?> (<?php echo $exam['course_code']; ?>) - 
                    <?php echo htmlspecialchars($exam['category_name']); ?>
                </p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                    <div>
                        <strong style="color: var(--text-secondary);">Exam Date:</strong>
                        <p style="margin: 0.5rem 0 0 0; font-size: 1.1rem; color: var(--text-primary); font-weight: 600;">
                            <?php echo date('M d, Y', strtotime($exam['exam_date'])); ?>
                        </p>
                    </div>
                    <div>
                        <strong style="color: var(--text-secondary);">Time:</strong>
                        <p style="margin: 0.5rem 0 0 0; font-size: 1.1rem; color: var(--text-primary); font-weight: 600;">
                            <?php echo date('g:i A', strtotime($exam['start_time'])); ?> - <?php echo date('g:i A', strtotime($exam['end_time'])); ?>
                        </p>
                    </div>
                    <div>
                        <strong style="color: var(--text-secondary);">Duration:</strong>
                        <p style="margin: 0.5rem 0 0 0; font-size: 1.1rem; color: var(--text-primary); font-weight: 600;">
                            <?php echo $exam['duration_minutes']; ?> minutes
                        </p>
                    </div>
                    <div>
                        <strong style="color: var(--text-secondary);">Total Questions:</strong>
                        <p style="margin: 0.5rem 0 0 0; font-size: 1.1rem; color: var(--text-primary); font-weight: 600;">
                            <?php echo $questions->num_rows; ?>
                        </p>
                    </div>
                    <div>
                        <strong style="color: var(--text-secondary);">Total Marks:</strong>
                        <p style="margin: 0.5rem 0 0 0; font-size: 1.1rem; color: var(--text-primary); font-weight: 600;">
                            <?php 
                            // Calculate actual total from questions
                            $actualTotal = 0;
                            $questions->data_seek(0);
                            while($q = $questions->fetch_assoc()) {
                                $actualTotal += $q['point_value'];
                            }
                            $questions->data_seek(0);
                            
                            echo $actualTotal;
                            if($actualTotal != $exam['total_marks']) {
                                echo ' <span style="color: #ffc107; font-size: 0.85rem;">(Needs Update)</span>';
                            }
                            ?>
                        </p>
                    </div>
                    <div>
                        <strong style="color: var(--text-secondary);">Pass Marks:</strong>
                        <p style="margin: 0.5rem 0 0 0; font-size: 1.1rem; color: var(--text-primary); font-weight: 600;">
                            <?php echo $exam['pass_marks']; ?>
                        </p>
                    </div>
                </div>
                
                <?php if($exam['instructions']): ?>
                <div style="margin-top: 1.5rem; padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md); border-left: 4px solid var(--primary-color);">
                    <strong style="color: var(--primary-color);">Instructions:</strong>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">
                        <?php echo nl2br(htmlspecialchars($exam['instructions'])); ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem; padding-top: 2rem; border-top: 2px solid var(--border-color);">
                    <?php if($exam['approval_status'] == 'draft'): ?>
                        <?php 
                        $questionCount = $questions->num_rows;
                        $canSubmit = $questionCount >= 5;
                        ?>
                        <?php if(!$canSubmit): ?>
                        <div style="flex: 1; background: #fff3cd; border: 2px solid #ffc107; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem;">
                            <strong style="color: #856404;">⚠️ Minimum 5 Questions Required</strong>
                            <p style="margin: 0.5rem 0 0 0; color: #856404;">
                                You need at least 5 questions to submit this exam for approval to ensure validity. 
                                Current questions: <strong><?php echo $questionCount; ?></strong> / 5
                            </p>
                        </div>
                        <?php endif; ?>
                        <a href="SubmitExamForApproval.php?exam_id=<?php echo $exam_id; ?>" 
                           class="btn btn-success <?php echo !$canSubmit ? 'disabled' : ''; ?>" 
                           <?php echo !$canSubmit ? 'onclick="return false;" style="opacity: 0.5; cursor: not-allowed;" title="Add at least 5 questions before submitting"' : 'onclick="return confirm(\'Submit this exam to the Exam Committee for approval?\')"'; ?>>
                            ✅ Submit for Approval
                        </a>
                    <?php endif; ?>
                    
                    <?php if($exam['approval_status'] == 'draft' || $exam['approval_status'] == 'revision'): ?>
                    <a href="AddQuestion.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-success">
                        ➕ Add Question
                    </a>
                    <?php if($actualTotal != $exam['total_marks']): ?>
                    <a href="UpdateExamTotalMarks.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-warning">
                        🔄 Update Total Marks
                    </a>
                    <?php endif; ?>
                    <?php else: ?>
                    <button class="btn btn-secondary" disabled title="Cannot modify exam after submission">
                        ➕ Add Question (Locked)
                    </button>
                    <?php endif; ?>
                    
                    <button class="btn btn-secondary" onclick="window.print()">
                        🖨️ Print Exam
                    </button>
                    <a href="ManageSchedules.php" class="btn btn-secondary">
                        ← Back to Schedules
                    </a>
                </div>
            </div>

            <!-- Questions List -->
            <div>
                <h3 style="margin-bottom: 1.5rem; color: var(--primary-color);">📝 Exam Questions</h3>
                
                <?php if($questions->num_rows > 0): ?>
                    <?php $qnum = 1; while($q = $questions->fetch_assoc()): ?>
                    <div class="question-preview">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <h4 style="margin: 0; color: var(--primary-color);">
                                Question <?php echo $qnum++; ?>
                            </h4>
                            <div style="display: flex; gap: 0.5rem;">
                                <?php if($q['topic_name']): ?>
                                <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 600;">
                                    <?php echo htmlspecialchars($q['topic_name']); ?>
                                </span>
                                <?php endif; ?>
                                <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 600;">
                                    <?php echo $q['difficulty_level']; ?>
                                </span>
                                <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 600;">
                                    <?php echo $q['point_value']; ?> pts
                                </span>
                            </div>
                        </div>
                        
                        <p style="font-size: 1.1rem; line-height: 1.6; margin: 1rem 0; color: var(--text-primary);">
                            <?php echo htmlspecialchars($q['question_text']); ?>
                        </p>
                        
                        <div style="margin-top: 1rem;">
                            <strong style="display: block; margin-bottom: 0.75rem; color: var(--primary-color);">Options:</strong>
                            
                            <div class="option-preview <?php echo ($q['correct_answer'] == 'A') ? 'correct' : ''; ?>">
                                <strong>A.</strong> <?php echo htmlspecialchars($q['option_a']); ?>
                                <?php if($q['correct_answer'] == 'A'): ?>
                                    <span style="float: right; color: var(--success-color); font-weight: 700;">✓ Correct Answer</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="option-preview <?php echo ($q['correct_answer'] == 'B') ? 'correct' : ''; ?>">
                                <strong>B.</strong> <?php echo htmlspecialchars($q['option_b']); ?>
                                <?php if($q['correct_answer'] == 'B'): ?>
                                    <span style="float: right; color: var(--success-color); font-weight: 700;">✓ Correct Answer</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($q['option_c']): ?>
                            <div class="option-preview <?php echo ($q['correct_answer'] == 'C') ? 'correct' : ''; ?>">
                                <strong>C.</strong> <?php echo htmlspecialchars($q['option_c']); ?>
                                <?php if($q['correct_answer'] == 'C'): ?>
                                    <span style="float: right; color: var(--success-color); font-weight: 700;">✓ Correct Answer</span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if($q['option_d']): ?>
                            <div class="option-preview <?php echo ($q['correct_answer'] == 'D') ? 'correct' : ''; ?>">
                                <strong>D.</strong> <?php echo htmlspecialchars($q['option_d']); ?>
                                <?php if($q['correct_answer'] == 'D'): ?>
                                    <span style="float: right; color: var(--success-color); font-weight: 700;">✓ Correct Answer</span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if($exam['approval_status'] == 'draft' || $exam['approval_status'] == 'revision'): ?>
                        <div style="display: flex; gap: 0.5rem; margin-top: 1rem; padding-top: 1rem; border-top: 2px solid var(--border-color);">
                            <button class="btn btn-primary btn-sm" onclick="editQuestion(<?php echo $q['question_id']; ?>)">
                                ✏️ Edit Question
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteQuestion(<?php echo $q['question_id']; ?>)">
                                🗑️ Delete
                            </button>
                        </div>
                        <?php else: ?>
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 2px solid var(--border-color);">
                            <span style="color: var(--text-secondary); font-size: 0.9rem;">
                                🔒 Question locked (Exam submitted for approval)
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 4rem; background: white; border-radius: var(--radius-lg);">
                        <h3 style="color: var(--text-secondary);">No questions in this exam</h3>
                        <p>Add questions to this exam to get started</p>
                        <a href="AddQuestion.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary" style="margin-top: 1rem;">
                            ➕ Add Questions
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Exam Summary -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">📊 Exam Summary</h3>
                </div>
                <div style="padding: 2rem;">
                    <div class="overview-list">
                        <div class="overview-item">
                            <span>Total Questions</span>
                            <strong><?php echo $questions->num_rows; ?></strong>
                        </div>
                        <div class="overview-item">
                            <span>Exam Category</span>
                            <strong><?php echo htmlspecialchars($exam['category_name']); ?></strong>
                        </div>
                        <div class="overview-item">
                            <span>Course</span>
                            <strong><?php echo htmlspecialchars($exam['course_name']); ?></strong>
                        </div>
                        <div class="overview-item">
                            <span>Department</span>
                            <strong><?php echo htmlspecialchars($exam['department_name']); ?></strong>
                        </div>
                        <div class="overview-item">
                            <span>Status</span>
                            <strong style="color: <?php echo $exam['is_active'] ? 'var(--success-color)' : 'var(--danger-color)'; ?>">
                                <?php echo $exam['is_active'] ? 'Active' : 'Inactive'; ?>
                            </strong>
                        </div>
                        <div class="overview-item">
                            <span>Created</span>
                            <strong><?php echo date('M d, Y', strtotime($exam['created_at'])); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function editQuestion(id) {
            window.location.href = 'EditQuestion.php?id=' + id;
        }
        
        function deleteQuestion(id) {
            if(confirm('Are you sure you want to delete this question?')) {
                window.location.href = 'DeleteQuestion.php?id=' + id;
            }
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
