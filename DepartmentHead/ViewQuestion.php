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

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
$pageTitle = "View Question Details";

$question_id = $_GET['id'] ?? 0;

// Get question details from modern questions table
$question = $con->query("SELECT q.*, 
    ec.exam_name,
    c.course_name,
    qt.topic_name,
    i.full_name as instructor_name
    FROM questions q
    LEFT JOIN exam_categories ec ON q.exam_category_id = ec.exam_id
    LEFT JOIN courses c ON q.course_id = c.course_id
    LEFT JOIN question_topics qt ON q.topic_id = qt.topic_id
    LEFT JOIN instructors i ON q.instructor_id = i.instructor_id
    WHERE q.question_id = '$question_id'")->fetch_assoc();

if(!$question) {
    header("Location: CheckQuestions.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Question - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .question-detail-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .choice-item {
            padding: 1rem;
            margin-bottom: 0.75rem;
            background: var(--bg-light);
            border-radius: var(--radius-md);
            border-left: 4px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .choice-item:hover {
            border-left-color: var(--primary-color);
            background: rgba(0, 51, 102, 0.05);
        }
        
        .choice-item.correct {
            border-left-color: var(--success-color);
            background: rgba(40, 167, 69, 0.1);
        }
        
        .approval-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem;
            border-radius: var(--radius-lg);
            margin-top: 2rem;
        }
        
        .comment-box {
            width: 100%;
            padding: 1rem;
            border-radius: var(--radius-md);
            border: 2px solid #e0e0e0;
            font-family: inherit;
            font-size: 1rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header">
                <h1>👁️ Question Details</h1>
                <p>Review question information</p>
            </div>

            <!-- Question Information -->
            <div class="question-detail-card">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 3px solid var(--secondary-color);">
                    <div>
                        <h2 style="margin: 0 0 0.5rem 0; color: var(--primary-color);">
                            <?php echo $question['exam_name'] ?? 'Exam Question'; ?>
                        </h2>
                        <div style="font-size: 1rem; color: var(--text-secondary);">
                            <strong>Course:</strong> <?php echo $question['course_name'] ?? 'N/A'; ?> | 
                            <strong>Question ID:</strong> <?php echo $question['question_id']; ?> | 
                            <strong>Topic:</strong> <?php echo $question['topic_name'] ?? 'N/A'; ?> |
                            <strong>Instructor:</strong> <?php echo $question['instructor_name'] ?? 'N/A'; ?>
                        </div>
                    </div>
                    <span style="padding: 0.5rem 1rem; border-radius: var(--radius-md); font-size: 1rem; font-weight: 700; background: #ffc107; color: #000;">
                        Pending Review
                    </span>
                </div>

                <!-- Question Content -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: var(--primary-color); margin-bottom: 1rem;">📝 Question</h3>
                    <div style="padding: 1.5rem; background: var(--bg-light); border-radius: var(--radius-md); border-left: 4px solid var(--primary-color);">
                        <p style="font-size: 1.1rem; line-height: 1.8; margin: 0; color: var(--text-primary);">
                            <?php echo htmlspecialchars($question['question_text']); ?>
                        </p>
                    </div>
                </div>

                <!-- Answer Choices -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: var(--primary-color); margin-bottom: 1rem;">✓ Answer Choices</h3>
                    
                    <div class="choice-item <?php echo ($question['correct_answer'] == 'A') ? 'correct' : ''; ?>">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="color: var(--primary-color);">Option A:</strong>
                                <span style="margin-left: 1rem;"><?php echo htmlspecialchars($question['option_a']); ?></span>
                            </div>
                            <?php if($question['correct_answer'] == 'A'): ?>
                            <span style="background: var(--success-color); color: white; padding: 0.25rem 0.75rem; border-radius: var(--radius-sm); font-weight: 700; font-size: 0.85rem;">
                                ✓ Correct Answer
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="choice-item <?php echo ($question['correct_answer'] == 'B') ? 'correct' : ''; ?>">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="color: var(--primary-color);">Option B:</strong>
                                <span style="margin-left: 1rem;"><?php echo htmlspecialchars($question['option_b']); ?></span>
                            </div>
                            <?php if($question['correct_answer'] == 'B'): ?>
                            <span style="background: var(--success-color); color: white; padding: 0.25rem 0.75rem; border-radius: var(--radius-sm); font-weight: 700; font-size: 0.85rem;">
                                ✓ Correct Answer
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if(!empty($question['option_c'])): ?>
                    <div class="choice-item <?php echo ($question['correct_answer'] == 'C') ? 'correct' : ''; ?>">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="color: var(--primary-color);">Option C:</strong>
                                <span style="margin-left: 1rem;"><?php echo htmlspecialchars($question['option_c']); ?></span>
                            </div>
                            <?php if($question['correct_answer'] == 'C'): ?>
                            <span style="background: var(--success-color); color: white; padding: 0.25rem 0.75rem; border-radius: var(--radius-sm); font-weight: 700; font-size: 0.85rem;">
                                ✓ Correct Answer
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($question['option_d'])): ?>
                    <div class="choice-item <?php echo ($question['correct_answer'] == 'D') ? 'correct' : ''; ?>">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="color: var(--primary-color);">Option D:</strong>
                                <span style="margin-left: 1rem;"><?php echo htmlspecialchars($question['option_d']); ?></span>
                            </div>
                            <?php if($question['correct_answer'] == 'D'): ?>
                            <span style="background: var(--success-color); color: white; padding: 0.25rem 0.75rem; border-radius: var(--radius-sm); font-weight: 700; font-size: 0.85rem;">
                                ✓ Correct Answer
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Question Metadata -->
                <div style="background: var(--bg-light); padding: 1.5rem; border-radius: var(--radius-md);">
                    <h3 style="color: var(--primary-color); margin-bottom: 1rem;">ℹ️ Additional Information</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                        <div>
                            <strong>Question Type:</strong>
                            <p style="margin: 0.5rem 0 0 0;">Multiple Choice</p>
                        </div>
                        <div>
                            <strong>Difficulty Level:</strong>
                            <p style="margin: 0.5rem 0 0 0;"><?php echo $question['difficulty_level'] ?? 'Medium'; ?></p>
                        </div>
                        <div>
                            <strong>Correct Answer:</strong>
                            <p style="margin: 0.5rem 0 0 0; color: var(--success-color); font-weight: 700; font-size: 1.2rem;">
                                Option <?php echo $question['correct_answer']; ?>
                            </p>
                        </div>
                        <div>
                            <strong>Point Value:</strong>
                            <p style="margin: 0.5rem 0 0 0;"><?php echo $question['point_value'] ?? 1; ?> point(s)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review Checklist -->
            <div class="question-detail-card">
                <h3 style="color: var(--primary-color); margin-bottom: 1.5rem;">✅ Review Checklist</h3>
                <div style="display: grid; gap: 1rem;">
                    <label style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md); cursor: pointer;">
                        <input type="checkbox" style="width: 20px; height: 20px;">
                        <span><strong>Content Accuracy:</strong> Question and answers are factually correct</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md); cursor: pointer;">
                        <input type="checkbox" style="width: 20px; height: 20px;">
                        <span><strong>Clarity:</strong> Question is clear and unambiguous</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md); cursor: pointer;">
                        <input type="checkbox" style="width: 20px; height: 20px;">
                        <span><strong>Relevance:</strong> Aligns with course objectives</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md); cursor: pointer;">
                        <input type="checkbox" style="width: 20px; height: 20px;">
                        <span><strong>Difficulty:</strong> Appropriate for course level</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md); cursor: pointer;">
                        <input type="checkbox" style="width: 20px; height: 20px;">
                        <span><strong>Grammar:</strong> No spelling or formatting errors</span>
                    </label>
                </div>
            </div>

            <!-- Approval Actions -->
            <div class="approval-section">
                <h3 style="margin: 0 0 1rem 0;">🎯 Take Action</h3>
                <p style="opacity: 0.95; margin-bottom: 1.5rem;">Review the question carefully and choose an action below:</p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                    <button class="btn btn-success btn-block" onclick="approveQuestion()" style="padding: 1rem; font-size: 1.1rem;">
                        ✓ Approve Question
                    </button>
                    <button class="btn btn-warning btn-block" onclick="showRevisionForm()" style="padding: 1rem; font-size: 1.1rem; background: #ffc107; color: #000;">
                        ✏️ Request Revision
                    </button>
                    <button class="btn btn-danger btn-block" onclick="rejectQuestion()" style="padding: 1rem; font-size: 1.1rem;">
                        ✗ Reject Question
                    </button>
                </div>

                <!-- Revision Comment Form -->
                <div id="revisionForm" style="display: none; background: rgba(255, 255, 255, 0.1); padding: 1.5rem; border-radius: var(--radius-md); margin-top: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Revision Comments:</label>
                    <textarea id="revisionComment" class="comment-box" rows="4" placeholder="Explain what needs to be revised..."></textarea>
                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <button class="btn btn-light" onclick="submitRevision()">Submit Revision Request</button>
                        <button class="btn btn-secondary" onclick="hideRevisionForm()">Cancel</button>
                    </div>
                </div>

                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid rgba(255, 255, 255, 0.2);">
                    <a href="CheckQuestions.php" class="btn btn-secondary">
                        ← Back to Questions List
                    </a>
                    <button class="btn btn-light" onclick="window.print()" style="margin-left: 1rem;">
                        🖨️ Print Question
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        const questionId = <?php echo $question_id; ?>;
        
        function approveQuestion() {
            if(confirm('Are you sure you want to approve this question?\n\nThis will make it available for exams.')) {
                fetch('ApproveQuestion.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=approve&question_id=${questionId}`
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if(data.success) {
                        window.location.href = 'CheckQuestions.php';
                    }
                })
                .catch(error => {
                    alert('Error: ' + error);
                });
            }
        }
        
        function showRevisionForm() {
            document.getElementById('revisionForm').style.display = 'block';
        }
        
        function hideRevisionForm() {
            document.getElementById('revisionForm').style.display = 'none';
            document.getElementById('revisionComment').value = '';
        }
        
        function submitRevision() {
            const comment = document.getElementById('revisionComment').value.trim();
            if(!comment) {
                alert('Please enter revision comments');
                return;
            }
            
            if(confirm('Send revision request to instructor?')) {
                fetch('ApproveQuestion.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=revision&question_id=${questionId}&comments=${encodeURIComponent(comment)}`
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if(data.success) {
                        window.location.href = 'CheckQuestions.php';
                    }
                })
                .catch(error => {
                    alert('Error: ' + error);
                });
            }
        }
        
        function rejectQuestion() {
            const reason = prompt('Enter reason for rejection:');
            if(reason && reason.trim()) {
                if(confirm('Are you sure you want to reject this question?')) {
                    fetch('ApproveQuestion.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=reject&question_id=${questionId}&reason=${encodeURIComponent(reason)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if(data.success) {
                            window.location.href = 'CheckQuestions.php';
                        }
                    })
                    .catch(error => {
                        alert('Error: ' + error);
                    });
                }
            }
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
