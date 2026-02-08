<?php
require_once(__DIR__ . "/../utils/session_manager.php");
SessionManager::startSession('Student');

if(!isset($_SESSION['Name'])){
    header("Location: ../index.php");
    exit();
}

if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Student'){
    SessionManager::destroySession();
    header("Location: ../auth/student-login.php");
    exit();
}

$studentId = $_SESSION['ID'];
$studentSemester = $_SESSION['Sem'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Exam - Debre Markos University Health Campus</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/student-modern.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/modern-header-styles.php'; ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
            position: relative;
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Poppins', sans-serif;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('../images/istockphoto-1772381872-612x612.jpg') center/cover no-repeat;
            opacity: 0.35;
            z-index: 1;
            pointer-events: none;
        }

        .main-content {
            flex: 1;
            position: relative;
            z-index: 100;
            padding: 2rem 0;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%) !important;
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 3rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
            border: 2px solid rgba(212, 175, 55, 0.3);
            animation: fadeInUp 0.8s ease;
            text-align: center;
        }

        .welcome-content h1 {
            font-size: 2.5rem;
            font-weight: 900;
            color: #ffffff !important;
            margin: 0 0 0.75rem 0;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .welcome-content p {
            font-size: 1.25rem;
            color: #ffd700 !important;
            margin: 0;
            font-weight: 600;
        }

        .alert {
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            font-weight: 500;
            background: white !important;
            opacity: 1 !important;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .alert-info {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
            border: 2px solid #2196f3;
            border-left: 4px solid #2196f3;
            color: #0d47a1;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1) !important;
            border: 2px solid #dc3545;
            border-left: 4px solid #dc3545;
            color: #991b1b;
        }

        .mt-4 {
            margin-top: 2rem;
        }

        .exam-card {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(212, 175, 55, 0.3);
            transition: all 0.3s ease;
            opacity: 1 !important;
            position: relative;
            overflow: visible;
        }

        .exam-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
            border-color: #d4af37;
        }

        .exam-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid rgba(212, 175, 55, 0.2);
        }

        .exam-info h3 {
            font-size: 1.75rem;
            color: #1a2b4a;
            margin: 0 0 1rem 0;
            font-weight: 900;
            letter-spacing: -0.5px;
        }

        .exam-meta {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .exam-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            color: #1a2b4a;
            font-weight: 600;
            background: rgba(212, 175, 55, 0.05);
            padding: 0.5rem 1rem;
            border-radius: 10px;
            border: 1px solid rgba(212, 175, 55, 0.2);
        }

        .exam-meta-item strong {
            color: #1a2b4a;
        }

        .status-badge {
            padding: 0.65rem 1.25rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        .status-available {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            animation: pulse-glow 2s ease-in-out infinite;
        }

        .status-upcoming {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: white;
        }

        .status-closed {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }

        .status-completed {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }

        @keyframes pulse-glow {
            0%, 100% { 
                box-shadow: 0 2px 10px rgba(40, 167, 69, 0.4);
            }
            50% { 
                box-shadow: 0 4px 20px rgba(40, 167, 69, 0.6);
            }
        }

        .exam-card-body {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 2.5rem;
            align-items: center;
        }

        .exam-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.25rem;
        }

        .detail-item {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            padding: 1.5rem;
            border-radius: 15px;
            border: 2px solid rgba(212, 175, 55, 0.2);
            transition: all 0.3s ease;
        }

        .detail-item:hover {
            border-color: #d4af37;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .detail-label {
            font-size: 0.85rem;
            color: #1a2b4a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
            font-weight: 700;
            opacity: 0.7;
        }

        .detail-value {
            font-size: 1.5rem;
            font-weight: 900;
            color: #1a2b4a;
        }

        .exam-actions {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            min-width: 220px;
        }

        .countdown {
            background: rgba(212, 175, 55, 0.1);
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            border: 2px solid #d4af37;
            box-shadow: 0 2px 10px rgba(212, 175, 55, 0.2);
        }

        .countdown-label {
            font-size: 0.85rem;
            color: #1a2b4a;
            margin-bottom: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .countdown-time {
            font-size: 2rem;
            font-weight: 900;
            color: #1a2b4a;
            font-family: 'Courier New', monospace;
        }

        .btn {
            padding: 1.25rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1.05rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .card {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(212, 175, 55, 0.3);
            overflow: hidden;
            opacity: 1 !important;
        }

        .card-header {
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
            padding: 1.5rem 2rem;
            border-bottom: 3px solid #d4af37;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin: 0;
        }

        .card ul {
            list-style: none;
            padding: 2rem;
            margin: 0;
        }

        .card ul li {
            padding: 1rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            font-size: 1rem;
            color: #1a2b4a;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .card ul li:last-child {
            border-bottom: none;
        }

        .card ul li:hover {
            color: #d4af37;
            padding-left: 1rem;
        }

        /* Modern Footer */
        .modern-footer {
            background: rgba(26, 43, 74, 0.98);
            backdrop-filter: blur(10px);
            color: white;
            padding: 1.5rem 0;
            margin-top: auto;
            border-top: 3px solid #d4af37;
            position: relative;
            z-index: 1000;
        }

        .footer-content {
            text-align: center;
        }

        .footer-content p {
            margin: 0;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.8);
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }

            .content-wrapper {
                padding: 1.5rem;
            }

            .content-wrapper > h1 {
                font-size: 2rem;
            }

            .exam-card {
                padding: 1.5rem;
            }

            .exam-card-body {
                grid-template-columns: 1fr;
            }

            .exam-actions {
                min-width: 100%;
            }

            .exam-meta {
                flex-direction: column;
                gap: 0.75rem;
            }

            .exam-info h3 {
                font-size: 1.5rem;
            }

            .detail-value {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="modern-header">
        <div class="header-top">
            <div class="container">
                <div class="university-info">
                    <img src="../images/logo1.png" alt="Debre Markos University Health Campus" class="university-logo" onerror="this.style.display='none'">
                    <div class="university-name">
                        <h1>Debre Markos University Health Campus</h1>
                        <p>Online Examination System - Student Portal</p>
                    </div>
                </div>
                <div class="header-actions">
                    <div class="user-dropdown">
                        <div class="user-info">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($_SESSION['Name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div style="font-weight: 600;"><?php echo $_SESSION['Name']; ?></div>
                                <div style="font-size: 0.75rem; opacity: 0.8;">Student</div>
                            </div>
                            <svg style="width: 20px; height: 20px; margin-left: 0.5rem;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="dropdown-menu">
                            <a href="Profile.php" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                <span>My Profile</span>
                            </a>
                            <a href="EditProfile.php" class="dropdown-item">
                                <i class="fas fa-cog"></i>
                                <span>Account Settings</span>
                            </a>
                            <a href="../Help.php" class="dropdown-item">
                                <i class="fas fa-question-circle"></i>
                                <span>Help</span>
                            </a>
                            <a href="../AboutUs.php" class="dropdown-item">
                                <i class="fas fa-info-circle"></i>
                                <span>About</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="Logout.php" class="dropdown-item logout">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Log Out</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <nav class="main-nav">
            <div class="container">
                <ul class="nav-menu">
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="StartExam.php" class="active">Take Exam</a></li>
                    <li><a href="Result.php">Results</a></li>
                    <li><a href="practice-selection.php">Practice</a></li>
                    <li><a href="Profile.php">Profile</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <div class="welcome-content">
                    <h1>📝 Available Examinations</h1>
                    <p>Welcome <?php echo $_SESSION['Name']; ?>! Below are your scheduled exams.</p>
                </div>
            </div>

            <div class="alert alert-info">
                <strong>ℹ️ Important:</strong> You can only take exams during their scheduled time window. Once started, you must complete the exam within the allocated duration.
            </div>

            <?php
                require_once('../Connections/OES.php');
                
                // Set timezone to East Africa Time
                date_default_timezone_set('Africa/Addis_Ababa');
                
                if (!$con) {
                    echo '<div class="alert alert-danger">Database connection error</div>';
                } else {
                    // Get current date and time
                    $currentDateTime = date('Y-m-d H:i:s');
                    $currentDate = date('Y-m-d');
                    $currentTime = date('H:i:s');
                    
                    // Get exams for courses the student is enrolled in
                    $sql = "SELECT e.*, ec.category_name as exam_type_name, c.course_name, c.semester
                            FROM exams e 
                            LEFT JOIN exam_categories ec ON e.exam_category_id = ec.exam_category_id
                            INNER JOIN courses c ON e.course_id = c.course_id
                            INNER JOIN student_courses sc ON c.course_id = sc.course_id
                            WHERE sc.student_id = ? AND e.is_active = 1 AND e.approval_status = 'approved'
                            ORDER BY e.exam_date ASC, e.start_time ASC";
                    
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param("i", $studentId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $scheduleId = $row['exam_id'];
                            $examType = $row['exam_type_name'] ? $row['exam_type_name'] : 'Unknown';
                            $course = $row['course_name'];
                            $examDate = $row['exam_date'];
                            $startTime = $row['start_time'];
                            $endTime = $row['end_time'] ? $row['end_time'] : date('H:i:s', strtotime($startTime) + 7200);
                            $duration = $row['duration_minutes'] ? $row['duration_minutes'] : 60;
                            
                            // Check if student has already taken this exam
                            $checkResult = $con->prepare("SELECT * FROM exam_results WHERE student_id = ? AND exam_id = ?");
                            $checkResult->bind_param("ii", $studentId, $scheduleId);
                            $checkResult->execute();
                            $hasCompleted = $checkResult->get_result()->num_rows > 0;
                            $checkResult->close();
                            
                            // Determine exam status
                            $is_active = '';
                            $statusClass = '';
                            $canTake = false;
                            $message = '';
                            
                            if ($hasCompleted) {
                                $is_active = 'Completed';
                                $statusClass = 'status-completed';
                                $message = 'You have already completed this exam';
                            } elseif ($examDate < $currentDate) {
                                $is_active = 'Closed';
                                $statusClass = 'status-closed';
                                $message = 'This exam has ended';
                            } elseif ($examDate == $currentDate) {
                                if ($currentTime < $startTime) {
                                    $is_active = 'Upcoming Today';
                                    $statusClass = 'status-upcoming';
                                    $message = 'Exam starts at ' . date('g:i A', strtotime($startTime));
                                } elseif ($currentTime >= $startTime && $currentTime <= $endTime) {
                                    $is_active = 'Available Now';
                                    $statusClass = 'status-available';
                                    $canTake = true;
                                    $message = 'You can take this exam now';
                                } else {
                                    $is_active = 'Closed';
                                    $statusClass = 'status-closed';
                                    $message = 'This exam has ended';
                                }
                            } elseif ($examDate > $currentDate) {
                                $is_active = 'Upcoming';
                                $statusClass = 'status-upcoming';
                                $daysUntil = floor((strtotime($examDate) - strtotime($currentDate)) / 86400);
                                $message = 'Exam in ' . $daysUntil . ' day' . ($daysUntil != 1 ? 's' : '');
                            }
                ?>
                
                <div class="exam-card">
                    <div class="exam-card-header">
                        <div class="exam-info">
                            <h3><?php echo htmlspecialchars($examType); ?></h3>
                            <div class="exam-meta">
                                <div class="exam-meta-item">
                                    📚 <strong><?php echo htmlspecialchars($course); ?></strong>
                                </div>
                                <div class="exam-meta-item">
                                    📅 <strong><?php echo date('M d, Y', strtotime($examDate)); ?></strong>
                                </div>
                                <div class="exam-meta-item">
                                    🕐 <strong><?php echo date('g:i A', strtotime($startTime)); ?> - <?php echo date('g:i A', strtotime($endTime)); ?></strong>
                                </div>
                            </div>
                        </div>
                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo $is_active; ?></span>
                    </div>
                    
                    <div class="exam-card-body">
                        <div class="exam-details">
                            <div class="detail-item">
                                <div class="detail-label">Duration</div>
                                <div class="detail-value"><?php echo $duration; ?> min</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Semester</div>
                                <div class="detail-value"><?php echo $row['semester']; ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Schedule ID</div>
                                <div class="detail-value">#<?php echo $scheduleId; ?></div>
                            </div>
                        </div>
                        
                        <div class="exam-actions">
                            <?php if ($canTake): ?>
                                <a href="exam-instructions.php?exam_id=<?php echo $scheduleId; ?>" class="btn btn-success" style="font-size: 1.1rem;">
                                    🚀 Start Exam
                                </a>
                                <div class="countdown">
                                    <div class="countdown-label">Time Remaining</div>
                                    <div class="countdown-time" id="countdown-<?php echo $scheduleId; ?>" data-end="<?php echo strtotime($examDate . ' ' . $endTime); ?>">
                                        <?php 
                                        $endDateTime = strtotime($examDate . ' ' . $endTime);
                                        $currentDateTime = time();
                                        $diff = $endDateTime - $currentDateTime;
                                        $hours = floor($diff / 3600);
                                        $minutes = floor(($diff % 3600) / 60);
                                        echo sprintf('%02d:%02d', $hours, $minutes);
                                        ?>
                                    </div>
                                </div>
                            <?php elseif ($is_active == 'Upcoming Today' || $is_active == 'Upcoming'): ?>
                                <button class="btn btn-secondary" disabled style="font-size: 1.1rem;">
                                    ⏳ Not Started Yet
                                </button>
                                <div class="countdown" style="border-color: #ffc107; background: rgba(255, 193, 7, 0.1);">
                                    <div class="countdown-label">Starts In</div>
                                    <div class="countdown-time" id="countdown-start-<?php echo $scheduleId; ?>" data-start="<?php echo strtotime($examDate . ' ' . $startTime); ?>">
                                        <?php 
                                        $startDateTime = strtotime($examDate . ' ' . $startTime);
                                        $currentDateTime = time();
                                        $diff = $startDateTime - $currentDateTime;
                                        if ($diff > 86400) {
                                            $days = floor($diff / 86400);
                                            $hours = floor(($diff % 86400) / 3600);
                                            echo $days . 'd ' . sprintf('%02dh', $hours);
                                        } else {
                                            $hours = floor($diff / 3600);
                                            $minutes = floor(($diff % 3600) / 60);
                                            echo sprintf('%02d:%02d', $hours, $minutes);
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled style="font-size: 1.1rem;">
                                    <?php echo $hasCompleted ? '✅ Completed' : '🔒 Not Available'; ?>
                                </button>
                                <div style="text-align: center; color: #1a2b4a; font-size: 0.95rem; font-weight: 600; opacity: 0.7;">
                                    <?php echo $message; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php
                        }
                    } else {
                        echo '<div class="card"><div style="padding: 3rem; text-align: center;">
                                <h3 style="color: #1a2b4a; font-weight: 800; margin-bottom: 1rem;">No Exams Scheduled</h3>
                                <p style="color: #1a2b4a; opacity: 0.7; font-weight: 500;">There are no exams scheduled for your semester at this time.</p>
                              </div></div>';
                    }
                    
                    $stmt->close();
                    mysqli_close($con);
                }
                ?>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">📋 Exam Instructions</h3>
                    </div>
                    <ul>
                        <li>✅ Ensure you have a stable internet connection before starting</li>
                        <li>⏰ You can only take the exam during the scheduled time window</li>
                        <li>⚠️ Once started, you must complete the exam within the allocated duration</li>
                        <li>📖 Read all questions carefully before answering</li>
                        <li>🔄 The exam will auto-submit when time expires</li>
                    </ul>
                </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="modern-footer">
        <div class="container">
            <div class="footer-content">
                <p>&copy;  2026 Debre Markos University Health Campus Online Examination System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Dropdown menu functionality
        const userDropdown = document.querySelector('.user-dropdown');
        const userInfo = userDropdown.querySelector('.user-info');
        const dropdownMenu = userDropdown.querySelector('.dropdown-menu');

        userInfo.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
        });

        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target)) {
                userDropdown.classList.remove('active');
            }
        });

        dropdownMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Countdown timer functionality
        function updateCountdowns() {
            // Update "Time Remaining" countdowns (for active exams)
            document.querySelectorAll('[id^="countdown-"][data-end]').forEach(function(element) {
                const endTime = parseInt(element.getAttribute('data-end'));
                const now = Math.floor(Date.now() / 1000);
                const diff = endTime - now;
                
                if (diff > 0) {
                    const hours = Math.floor(diff / 3600);
                    const minutes = Math.floor((diff % 3600) / 60);
                    const seconds = diff % 60;
                    element.textContent = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
                } else {
                    element.textContent = '00:00:00';
                    // Reload page when time expires
                    setTimeout(() => location.reload(), 1000);
                }
            });
            
            // Update "Starts In" countdowns (for upcoming exams)
            document.querySelectorAll('[id^="countdown-start-"][data-start]').forEach(function(element) {
                const startTime = parseInt(element.getAttribute('data-start'));
                const now = Math.floor(Date.now() / 1000);
                const diff = startTime - now;
                
                if (diff > 0) {
                    if (diff > 86400) {
                        // More than 1 day
                        const days = Math.floor(diff / 86400);
                        const hours = Math.floor((diff % 86400) / 3600);
                        element.textContent = days + 'd ' + String(hours).padStart(2, '0') + 'h';
                    } else {
                        // Less than 1 day - show hours:minutes:seconds
                        const hours = Math.floor(diff / 3600);
                        const minutes = Math.floor((diff % 3600) / 60);
                        const seconds = diff % 60;
                        element.textContent = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
                    }
                } else {
                    element.textContent = '00:00:00';
                    // Reload page when exam starts
                    setTimeout(() => location.reload(), 1000);
                }
            });
        }
        
        // Update countdowns every second
        updateCountdowns();
        setInterval(updateCountdowns, 1000);

        // Auto-refresh page every 5 minutes to update exam availability
        setTimeout(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>
