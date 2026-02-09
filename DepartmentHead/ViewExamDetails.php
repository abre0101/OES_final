<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Department Head session
SessionManager::startSession('DepartmentHead');

// Check if user is logged in
if(!isset($_SESSION['Name'])){
    header("Location:../auth/staff-login.php");
    exit();
}

// Validate user role
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'DepartmentHead'){
    SessionManager::destroySession();
    header("Location:../auth/staff-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$exam_id = $_GET['id'] ?? $_GET['exam_id'] ?? 0;

// Get exam details - include all approved exams
$exam = $con->query("SELECT es.*, c.course_name, c.course_code, c.credit_hours,
    d.department_name, 
    i.full_name as instructor_name, i.email as instructor_email,
    ec.category_name,
    (SELECT COUNT(*) FROM exam_questions eq WHERE eq.exam_id = es.exam_id) as question_count,
    (SELECT COUNT(*) FROM student_courses sc WHERE sc.course_id = c.course_id) as enrolled_count,
    (SELECT COUNT(*) FROM exam_results er WHERE er.exam_id = es.exam_id) as completed_count
    FROM exams es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN departments d ON c.department_id = d.department_id
    INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    LEFT JOIN instructors i ON es.created_by = i.instructor_id
    WHERE es.exam_id = $exam_id
    LIMIT 1")->fetch_assoc();

if(!$exam) {
    $_SESSION['error'] = "Exam not found.";
    header("Location: MonitorExams.php");
    exit();
}

// Get questions for this exam
$questions = $con->query("SELECT q.*, qt.topic_name
    FROM exam_questions eq
    INNER JOIN questions q ON eq.question_id = q.question_id
    LEFT JOIN question_topics qt ON q.topic_id = qt.topic_id
    WHERE eq.exam_id = $exam_id
    ORDER BY eq.question_order");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Exam Details - <?php echo htmlspecialchars($exam['exam_name']); ?></title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
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
        .option.correct { background: #28a745 !important; border-left-color: #1e7e34 !important; border-left-width: 5px !important; font-weight: 700 !important; color: white !important; }
        .action-section { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); margin-top: 2rem; }
        
        /* Modal Styles */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.6); z-index: 9999; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; }
        .modal-content { background: white; border-radius: 16px; padding: 2rem; max-width: 500px; width: 90%; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); animation: modalSlideIn 0.3s ease; }
        @keyframes modalSlideIn { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
        .modal-icon { font-size: 3rem; }
        .modal-title { font-size: 1.5rem; font-weight: 700; color: #003366; margin: 0; }
        .modal-body { margin-bottom: 1.5rem; color: #495057; line-height: 1.6; }
        .modal-footer { display: flex; gap: 1rem; justify-content: flex-end; }
        .modal-btn { padding: 0.75rem 1.5rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem; transition: all 0.3s; }
        .modal-btn-primary { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; }
        .modal-btn-success { background: linear-gradient(135deg, #28a745 0%, #218838 100%); color: white; }
        .modal-btn-danger { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; }
        .modal-btn-warning { background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%); color: white; }
        .modal-btn-secondary { background: #6c757d; color: white; }
        .modal-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2); }
        .modal-input { width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; font-family: 'Poppins', sans-serif; margin-top: 1rem; }
        .modal-input:focus { outline: none; border-color: #003366; }
        .modal-textarea { width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; font-family: 'Poppins', sans-serif; margin-top: 1rem; min-height: 120px; resize: vertical; }
        .modal-textarea:focus { outline: none; border-color: #003366; }
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
                        <h1 style="color: #FFD700;"><?php echo htmlspecialchars($exam['exam_name']); ?></h1>
                        <p style="margin: 0; color: #90EE90; font-weight: 600; font-size: 1.1rem;"><?php echo htmlspecialchars($exam['course_code']); ?> - <?php echo htmlspecialchars($exam['course_name']); ?></p>
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
                <?php if($exam['instructor_name']): ?>
                <div class="info-card">
                    <div class="info-label">Instructor</div>
                    <div class="info-value"><?php echo htmlspecialchars($exam['instructor_name']); ?></div>
                </div>
                <?php endif; ?>
                <div class="info-card">
                    <div class="info-label">Exam Category</div>
                    <div class="info-value"><?php echo htmlspecialchars($exam['category_name']); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Duration</div>
                    <div class="info-value"><?php echo $exam['duration_minutes']; ?> Minutes</div>
                </div>
                <div class="info-card">
                    <div class="info-label">Total Marks</div>
                    <div class="info-value"><?php echo $exam['total_marks']; ?> Points</div>
                </div>
                <?php if($exam['question_count'] > 0): ?>
                <div class="info-card">
                    <div class="info-label">Total Questions</div>
                    <div class="info-value"><?php echo $exam['question_count']; ?> Questions</div>
                </div>
                <?php endif; ?>
                <?php if($exam['exam_date'] && $exam['exam_date'] != '0000-00-00'): ?>
                <div class="info-card">
                    <div class="info-label">Exam Date</div>
                    <div class="info-value"><?php echo date('M d, Y', strtotime($exam['exam_date'])); ?></div>
                </div>
                <?php endif; ?>
                <?php if($exam['start_time']): ?>
                <div class="info-card">
                    <div class="info-label">Start Time</div>
                    <div class="info-value"><?php echo date('h:i A', strtotime($exam['start_time'])); ?></div>
                </div>
                <?php endif; ?>
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
                            <?php 
                            // Check if this is a True/False question
                            $isTrueFalse = (strtolower(trim($q['option_a'])) === 'true' && strtolower(trim($q['option_b'])) === 'false') ||
                                          (strtolower(trim($q['option_a'])) === 'false' && strtolower(trim($q['option_b'])) === 'true');
                            
                            // Normalize correct answer - handle both uppercase and lowercase, trim whitespace
                            $correctAnswer = strtoupper(trim($q['correct_answer']));
                            
                            // Display Option A
                            $isCorrectA = ($correctAnswer === 'A');
                            ?>
                            <div class="option <?php echo $isCorrectA ? 'correct' : ''; ?>">
                                <strong>A.</strong> <?php echo htmlspecialchars($q['option_a']); ?>
                                <?php if($isCorrectA): ?><span style="float: right; font-weight: 700;">✓ Correct</span><?php endif; ?>
                            </div>
                            
                            <?php 
                            // Display Option B
                            $isCorrectB = ($correctAnswer === 'B');
                            ?>
                            <div class="option <?php echo $isCorrectB ? 'correct' : ''; ?>">
                                <strong>B.</strong> <?php echo htmlspecialchars($q['option_b']); ?>
                                <?php if($isCorrectB): ?><span style="float: right; font-weight: 700;">✓ Correct</span><?php endif; ?>
                            </div>
                            
                            <?php if(!empty($q['option_c'])): 
                                $isCorrectC = ($correctAnswer === 'C');
                            ?>
                            <div class="option <?php echo $isCorrectC ? 'correct' : ''; ?>">
                                <strong>C.</strong> <?php echo htmlspecialchars($q['option_c']); ?>
                                <?php if($isCorrectC): ?><span style="float: right; font-weight: 700;">✓ Correct</span><?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($q['option_d'])): 
                                $isCorrectD = ($correctAnswer === 'D');
                            ?>
                            <div class="option <?php echo $isCorrectD ? 'correct' : ''; ?>">
                                <strong>D.</strong> <?php echo htmlspecialchars($q['option_d']); ?>
                                <?php if($isCorrectD): ?><span style="float: right; font-weight: 700;">✓ Correct</span><?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e0e0e0; display: flex; gap: 2rem; font-size: 0.9rem; color: #6c757d;">
                            <span><strong>Points:</strong> <?php echo $q['point_value'] ?? 1; ?></span>
                            <span><strong>Type:</strong> <?php echo $isTrueFalse ? 'True/False' : 'Multiple Choice'; ?></span>
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
                    <button onclick="goBack()" style="background: #6c757d; color: white; padding: 1rem 2rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem;">
                        ← Back to List
                    </button>
                </div>
            </div>
            <?php else: ?>
            <div class="action-section">
                <button onclick="goBack()" style="background: #6c757d; color: white; padding: 1rem 2rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem;">
                    ← Back to List
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    
    <!-- Modal Overlays -->
    <div id="approveModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">✅</div>
                <h3 class="modal-title">Approve Exam</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve this exam?</p>
                <p style="font-size: 0.9rem; color: #6c757d;">This will make the exam available to students on the scheduled date.</p>
                <textarea id="approveComments" class="modal-textarea" placeholder="Add optional comments or feedback for the instructor..."></textarea>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-secondary" onclick="closeModal('approveModal')">Cancel</button>
                <button class="modal-btn modal-btn-success" onclick="submitApproval()">✓ Approve</button>
            </div>
        </div>
    </div>

    <div id="revisionModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">✏️</div>
                <h3 class="modal-title">Request Revision</h3>
            </div>
            <div class="modal-body">
                <p>Please provide detailed feedback for the instructor:</p>
                <textarea id="revisionComments" class="modal-textarea" placeholder="Describe what needs to be revised..." required></textarea>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-secondary" onclick="closeModal('revisionModal')">Cancel</button>
                <button class="modal-btn modal-btn-warning" onclick="submitRevision()">✏️ Request Revision</button>
            </div>
        </div>
    </div>

    <div id="rejectModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">❌</div>
                <h3 class="modal-title">Reject Exam</h3>
            </div>
            <div class="modal-body">
                <p style="color: #dc3545; font-weight: 600;">This action cannot be undone.</p>
                <p>Please provide a reason for rejection:</p>
                <textarea id="rejectComments" class="modal-textarea" placeholder="Explain why this exam is being rejected..." required></textarea>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-secondary" onclick="closeModal('rejectModal')">Cancel</button>
                <button class="modal-btn modal-btn-danger" onclick="submitRejection()">✗ Reject Exam</button>
            </div>
        </div>
    </div>
    
    <script>
        function goBack() {
            window.history.back();
        }
        
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        // Close modal when clicking outside
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if(e.target === this) {
                    this.classList.remove('active');
                }
            });
        });
        
        function approveExam() {
            openModal('approveModal');
        }
        
        function requestRevision() {
            openModal('revisionModal');
        }
        
        function rejectExam() {
            openModal('rejectModal');
        }
        
        function submitApproval() {
            const comments = document.getElementById('approveComments').value;
            window.location.href = 'ProcessApproval.php?exam_id=<?php echo $exam_id; ?>&action=approve&comments=' + encodeURIComponent(comments);
        }
        
        function submitRevision() {
            const comments = document.getElementById('revisionComments').value.trim();
            if(!comments) {
                alert('Please provide feedback for revision.');
                return;
            }
            window.location.href = 'ProcessApproval.php?exam_id=<?php echo $exam_id; ?>&action=revision&comments=' + encodeURIComponent(comments);
        }
        
        function submitRejection() {
            const comments = document.getElementById('rejectComments').value.trim();
            if(!comments) {
                alert('Please provide a reason for rejection.');
                return;
            }
            window.location.href = 'ProcessApproval.php?exam_id=<?php echo $exam_id; ?>&action=reject&comments=' + encodeURIComponent(comments);
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
