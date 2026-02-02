<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location: ../index.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
$studentId = $_SESSION['ID'];
$studentSemester = $_SESSION['Sem'];

// Get scheduled exams for student's semester
$scheduledExams = $con->query("SELECT s.*, ec.exam_name, 
    TIMESTAMPDIFF(SECOND, NOW(), CONCAT(s.date, ' ', s.time)) as seconds_until_exam
    FROM exams s
    LEFT JOIN exam_categories ec ON s.exam_id = ec.exam_id
    WHERE s.semester = '$studentSemester'
    ORDER BY s.date ASC, s.time ASC");

$con->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Schedule - Student Portal</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/student-modern.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .countdown-timer {
            font-family: 'Courier New', monospace;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-color);
            text-align: center;
            padding: 1rem;
            background: linear-gradient(135deg, rgba(0, 51, 102, 0.1) 0%, rgba(212, 175, 55, 0.1) 100%);
            border-radius: var(--radius-md);
            margin: 1rem 0;
        }
        
        .countdown-expired {
            color: #dc3545;
        }
        
        .exam-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-left: 5px solid var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .exam-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        .exam-card.status-open {
            border-left-color: var(--success-color);
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.05) 0%, white 100%);
        }
        
        .exam-card.status-closed {
            border-left-color: #dc3545;
            opacity: 0.7;
        }
        
        .exam-card.status-upcoming {
            border-left-color: var(--secondary-color);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-open {
            background: var(--success-color);
            color: white;
            animation: pulse 2s infinite;
        }
        
        .status-closed {
            background: #dc3545;
            color: white;
        }
        
        .status-upcoming {
            background: var(--secondary-color);
            color: var(--primary-dark);
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .exam-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .exam-info-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .exam-info-icon {
            font-size: 1.5rem;
            width: 40px;
            text-align: center;
        }
        
        .exam-info-content strong {
            display: block;
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }
        
        .exam-info-content span {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
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
                                <span class="dropdown-icon">??</span>
                                <span>My Profile</span>
                            </a>
                            <a href="EditProfile.php?Id=<?php echo $_SESSION['ID']; ?>" class="dropdown-item">
                                <span class="dropdown-icon">??</span>
                                <span>Account Settings</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="Logout.php" class="dropdown-item logout">
                                <span class="dropdown-icon">??</span>
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
                    <li><a href="StartExam.php">Take Exam</a></li>
                    <li><a href="Result.php">Results</a></li>
                    <li><a href="Shedule.php" class="active">Schedule</a></li>
                    <li><a href="Profile.php">Profile</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="content-wrapper">
                <h1>?? Examination Schedule</h1>
                <p class="text-secondary">View your upcoming exams with countdown timers and is_active</p>

                <?php if($scheduledExams && $scheduledExams->num_rows > 0): ?>
                    <?php while($exam = $scheduledExams->fetch_assoc()): 
                        $examDateTime = strtotime($exam['date'] . ' ' . $exam['time']);
                        $currentTime = time();
                        $examEndTime = $examDateTime + ($exam['duration'] * 60);
                        
                        // Determine exam status
                        if($currentTime >= $examDateTime && $currentTime <= $examEndTime) {
                            $is_active = 'open';
                            $statusText = '?? Open Now';
                            $statusClass = 'status-open';
                        } elseif($currentTime > $examEndTime) {
                            $is_active = 'closed';
                            $statusText = '?? Closed';
                            $statusClass = 'status-closed';
                        } else {
                            $is_active = 'upcoming';
                            $statusText = '?? Upcoming';
                            $statusClass = 'status-upcoming';
                        }
                    ?>
                    <div class="exam-card <?php echo $statusClass; ?>" data-exam-time="<?php echo $examDateTime; ?>" data-duration="<?php echo $exam['duration']; ?>">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <div>
                                <h2 style="margin: 0 0 0.5rem 0; color: var(--primary-color);">
                                    <?php echo htmlspecialchars($exam['exam_name'] ?? 'Exam'); ?>
                                </h2>
                                <p style="margin: 0; color: var(--text-secondary); font-weight: 600;">
                                    Course: <?php echo htmlspecialchars($exam['course_name']); ?>
                                </p>
                            </div>
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <?php echo $statusText; ?>
                            </span>
                        </div>

                        <div class="exam-info-grid">
                            <div class="exam-info-item">
                                <div class="exam-info-icon">??</div>
                                <div class="exam-info-content">
                                    <strong>Date</strong>
                                    <span><?php echo date('F j, Y', $examDateTime); ?></span>
                                </div>
                            </div>
                            
                            <div class="exam-info-item">
                                <div class="exam-info-icon">?</div>
                                <div class="exam-info-content">
                                    <strong>Time</strong>
                                    <span><?php echo date('g:i A', $examDateTime); ?></span>
                                </div>
                            </div>
                            
                            <div class="exam-info-item">
                                <div class="exam-info-icon">??</div>
                                <div class="exam-info-content">
                                    <strong>Duration</strong>
                                    <span><?php echo $exam['duration']; ?> minutes</span>
                                </div>
                            </div>
                            
                            <div class="exam-info-item">
                                <div class="exam-info-icon">??</div>
                                <div class="exam-info-content">
                                    <strong>Semester</strong>
                                    <span><?php echo $exam['semester']; ?></span>
                                </div>
                            </div>
                        </div>

                        <?php if($is_active == 'upcoming'): ?>
                        <div class="countdown-timer" id="countdown-<?php echo $exam['exam_id']; ?>">
                            <div style="font-size: 0.9rem; margin-bottom: 0.5rem; color: var(--text-secondary);">Starts in:</div>
                            <div class="countdown-display">Calculating...</div>
                        </div>
                        <?php elseif($is_active == 'open'): ?>
                        <div class="countdown-timer" style="background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(40, 167, 69, 0.2) 100%);">
                            <div style="font-size: 0.9rem; margin-bottom: 0.5rem; color: var(--success-color);">? Exam is LIVE!</div>
                            <div class="countdown-display" id="countdown-<?php echo $exam['exam_id']; ?>">Time remaining...</div>
                        </div>
                        <?php endif; ?>

                        <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                            <?php if($is_active == 'open'): ?>
                            <a href="exam-instructions.php?exam_id=<?php echo $exam['exam_id']; ?>" class="btn btn-success" style="flex: 1;">
                                ?? Start Exam Now
                            </a>
                            <?php elseif($is_active == 'upcoming'): ?>
                            <button class="btn btn-secondary" style="flex: 1;" disabled>
                                ? Not Yet Available
                            </button>
                            <?php else: ?>
                            <button class="btn btn-danger" style="flex: 1;" disabled>
                                ?? Exam Closed
                            </button>
                            <?php endif; ?>
                            
                            <a href="exam-instructions.php?exam_id=<?php echo $exam['exam_id']; ?>" class="btn btn-primary">
                                ?? View Instructions
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="card">
                        <div style="text-align: center; padding: 4rem 2rem;">
                            <div style="font-size: 4rem; margin-bottom: 1rem;">??</div>
                            <h3 style="color: var(--text-secondary);">No Scheduled Exams</h3>
                            <p>There are no exams scheduled for your semester at this time.</p>
                            <a href="index.php" class="btn btn-primary" style="margin-top: 1rem;">
                                ? Back to Dashboard
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="modern-footer">
        <div class="container">
            <div class="footer-content">
                <p>&copy; 2026 Debre Markos University Health Campus Online Examination System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Dropdown functionality
        const userDropdown = document.querySelector('.user-dropdown');
        if(userDropdown) {
            const userInfo = userDropdown.querySelector('.user-info');
            userInfo.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });
            document.addEventListener('click', function(e) {
                if (!userDropdown.contains(e.target)) {
                    userDropdown.classList.remove('active');
                }
            });
        }

        // Countdown Timer Function
        function updateCountdowns() {
            const examCards = document.querySelectorAll('.exam-card');
            
            examCards.forEach(card => {
                const examTime = parseInt(card.dataset.examTime) * 1000;
                const duration = parseInt(card.dataset.duration) * 60 * 1000;
                const examEndTime = examTime + duration;
                const now = Date.now();
                const countdownElement = card.querySelector('.countdown-display');
                
                if(!countdownElement) return;
                
                let timeLeft;
                let isExamLive = false;
                
                // Check if exam is live
                if(now >= examTime && now <= examEndTime) {
                    timeLeft = examEndTime - now;
                    isExamLive = true;
                } else if(now < examTime) {
                    timeLeft = examTime - now;
                } else {
                    countdownElement.innerHTML = '<span class="countdown-expired">Exam Ended</span>';
                    return;
                }
                
                if(timeLeft > 0) {
                    const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
                    
                    let countdownHTML = '';
                    
                    if(days > 0) {
                        countdownHTML = `<span style="font-size: 2rem;">${days}d ${hours}h ${minutes}m ${seconds}s</span>`;
                    } else if(hours > 0) {
                        countdownHTML = `<span style="font-size: 2rem;">${hours}h ${minutes}m ${seconds}s</span>`;
                    } else {
                        countdownHTML = `<span style="font-size: 2rem; ${isExamLive ? 'color: var(--success-color);' : ''}">${minutes}m ${seconds}s</span>`;
                    }
                    
                    countdownElement.innerHTML = countdownHTML;
                } else {
                    if(isExamLive) {
                        countdownElement.innerHTML = '<span class="countdown-expired">Time Expired!</span>';
                    } else {
                        countdownElement.innerHTML = '<span style="color: var(--success-color); font-size: 1.5rem;">?? Exam is Open!</span>';
                    }
                }
            });
        }
        
        // Update countdowns every second
        updateCountdowns();
        setInterval(updateCountdowns, 1000);
    </script>
</body>
</html>
