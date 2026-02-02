<?php
session_start();
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "My Exams";
$instructor_id = $_SESSION['ID'];

// Get exams by status
$examsQuery = $con->prepare("SELECT 
    es.*,
    c.course_name,
    c.course_code,
    ec.category_name,
    (SELECT COUNT(*) FROM exam_questions WHERE exam_id = es.exam_id) as question_count,
    (SELECT COUNT(*) FROM exam_results WHERE exam_id = es.exam_id) as result_count
    FROM exams es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    WHERE es.created_by = ?
    ORDER BY es.created_at DESC");
$examsQuery->bind_param("i", $instructor_id);
$examsQuery->execute();
$exams = $examsQuery->get_result();

// Categorize exams
$draft_exams = [];
$pending_exams = [];
$approved_exams = [];
$revision_exams = [];
$rejected_exams = [];

while($exam = $exams->fetch_assoc()) {
    switch($exam['approval_status']) {
        case 'draft':
            $draft_exams[] = $exam;
            break;
        case 'pending':
            $pending_exams[] = $exam;
            break;
        case 'approved':
            $approved_exams[] = $exam;
            break;
        case 'revision':
            $revision_exams[] = $exam;
            break;
        case 'rejected':
            $rejected_exams[] = $exam;
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Exams - Instructor</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        .page-header-modern { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2); margin-bottom: 2rem; }
        .page-header-modern h1 { margin: 0 0 0.5rem 0; font-size: 2.2rem; font-weight: 800; color: white; }
        .page-header-modern p { margin: 0; opacity: 0.95; font-size: 1.05rem; color: white; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); text-align: center; }
        .stat-value { font-size: 2rem; font-weight: 900; color: #003366; }
        .stat-label { font-size: 0.9rem; color: #6c757d; margin-top: 0.5rem; }
        .btn { padding: 0.85rem 1.75rem; border-radius: 8px; font-weight: 600; font-size: 0.95rem; border: none; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-primary { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3); }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-sm { padding: 0.5rem 1rem; font-size: 0.85rem; }
        .tabs { background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); overflow: hidden; }
        .tab-header { display: flex; background: #f8f9fa; border-bottom: 2px solid #e0e0e0; }
        .tab-btn { flex: 1; padding: 1rem; border: none; background: transparent; font-weight: 600; color: #6c757d; cursor: pointer; border-bottom: 3px solid transparent; }
        .tab-btn.active { color: #003366; border-bottom-color: #003366; background: white; }
        .tab-content { display: none; padding: 2rem; }
        .tab-content.active { display: block; }
        .exam-card { background: #f8f9fa; border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem; border-left: 4px solid #003366; }
        .exam-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem; }
        .exam-title { font-size: 1.2rem; font-weight: 700; color: #003366; margin: 0 0 0.5rem 0; }
        .exam-meta { font-size: 0.9rem; color: #6c757d; }
        .exam-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 1rem; }
        .status-badge { padding: 0.35rem 0.85rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .status-draft { background: #e0e0e0; color: #555; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-revision { background: #fff3cd; color: #856404; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .alert { padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header-modern">
                <h1>📝 My Exams</h1>
                <p>Create, manage, and track your exam submissions</p>
            </div>

            <?php if(isset($_GET['success']) && $_GET['success'] == 'submitted'): ?>
            <div class="alert alert-success">✅ Exam submitted for approval successfully!</div>
            <?php endif; ?>

            <div style="margin-bottom: 2rem;">
                <a href="CreateExam.php" class="btn btn-primary">
                    ➕ Create New Exam
                </a>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo count($draft_exams); ?></div>
                    <div class="stat-label">📝 Drafts</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo count($pending_exams); ?></div>
                    <div class="stat-label">⏳ Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo count($approved_exams); ?></div>
                    <div class="stat-label">✅ Approved</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo count($revision_exams); ?></div>
                    <div class="stat-label">🔄 Revision</div>
                </div>
            </div>

            <div class="tabs">
                <div class="tab-header">
                    <button class="tab-btn active" onclick="showTab('draft')">📝 Drafts (<?php echo count($draft_exams); ?>)</button>
                    <button class="tab-btn" onclick="showTab('pending')">⏳ Pending (<?php echo count($pending_exams); ?>)</button>
                    <button class="tab-btn" onclick="showTab('approved')">✅ Approved (<?php echo count($approved_exams); ?>)</button>
                    <button class="tab-btn" onclick="showTab('revision')">🔄 Revision (<?php echo count($revision_exams); ?>)</button>
                </div>

                <!-- Draft Exams -->
                <div id="draft-tab" class="tab-content active">
                    <?php if(count($draft_exams) > 0): ?>
                        <?php foreach($draft_exams as $exam): ?>
                        <div class="exam-card">
                            <div class="exam-header">
                                <div>
                                    <h3 class="exam-title"><?php echo htmlspecialchars($exam['exam_name']); ?></h3>
                                    <div class="exam-meta">
                                        <?php echo htmlspecialchars($exam['course_code'] . ' - ' . $exam['course_name']); ?> | 
                                        <?php echo htmlspecialchars($exam['category_name']); ?> | 
                                        <?php echo $exam['question_count']; ?> Questions
                                    </div>
                                </div>
                                <span class="status-badge status-draft">DRAFT</span>
                            </div>
                            <div class="exam-actions">
                                <a href="ManageExamQuestions.php?exam_id=<?php echo $exam['exam_id']; ?>" class="btn btn-primary btn-sm">
                                    📝 Manage Questions
                                </a>
                                <?php if($exam['question_count'] > 0): ?>
                                <a href="SubmitExamForApproval.php?exam_id=<?php echo $exam['exam_id']; ?>" class="btn btn-success btn-sm">
                                    ✅ Submit for Approval
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #6c757d; padding: 2rem;">No draft exams. <a href="CreateExam.php">Create one now</a></p>
                    <?php endif; ?>
                </div>

                <!-- Pending Exams -->
                <div id="pending-tab" class="tab-content">
                    <?php if(count($pending_exams) > 0): ?>
                        <?php foreach($pending_exams as $exam): ?>
                        <div class="exam-card">
                            <div class="exam-header">
                                <div>
                                    <h3 class="exam-title"><?php echo htmlspecialchars($exam['exam_name']); ?></h3>
                                    <div class="exam-meta">
                                        <?php echo htmlspecialchars($exam['course_code'] . ' - ' . $exam['course_name']); ?> | 
                                        Submitted: <?php echo date('M d, Y', strtotime($exam['submitted_at'])); ?>
                                    </div>
                                </div>
                                <span class="status-badge status-pending">PENDING APPROVAL</span>
                            </div>
                            <p style="color: #6c757d; margin: 0;">⏳ Waiting for department head approval...</p>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #6c757d; padding: 2rem;">No pending exams</p>
                    <?php endif; ?>
                </div>

                <!-- Approved Exams -->
                <div id="approved-tab" class="tab-content">
                    <?php if(count($approved_exams) > 0): ?>
                        <?php foreach($approved_exams as $exam): ?>
                        <div class="exam-card">
                            <div class="exam-header">
                                <div>
                                    <h3 class="exam-title"><?php echo htmlspecialchars($exam['exam_name']); ?></h3>
                                    <div class="exam-meta">
                                        <?php echo htmlspecialchars($exam['course_code'] . ' - ' . $exam['course_name']); ?> | 
                                        <?php if($exam['exam_date']): ?>
                                            Scheduled: <?php echo date('M d, Y', strtotime($exam['exam_date'])); ?>
                                            <?php if($exam['start_time']): ?>
                                                at <?php echo date('h:i A', strtotime($exam['start_time'])); ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            Not scheduled yet
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="status-badge status-approved">APPROVED</span>
                            </div>
                            <?php if($exam['approval_comments']): ?>
                            <div style="margin-top: 1rem; padding: 1rem; background: #d4edda; border-radius: 6px; font-size: 0.9rem;">
                                <strong>Approval Comments:</strong> <?php echo htmlspecialchars($exam['approval_comments']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #6c757d; padding: 2rem;">No approved exams yet</p>
                    <?php endif; ?>
                </div>

                <!-- Revision Required -->
                <div id="revision-tab" class="tab-content">
                    <?php if(count($revision_exams) > 0): ?>
                        <?php foreach($revision_exams as $exam): ?>
                        <div class="exam-card">
                            <div class="exam-header">
                                <div>
                                    <h3 class="exam-title"><?php echo htmlspecialchars($exam['exam_name']); ?></h3>
                                    <div class="exam-meta">
                                        <?php echo htmlspecialchars($exam['course_code'] . ' - ' . $exam['course_name']); ?>
                                    </div>
                                </div>
                                <span class="status-badge status-revision">REVISION REQUIRED</span>
                            </div>
                            <?php if($exam['approval_comments']): ?>
                            <div style="margin-top: 1rem; padding: 1rem; background: #fff3cd; border-radius: 6px; font-size: 0.9rem;">
                                <strong>Revision Comments:</strong> <?php echo htmlspecialchars($exam['approval_comments']); ?>
                            </div>
                            <?php endif; ?>
                            <div class="exam-actions">
                                <a href="ManageExamQuestions.php?exam_id=<?php echo $exam['exam_id']; ?>" class="btn btn-warning btn-sm">
                                    🔄 Make Revisions
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #6c757d; padding: 2rem;">No exams requiring revision</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
