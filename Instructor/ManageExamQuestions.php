<?php
session_start();
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Manage Exam Questions";
$instructor_id = $_SESSION['ID'];
$exam_id = $_GET['exam_id'] ?? 0;
$is_new = $_GET['new'] ?? 0;

// Get exam details
$examQuery = $con->prepare("SELECT es.*, c.course_name, c.course_code, ec.category_name
    FROM exams es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    WHERE es.exam_id = ? AND es.created_by = ?");
$examQuery->bind_param("ii", $exam_id, $instructor_id);
$examQuery->execute();
$exam = $examQuery->get_result()->fetch_assoc();

if(!$exam) {
    die("Exam not found or you don't have permission to edit it.");
}

// Get questions already in this exam
$examQuestionsQuery = $con->prepare("SELECT eq.*, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_answer, q.point_value
    FROM exam_questions eq
    INNER JOIN questions q ON eq.question_id = q.question_id
    WHERE eq.exam_id = ?
    ORDER BY eq.question_order");
$examQuestionsQuery->bind_param("i", $exam_id);
$examQuestionsQuery->execute();
$examQuestions = $examQuestionsQuery->get_result();

// Get available questions from question bank for this course
$availableQuestionsQuery = $con->prepare("SELECT q.*, qt.topic_name
    FROM questions q
    LEFT JOIN question_topics qt ON q.topic_id = qt.topic_id
    WHERE q.course_id = ? 
    AND q.question_id NOT IN (SELECT question_id FROM exam_questions WHERE exam_id = ?)
    ORDER BY q.created_at DESC");
$availableQuestionsQuery->bind_param("ii", $exam['course_id'], $exam_id);
$availableQuestionsQuery->execute();
$availableQuestions = $availableQuestionsQuery->get_result();

// Handle adding question to exam
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_question'])) {
    $question_id = intval($_POST['question_id']);
    
    // Get next order number
    $orderQuery = $con->prepare("SELECT COALESCE(MAX(question_order), 0) + 1 as next_order FROM exam_questions WHERE exam_id = ?");
    $orderQuery->bind_param("i", $exam_id);
    $orderQuery->execute();
    $next_order = $orderQuery->get_result()->fetch_assoc()['next_order'];
    
    $insertQuery = $con->prepare("INSERT INTO exam_questions (exam_id, question_id, question_order) VALUES (?, ?, ?)");
    $insertQuery->bind_param("iii", $exam_id, $question_id, $next_order);
    
    if($insertQuery->execute()) {
        header("Location: ManageExamQuestions.php?exam_id=" . $exam_id . "&success=added");
        exit();
    }
}

// Handle removing question from exam
if(isset($_GET['remove'])) {
    $eq_id = intval($_GET['remove']);
    $deleteQuery = $con->prepare("DELETE FROM exam_questions WHERE exam_question_id = ? AND exam_id = ?");
    $deleteQuery->bind_param("ii", $eq_id, $exam_id);
    $deleteQuery->execute();
    header("Location: ManageExamQuestions.php?exam_id=" . $exam_id . "&success=removed");
    exit();
}

$question_count = $examQuestions->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Exam Questions - Instructor</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        .page-header-modern { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2); margin-bottom: 2rem; }
        .page-header-modern h1 { margin: 0 0 0.5rem 0; font-size: 2.2rem; font-weight: 800; color: white; }
        .page-header-modern p { margin: 0; opacity: 0.95; font-size: 1.05rem; color: white; }
        .exam-info { background: white; border-radius: 12px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 1rem; }
        .info-item { padding: 1rem; background: #f8f9fa; border-radius: 8px; }
        .info-label { font-size: 0.85rem; color: #6c757d; margin-bottom: 0.25rem; }
        .info-value { font-size: 1.1rem; font-weight: 700; color: #003366; }
        .card { background: white; border-radius: 12px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); }
        .card h2 { margin: 0 0 1.5rem 0; color: #003366; font-size: 1.5rem; }
        .question-item { padding: 1.5rem; border: 2px solid #e0e0e0; border-radius: 8px; margin-bottom: 1rem; }
        .question-text { font-weight: 600; color: #003366; margin-bottom: 1rem; }
        .options { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; margin-bottom: 1rem; }
        .option { padding: 0.75rem; background: #f8f9fa; border-radius: 6px; font-size: 0.9rem; }
        .option.correct { background: #d4edda; border: 2px solid #28a745; }
        .btn { padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; font-size: 0.9rem; border: none; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-block; }
        .btn-primary { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-sm { padding: 0.5rem 1rem; font-size: 0.85rem; }
        .alert { padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-warning { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        .status-badge { padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; }
        .status-draft { background: #e0e0e0; color: #555; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header-modern">
                <h1>📝 Manage Exam Questions</h1>
                <p>Add questions to your exam from the question bank</p>
            </div>

            <?php if($is_new): ?>
            <div class="alert alert-success">✅ Exam created successfully! Now add questions to complete your exam.</div>
            <?php endif; ?>

            <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">
                ✅ Question <?php echo $_GET['success'] == 'added' ? 'added to' : 'removed from'; ?> exam successfully!
            </div>
            <?php endif; ?>

            <!-- Exam Info -->
            <div class="exam-info">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                    <div>
                        <h2 style="margin: 0 0 0.5rem 0; color: #003366;"><?php echo htmlspecialchars($exam['exam_name']); ?></h2>
                        <p style="margin: 0; color: #6c757d;"><?php echo htmlspecialchars($exam['course_code'] . ' - ' . $exam['course_name']); ?></p>
                    </div>
                    <span class="status-badge status-<?php echo $exam['approval_status']; ?>">
                        <?php echo strtoupper($exam['approval_status']); ?>
                    </span>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Category</div>
                        <div class="info-value"><?php echo htmlspecialchars($exam['category_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Duration</div>
                        <div class="info-value"><?php echo $exam['duration_minutes']; ?> mins</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Total Marks</div>
                        <div class="info-value"><?php echo $exam['total_marks']; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Questions Added</div>
                        <div class="info-value"><?php echo $question_count; ?></div>
                    </div>
                </div>

                <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    <?php if($exam['approval_status'] == 'draft' && $question_count > 0): ?>
                    <a href="SubmitExamForApproval.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-success">
                        ✅ Submit for Approval
                    </a>
                    <?php endif; ?>
                    <a href="ManageSchedules.php" class="btn btn-secondary">
                        ← Back to Exams
                    </a>
                </div>
            </div>

            <?php if($question_count == 0): ?>
            <div class="alert alert-warning">
                ⚠️ No questions added yet. Add at least one question before submitting for approval.
            </div>
            <?php endif; ?>

            <!-- Current Questions -->
            <div class="card">
                <h2>📋 Questions in This Exam (<?php echo $question_count; ?>)</h2>
                
                <?php if($question_count > 0): ?>
                    <?php 
                    $examQuestions->data_seek(0);
                    $qnum = 1;
                    while($eq = $examQuestions->fetch_assoc()): 
                    ?>
                    <div class="question-item">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <div class="question-text">Q<?php echo $qnum++; ?>. <?php echo htmlspecialchars($eq['question_text']); ?></div>
                            <?php if($exam['approval_status'] == 'draft'): ?>
                            <a href="?exam_id=<?php echo $exam_id; ?>&remove=<?php echo $eq['exam_question_id']; ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Remove this question from exam?')">
                                🗑️ Remove
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="options">
                            <div class="option <?php echo $eq['correct_answer'] == 'A' ? 'correct' : ''; ?>">
                                A) <?php echo htmlspecialchars($eq['option_a']); ?>
                            </div>
                            <div class="option <?php echo $eq['correct_answer'] == 'B' ? 'correct' : ''; ?>">
                                B) <?php echo htmlspecialchars($eq['option_b']); ?>
                            </div>
                            <?php if($eq['option_c']): ?>
                            <div class="option <?php echo $eq['correct_answer'] == 'C' ? 'correct' : ''; ?>">
                                C) <?php echo htmlspecialchars($eq['option_c']); ?>
                            </div>
                            <?php endif; ?>
                            <?php if($eq['option_d']): ?>
                            <div class="option <?php echo $eq['correct_answer'] == 'D' ? 'correct' : ''; ?>">
                                D) <?php echo htmlspecialchars($eq['option_d']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 0.85rem; color: #6c757d;">
                            Points: <?php echo $eq['point_value']; ?> | Correct Answer: <?php echo $eq['correct_answer']; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #6c757d; padding: 2rem;">No questions added yet.</p>
                <?php endif; ?>
            </div>

            <!-- Available Questions -->
            <?php if($exam['approval_status'] == 'draft'): ?>
            <div class="card">
                <h2>➕ Add Questions from Question Bank</h2>
                
                <?php if($availableQuestions->num_rows > 0): ?>
                    <?php while($q = $availableQuestions->fetch_assoc()): ?>
                    <div class="question-item">
                        <div class="question-text"><?php echo htmlspecialchars($q['question_text']); ?></div>
                        <div class="options">
                            <div class="option <?php echo $q['correct_answer'] == 'A' ? 'correct' : ''; ?>">
                                A) <?php echo htmlspecialchars($q['option_a']); ?>
                            </div>
                            <div class="option <?php echo $q['correct_answer'] == 'B' ? 'correct' : ''; ?>">
                                B) <?php echo htmlspecialchars($q['option_b']); ?>
                            </div>
                            <?php if($q['option_c']): ?>
                            <div class="option <?php echo $q['correct_answer'] == 'C' ? 'correct' : ''; ?>">
                                C) <?php echo htmlspecialchars($q['option_c']); ?>
                            </div>
                            <?php endif; ?>
                            <?php if($q['option_d']): ?>
                            <div class="option <?php echo $q['correct_answer'] == 'D' ? 'correct' : ''; ?>">
                                D) <?php echo htmlspecialchars($q['option_d']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                            <div style="font-size: 0.85rem; color: #6c757d;">
                                Topic: <?php echo htmlspecialchars($q['topic_name'] ?? 'N/A'); ?> | 
                                Correct Answer: <?php echo $q['correct_answer']; ?> | 
                                Points: <?php echo $q['point_value']; ?>
                            </div>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="question_id" value="<?php echo $q['question_id']; ?>">
                                <button type="submit" name="add_question" class="btn btn-primary btn-sm">
                                    ➕ Add to Exam
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #6c757d; padding: 2rem;">
                        No more questions available. <a href="AddQuestion.php?course_id=<?php echo $exam['course_id']; ?>">Create new questions</a>
                    </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>
