<?php
session_start();
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$schedule_id = $_GET['schedule_id'] ?? 0;

// Get exam details - exclude draft exams
$exam = $con->query("SELECT es.*, c.course_name, c.course_code, c.credit_hours,
    d.department_name, 
    i.full_name as instructor_name, i.email as instructor_email,
    ec.category_name,
    (SELECT COUNT(*) FROM exam_questions eq WHERE eq.schedule_id = es.schedule_id) as question_count
    FROM exam_schedules es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN departments d ON c.department_id = d.department_id
    INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    INNER JOIN instructors i ON ic.instructor_id = i.instructor_id
    WHERE es.schedule_id = $schedule_id AND ic.is_active = TRUE AND es.approval_status != 'draft'
    LIMIT 1")->fetch_assoc();

if(!$exam) {
    $_SESSION['error'] = "Exam not found or not available for review.";
    header("Location: PendingApprovals.php");
    exit();
}

// Get questions for this exam
$questions = $con->query("SELECT q.*, qt.topic_name
    FROM exam_questions eq
    INNER JOIN questions q ON eq.question_id = q.question_id
    LEFT JOIN question_topics qt ON q.topic_id = qt.topic_id
    WHERE eq.schedule_id = $schedule_id
    ORDER BY eq.question_order");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Details - <?php echo htmlspecialchars($exam['exam_name']); ?></title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .exam-detail-header { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; padding: 2.5rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2); }
        .exam-detail-header h1 { margin: 0 0 0.5rem 0; font-size: 2rem; font-weight: 800; }
        .status-badge { display: inline-block; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 700; font-size: 0.9rem; }
        .status-pending { background: #ffc107; color: #000; }
        .status-approved { background: #28a745; color: white; }
        .status-rejected { background: #dc3545; color: white; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .info-card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); }
        .info-label { font-size: 0.85rem; color: #6c757d; font-weight: 600; margin-bottom: 0.5rem; }
        .info-value { font-size: 1.2rem; color: #003366; font-weight: 700; }
        .question-card { background: white; padding: 2rem; border-radius: 12px; margin-bottom: 1.5rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); border-left: 4px solid #003366; }
        .question-text { font-size: 1.1rem; font-weight: 600; color: #003366; margin-bottom: 1rem; }
        .option { padding: 0.75rem; margin-bottom: 0.5rem; background: #f8f9fa; border-radius: 8px; border-left: 3px solid #e0e0e0; }
        .option.correct { background: rgba(40, 167, 69, 0.1); border-left-color: #28a745; font-weight: 600; }
        .action-section { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); margin-top: 2rem; }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php $pageTitle = 'Exam Details'; include 'header-component.php'; ?>

        <div class="admin-content">
            <!-- Exam Header -->
            <div class="exam-detail-header">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <h1><?php echo htmlspecialchars($exam['exam_name']); ?></h1>
                        <p style="margin: 0; opacity: 0.95;"><?php echo htmlspecialchars($exam['course_code']); ?> - <?php echo htmlspecialchars($exam['course_name']); ?></p>
                    </div>
                    <span class="status-badge status-<?php echo $exam['approval_status']; ?>">
                        <?php 
                        $statusIcons = ['pending' => '⏳', 'approved' => '✓', 'rejected' => '✗'];
                        echo $statusIcons[$exam['approval_status']] ?? ''; 
                        ?> 
                        <?php echo ucfirst($exam['approval_status']); ?>
                    </span>
                </div>
            </div>

            <!-- Exam Information -->
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-label">Department</div>
                    <div class="info-value"><?php echo htmlspecialchars($exam['department_name']); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Instructor</div>
                    <div class="info-value"><?php echo htmlspecialchars($exam['instructor_name']); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Exam Category</div>
                    <div class="info-value"><?php echo htmlspecialchars($exam['category_name']); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Exam Date</div>
                    <div class="info-value"><?php echo date('M d, Y', strtotime($exam['exam_date'])); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Start Time</div>
                    <div class="info-value"><?php echo date('h:i A', strtotime($exam['start_time'])); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Duration</div>
                    <div class="info-value"><?php echo $exam['duration_minutes']; ?> Minutes</div>
                </div>
                <div class="info-card">
                    <div class="info-label">Total Questions</div>
                    <div class="info-value"><?php echo $exam['question_count']; ?> Questions</div>
                </div>
                <div class="info-card">
                    <div class="info-label">Submitted</div>
                    <div class="info-value"><?php echo date('M d, Y', strtotime($exam['submitted_at'])); ?></div>
                </div>
            </div>

            <!-- Questions Section -->
            <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); margin-bottom: 2rem;">
                <h2 style="color: #003366; margin-bottom: 1.5rem;">📝 Exam Questions (<?php echo $exam['question_count']; ?>)</h2>
                
                <?php if($questions->num_rows > 0): ?>
                    <?php $qNum = 1; while($q = $questions->fetch_assoc()): ?>
                    <div class="question-card">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <div class="question-text">
                                <span style="color: #0055aa;">Q<?php echo $qNum; ?>.</span> 
                                <?php echo htmlspecialchars($q['question_text']); ?>
                            </div>
                            <div style="text-align: right;">
                                <span style="background: #e3f2fd; color: #0055aa; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem; font-weight: 600;">
                                    <?php echo $q['point_value']; ?> pt(s)
                                </span>
                                <?php if($q['topic_name']): ?>
                                <div style="margin-top: 0.5rem; font-size: 0.85rem; color: #6c757d;">
                                    📚 <?php echo htmlspecialchars($q['topic_name']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div style="margin-left: 2rem;">
                            <div class="option <?php echo $q['correct_answer'] == 'A' ? 'correct' : ''; ?>">
                                <strong>A.</strong> <?php echo htmlspecialchars($q['option_a']); ?>
                                <?php if($q['correct_answer'] == 'A'): ?><span style="color: #28a745; float: right;">✓ Correct</span><?php endif; ?>
                            </div>
                            <div class="option <?php echo $q['correct_answer'] == 'B' ? 'correct' : ''; ?>">
                                <strong>B.</strong> <?php echo htmlspecialchars($q['option_b']); ?>
                                <?php if($q['correct_answer'] == 'B'): ?><span style="color: #28a745; float: right;">✓ Correct</span><?php endif; ?>
                            </div>
                            <?php if(!empty($q['option_c'])): ?>
                            <div class="option <?php echo $q['correct_answer'] == 'C' ? 'correct' : ''; ?>">
                                <strong>C.</strong> <?php echo htmlspecialchars($q['option_c']); ?>
                                <?php if($q['correct_answer'] == 'C'): ?><span style="color: #28a745; float: right;">✓ Correct</span><?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <?php if(!empty($q['option_d'])): ?>
                            <div class="option <?php echo $q['correct_answer'] == 'D' ? 'correct' : ''; ?>">
                                <strong>D.</strong> <?php echo htmlspecialchars($q['option_d']); ?>
                                <?php if($q['correct_answer'] == 'D'): ?><span style="color: #28a745; float: right;">✓ Correct</span><?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e0e0e0; display: flex; gap: 2rem; font-size: 0.9rem; color: #6c757d;">
                            <span><strong>Difficulty:</strong> <?php echo $q['difficulty_level']; ?></span>
                            <span><strong>Type:</strong> Multiple Choice</span>
                        </div>
                    </div>
                    <?php $qNum++; endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #6c757d; padding: 2rem;">No questions found for this exam.</p>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <?php if($exam['approval_status'] == 'pending'): ?>
            <div class="action-section">
                <h3 style="color: #003366; margin-bottom: 1.5rem;">🎯 Review Actions</h3>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <button onclick="approveExam()" style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); color: white; padding: 1rem 2rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem;">
                        ✓ Approve Exam
                    </button>
                    <button onclick="requestRevision()" style="background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%); color: white; padding: 1rem 2rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem;">
                        ✏️ Request Revision
                    </button>
                    <button onclick="rejectExam()" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 1rem 2rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem;">
                        ✗ Reject Exam
                    </button>
                    <a href="PendingApprovals.php" style="background: #6c757d; color: white; padding: 1rem 2rem; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-block;">
                        ← Back to List
                    </a>
                </div>
            </div>
            <?php else: ?>
            <div class="action-section">
                <a href="PendingApprovals.php" style="background: #6c757d; color: white; padding: 1rem 2rem; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-block;">
                    ← Back to List
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function approveExam() {
            if(confirm('Are you sure you want to approve this exam?\n\nThis will make it available to students on the scheduled date.')) {
                window.location.href = 'ProcessApproval.php?schedule_id=<?php echo $schedule_id; ?>&action=approve';
            }
        }
        
        function requestRevision() {
            const comments = prompt('Please provide feedback for revision:');
            if(comments && comments.trim()) {
                window.location.href = 'ProcessApproval.php?schedule_id=<?php echo $schedule_id; ?>&action=revision&comments=' + encodeURIComponent(comments);
            }
        }
        
        function rejectExam() {
            const reason = prompt('Please provide a reason for rejection:');
            if(reason && reason.trim()) {
                if(confirm('Are you sure you want to reject this exam?')) {
                    window.location.href = 'ProcessApproval.php?schedule_id=<?php echo $schedule_id; ?>&action=reject&comments=' + encodeURIComponent(reason);
                }
            }
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
