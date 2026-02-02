<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Schedule Exams";

$message = '';
$messageType = '';
$deptId = $_SESSION['DeptId'] ?? null;

// Get current tab
$activeTab = $_GET['tab'] ?? 'pending';

// Get pending exams (approved but not scheduled)
$pending_query = "SELECT es.*, c.course_name, c.course_code, ec.category_name, i.full_name as instructor_name
                  FROM exams es
                  LEFT JOIN courses c ON es.course_id = c.course_id
                  LEFT JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
                  LEFT JOIN instructors i ON es.created_by = i.instructor_id
                  WHERE c.department_id = ? 
                  AND es.approval_status = 'approved'
                  AND (es.exam_date IS NULL OR es.exam_date = '0000-00-00')
                  ORDER BY es.updated_at DESC";
$stmt = $con->prepare($pending_query);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$pending_exams = $stmt->get_result();

// Get scheduled exams (future exams)
$scheduled_query = "SELECT es.*, c.course_name, c.course_code, ec.category_name, i.full_name as instructor_name
                    FROM exams es
                    LEFT JOIN courses c ON es.course_id = c.course_id
                    LEFT JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
                    LEFT JOIN instructors i ON es.created_by = i.instructor_id
                    WHERE c.department_id = ? 
                    AND es.approval_status = 'approved'
                    AND es.exam_date IS NOT NULL 
                    AND es.exam_date != '0000-00-00'
                    AND es.exam_date >= CURDATE()
                    ORDER BY es.exam_date ASC, es.start_time ASC";
$stmt = $con->prepare($scheduled_query);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$scheduled_exams = $stmt->get_result();

// Get past exams
$past_query = "SELECT es.*, c.course_name, c.course_code, ec.category_name, i.full_name as instructor_name
               FROM exams es
               LEFT JOIN courses c ON es.course_id = c.course_id
               LEFT JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
               LEFT JOIN instructors i ON es.created_by = i.instructor_id
               WHERE c.department_id = ? 
               AND es.approval_status = 'approved'
               AND es.exam_date IS NOT NULL 
               AND es.exam_date != '0000-00-00'
               AND es.exam_date < CURDATE()
               ORDER BY es.exam_date DESC";
$stmt = $con->prepare($past_query);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$past_exams = $stmt->get_result();

// Handle scheduling
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['schedule_exam'])) {
    $exam_id = $_POST['exam_id'];
    $exam_date = $_POST['exam_date'];
    $start_time = $_POST['start_time'];
    $duration = $_POST['duration_minutes'];
    $academic_year = mysqli_real_escape_string($con, $_POST['academic_year']);
    $room_lab = mysqli_real_escape_string($con, $_POST['room_lab'] ?? '');
    
    // Calculate end time
    $start_datetime = new DateTime($exam_date . ' ' . $start_time);
    $start_datetime->add(new DateInterval('PT' . $duration . 'M'));
    $end_time = $start_datetime->format('H:i:s');
    
    $instructions_append = !empty($room_lab) ? "\nRoom/Lab: " . $room_lab : '';
    
    $update_query = "UPDATE exams 
                     SET exam_date = ?, 
                         start_time = ?, 
                         end_time = ?,
                         duration_minutes = ?,
                         instructions = CONCAT(COALESCE(instructions, ''), ?),
                         is_active = 1,
                         updated_at = NOW()
                     WHERE exam_id = ? AND approval_status = 'approved'";
    $stmt = $con->prepare($update_query);
    $stmt->bind_param("sssisi", $exam_date, $start_time, $end_time, $duration, $instructions_append, $exam_id);
    
    if($stmt->execute()) {
        $message = "Exam scheduled successfully!";
        $messageType = "success";
        header("Location: ScheduleExam.php?tab=scheduled&success=1");
        exit();
    } else {
        $message = "Error scheduling exam: " . $con->error;
        $messageType = "error";
    }
}

// Handle publish/unpublish
if(isset($_GET['action']) && isset($_GET['id'])) {
    $exam_id = $_GET['id'];
    $action = $_GET['action'];
    
    if($action == 'publish') {
        $update = "UPDATE exams SET is_active = 1 WHERE exam_id = ?";
        $msg = "Exam published successfully!";
    } elseif($action == 'unpublish') {
        $update = "UPDATE exams SET is_active = 0 WHERE exam_id = ?";
        $msg = "Exam unpublished successfully!";
    }
    
    if(isset($update)) {
        $stmt = $con->prepare($update);
        $stmt->bind_param("i", $exam_id);
        if($stmt->execute()) {
            header("Location: ScheduleExam.php?tab=" . $activeTab . "&success=1&msg=" . urlencode($msg));
            exit();
        }
    }
}

// Set success message from redirect
if(isset($_GET['success']) && isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = "success";
} elseif(isset($_GET['success'])) {
    $message = "Operation completed successfully!";
    $messageType = "success";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Exams - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <style>
        .tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #e9ecef;
        }
        .tab {
            padding: 1rem 2rem;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            color: var(--text-secondary);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .tab:hover {
            color: var(--primary-color);
            background: rgba(0, 51, 102, 0.05);
        }
        .tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-published {
            background: #d4edda;
            color: #155724;
        }
        .status-unpublished {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>
    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>
        <div class="admin-content">
            <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: var(--primary-color); font-weight: 700;">📅 Schedule Exams</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 1.05rem;">
                    Manage exam schedules for <?php echo $_SESSION['Dept']; ?> Department
                </p>
            </div>

            <?php if($message): ?>
            <div class="alert alert-<?php echo $messageType == 'success' ? 'success' : 'danger'; ?>" style="margin-bottom: 1.5rem;">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <!-- Tabs -->
            <div class="tabs">
                <a href="?tab=pending" class="tab <?php echo $activeTab == 'pending' ? 'active' : ''; ?>">
                    ⏳ Pending (<?php echo $pending_exams->num_rows; ?>)
                </a>
                <a href="?tab=scheduled" class="tab <?php echo $activeTab == 'scheduled' ? 'active' : ''; ?>">
                    📅 Scheduled (<?php echo $scheduled_exams->num_rows; ?>)
                </a>
                <a href="?tab=past" class="tab <?php echo $activeTab == 'past' ? 'active' : ''; ?>">
                    📚 Past Exams (<?php echo $past_exams->num_rows; ?>)
                </a>
            </div>

            <!-- Pending Exams Tab -->
            <div class="tab-content <?php echo $activeTab == 'pending' ? 'active' : ''; ?>">
                <div class="card">
                    <div class="card-header">
                        <h3>Pending Exams (Awaiting Schedule)</h3>
                    </div>
                    <div class="card-body">
                        <?php if($pending_exams->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Exam Name</th>
                                        <th>Course</th>
                                        <th>Category</th>
                                        <th>Instructor</th>
                                        <th>Duration</th>
                                        <th>Marks</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $pending_exams->data_seek(0);
                                    while($exam = $pending_exams->fetch_assoc()): 
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($exam['exam_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($exam['course_code']); ?></td>
                                        <td><span class="badge badge-info"><?php echo htmlspecialchars($exam['category_name']); ?></span></td>
                                        <td><?php echo htmlspecialchars($exam['instructor_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo $exam['duration_minutes']; ?> min</td>
                                        <td><?php echo $exam['total_marks']; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="openScheduleModal(<?php echo $exam['exam_id']; ?>, '<?php echo htmlspecialchars(addslashes($exam['exam_name'])); ?>', <?php echo $exam['duration_minutes']; ?>)">
                                                📅 Schedule
                                            </button>
                                            <a href="ViewExamDetails.php?id=<?php echo $exam['exam_id']; ?>" class="btn btn-sm btn-info">View</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <p>No pending exams awaiting schedule.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Scheduled Exams Tab -->
            <div class="tab-content <?php echo $activeTab == 'scheduled' ? 'active' : ''; ?>">
                <div class="card">
                    <div class="card-header">
                        <h3>Scheduled Exams (Upcoming)</h3>
                    </div>
                    <div class="card-body">
                        <?php if($scheduled_exams->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Exam Name</th>
                                        <th>Course</th>
                                        <th>Date & Time</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $scheduled_exams->data_seek(0);
                                    while($exam = $scheduled_exams->fetch_assoc()): 
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($exam['exam_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($exam['course_code']); ?></td>
                                        <td>
                                            <?php echo date('M d, Y', strtotime($exam['exam_date'])); ?><br>
                                            <small><?php echo date('h:i A', strtotime($exam['start_time'])); ?></small>
                                        </td>
                                        <td><?php echo $exam['duration_minutes']; ?> min</td>
                                        <td>
                                            <?php if($exam['is_active']): ?>
                                            <span class="status-badge status-published">✓ Published</span>
                                            <?php else: ?>
                                            <span class="status-badge status-unpublished">✗ Unpublished</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="ViewExamDetails.php?id=<?php echo $exam['exam_id']; ?>" class="btn btn-sm btn-info">View</a>
                                            <?php if($exam['is_active']): ?>
                                            <a href="?action=unpublish&id=<?php echo $exam['exam_id']; ?>&tab=scheduled" class="btn btn-sm btn-warning" onclick="return confirm('Unpublish this exam? Students will not be able to see it.')">
                                                Unpublish
                                            </a>
                                            <?php else: ?>
                                            <a href="?action=publish&id=<?php echo $exam['exam_id']; ?>&tab=scheduled" class="btn btn-sm btn-success">
                                                Publish
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <p>No scheduled exams.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Past Exams Tab -->
            <div class="tab-content <?php echo $activeTab == 'past' ? 'active' : ''; ?>">
                <div class="card">
                    <div class="card-header">
                        <h3>Past Exams</h3>
                    </div>
                    <div class="card-body">
                        <?php if($past_exams->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Exam Name</th>
                                        <th>Course</th>
                                        <th>Date & Time</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $past_exams->data_seek(0);
                                    while($exam = $past_exams->fetch_assoc()): 
                                    ?>
                                    <tr style="opacity: 0.8;">
                                        <td><strong><?php echo htmlspecialchars($exam['exam_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($exam['course_code']); ?></td>
                                        <td>
                                            <?php echo date('M d, Y', strtotime($exam['exam_date'])); ?><br>
                                            <small><?php echo date('h:i A', strtotime($exam['start_time'])); ?></small>
                                        </td>
                                        <td><?php echo $exam['duration_minutes']; ?> min</td>
                                        <td>
                                            <span class="badge badge-secondary">Completed</span>
                                        </td>
                                        <td>
                                            <a href="ViewExamDetails.php?id=<?php echo $exam['exam_id']; ?>" class="btn btn-sm btn-info">View</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <p>No past exams.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Modal -->
    <div id="scheduleModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; padding: 2rem; border-radius: 12px; max-width: 500px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
            <h3 style="margin: 0 0 1.5rem 0; color: var(--primary-color);">Schedule Exam</h3>
            <form method="POST" action="">
                <input type="hidden" name="exam_id" id="modal_exam_id">
                <input type="hidden" name="duration_minutes" id="modal_duration">
                
                <div style="margin-bottom: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <strong id="modal_exam_name"></strong>
                </div>

                <div class="form-group">
                    <label>Exam Date *</label>
                    <input type="date" name="exam_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label>Start Time *</label>
                    <input type="time" name="start_time" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Student Batch *</label>
                    <select name="academic_year" class="form-control" required>
                        <option value="">-- Select Batch --</option>
                        <option value="Year 1">Year 1</option>
                        <option value="Year 2">Year 2</option>
                        <option value="Year 3">Year 3</option>
                        <option value="Year 4">Year 4</option>
                        <option value="Year 5">Year 5</option>
                        <option value="All Years">All Years</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Room/Lab (Optional)</label>
                    <input type="text" name="room_lab" class="form-control" placeholder="e.g., Room 101, Lab A">
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" name="schedule_exam" class="btn btn-primary" style="flex: 1;">Schedule & Publish</button>
                    <button type="button" onclick="closeScheduleModal()" class="btn btn-secondary" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openScheduleModal(scheduleId, examName, duration) {
            document.getElementById('modal_exam_id').value = scheduleId;
            document.getElementById('modal_duration').value = duration;
            document.getElementById('modal_exam_name').textContent = examName + ' (' + duration + ' minutes)';
            document.getElementById('scheduleModal').style.display = 'flex';
        }

        function closeScheduleModal() {
            document.getElementById('scheduleModal').style.display = 'none';
        }

        // Close modal when clicking outside
        document.getElementById('scheduleModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeScheduleModal();
            }
        });
    </script>
</body>
</html>
