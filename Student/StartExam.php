 <?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location: ../index.php");
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .exam-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .exam-card:hover {
            box-shadow: var(--shadow-lg);
        }

        .exam-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .exam-info h3 {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin: 0 0 0.5rem 0;
            font-weight: 700;
        }

        .exam-meta {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-top: 0.75rem;
        }

        .exam-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            color: var(--text-secondary);
        }

        .exam-meta-item strong {
            color: var(--text-primary);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: var(--radius-full);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-available {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border: 2px solid var(--success-color);
        }

        .status-upcoming {
            background: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
            border: 2px solid var(--warning-color);
        }

        .status-closed {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            border: 2px solid var(--danger-color);
        }

        .status-completed {
            background: rgba(23, 162, 184, 0.1);
            color: var(--accent-teal);
            border: 2px solid var(--accent-teal);
        }

        .exam-card-body {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 2rem;
            align-items: center;
        }

        .exam-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .detail-item {
            background: var(--bg-light);
            padding: 1rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
        }

        .detail-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .detail-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .exam-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            min-width: 200px;
        }

        .countdown {
            background: var(--bg-light);
            padding: 1rem;
            border-radius: var(--radius-md);
            text-align: center;
            border: 2px solid var(--border-color);
        }

        .countdown-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .countdown-time {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .exam-card-body {
                grid-template-columns: 1fr;
            }

            .exam-actions {
                min-width: 100%;
            }

            .exam-meta {
                flex-direction: column;
                gap: 0.5rem;
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
                            <a href="Profile-modern.php" class="dropdown-item">
                                <span class="dropdown-icon">👤</span>
                                <span>My Profile</span>
                            </a>
                            <a href="EditProfile-modern.php?Id=<?php echo $_SESSION['ID']; ?>" class="dropdown-item">
                                <span class="dropdown-icon">⚙️</span>
                                <span>Account Settings</span>
                            </a>
                            <a href="../Help-modern.php" class="dropdown-item">
                                <span class="dropdown-icon">❓</span>
                                <span>Help</span>
                            </a>
                            <a href="../AboutUs-modern.php" class="dropdown-item">
                                <span class="dropdown-icon">ℹ️</span>
                                <span>About</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="Logout.php" class="dropdown-item logout">
                                <span class="dropdown-icon">🚪</span>
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
                    <li><a href="StartExam-modern.php" class="active">Take Exam</a></li>
                    <li><a href="Result-modern.php">Results</a></li>
                    <li><a href="practice-selection.php">Practice</a></li>
                    <li><a href="Profile-modern.php">Profile</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="content-wrapper">
                <h1>📝 Available Examinations</h1>
                <p class="text-secondary">Welcome <?php echo $_SESSION['Name']; ?>! Below are your scheduled exams.</p>

                <div class="alert alert-info mt-4">
                    <strong>⚠️ Important:</strong> You can only take exams during their scheduled time window. Once started, you must complete the exam within the allocated duration.
                </div>

                <?php
                require_once('../Connections/OES.php');
                
                if (!$con) {
                    echo '<div class="alert alert-danger">Database connection error</div>';
                } else {
                    // Get current date and time
                    $currentDateTime = date('Y-m-d H:i:s');
                    $currentDate = date('Y-m-d');
                    $currentTime = date('H:i:s');
                    
                    // Get exams for student's semester (via courses)
                    $sql = "SELECT s.*, ec.category_name as exam_type_name, c.course_name, c.semester
                            FROM exam_schedules s 
                            LEFT JOIN exam_categories ec ON s.exam_category_id = ec.exam_category_id
                            INNER JOIN courses c ON s.course_id = c.course_id
                            WHERE c.semester = ? AND s.is_active = 1
                            ORDER BY s.exam_date ASC, s.start_time ASC";
                    
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param("i", $studentSemester);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $scheduleId = $row['schedule_id'];
                            $examType = $row['exam_type_name'] ? $row['exam_type_name'] : 'Unknown';
                            $course = $row['course_name'];
                            $examDate = $row['exam_date'];
                            $startTime = $row['start_time'];
                            $endTime = $row['end_time'] ? $row['end_time'] : date('H:i:s', strtotime($startTime) + 7200);
                            $duration = $row['duration_minutes'] ? $row['duration_minutes'] : 60;
                            
                            // Check if student has already taken this exam
                            $checkResult = $con->prepare("SELECT * FROM exam_results WHERE student_id = ? AND schedule_id = ?");
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
                                    ⏰ <strong><?php echo date('g:i A', strtotime($startTime)); ?> - <?php echo date('g:i A', strtotime($endTime)); ?></strong>
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
                                <a href="exam-instructions.php?schedule_id=<?php echo $scheduleId; ?>" class="btn btn-success" style="font-size: 1.1rem;">
                                    🚀 Start Exam
                                </a>
                                <div class="countdown">
                                    <div class="countdown-label">Time Remaining</div>
                                    <div class="countdown-time" id="countdown-<?php echo $scheduleId; ?>">
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
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled style="font-size: 1.1rem;">
                                    <?php echo $hasCompleted ? '✓ Completed' : '🔒 Not Available'; ?>
                                </button>
                                <div style="text-align: center; color: var(--text-secondary); font-size: 0.9rem;">
                                    <?php echo $message; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php
                        }
                    } else {
                        echo '<div class="card"><div style="padding: 3rem; text-align: center; color: var(--text-secondary);">
                                <h3>No Exams Scheduled</h3>
                                <p>There are no exams scheduled for your semester at this time.</p>
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
                    <div style="padding: 1.5rem;">
                        <ul style="list-style: none; padding: 0;">
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--border-color);">
                                ✓ Ensure you have a stable internet connection before starting
                            </li>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--border-color);">
                                ✓ You can only take the exam during the scheduled time window
                            </li>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--border-color);">
                                ✓ Once started, you must complete the exam within the allocated duration
                            </li>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--border-color);">
                                ✓ Read all questions carefully before answering
                            </li>
                            <li style="padding: 0.75rem 0;">
                                ✓ The exam will auto-submit when time expires
                            </li>
                        </ul>
                    </div>
                </div>
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

        // Auto-refresh page every minute to update exam availability
        setTimeout(function() {
            location.reload();
        }, 60000);
    </script>
</body>
</html>
