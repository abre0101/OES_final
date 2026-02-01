<?php
session_start();
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Pending Approvals";

// Get filter parameters
$departmentFilter = $_GET['department'] ?? '';
$courseFilter = $_GET['course'] ?? '';

// Build query
$query = "SELECT es.*, c.course_name, c.course_code, d.department_name, 
    ec.category_name,
    (SELECT COUNT(*) FROM exam_questions eq WHERE eq.schedule_id = es.schedule_id) as question_count
    FROM exam_schedules es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN departments d ON c.department_id = d.department_id
    INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    WHERE es.approval_status = 'pending' AND es.submitted_for_approval = TRUE";

$params = [];
$types = "";

if($departmentFilter) {
    $query .= " AND d.department_id = ?";
    $params[] = $departmentFilter;
    $types .= "i";
}

if($courseFilter) {
    $query .= " AND c.course_id = ?";
    $params[] = $courseFilter;
    $types .= "i";
}

$query .= " ORDER BY es.submitted_at DESC";

$stmt = $con->prepare($query);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$pendingExams = $stmt->get_result();

// Get departments for filter
$departments = $con->query("SELECT DISTINCT d.department_id, d.department_name 
    FROM departments d 
    INNER JOIN courses c ON d.department_id = c.department_id
    INNER JOIN exam_schedules es ON c.course_id = es.course_id
    WHERE es.approval_status = 'pending'
    ORDER BY d.department_name");

// Get courses for filter
$courses = $con->query("SELECT DISTINCT c.course_id, c.course_name, c.course_code
    FROM courses c
    INNER JOIN exam_schedules es ON c.course_id = es.course_id
    WHERE es.approval_status = 'pending'
    ORDER BY c.course_name");

// Get statistics
$stats = $con->query("SELECT 
    COUNT(*) as total_pending,
    SUM(CASE WHEN es.submitted_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) as new_today,
    SUM(CASE WHEN es.submitted_at < DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as overdue
    FROM exam_schedules es
    WHERE es.approval_status = 'pending' AND es.submitted_for_approval = TRUE")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approvals - Exam Committee</title>
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
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; border-radius: 12px; padding: 1.75rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); border-left: 5px solid; transition: transform 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.info { border-left-color: #17a2b8; }
        .stat-card.danger { border-left-color: #dc3545; }
        .stat-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
        .stat-value { font-size: 2.5rem; font-weight: 900; color: #003366; margin-bottom: 0.5rem; }
        .stat-label { font-size: 0.95rem; color: #6c757d; font-weight: 500; }
        .filter-section { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); margin-bottom: 2rem; }
        .filter-section h3 { margin: 0 0 1.5rem 0; font-size: 1.5rem; font-weight: 700; color: #003366; }
        .filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem; }
        .filter-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #003366; font-size: 0.95rem; }
        .filter-group select { width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 0.95rem; transition: all 0.3s ease; font-family: 'Poppins', sans-serif; }
        .filter-group select:focus { outline: none; border-color: #003366; box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1); }
        .btn-filter { padding: 0.85rem 1.75rem; border-radius: 8px; font-weight: 600; font-size: 0.95rem; border: none; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; }
        .btn-filter:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3); }
        .exam-card { background: white; border-radius: 12px; padding: 2rem; margin-bottom: 1.5rem; border-left: 5px solid #ffc107; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); transition: all 0.3s; }
        .exam-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12); }
        .exam-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem; }
        .exam-title { font-size: 1.4rem; font-weight: 700; color: #003366; margin: 0 0 0.5rem 0; }
        .exam-meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; padding: 1.5rem; background: #f8f9fa; border-radius: 8px; margin: 1rem 0; }
        .meta-item { display: flex; flex-direction: column; gap: 0.25rem; }
        .meta-label { font-size: 0.85rem; color: #6c757d; font-weight: 600; }
        .meta-value { font-size: 1rem; color: #003366; font-weight: 600; }
        .action-buttons { display: flex; gap: 1rem; flex-wrap: wrap; }
        .btn-approve { background: linear-gradient(135deg, #28a745 0%, #218838 100%); color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-approve:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3); }
        .btn-revision { background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%); color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-revision:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3); }
        .btn-reject { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-reject:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3); }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 2rem; border-radius: 12px; max-width: 600px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .modal-title { font-size: 1.5rem; font-weight: 700; color: #003366; }
        .close { font-size: 2rem; font-weight: bold; color: #6c757d; cursor: pointer; }
        .close:hover { color: #003366; }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php $pageTitle = 'Pending Approvals'; include 'header-component.php'; ?>

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
                <h1><span>⏳</span> Pending Approvals</h1>
                <p>Review and approve examination submissions</p>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card warning">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-value"><?php echo number_format($stats['total_pending']); ?></div>
                    <div class="stat-label">Total Pending</div>
                </div>
                <div class="stat-card info">
                    <div class="stat-icon">🆕</div>
                    <div class="stat-value"><?php echo number_format($stats['new_today']); ?></div>
                    <div class="stat-label">New Today</div>
                </div>
                <div class="stat-card danger">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-value"><?php echo number_format($stats['overdue']); ?></div>
                    <div class="stat-label">Over 7 Days</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-section">
                <h3>🔍 Filter Exams</h3>
                <form method="GET" action="">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label>Department</label>
                            <select name="department">
                                <option value="">All Departments</option>
                                <?php while($dept = $departments->fetch_assoc()): ?>
                                <option value="<?php echo $dept['department_id']; ?>" <?php echo $departmentFilter == $dept['department_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Course</label>
                            <select name="course">
                                <option value="">All Courses</option>
                                <?php while($course = $courses->fetch_assoc()): ?>
                                <option value="<?php echo $course['course_id']; ?>" <?php echo $courseFilter == $course['course_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_code']); ?> - <?php echo htmlspecialchars($course['course_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-filter"><span>🔍</span> Apply Filters</button>
                    <?php if($departmentFilter || $courseFilter): ?>
                    <a href="PendingApprovals.php" class="btn-filter" style="background: #6c757d; margin-left: 1rem;">Clear Filters</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Pending Exams List -->
            <?php if($pendingExams->num_rows > 0): ?>
                <?php while($exam = $pendingExams->fetch_assoc()): ?>
                <div class="exam-card">
                    <div class="exam-header">
                        <div>
                            <h3 class="exam-title"><?php echo htmlspecialchars($exam['exam_name']); ?></h3>
                            <p style="color: #6c757d; margin: 0;">Submitted <?php echo date('M d, Y \a\t H:i', strtotime($exam['submitted_at'])); ?></p>
                        </div>
                        <span style="background: #ffc107; color: #000; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 700;">⏳ Pending</span>
                    </div>

                    <div class="exam-meta">
                        <div class="meta-item">
                            <span class="meta-label">Course</span>
                            <span class="meta-value"><?php echo htmlspecialchars($exam['course_code']); ?> - <?php echo htmlspecialchars($exam['course_name']); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Department</span>
                            <span class="meta-value"><?php echo htmlspecialchars($exam['department_name']); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Category</span>
                            <span class="meta-value"><?php echo htmlspecialchars($exam['category_name']); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Exam Date</span>
                            <span class="meta-value"><?php echo date('M d, Y', strtotime($exam['exam_date'])); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Questions</span>
                            <span class="meta-value"><?php echo $exam['question_count']; ?> Questions</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Duration</span>
                            <span class="meta-value"><?php echo $exam['duration_minutes']; ?> Minutes</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Revision Count</span>
                            <span class="meta-value"><?php echo $exam['revision_count']; ?> Times</span>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn-approve" onclick="showApprovalModal(<?php echo $exam['schedule_id']; ?>, 'approve', '<?php echo htmlspecialchars($exam['exam_name']); ?>')">
                            ✓ Approve
                        </button>
                        <button class="btn-revision" onclick="showApprovalModal(<?php echo $exam['schedule_id']; ?>, 'revision', '<?php echo htmlspecialchars($exam['exam_name']); ?>')">
                            ✏️ Request Revision
                        </button>
                        <button class="btn-reject" onclick="showApprovalModal(<?php echo $exam['schedule_id']; ?>, 'reject', '<?php echo htmlspecialchars($exam['exam_name']); ?>')">
                            ✗ Reject
                        </button>
                        <a href="ViewExamDetails.php?schedule_id=<?php echo $exam['schedule_id']; ?>" class="btn-filter" style="background: #6c757d;">
                            👁️ View Details
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="background: white; border-radius: 12px; padding: 3rem; text-align: center; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);">
                    <div style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;">✓</div>
                    <h3 style="color: #6c757d; margin-bottom: 0.5rem;">No Pending Approvals</h3>
                    <p style="color: #6c757d;">All exams have been reviewed. Great job!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Approval Modal -->
    <div id="approvalModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Approve Exam</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" action="ProcessApproval.php">
                <input type="hidden" name="schedule_id" id="modalScheduleId">
                <input type="hidden" name="action" id="modalAction">
                
                <p id="modalMessage" style="margin-bottom: 1.5rem; color: #6c757d;"></p>
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #003366;">Comments <span id="requiredLabel">(Required)</span></label>
                    <textarea name="comments" id="modalComments" rows="4" style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 0.95rem;" placeholder="Enter your comments..."></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="closeModal()" style="padding: 0.75rem 1.5rem; border: 2px solid #6c757d; background: white; color: #6c757d; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit" id="modalSubmitBtn" style="padding: 0.75rem 1.5rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; color: white;">
                        Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showApprovalModal(scheduleId, action, examName) {
            const modal = document.getElementById('approvalModal');
            const title = document.getElementById('modalTitle');
            const message = document.getElementById('modalMessage');
            const submitBtn = document.getElementById('modalSubmitBtn');
            const requiredLabel = document.getElementById('requiredLabel');
            const commentsField = document.getElementById('modalComments');
            
            document.getElementById('modalScheduleId').value = scheduleId;
            document.getElementById('modalAction').value = action;
            
            const config = {
                approve: {
                    title: '✓ Approve Exam',
                    message: `Are you sure you want to approve "${examName}"? This will make it available to students.`,
                    btnColor: '#28a745',
                    required: false
                },
                revision: {
                    title: '✏️ Request Revision',
                    message: `Request revision for "${examName}". Please provide specific feedback for the instructor.`,
                    btnColor: '#ff9800',
                    required: true
                },
                reject: {
                    title: '✗ Reject Exam',
                    message: `Are you sure you want to reject "${examName}"? Please provide a reason.`,
                    btnColor: '#dc3545',
                    required: true
                }
            };
            
            const settings = config[action];
            title.textContent = settings.title;
            message.textContent = settings.message;
            submitBtn.style.background = settings.btnColor;
            requiredLabel.style.display = settings.required ? 'inline' : 'none';
            commentsField.required = settings.required;
            commentsField.value = '';
            
            modal.style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('approvalModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('approvalModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>
