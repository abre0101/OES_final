<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "View Topic Questions";
$instructor_id = $_SESSION['ID'];
$topic_id = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;

if($topic_id == 0) {
    header("Location: ManageQuestions.php");
    exit();
}

// Get topic details
$topicQuery = $con->prepare("SELECT qt.*, c.course_name, c.course_code
    FROM question_topics qt
    INNER JOIN courses c ON qt.course_id = c.course_id
    WHERE qt.topic_id = ?");
$topicQuery->bind_param("i", $topic_id);
$topicQuery->execute();
$topic = $topicQuery->get_result()->fetch_assoc();

if(!$topic) {
    header("Location: ManageQuestions.php");
    exit();
}

// Get all questions for this topic
$questionsQuery = $con->prepare("SELECT * FROM questions 
    WHERE topic_id = ? AND created_by = ?
    ORDER BY created_at DESC");
$questionsQuery->bind_param("ii", $topic_id, $instructor_id);
$questionsQuery->execute();
$questions = $questionsQuery->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($topic['topic_name']); ?> - Questions</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        .page-header-modern { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2); margin-bottom: 2rem; }
        .page-header-modern h1 { margin: 0 0 0.5rem 0; font-size: 2.2rem; font-weight: 800; display: flex; align-items: center; gap: 1rem; color: white; }
        .page-header-modern p { margin: 0; opacity: 0.95; font-size: 1.05rem; color: white; }
        .btn-modern { padding: 0.85rem 1.75rem; border-radius: 8px; font-weight: 600; font-size: 0.95rem; border: none; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3); }
        .btn-success { background: linear-gradient(135deg, #28a745 0%, #218838 100%); color: white; }
        .btn-success:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3); }
        .btn-secondary { background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; }
        .btn-secondary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3); }
        .btn-danger { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; }
        .btn-danger:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3); }
        .btn-sm { padding: 0.5rem 1rem; font-size: 0.875rem; }
        .question-card { background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); border-left: 4px solid #003366; transition: all 0.3s ease; }
        .question-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12); }
        .option-preview { padding: 0.75rem; margin: 0.5rem 0; background: #f8f9fa; border-radius: 8px; border-left: 3px solid #e0e0e0; }
        .option-preview.correct { border-left-color: #28a745; background: rgba(40, 167, 69, 0.1); }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header-modern">
                <h1><span>📖</span> <?php echo htmlspecialchars($topic['topic_name']); ?></h1>
                <p>
                    <?php echo htmlspecialchars($topic['course_name']); ?> (<?php echo $topic['course_code']; ?>)
                    <?php if($topic['chapter_number']): ?>
                    - Chapter <?php echo $topic['chapter_number']; ?>
                    <?php endif; ?>
                </p>
            </div>

            <div style="margin-bottom: 2rem; display: flex; gap: 1rem;">
                <a href="ManageQuestions.php" class="btn-modern btn-secondary">
                    ← Back to Question Bank
                </a>
                <a href="AddQuestion.php?topic_id=<?php echo $topic_id; ?>" class="btn-modern btn-success">
                    ➕ Add Question
                </a>
            </div>

            <?php if($questions->num_rows > 0): ?>
                <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);">
                    <h3 style="margin: 0 0 1.5rem 0; color: #003366;">
                        All Questions (<?php echo $questions->num_rows; ?>)
                    </h3>
                    
                    <?php $qnum = 1; while($q = $questions->fetch_assoc()): ?>
                    <div class="question-card">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <h4 style="margin: 0; color: #003366;">Question <?php echo $qnum++; ?></h4>
                            <div style="display: flex; gap: 0.5rem;">
                                <span style="background: #f8f9fa; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                                    <?php echo $q['difficulty_level']; ?>
                                </span>
                                <span style="background: #f8f9fa; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                                    <?php echo $q['point_value']; ?> pts
                                </span>
                                <span style="background: <?php echo $q['approval_status'] == 'approved' ? 'rgba(40, 167, 69, 0.1)' : 'rgba(255, 193, 7, 0.1)'; ?>; color: <?php echo $q['approval_status'] == 'approved' ? '#28a745' : '#ffc107'; ?>; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                                    <?php echo ucfirst($q['approval_status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <p style="font-size: 1.1rem; line-height: 1.6; margin: 1rem 0; color: #212529;">
                            <?php echo htmlspecialchars($q['question_text']); ?>
                        </p>
                        
                        <div style="margin-top: 1rem;">
                            <strong style="display: block; margin-bottom: 0.75rem; color: #003366;">Options:</strong>
                            
                            <div class="option-preview <?php echo ($q['correct_answer'] == 'A') ? 'correct' : ''; ?>">
                                <strong>A.</strong> <?php echo htmlspecialchars($q['option_a']); ?>
                                <?php if($q['correct_answer'] == 'A'): ?>
                                    <span style="float: right; color: #28a745; font-weight: 700;">✓ Correct Answer</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="option-preview <?php echo ($q['correct_answer'] == 'B') ? 'correct' : ''; ?>">
                                <strong>B.</strong> <?php echo htmlspecialchars($q['option_b']); ?>
                                <?php if($q['correct_answer'] == 'B'): ?>
                                    <span style="float: right; color: #28a745; font-weight: 700;">✓ Correct Answer</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($q['option_c']): ?>
                            <div class="option-preview <?php echo ($q['correct_answer'] == 'C') ? 'correct' : ''; ?>">
                                <strong>C.</strong> <?php echo htmlspecialchars($q['option_c']); ?>
                                <?php if($q['correct_answer'] == 'C'): ?>
                                    <span style="float: right; color: #28a745; font-weight: 700;">✓ Correct Answer</span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if($q['option_d']): ?>
                            <div class="option-preview <?php echo ($q['correct_answer'] == 'D') ? 'correct' : ''; ?>">
                                <strong>D.</strong> <?php echo htmlspecialchars($q['option_d']); ?>
                                <?php if($q['correct_answer'] == 'D'): ?>
                                    <span style="float: right; color: #28a745; font-weight: 700;">✓ Correct Answer</span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if($q['revision_comments']): ?>
                        <div style="margin-top: 1rem; padding: 0.75rem; background: rgba(255,193,7,0.1); border-left: 3px solid #ffc107; border-radius: 4px;">
                            <strong style="font-size: 0.85rem; color: #f57c00;">Revision Comments:</strong>
                            <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #6c757d;">
                                <?php echo htmlspecialchars($q['revision_comments']); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <div style="display: flex; gap: 0.5rem; margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #e0e0e0;">
                            <a href="EditQuestion.php?id=<?php echo $q['question_id']; ?>" class="btn-modern btn-primary btn-sm">
                                ✏️ Edit Question
                            </a>
                            <button class="btn-modern btn-danger btn-sm" onclick="deleteQuestion(<?php echo $q['question_id']; ?>)">
                                🗑️ Delete
                            </button>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 4rem; background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📝</div>
                    <h3 style="color: #6c757d;">No Questions Yet</h3>
                    <p>Start adding questions to this topic</p>
                    <a href="AddQuestion.php?topic_id=<?php echo $topic_id; ?>" class="btn-modern btn-primary" style="margin-top: 1rem;">
                        Create First Question
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function deleteQuestion(id) {
            if(confirm('Are you sure you want to delete this question?')) {
                window.location.href = 'DeleteQuestion.php?id=' + id;
            }
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
