<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Manage Exam Schedules";
$instructor_id = $_SESSION['ID'];

// Get instructor's courses with exam schedules
$schedulesQuery = $con->prepare("SELECT 
    es.*,
    c.course_name,
    c.course_code,
    c.semester,
    ec.category_name,
    COUNT(DISTINCT eq.question_id) as question_count
    FROM exams es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    LEFT JOIN exam_questions eq ON es.exam_id = eq.exam_id
    WHERE ic.instructor_id = ?
    GROUP BY es.exam_id
    ORDER BY es.exam_date DESC, es.start_time DESC");
$schedulesQuery->bind_param("i", $instructor_id);
$schedulesQuery->execute();
$schedules = $schedulesQuery->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Exam Schedules - Instructor</title>
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
        .btn-warning { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #212529; }
        .btn-warning:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3); }
        .btn-danger { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; }
        .btn-danger:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3); }
        .btn-sm { padding: 0.5rem 1rem; font-size: 0.875rem; }
        
        .schedule-card { background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); border-left: 4px solid #003366; transition: all 0.3s ease; }
        .schedule-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12); }
        .schedule-card.upcoming { border-left-color: #28a745; }
        .schedule-card.ongoing { border-left-color: #ffc107; }
        .schedule-card.completed { border-left-color: #6c757d; }
        
        .status-badge { padding: 0.35rem 0.85rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; display: inline-block; }
        .status-badge.upcoming { background: rgba(40, 167, 69, 0.1); color: #28a745; }
        .status-badge.ongoing { background: rgba(255, 193, 7, 0.1); color: #f57c00; }
        .status-badge.completed { background: rgba(108, 117, 125, 0.1); color: #6c757d; }
        .status-badge.pending { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .status-badge.approved { background: rgba(40, 167, 69, 0.1); color: #28a745; }
        .status-badge.rejected { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
        
        .tabs-container { background: white; border-radius: 12px; padding: 0; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); overflow: hidden; }
        .tabs-header { display: flex; background: #f8f9fa; border-bottom: 2px solid #e0e0e0; }
        .tab-btn { flex: 1; padding: 1rem 1.5rem; border: none; background: transparent; font-weight: 600; font-size: 0.95rem; color: #6c757d; cursor: pointer; transition: all 0.3s ease; border-bottom: 3px solid transparent; }
        .tab-btn.active { color: #003366; border-bottom-color: #003366; background: white; }
        .tab-btn:hover { background: rgba(0, 51, 102, 0.05); }
        .tab-content { display: none; padding: 2rem; }
        .tab-content.active { display: block; }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header-modern">
                <h1><span>📅</span> Exam Schedules</h1>
                <p>Create and manage exam schedules for your courses</p>
            </div>

            <!-- Action Buttons -->
            <div style="margin-bottom: 2rem;">
                <a href="CreateSchedule.php" class="btn-modern btn-success">
                    ➕ Create New Schedule
                </a>
            </div>

            <!-- Schedules List -->
            <div class="tabs-container">
                <div class="tabs-header">
                    <button class="tab-btn active" onclick="switchTab(0)">All Schedules</button>
                    <button class="tab-btn" onclick="switchTab(1)">Upcoming</button>
                    <button class="tab-btn" onclick="switchTab(2)">Ongoing</button>
                    <button class="tab-btn" onclick="switchTab(3)">Completed</button>
                </div>

                <!-- All Schedules Tab -->
                <div class="tab-content active">
                    <?php
                    if($schedules->num_rows > 0):
                        $schedules->data_seek(0);
                        while($schedule = $schedules->fetch_assoc()):
                            $now = new DateTime();
                            $examDate = new DateTime($schedule['exam_date'] . ' ' . $schedule['start_time']);
                            $endDate = new DateTime($schedule['exam_date'] . ' ' . $schedule['end_time']);
                            
                            if($now < $examDate) {
                                $status = 'upcoming';
                                $statusLabel = 'Upcoming';
                            } elseif($now >= $examDate && $now <= $endDate) {
                                $status = 'ongoing';
                                $statusLabel = 'Ongoing';
                            } else {
                                $status = 'completed';
                                $statusLabel = 'Completed';
                            }
                    ?>
                    <div class="schedule-card <?php echo $status; ?>">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <div style="flex: 1;">
                                <h3 style="margin: 0 0 0.5rem 0; color: #003366; font-size: 1.3rem;">
                                    <?php echo htmlspecialchars($schedule['exam_name']); ?>
                                </h3>
                                <p style="margin: 0 0 0.5rem 0; color: #6c757d; font-size: 1.05rem;">
                                    <?php echo htmlspecialchars($schedule['course_name']); ?> (<?php echo $schedule['course_code']; ?>) - Semester <?php echo $schedule['semester']; ?>
                                </p>
                                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: 0.75rem;">
                                    <span class="status-badge <?php echo $status; ?>">
                                        <?php echo $statusLabel; ?>
                                    </span>
                                    <?php if($schedule['approval_status'] == 'pending'): ?>
                                    <span class="status-badge pending">
                                        ⏳ Awaiting Committee Approval
                                    </span>
                                    <?php elseif($schedule['approval_status'] == 'approved'): ?>
                                    <span class="status-badge approved">
                                        ✅ Approved
                                    </span>
                                    <?php elseif($schedule['approval_status'] == 'draft'): ?>
                                    <span class="status-badge" style="background: rgba(108, 117, 125, 0.1); color: #6c757d;">
                                        📝 Draft - Not Submitted
                                    </span>
                                    <?php elseif($schedule['approval_status'] == 'revision'): ?>
                                    <span class="status-badge" style="background: rgba(255, 152, 0, 0.1); color: #ff9800;">
                                        ✏️ Revision Required
                                    </span>
                                    <?php elseif($schedule['approval_status'] == 'rejected'): ?>
                                    <span class="status-badge rejected">
                                        ❌ Rejected
                                    </span>
                                    <?php endif; ?>
                                    <span style="color: #6c757d; font-size: 0.9rem;">
                                        📝 <?php echo $schedule['question_count']; ?> Questions
                                    </span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <?php if($schedule['approval_status'] == 'draft'): ?>
                                    <?php 
                                    $canSubmit = $schedule['question_count'] >= 5;
                                    ?>
                                    <a href="SubmitExamForApproval.php?exam_id=<?php echo $schedule['exam_id']; ?>" 
                                       class="btn-modern btn-success btn-sm <?php echo !$canSubmit ? 'disabled' : ''; ?>" 
                                       <?php if(!$canSubmit): ?>
                                       onclick="alert('Minimum 5 questions required. Current: <?php echo $schedule['question_count']; ?>'); return false;" 
                                       style="opacity: 0.5; cursor: not-allowed;" 
                                       title="Add at least 5 questions before submitting (Current: <?php echo $schedule['question_count']; ?>)"
                                       <?php else: ?>
                                       onclick="return confirm('Submit this exam for committee approval? You won\'t be able to edit it until reviewed.')"
                                       <?php endif; ?>>
                                        📤 Submit for Approval
                                    </a>
                                <?php endif; ?>
                                
                                <?php if($schedule['approval_status'] == 'revision'): ?>
                                <a href="EditSchedule.php?id=<?php echo $schedule['exam_id']; ?>" class="btn-modern btn-warning btn-sm">
                                    ✏️ Revise & Resubmit
                                </a>
                                <?php endif; ?>
                                
                                <a href="ViewExam.php?id=<?php echo $schedule['exam_id']; ?>" class="btn-modern btn-primary btn-sm">
                                    👁️ View
                                </a>
                                
                                <?php if($schedule['approval_status'] == 'draft' || $schedule['approval_status'] == 'revision'): ?>
                                <a href="EditSchedule.php?id=<?php echo $schedule['exam_id']; ?>" class="btn-modern btn-warning btn-sm">
                                    ✏️ Edit
                                </a>
                                <button class="btn-modern btn-danger btn-sm" onclick="deleteSchedule(<?php echo $schedule['exam_id']; ?>)">
                                    🗑️ Delete
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px; margin-top: 1rem;">
                            <div>
                                <strong style="color: #6c757d; font-size: 0.85rem;">Date & Time</strong>
                                <p style="margin: 0.25rem 0 0 0; color: #212529; font-weight: 600;">
                                    <?php echo date('M d, Y', strtotime($schedule['exam_date'])); ?><br>
                                    <span style="font-size: 0.9rem; font-weight: normal;">
                                        <?php echo date('g:i A', strtotime($schedule['start_time'])); ?> - <?php echo date('g:i A', strtotime($schedule['end_time'])); ?>
                                    </span>
                                </p>
                            </div>
                            <div>
                                <strong style="color: #6c757d; font-size: 0.85rem;">Duration</strong>
                                <p style="margin: 0.25rem 0 0 0; color: #212529; font-weight: 600;">
                                    <?php echo $schedule['duration_minutes']; ?> minutes
                                </p>
                            </div>
                            <div>
                                <strong style="color: #6c757d; font-size: 0.85rem;">Category</strong>
                                <p style="margin: 0.25rem 0 0 0; color: #212529; font-weight: 600;">
                                    <?php echo htmlspecialchars($schedule['category_name']); ?>
                                </p>
                            </div>
                            <div>
                                <strong style="color: #6c757d; font-size: 0.85rem;">Marks</strong>
                                <p style="margin: 0.25rem 0 0 0; color: #212529; font-weight: 600;">
                                    Total: <?php echo $schedule['total_marks']; ?> | Pass: <?php echo $schedule['pass_marks']; ?>
                                </p>
                            </div>
                        </div>
                        
                        <?php if(($schedule['approval_status'] == 'revision' || $schedule['approval_status'] == 'rejected') && !empty($schedule['reviewer_comments'])): ?>
                        <div style="margin-top: 1rem; padding: 1rem; background: <?php echo $schedule['approval_status'] == 'rejected' ? 'rgba(220, 53, 69, 0.1)' : 'rgba(255, 152, 0, 0.1)'; ?>; border-radius: 8px; border-left: 4px solid <?php echo $schedule['approval_status'] == 'rejected' ? '#dc3545' : '#ff9800'; ?>;">
                            <strong style="color: <?php echo $schedule['approval_status'] == 'rejected' ? '#dc3545' : '#ff9800'; ?>; font-size: 0.9rem;">
                                <?php echo $schedule['approval_status'] == 'rejected' ? '❌ Rejection Reason:' : '✏️ Committee Feedback:'; ?>
                            </strong>
                            <p style="margin: 0.5rem 0 0 0; color: #212529; font-size: 0.95rem;">
                                <?php echo nl2br(htmlspecialchars($schedule['reviewer_comments'])); ?>
                            </p>
                            <?php if(!empty($schedule['reviewed_by'])): ?>
                            <p style="margin: 0.5rem 0 0 0; color: #6c757d; font-size: 0.85rem;">
                                — <?php echo htmlspecialchars($schedule['reviewed_by']); ?> 
                                <?php if(!empty($schedule['reviewed_at'])): ?>
                                on <?php echo date('M d, Y', strtotime($schedule['reviewed_at'])); ?>
                                <?php endif; ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <div style="text-align: center; padding: 4rem;">
                            <div style="font-size: 4rem; margin-bottom: 1rem;">📅</div>
                            <h3 style="color: #6c757d;">No Exam Schedules Yet</h3>
                            <p>Create your first exam schedule to get started</p>
                            <a href="CreateSchedule.php" class="btn-modern btn-success" style="margin-top: 1rem;">
                                Create Schedule
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Upcoming Tab -->
                <div class="tab-content">
                    <?php
                    $schedules->data_seek(0);
                    $hasUpcoming = false;
                    while($schedule = $schedules->fetch_assoc()):
                        $now = new DateTime();
                        $examDate = new DateTime($schedule['exam_date'] . ' ' . $schedule['start_time']);
                        
                        if($now < $examDate):
                            $hasUpcoming = true;
                    ?>
                    <div class="schedule-card upcoming">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <h3 style="margin: 0 0 0.5rem 0; color: #003366;">
                                    <?php echo htmlspecialchars($schedule['exam_name']); ?>
                                </h3>
                                <p style="margin: 0; color: #6c757d;">
                                    <?php echo htmlspecialchars($schedule['course_name']); ?> (Semester <?php echo $schedule['semester']; ?>) - 
                                    <?php echo date('M d, Y g:i A', strtotime($schedule['exam_date'] . ' ' . $schedule['start_time'])); ?>
                                </p>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="ViewExam.php?id=<?php echo $schedule['exam_id']; ?>" class="btn-modern btn-primary btn-sm">👁️</a>
                                <a href="EditSchedule.php?id=<?php echo $schedule['exam_id']; ?>" class="btn-modern btn-warning btn-sm">✏️</a>
                            </div>
                        </div>
                    </div>
                    <?php 
                        endif;
                    endwhile;
                    
                    if(!$hasUpcoming):
                    ?>
                        <div style="text-align: center; padding: 4rem;">
                            <h3 style="color: #6c757d;">No Upcoming Exams</h3>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Ongoing Tab -->
                <div class="tab-content">
                    <?php
                    $schedules->data_seek(0);
                    $hasOngoing = false;
                    while($schedule = $schedules->fetch_assoc()):
                        $now = new DateTime();
                        $examDate = new DateTime($schedule['exam_date'] . ' ' . $schedule['start_time']);
                        $endDate = new DateTime($schedule['exam_date'] . ' ' . $schedule['end_time']);
                        
                        if($now >= $examDate && $now <= $endDate):
                            $hasOngoing = true;
                    ?>
                    <div class="schedule-card ongoing">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <h3 style="margin: 0 0 0.5rem 0; color: #003366;">
                                    <?php echo htmlspecialchars($schedule['exam_name']); ?>
                                </h3>
                                <p style="margin: 0; color: #6c757d;">
                                    <?php echo htmlspecialchars($schedule['course_name']); ?> (Semester <?php echo $schedule['semester']; ?>) - 
                                    Ends at <?php echo date('g:i A', strtotime($schedule['end_time'])); ?>
                                </p>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="ViewExam.php?id=<?php echo $schedule['exam_id']; ?>" class="btn-modern btn-primary btn-sm">👁️</a>
                            </div>
                        </div>
                    </div>
                    <?php 
                        endif;
                    endwhile;
                    
                    if(!$hasOngoing):
                    ?>
                        <div style="text-align: center; padding: 4rem;">
                            <h3 style="color: #6c757d;">No Ongoing Exams</h3>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Completed Tab -->
                <div class="tab-content">
                    <?php
                    $schedules->data_seek(0);
                    $hasCompleted = false;
                    while($schedule = $schedules->fetch_assoc()):
                        $now = new DateTime();
                        $endDate = new DateTime($schedule['exam_date'] . ' ' . $schedule['end_time']);
                        
                        if($now > $endDate):
                            $hasCompleted = true;
                    ?>
                    <div class="schedule-card completed">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <h3 style="margin: 0 0 0.5rem 0; color: #003366;">
                                    <?php echo htmlspecialchars($schedule['exam_name']); ?>
                                </h3>
                                <p style="margin: 0; color: #6c757d;">
                                    <?php echo htmlspecialchars($schedule['course_name']); ?> (Semester <?php echo $schedule['semester']; ?>) - 
                                    <?php echo date('M d, Y', strtotime($schedule['exam_date'])); ?>
                                </p>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="ViewExam.php?id=<?php echo $schedule['exam_id']; ?>" class="btn-modern btn-primary btn-sm">👁️</a>
                                <a href="ResultsOverview.php?exam_id=<?php echo $schedule['exam_id']; ?>" class="btn-modern btn-success btn-sm">📊 Results</a>
                            </div>
                        </div>
                    </div>
                    <?php 
                        endif;
                    endwhile;
                    
                    if(!$hasCompleted):
                    ?>
                        <div style="text-align: center; padding: 4rem;">
                            <h3 style="color: #6c757d;">No Completed Exams</h3>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function switchTab(index) {
            const tabs = document.querySelectorAll('.tab-content');
            const buttons = document.querySelectorAll('.tab-btn');
            
            tabs.forEach(tab => tab.classList.remove('active'));
            buttons.forEach(btn => btn.classList.remove('active'));
            
            tabs[index].classList.add('active');
            buttons[index].classList.add('active');
        }
        
        function deleteSchedule(id) {
            if(confirm('Are you sure you want to delete this exam schedule? This will also delete all associated questions and results.')) {
                window.location.href = 'DeleteSchedule.php?id=' + id;
            }
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
