<?php
session_start();
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$instructor_id = $_SESSION['ID'];
$pageTitle = "Manage Exams";

// Get filter
$statusFilter = $_GET['status'] ?? 'all';

// Build query
$query = "SELECT es.*, c.course_name, c.course_code, ec.category_name,
    (SELECT COUNT(*) FROM exam_questions eq WHERE eq.schedule_id = es.schedule_id) as question_count,
    (SELECT COUNT(DISTINCT er.student_id) FROM exam_results er WHERE er.schedule_id = es.schedule_id) as student_count
    FROM exam_schedules es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE";

if($statusFilter != 'all') {
    $query .= " AND es.approval_status = ?";
}

$query .= " ORDER BY es.exam_date DESC";

$stmt = $con->prepare($query);
if($statusFilter != 'all') {
    $stmt->bind_param("is", $instructor_id, $statusFilter);
} else {
    $stmt->bind_param("i", $instructor_id);
}
$stmt->execute();
$exams = $stmt->get_result();

// Get statistics
$stats = $con->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN approval_status = 'revision' THEN 1 ELSE 0 END) as revision,
    SUM(CASE WHEN approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM exam_schedules es
    INNER JOIN instructor_courses ic ON es.course_id = ic.course_id
    WHERE ic.instructor_id = $instructor_id AND ic.is_active = TRUE")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Exams - Instructor</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        .page-header-modern { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2); margin-bottom: 2rem; }
        .page-header-modern h1 { margin: 0 0 0.5rem 0; font-size: 2.2rem; font-weight: 800; display: flex; align-items: center; gap: 1rem; color: white; }
        .page-header-modern h1 span { color: white; }
        .page-header-modern p { margin: 0; opacity: 0.95; font-size: 1.05rem; color: white; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); border-left: 5px solid; transition: transform 0.3s ease; text-align: center; cursor: pointer; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card.all { border-left-color: #007bff; }
        .stat-card.pending { border-left-color: #ffc107; }
        .stat-card.approved { border-left-color: #28a745; }
        .stat-card.revision { border-left-color: #ff9800; }
        .stat-card.rejected { border-left-color: #dc3545; }
        .stat-card.active { background: linear-gradient(135deg, rgba(0, 51, 102, 0.1), rgba(0, 85, 170, 0.05)); }
        .stat-value { font-size: 2rem; font-weight: 900; color: #003366; margin-bottom: 0.5rem; }
        .stat-label { font-size: 0.9rem; color: #6c757d; font-weight: 600; }
        .exam-card { background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; border-left: 5px solid; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); transition: all 0.3s; }
        .exam-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12); }
        .exam-card.pending { border-left-color: #ffc107; }
        .exam-card.approved { border-left-color: #28a745; }
        .exam-card.revision { border-left-color: #ff9800; }
        .exam-card.rejected { border-left-color: #dc3545; }
        .status-badge { padding: 0.5rem 1rem; border-radius: 20px; font-weight: 700; font-size: 0.85rem; }
        .status-pending { background: #ffc107; color: #000; }
        .status-approved { background: #28a745; color: white; }
        .status-revision { background: #ff9800; color: white; }
        .status-rejected { background: #dc3545; color: white; }
        .btn-submit { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3); }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php $pageTitle = 'Manage Exams'; include 'header-component.php'; ?>

        <div class="admin-content">
            <?php if(isset($_SESSION['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #28a745;">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #dc3545;">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>

            <div class="page-header-modern">
                <h1><span>📋</span> Manage Exams</h1>
                <p>Create, submit, and track your examination approvals</p>
            </div>

            <!-- Status Filter -->
            <div class="stats-grid">
                <a href="?status=all" style="text-decoration: none;">
                    <div class="stat-card all <?php echo $statusFilter == 'all' ? 'active' : ''; ?>">
                        <div class="stat-value"><?php echo $stats['total']; ?></div>
                        <div class="stat-label">All Exams</div>
                    </div>
                </a>
                <a href="?status=pending" style="text-decoration: none;">
                    <div class="stat-card pending <?php echo $statusFilter == 'pending' ? 'active' : ''; ?>">
                        <div class="stat-value"><?php echo $stats['pending']; ?></div>
                        <div class="stat-label">⏳ Pending</div>
                    </div>
                </a>
                <a href="?status=approved" style="text-decoration: none;">
                    <div class="stat-card approved <?php echo $statusFilter == 'approved' ? 'active' : ''; ?>">
                        <div class="stat-value"><?php echo $stats['approved']; ?></div>
                        <div class="stat-label">✓ Approved</div>
                    </div>
                </a>
                <a href="?status=revision" style="text-decoration: none;">
                    <div class="stat-card revision <?php echo $statusFilter == 'revision' ? 'active' : ''; ?>">
                        <div class="stat-value"><?php echo $stats['revision']; ?></div>
                        <div class="stat-label">✏️ Revision</div>
                    </div>
                </a>
                <a href="?status=rejected" style="text-decoration: none;">
                    <div class="stat-card rejected <?php echo $statusFilter == 'rejected' ? 'active' : ''; ?>">
                        <div class="stat-value"><?php echo $stats['rejected']; ?></div>
                        <div class="stat-label">✗ Rejected</div>
                    </div>
                </a>
            </div>

            <!-- Exams List -->
            <?php if($exams->num_rows > 0): ?>
                <?php while($exam = $exams->fetch_assoc()): 
                    $status = $exam['approval_status'] ?? 'pending';
                    $canSubmit = !$exam['submitted_for_approval'] && $exam['question_count'] >= 5;
                    $canResubmit = $status == 'revision' && $exam['question_count'] >= 5;
                    $hasMinQuestions = $exam['question_count'] >= 5;
                ?>
                <div class="exam-card <?php echo $status; ?>">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <div>
                            <h3 style="margin: 0 0 0.5rem 0; color: #003366; font-size: 1.3rem;">
                                <?php echo htmlspecialchars($exam['exam_name']); ?>
                            </h3>
                            <p style="color: #6c757d; margin: 0; font-size: 0.9rem;">
                                <?php echo htmlspecialchars($exam['course_code']); ?> - <?php echo htmlspecialchars($exam['course_name']); ?>
                            </p>
                        </div>
                        <span class="status-badge status-<?php echo $status; ?>">
                            <?php 
                            $icons = ['pending' => '⏳', 'approved' => '✓', 'revision' => '✏️', 'rejected' => '✗'];
                            echo $icons[$status] . ' ' . ucfirst($status); 
                            ?>
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px; margin: 1rem 0;">
                        <div>
                            <div style="font-size: 0.85rem; color: #6c757d; font-weight: 600;">Category</div>
                            <div style="font-size: 1rem; color: #003366; font-weight: 600;"><?php echo htmlspecialchars($exam['category_name']); ?></div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: #6c757d; font-weight: 600;">Exam Date</div>
                            <div style="font-size: 1rem; color: #003366; font-weight: 600;"><?php echo date('M d, Y', strtotime($exam['exam_date'])); ?></div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: #6c757d; font-weight: 600;">Questions</div>
                            <div style="font-size: 1rem; color: #003366; font-weight: 600;"><?php echo $exam['question_count']; ?> Questions</div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: #6c757d; font-weight: 600;">Duration</div>
                            <div style="font-size: 1rem; color: #003366; font-weight: 600;"><?php echo $exam['duration_minutes']; ?> Min</div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: #6c757d; font-weight: 600;">Students Taken</div>
                            <div style="font-size: 1rem; color: #003366; font-weight: 600;"><?php echo $exam['student_count']; ?> Students</div>
                        </div>
                    </div>

                    <?php if($exam['review_comments']): ?>
                    <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 1rem; border-radius: 8px; margin: 1rem 0;">
                        <strong style="color: #856404;">Committee Comments:</strong>
                        <p style="margin: 0.5rem 0 0 0; color: #856404;"><?php echo htmlspecialchars($exam['review_comments']); ?></p>
                    </div>
                    <?php endif; ?>

                    <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem;">
                        <a href="ManageQuestions.php?schedule_id=<?php echo $exam['schedule_id']; ?>" class="btn-submit" style="background: #6c757d;">
                            📝 Manage Questions
                        </a>
                        
                        <?php if(!$exam['submitted_for_approval'] && $exam['question_count'] > 0): ?>
                            <?php if(!$hasMinQuestions): ?>
                            <div style="flex: 1; background: #fff3cd; border: 2px solid #ffc107; padding: 0.75rem; border-radius: 8px;">
                                <strong style="color: #856404;">⚠️ Minimum 5 Questions Required</strong>
                                <p style="margin: 0.25rem 0 0 0; color: #856404; font-size: 0.9rem;">
                                    Current: <?php echo $exam['question_count']; ?> / 5 questions
                                </p>
                            </div>
                            <?php endif; ?>
                            <button type="button" 
                                    class="btn-submit <?php echo !$canSubmit ? 'disabled' : ''; ?>" 
                                    <?php if($canSubmit): ?>
                                    onclick="if(confirm('Submit this exam for approval?')) { window.location.href='SubmitExamForApproval.php?schedule_id=<?php echo $exam['schedule_id']; ?>'; }"
                                    <?php else: ?>
                                    onclick="alert('Minimum 5 questions required. Current: <?php echo $exam['question_count']; ?>');" 
                                    style="opacity: 0.5; cursor: not-allowed;"
                                    <?php endif; ?>>
                                📤 Submit for Approval
                            </button>
                        <?php endif; ?>
                        
                        <?php if($status == 'revision' && $exam['question_count'] > 0): ?>
                            <?php if(!$hasMinQuestions): ?>
                            <div style="flex: 1; background: #fff3cd; border: 2px solid #ffc107; padding: 0.75rem; border-radius: 8px;">
                                <strong style="color: #856404;">⚠️ Minimum 5 Questions Required</strong>
                                <p style="margin: 0.25rem 0 0 0; color: #856404; font-size: 0.9rem;">
                                    Current: <?php echo $exam['question_count']; ?> / 5 questions
                                </p>
                            </div>
                            <?php endif; ?>
                            <button type="button" 
                                    class="btn-submit <?php echo !$canResubmit ? 'disabled' : ''; ?>" 
                                    style="background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%); <?php echo !$canResubmit ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>" 
                                    <?php if($canResubmit): ?>
                                    onclick="if(confirm('Resubmit this exam for approval?')) { window.location.href='SubmitExamForApproval.php?schedule_id=<?php echo $exam['schedule_id']; ?>'; }"
                                    <?php else: ?>
                                    onclick="alert('Minimum 5 questions required. Current: <?php echo $exam['question_count']; ?>');"
                                    <?php endif; ?>>
                                🔄 Resubmit
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="background: white; border-radius: 12px; padding: 3rem; text-align: center; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);">
                    <div style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;">📋</div>
                    <h3 style="color: #6c757d; margin-bottom: 0.5rem;">No Exams Found</h3>
                    <p style="color: #6c757d;">Create your first exam to get started.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>
