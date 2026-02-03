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
        case 'draft': $draft_exams[] = $exam; break;
        case 'pending': $pending_exams[] = $exam; break;
        case 'approved': $approved_exams[] = $exam; break;
        case 'revision': $revision_exams[] = $exam; break;
        case 'rejected': $rejected_exams[] = $exam; break;
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
        
        .page-header-modern {
            background: linear-gradient(135deg, #003366 0%, #0055aa 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-header-modern h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2.2rem;
            font-weight: 800;
            color: white;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .page-header-modern p {
            margin: 0;
            opacity: 0.95;
            font-size: 1.05rem;
            color: white;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: all 0.3s ease;
            border-top: 4px solid;
        }
        
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12); }
        .stat-card.draft { border-top-color: #6c757d; }
        .stat-card.pending { border-top-color: #ffc107; }
        .stat-card.approved { border-top-color: #28a745; }
        .stat-card.revision { border-top-color: #fd7e14; }
        
        .stat-icon { font-size: 3rem; margin-bottom: 1rem; }
        .stat-value { font-size: 2.5rem; font-weight: 900; color: #003366; margin-bottom: 0.5rem; }
        .stat-label { font-size: 0.95rem; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .btn {
            padding: 0.85rem 1.75rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #003366 0%, #0055aa 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3);
        }
        
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; transform: translateY(-2px); }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; transform: translateY(-2px); }
        .btn-info { background: #17a2b8; color: white; }
        .btn-info:hover { background: #138496; transform: translateY(-2px); }
        .btn-sm { padding: 0.6rem 1.2rem; font-size: 0.875rem; }
        
        .tabs-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .tab-header {
            display: flex;
            background: #f8f9fa;
            border-bottom: 2px solid #e0e0e0;
            overflow-x: auto;
        }
        
        .tab-btn {
            flex: 1;
            min-width: 150px;
            padding: 1.25rem 1.5rem;
            border: none;
            background: transparent;
            font-weight: 600;
            font-size: 0.95rem;
            color: #6c757d;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .tab-btn:hover { background: rgba(0, 51, 102, 0.05); }
        .tab-btn.active {
            color: #003366;
            border-bottom-color: #003366;
            background: white;
        }
        
        .tab-content {
            display: none;
            padding: 2rem;
            min-height: 300px;
        }
        
        .tab-content.active { display: block; }
        
        .exam-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #003366;
            transition: all 0.3s ease;
        }
        
        .exam-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        }
        
        .exam-card.draft { border-left-color: #6c757d; }
        .exam-card.pending { border-left-color: #ffc107; }
        .exam-card.approved { border-left-color: #28a745; }
        .exam-card.revision { border-left-color: #fd7e14; }
        
        .exam-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1.5rem;
            gap: 1rem;
        }
        
        .exam-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #003366;
            margin: 0 0 0.75rem 0;
        }
        
        .exam-meta {
            font-size: 0.9rem;
            color: #6c757d;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
        }
        
        .exam-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .exam-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        
        .status-draft { background: #e9ecef; color: #495057; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-revision { background: #ffe5d0; color: #d63384; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
        .comment-box {
            margin-top: 1.5rem;
            padding: 1.25rem;
            border-radius: 8px;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        .comment-box.success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }
        
        .comment-box.warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }
        
        .comment-box strong {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }
        
        .empty-state-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            opacity: 0.3;
        }
        
        .empty-state h3 {
            color: #495057;
            margin-bottom: 0.75rem;
        }
        
        .empty-state p {
            margin-bottom: 1.5rem;
        }
        
        .alert {
            padding: 1.25rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        @media (max-width: 768px) {
            .page-header-modern { flex-direction: column; align-items: flex-start; }
            .stats-grid { grid-template-columns: 1fr; }
            .tab-header { flex-wrap: wrap; }
            .tab-btn { min-width: 120px; font-size: 0.85rem; padding: 1rem; }
            .exam-header { flex-direction: column; }
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <!-- Page Header -->
            <div class="page-header-modern">
                <div>
                    <h1>📝 My Exams</h1>
                    <p>Create, manage, and track your exam submissions</p>
                </div>
                <a href="CreateExam.php" class="btn btn-primary" style="background: white; color: #003366;">
                    <span>➕</span> Create New Exam
                </a>
            </div>

            <?php if(isset($_GET['success']) && $_GET['success'] == 'submitted'): ?>
            <div class="alert alert-success">
                <span style="font-size: 1.5rem;">✅</span>
                <span>Exam submitted for approval successfully!</span>
            </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card draft">
                    <div class="stat-icon">📝</div>
                    <div class="stat-value"><?php echo count($draft_exams); ?></div>
                    <div class="stat-label">Draft Exams</div>
                </div>
                <div class="stat-card pending">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-value"><?php echo count($pending_exams); ?></div>
                    <div class="stat-label">Pending Approval</div>
                </div>
                <div class="stat-card approved">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value"><?php echo count($approved_exams); ?></div>
                    <div class="stat-label">Approved Exams</div>
                </div>
                <div class="stat-card revision">
                    <div class="stat-icon">🔄</div>
                    <div class="stat-value"><?php echo count($revision_exams); ?></div>
                    <div class="stat-label">Needs Revision</div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tabs-container">
                <div class="tab-header">
                    <button class="tab-btn active" onclick="showTab('draft')">
                        📝 Drafts (<?php echo count($draft_exams); ?>)
                    </button>
                    <button class="tab-btn" onclick="showTab('pending')">
                        ⏳ Pending (<?php echo count($pending_exams); ?>)
                    </button>
                    <button class="tab-btn" onclick="showTab('approved')">
                        ✅ Approved (<?php echo count($approved_exams); ?>)
                    </button>
                    <button class="tab-btn" onclick="showTab('revision')">
                        🔄 Revision (<?php echo count($revision_exams); ?>)
                    </button>
                </div>

                <!-- Draft Exams Tab -->
                <div id="draft-tab" class="tab-content active">
                    <?php if(count($draft_exams) > 0): ?>
                        <?php foreach($draft_exams as $exam): ?>
                        <div class="exam-card draft">
                            <div class="exam-header">
                                <div style="flex: 1;">
                                    <h3 class="exam-title"><?php echo htmlspecialchars($exam['exam_name']); ?></h3>
                                    <div class="exam-meta">
                                        <span class="exam-meta-item">
                                            <strong>📚</strong> <?php echo htmlspecialchars($exam['course_code']); ?>
                                        </span>
                                        <span class="exam-meta-item">
                                            <strong>📖</strong> <?php echo htmlspecialchars($exam['course_name']); ?>
                                        </span>
                                        <span class="exam-meta-item">
                                            <strong>📋</strong> <?php echo htmlspecialchars($exam['category_name']); ?>
                                        </span>
                                        <span class="exam-meta-item">
                                            <strong>❓</strong> <?php echo $exam['question_count']; ?> Questions
                                        </span>
                                        <span class="exam-meta-item">
                                            <strong>⏱️</strong> <?php echo $exam['duration_minutes']; ?> mins
                                        </span>
                                    </div>
                                </div>
                                <span class="status-badge status-draft">Draft</span>
                            </div>
                            
                            <?php if($exam['question_count'] == 0): ?>
                            <div style="padding: 1rem; background: #fff3cd; border-radius: 6px; margin-bottom: 1rem; font-size: 0.9rem; color: #856404;">
                                ⚠️ <strong>Action Required:</strong> Add questions before submitting for approval
                            </div>
                            <?php endif; ?>
                            
                            <div class="exam-actions">
                                <a href="ManageExamQuestions.php?exam_id=<?php echo $exam['exam_id']; ?>" class="btn btn-primary btn-sm">
                                    <span>📝</span> Manage Questions
                                </a>
                                <?php if($exam['question_count'] > 0): ?>
                                <a href="SubmitExamForApproval.php?exam_id=<?php echo $exam['exam_id']; ?>" class="btn btn-success btn-sm">
                                    <span>✅</span> Submit for Approval
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📝</div>
                            <h3>No Draft Exams</h3>
                            <p>You haven't created any exams yet. Start by creating your first exam.</p>
                            <a href="CreateExam.php" class="btn btn-primary">
                                <span>➕</span> Create New Exam
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pending Exams Tab -->
                <div id="pending-tab" class="tab-content">
                    <?php if(count($pending_exams) > 0): ?>
                        <?php foreach($pending_exams as $exam): ?>
                        <div class="exam-card pending">
                            <div class="exam-header">
                                <div style="flex: 1;">
                                    <h3 class="exam-title"><?php echo htmlspecialchars($exam['exam_name']); ?></h3>
                                    <div class="exam-meta">
                                        <span class="exam-meta-item">
                                            <strong>📚</strong> <?php echo htmlspecialchars($exam['course_code']); ?>
                                        </span>
                                        <span class="exam-meta-item">
                                            <strong>📖</strong> <?php echo htmlspecialchars($exam['course_name']); ?>
                                        </span>
                                        <span class="exam-meta-item">
                                            <strong>📅</strong> Submitted: <?php echo date('M d, Y', strtotime($exam['submitted_at'])); ?>
                                        </span>
                                        <span class="exam-meta-item">
                                            <strong>❓</strong> <?php echo $exam['question_count']; ?> Questions
                                        </span>
                                    </div>
                                </div>
                                <span class="status-badge status-pending">Pending</span>
                            </div>
                            <div style="padding: 1rem; background: #e7f3ff; border-radius: 6px; font-size: 0.9rem; color: #004085;">
                                ⏳ <strong>Status:</strong> Waiting for department head approval. You will be notified once reviewed.
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">⏳</div>
                            <h3>No Pending Exams</h3>
                            <p>You don't have any exams waiting for approval.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Approved Exams Tab -->
                <div id="approved-tab" class="tab-content">
                    <?php if(count($approved_exams) > 0): ?>
                        <?php foreach($approved_exams as $exam): ?>
                        <div class="exam-card approved">
                            <div class="exam-header">
                                <div style="flex: 1;">
                                    <h3 class="exam-title"><?php echo htmlspecialchars($exam['exam_name']); ?></h3>
                                    <div class="exam-meta">
                                        <span class="exam-meta-item">
                                            <strong>📚</strong> <?php echo htmlspecialchars($exam['course_code']); ?>
                                        </span>
                                        <span class="exam-meta-item">
                                            <strong>📖</strong> <?php echo htmlspecialchars($exam['course_name']); ?>
                                        </span>
                                        <?php if($exam['exam_date']): ?>
                                        <span class="exam-meta-item">
                                            <strong>📅</strong> <?php echo date('M d, Y', strtotime($exam['exam_date'])); ?>
                                            <?php if($exam['start_time']): ?>
                                                at <?php echo date('h:i A', strtotime($exam['start_time'])); ?>
                                            <?php endif; ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="exam-meta-item">
                                            <strong>📅</strong> Not scheduled yet
                                        </span>
                                        <?php endif; ?>
                                        <span class="exam-meta-item">
                                            <strong>❓</strong> <?php echo $exam['question_count']; ?> Questions
                                        </span>
                                    </div>
                                </div>
                                <span class="status-badge status-approved">Approved</span>
                            </div>
                            
                            <?php if($exam['approval_comments']): ?>
                            <div class="comment-box success">
                                <strong>💬 Approval Comments:</strong>
                                <?php echo nl2br(htmlspecialchars($exam['approval_comments'])); ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if($exam['result_count'] > 0): ?>
                            <div class="exam-actions">
                                <a href="ResultsOverview.php?exam=<?php echo $exam['exam_id']; ?>" class="btn btn-info btn-sm">
                                    <span>📊</span> View Results (<?php echo $exam['result_count']; ?>)
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">✅</div>
                            <h3>No Approved Exams</h3>
                            <p>You don't have any approved exams yet.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Revision Required Tab -->
                <div id="revision-tab" class="tab-content">
                    <?php if(count($revision_exams) > 0): ?>
                        <?php foreach($revision_exams as $exam): ?>
                        <div class="exam-card revision">
                            <div class="exam-header">
                                <div style="flex: 1;">
                                    <h3 class="exam-title"><?php echo htmlspecialchars($exam['exam_name']); ?></h3>
                                    <div class="exam-meta">
                                        <span class="exam-meta-item">
                                            <strong>📚</strong> <?php echo htmlspecialchars($exam['course_code']); ?>
                                        </span>
                                        <span class="exam-meta-item">
                                            <strong>📖</strong> <?php echo htmlspecialchars($exam['course_name']); ?>
                                        </span>
                                        <span class="exam-meta-item">
                                            <strong>🔄</strong> Revision #<?php echo $exam['revision_count']; ?>
                                        </span>
                                    </div>
                                </div>
                                <span class="status-badge status-revision">Revision Required</span>
                            </div>
                            
                            <?php if($exam['approval_comments']): ?>
                            <div class="comment-box warning">
                                <strong>💬 Revision Comments from Department Head:</strong>
                                <?php echo nl2br(htmlspecialchars($exam['approval_comments'])); ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="exam-actions">
                                <a href="ManageExamQuestions.php?exam_id=<?php echo $exam['exam_id']; ?>" class="btn btn-warning btn-sm">
                                    <span>🔄</span> Make Revisions
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">🔄</div>
                            <h3>No Exams Requiring Revision</h3>
                            <p>All your exams are in good standing.</p>
                        </div>
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
