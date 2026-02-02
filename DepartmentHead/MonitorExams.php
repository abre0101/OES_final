<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Monitor Progress";
$deptId = $_SESSION['DeptId'] ?? null;

// Get live/ongoing exams (happening right now)
$live_query = "SELECT es.*, c.course_name, c.course_code, ec.category_name,
                i.full_name as instructor_name,
                COUNT(DISTINCT sc.student_id) as enrolled_count,
                COUNT(DISTINCT er.result_id) as completed_count,
                COUNT(DISTINCT CASE WHEN er.exam_submitted_at IS NULL THEN er.result_id END) as in_progress_count
                FROM exams es
                LEFT JOIN courses c ON es.course_id = c.course_id
                LEFT JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
                LEFT JOIN instructors i ON es.created_by = i.instructor_id
                LEFT JOIN student_courses sc ON c.course_id = sc.course_id
                LEFT JOIN exam_results er ON es.exam_id = er.exam_id
                WHERE c.department_id = ? 
                AND es.is_active = 1 
                AND es.exam_date = CURDATE()
                AND TIME(NOW()) BETWEEN es.start_time AND es.end_time
                GROUP BY es.exam_id
                ORDER BY es.start_time ASC";
$stmt = $con->prepare($live_query);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$live_exams = $stmt->get_result();

// Get today's scheduled exams
$today_query = "SELECT es.*, c.course_name, c.course_code, ec.category_name,
                i.full_name as instructor_name,
                COUNT(DISTINCT sc.student_id) as enrolled_count,
                COUNT(DISTINCT er.result_id) as completed_count
                FROM exams es
                LEFT JOIN courses c ON es.course_id = c.course_id
                LEFT JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
                LEFT JOIN instructors i ON es.created_by = i.instructor_id
                LEFT JOIN student_courses sc ON c.course_id = sc.course_id
                LEFT JOIN exam_results er ON es.exam_id = er.exam_id
                WHERE c.department_id = ? 
                AND es.is_active = 1 
                AND es.exam_date = CURDATE()
                GROUP BY es.exam_id
                ORDER BY es.start_time ASC";
$stmt = $con->prepare($today_query);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$today_exams = $stmt->get_result();

// Get students currently taking exams
$active_students_query = "SELECT s.full_name, s.student_code, c.course_code, es.exam_name,
                          er.exam_started_at, es.duration_minutes, es.exam_id,
                          TIMESTAMPDIFF(MINUTE, er.exam_started_at, NOW()) as elapsed_minutes
                          FROM exam_results er
                          INNER JOIN students s ON er.student_id = s.student_id
                          INNER JOIN exams es ON er.exam_id = es.exam_id
                          INNER JOIN courses c ON es.course_id = c.course_id
                          WHERE c.department_id = ?
                          AND er.exam_submitted_at IS NULL
                          AND es.exam_date = CURDATE()
                          AND TIME(NOW()) BETWEEN es.start_time AND es.end_time
                          ORDER BY er.exam_started_at DESC";
$stmt = $con->prepare($active_students_query);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$active_students = $stmt->get_result();

// Get recent issues/flags (if technical_issues table exists)
$issues_query = "SELECT * FROM technical_issues 
                 WHERE department_id = ? 
                 AND DATE(reported_at) = CURDATE()
                 ORDER BY reported_at DESC 
                 LIMIT 10";
$issues_result = null;
if($con->query("SHOW TABLES LIKE 'technical_issues'")->num_rows > 0) {
    $stmt = $con->prepare($issues_query);
    $stmt->bind_param("i", $deptId);
    $stmt->execute();
    $issues_result = $stmt->get_result();
}

// Calculate statistics
$live_count = $live_exams->num_rows;
$today_count = $today_exams->num_rows;
$active_students_count = $active_students->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Progress - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <style>
        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #dc3545;
            color: white;
            border-radius: 20px;
            font-weight: 600;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .live-dot {
            width: 10px;
            height: 10px;
            background: white;
            border-radius: 50%;
            animation: blink 1s infinite;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        .progress-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: conic-gradient(#28a745 var(--progress), #e9ecef 0);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .progress-circle::before {
            content: '';
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: white;
            position: absolute;
        }
        .progress-text {
            position: relative;
            z-index: 1;
            font-weight: 700;
            font-size: 0.9rem;
        }
        .time-alert {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .time-critical { background: #f8d7da; color: #721c24; }
        .time-warning { background: #fff3cd; color: #856404; }
        .time-good { background: #d4edda; color: #155724; }
        .student-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .issue-card {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-radius: 4px;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>
    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>
        <div class="admin-content">
            <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: var(--primary-color); font-weight: 700;">📊 Monitor Progress</h1>
                    <p style="margin: 0; color: var(--text-secondary); font-size: 1.05rem;">
                        Live exam monitoring dashboard
                    </p>
                </div>
                <?php if($live_count > 0): ?>
                <div class="live-indicator">
                    <span class="live-dot"></span>
                    <?php echo $live_count; ?> LIVE EXAM<?php echo $live_count > 1 ? 'S' : ''; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Statistics Cards -->
            <div class="dashboard-grid">
                <div style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3); color: white;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="font-size: 2.5rem;">🔴</div>
                        <div style="flex: 1;">
                            <h3 style="margin: 0; font-size: 2rem; font-weight: 700; color: white;"><?php echo $live_count; ?></h3>
                            <p style="margin: 0.25rem 0 0 0; color: white; font-size: 0.9rem; font-weight: 500;">Live Exams Now</p>
                        </div>
                    </div>
                </div>
                <div style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3); color: white;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="font-size: 2.5rem;">📅</div>
                        <div style="flex: 1;">
                            <h3 style="margin: 0; font-size: 2rem; font-weight: 700; color: white;"><?php echo $today_count; ?></h3>
                            <p style="margin: 0.25rem 0 0 0; color: white; font-size: 0.9rem; font-weight: 500;">Exams Today</p>
                        </div>
                    </div>
                </div>
                <div style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3); color: white;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="font-size: 2.5rem;">👥</div>
                        <div style="flex: 1;">
                            <h3 style="margin: 0; font-size: 2rem; font-weight: 700; color: white;"><?php echo $active_students_count; ?></h3>
                            <p style="margin: 0.25rem 0 0 0; color: white; font-size: 0.9rem; font-weight: 500;">Students Active</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Exams Dashboard -->
            <?php if($live_count > 0): ?>
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white;">
                    <h3 style="margin: 0; display: flex; align-items: center; gap: 0.5rem; color: white;">
                        <span class="live-dot"></span> Live Exam Monitoring
                    </h3>
                </div>
                <div class="card-body">
                    <?php 
                    $live_exams->data_seek(0);
                    while($exam = $live_exams->fetch_assoc()): 
                        $progress = $exam['enrolled_count'] > 0 ? 
                            round(($exam['completed_count'] / $exam['enrolled_count']) * 100) : 0;
                        
                        $exam_start = strtotime($exam['exam_date'] . ' ' . $exam['start_time']);
                        $elapsed_minutes = floor((time() - $exam_start) / 60);
                        $remaining_minutes = $exam['duration_minutes'] - $elapsed_minutes;
                        
                        $time_class = 'time-good';
                        if($remaining_minutes <= 10) $time_class = 'time-critical';
                        elseif($remaining_minutes <= 30) $time_class = 'time-warning';
                    ?>
                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 12px; margin-bottom: 1rem; border-left: 4px solid #dc3545;">
                        <div style="display: flex; align-items: center; gap: 2rem; flex-wrap: wrap;">
                            <div class="progress-circle" style="--progress: <?php echo $progress * 3.6; ?>deg;">
                                <span class="progress-text"><?php echo $progress; ?>%</span>
                            </div>
                            <div style="flex: 1; min-width: 200px;">
                                <h4 style="margin: 0 0 0.5rem 0; color: #003366; font-weight: 700;">
                                    <?php echo htmlspecialchars($exam['exam_name']); ?>
                                </h4>
                                <div style="color: #495057; font-size: 0.9rem; font-weight: 500;">
                                    <div><strong style="color: #003366;"><?php echo htmlspecialchars($exam['course_code']); ?></strong> - <?php echo htmlspecialchars($exam['category_name']); ?></div>
                                    <div>Instructor: <strong><?php echo htmlspecialchars($exam['instructor_name'] ?? 'N/A'); ?></strong></div>
                                </div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 1.5rem; font-weight: 700; color: #003366;">
                                    <?php echo $exam['completed_count']; ?> / <?php echo $exam['enrolled_count']; ?>
                                </div>
                                <div style="color: #495057; font-size: 0.85rem; font-weight: 600;">Submissions</div>
                            </div>
                            <div style="text-align: center;">
                                <span class="time-alert <?php echo $time_class; ?>">
                                    ⏱️ <?php echo max(0, $remaining_minutes); ?> min left
                                </span>
                                <div style="color: #495057; font-size: 0.85rem; margin-top: 0.5rem; font-weight: 600;">
                                    Started: <?php echo date('h:i A', $exam_start); ?>
                                </div>
                            </div>
                            <div>
                                <a href="ViewExamDetails.php?id=<?php echo $exam['exam_id']; ?>" class="btn btn-sm btn-primary">
                                    📊 View Details
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <!-- Students Currently Taking Exams -->
                <div class="card">
                    <div class="card-header">
                        <h3>👥 Students Currently Taking Exams (<?php echo $active_students_count; ?>)</h3>
                    </div>
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        <?php if($active_students_count > 0): ?>
                            <?php 
                            $active_students->data_seek(0);
                            while($student = $active_students->fetch_assoc()): 
                                $elapsed = $student['elapsed_minutes'];
                                $duration = $student['duration_minutes'];
                                $progress_pct = min(100, round(($elapsed / $duration) * 100));
                                
                                $time_class = 'time-good';
                                $remaining = $duration - $elapsed;
                                if($remaining <= 10) $time_class = 'time-critical';
                                elseif($remaining <= 30) $time_class = 'time-warning';
                            ?>
                            <div class="student-card">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">
                                    <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 700; color: #212529;"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                    <div style="font-size: 0.85rem; color: #495057; font-weight: 500;">
                                        <?php echo htmlspecialchars($student['student_code']); ?> • 
                                        <?php echo htmlspecialchars($student['course_code']); ?>
                                    </div>
                                    <div style="margin-top: 0.25rem;">
                                        <div style="width: 100%; height: 4px; background: #e9ecef; border-radius: 10px; overflow: hidden;">
                                            <div style="width: <?php echo $progress_pct; ?>%; height: 100%; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);"></div>
                                        </div>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span class="time-alert <?php echo $time_class; ?>" style="font-size: 0.75rem;">
                                        <?php echo max(0, $remaining); ?> min
                                    </span>
                                    <div style="font-size: 0.75rem; color: #212529; margin-top: 0.25rem; font-weight: 700;">
                                        <?php echo $progress_pct; ?>% complete
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <p>No students currently taking exams.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Issue Reporting/Flagging -->
                <div class="card">
                    <div class="card-header">
                        <h3>🚩 Recent Issues & Flags</h3>
                    </div>
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        <?php if($issues_result && $issues_result->num_rows > 0): ?>
                            <?php while($issue = $issues_result->fetch_assoc()): ?>
                            <div class="issue-card">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <strong style="color: #664d03; font-size: 1rem;"><?php echo htmlspecialchars($issue['issue_type'] ?? 'Technical Issue'); ?></strong>
                                    <small style="color: #664d03; font-weight: 600;"><?php echo date('h:i A', strtotime($issue['reported_at'])); ?></small>
                                </div>
                                <div style="font-size: 0.9rem; color: #664d03; font-weight: 500;">
                                    <?php echo htmlspecialchars($issue['description'] ?? 'No description'); ?>
                                </div>
                                <?php if(isset($issue['student_name'])): ?>
                                <div style="font-size: 0.85rem; color: #664d03; margin-top: 0.5rem; font-weight: 600;">
                                    Reported by: <?php echo htmlspecialchars($issue['student_name']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="alert alert-success">
                                <p>✅ No issues reported today!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Today's Exam Schedule -->
            <div class="card">
                <div class="card-header">
                    <h3>📅 Today's Exam Schedule</h3>
                </div>
                <div class="card-body">
                    <?php if($today_count > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Exam Name</th>
                                    <th>Course</th>
                                    <th>Duration</th>
                                    <th>Submission Status</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $today_exams->data_seek(0);
                                while($exam = $today_exams->fetch_assoc()): 
                                    $exam_start = strtotime($exam['exam_date'] . ' ' . $exam['start_time']);
                                    $exam_end = $exam_start + ($exam['duration_minutes'] * 60);
                                    $now = time();
                                    
                                    $is_live = ($now >= $exam_start && $now <= $exam_end);
                                    $is_upcoming = $now < $exam_start;
                                    $is_completed = $now > $exam_end;
                                    
                                    $status_badge = $is_live ? '<span class="badge badge-danger">🔴 Live</span>' : 
                                                   ($is_upcoming ? '<span class="badge badge-info">⏰ Upcoming</span>' : 
                                                   '<span class="badge badge-success">✅ Completed</span>');
                                    
                                    $progress = $exam['enrolled_count'] > 0 ? 
                                        round(($exam['completed_count'] / $exam['enrolled_count']) * 100) : 0;
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo date('h:i A', $exam_start); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($exam['exam_name']); ?></td>
                                    <td><?php echo htmlspecialchars($exam['course_code']); ?></td>
                                    <td><?php echo $exam['duration_minutes']; ?> min</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div style="flex: 1; min-width: 100px;">
                                                <div style="width: 100%; height: 6px; background: #e9ecef; border-radius: 10px; overflow: hidden;">
                                                    <div style="width: <?php echo $progress; ?>%; height: 100%; background: linear-gradient(90deg, #28a745 0%, #20c997 100%);"></div>
                                                </div>
                                            </div>
                                            <small><strong><?php echo $progress; ?>%</strong></small>
                                        </div>
                                        <small style="color: #495057; font-weight: 600;"><?php echo $exam['completed_count']; ?> / <?php echo $exam['enrolled_count']; ?> submitted</small>
                                    </td>
                                    <td><?php echo $status_badge; ?></td>
                                    <td>
                                        <a href="ViewExamDetails.php?id=<?php echo $exam['exam_id']; ?>" class="btn btn-sm btn-info">
                                            View
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <p>No exams scheduled for today.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh every 15 seconds for live monitoring
        setTimeout(function() {
            location.reload();
        }, 15000);
    </script>
</body>
</html>
