<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location: ../index.php");
    exit();
}

// Get student stats
require_once('../Connections/OES.php');
$studentId = $_SESSION['ID'];

// Get student information with department
$stmt = $con->prepare("SELECT s.*, d.department_name, f.faculty_name 
    FROM students s 
    LEFT JOIN departments d ON s.department_id = d.department_id 
    LEFT JOIN faculties f ON d.faculty_id = f.faculty_id 
    WHERE s.student_id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$studentData = $stmt->get_result()->fetch_assoc();
$enrollmentYear = date('Y', strtotime($studentData['created_at']));
$stmt->close();

// Calculate student year level (1st, 2nd, 3rd, 4th year)
$currentYear = date('Y');
$yearLevel = '';
if ($enrollmentYear) {
    $yearsStudied = $currentYear - intval($enrollmentYear);
    $yearNumber = $yearsStudied + 1; // +1 because first year is year 1, not year 0
    
    // Limit to 1-5 years (typical undergraduate program)
    if ($yearNumber < 1) $yearNumber = 1;
    if ($yearNumber > 5) $yearNumber = 5;
    
    // Add ordinal suffix (1st, 2nd, 3rd, 4th, 5th)
    $suffix = 'th';
    if ($yearNumber == 1) $suffix = 'st';
    elseif ($yearNumber == 2) $suffix = 'nd';
    elseif ($yearNumber == 3) $suffix = 'rd';
    
    $yearLevel = $yearNumber . $suffix . ' Year';
} else {
    $yearLevel = 'N/A';
}

// Count available exams for student's courses
$studentSemester = $_SESSION['Sem'];
$examCountQuery = $con->prepare("SELECT COUNT(DISTINCT es.schedule_id) as count 
    FROM exam_schedules es 
    INNER JOIN courses c ON es.course_id = c.course_id 
    WHERE c.semester = ? AND es.is_active = 1");
$examCountQuery->bind_param("i", $studentSemester);
$examCountQuery->execute();
$examCount = $examCountQuery->get_result()->fetch_assoc()['count'];
$examCountQuery->close();

// Count completed exams
$completedCount = $con->query("SELECT COUNT(*) as count FROM exam_results WHERE student_id='$studentId'")->fetch_assoc()['count'];

// Get average score
$avgResult = $con->query("SELECT AVG(percentage_score) as avg FROM exam_results WHERE student_id='$studentId'")->fetch_assoc();
$avgScore = $avgResult['avg'] ? round($avgResult['avg'], 1) : 0;

$con->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Debre Markos University Health Campus</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/student-modern.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* Enhanced Student Dashboard Styles */
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
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('../images/exam.webp') center/cover no-repeat;
            opacity: 0.08;
            z-index: 1;
            pointer-events: none;
        }

        /* Modern Header */
        .modern-header {
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 4px solid #d4af37;
        }

        .header-top {
            padding: 1.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .header-top .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }

        .university-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .university-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.1));
        }

        .university-name h1 {
            font-size: 1.5rem;
            font-weight: 900;
            color: #ffffff;
            margin: 0;
            line-height: 1.2;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.5px;
        }

        .university-name p {
            font-size: 1rem;
            color: #ffd700;
            font-weight: 700;
            margin: 0.25rem 0 0 0;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            letter-spacing: 0.3px;
        }

        /* User Dropdown */
        .user-dropdown {
            position: relative;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.25rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
        }

        .user-info:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #d4af37 0%, #f4d03f 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 1.25rem;
            color: #1a2b4a;
        }

        .dropdown-menu {
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            min-width: 220px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .user-dropdown.active .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            color: #1a2b4a;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .dropdown-item:hover {
            background: rgba(212, 175, 55, 0.1);
        }

        .dropdown-item.logout {
            color: #dc3545;
        }

        .dropdown-item.logout:hover {
            background: rgba(220, 53, 69, 0.1);
        }

        .dropdown-divider {
            height: 1px;
            background: rgba(0, 0, 0, 0.1);
            margin: 0.5rem 0;
        }

        /* Navigation */
        .main-nav {
            background: #1a2b4a;
        }

        .nav-menu {
            list-style: none;
            display: flex;
            gap: 0;
            margin: 0;
            padding: 0;
        }

        .nav-menu li a {
            display: block;
            padding: 1rem 1.5rem;
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-menu li a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 3px;
            background: #d4af37;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-menu li a:hover,
        .nav-menu li a.active {
            background: rgba(212, 175, 55, 0.1);
            color: #d4af37;
        }

        .nav-menu li a:hover::after,
        .nav-menu li a.active::after {
            width: 80%;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            position: relative;
            z-index: 100;
            padding: 2rem 0;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, rgba(26, 43, 74, 0.95) 0%, rgba(44, 83, 100, 0.95) 100%);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 3rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
            border: 2px solid rgba(212, 175, 55, 0.3);
            animation: fadeInUp 0.8s ease;
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

        .welcome-content h1 {
            font-size: 2.5rem;
            font-weight: 900;
            color: #ffffff;
            margin: 0 0 0.75rem 0;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .welcome-content p {
            font-size: 1.25rem;
            color: #ffd700;
            margin: 0;
            font-weight: 600;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(212, 175, 55, 0.3);
            transition: all 0.3s ease;
            animation: fadeInUp 0.8s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
            border-color: #d4af37;
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            flex-shrink: 0;
        }

        .stat-primary .stat-icon {
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
        }

        .stat-success .stat-icon {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .stat-warning .stat-icon {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        }

        .stat-info .stat-icon {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        }

        .stat-details {
            flex: 1;
        }

        .stat-value {
            font-size: 2.25rem;
            font-weight: 900;
            color: #1a2b4a;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            color: #6c757d;
            font-weight: 600;
        }

        /* Content Wrapper */
        .content-wrapper {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 2.5rem;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
            border: 2px solid rgba(212, 175, 55, 0.3);
            margin-bottom: 2rem;
        }

        .content-wrapper h2 {
            font-size: 2rem;
            font-weight: 900;
            color: #1a2b4a;
            margin: 0 0 2rem 0;
        }

        /* Quick Actions Grid */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
        }

        .action-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 20px;
            padding: 2rem 1.5rem;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid rgba(212, 175, 55, 0.2);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .action-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            border-color: #d4af37;
        }

        .action-icon {
            font-size: 3.5rem;
            margin-bottom: 0.5rem;
        }

        .action-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a2b4a;
            margin-bottom: 0.5rem;
        }

        .action-desc {
            font-size: 0.95rem;
            color: #6c757d;
        }

        /* Grid */
        .grid {
            display: grid;
            gap: 2rem;
        }

        .grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        }

        .mt-4 {
            margin-top: 2rem;
        }

        /* Card */
        .card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(212, 175, 55, 0.3);
            overflow: hidden;
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

        /* Info List */
        .info-list {
            padding: 2rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 700;
            color: #1a2b4a;
        }

        .info-value {
            color: #6c757d;
            font-weight: 600;
        }

        /* Announcements */
        .announcement-list {
            padding: 2rem;
        }

        .announcement-item {
            display: flex;
            gap: 1rem;
            padding: 1.25rem;
            background: rgba(212, 175, 55, 0.05);
            border-radius: 15px;
            margin-bottom: 1rem;
            border-left: 4px solid #d4af37;
        }

        .announcement-item:last-child {
            margin-bottom: 0;
        }

        .announcement-icon {
            font-size: 2rem;
            flex-shrink: 0;
        }

        .announcement-content {
            flex: 1;
        }

        .announcement-title {
            font-weight: 700;
            color: #1a2b4a;
            margin-bottom: 0.25rem;
        }

        .announcement-time {
            font-size: 0.9rem;
            color: #6c757d;
        }

        /* Footer */
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

        /* Responsive */
        @media (max-width: 768px) {
            .university-name h1 {
                font-size: 1.1rem;
            }

            .university-name p {
                font-size: 0.85rem;
            }

            .nav-menu {
                flex-wrap: wrap;
            }

            .nav-menu li a {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }

            .welcome-content h1 {
                font-size: 1.75rem;
            }

            .welcome-content p {
                font-size: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .grid-2 {
                grid-template-columns: 1fr;
            }

            .quick-actions-grid {
                grid-template-columns: repeat(2, 1fr);
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
                                <span class="dropdown-icon">👤</span>
                                <span>My Profile</span>
                            </a>
                            <a href="EditProfile.php?Id=<?php echo $_SESSION['ID']; ?>" class="dropdown-item">
                                <span class="dropdown-icon">⚙️</span>
                                <span>Account Settings</span>
                            </a>
                            <a href="../Help.php" class="dropdown-item">
                                <span class="dropdown-icon">❓</span>
                                <span>Help</span>
                            </a>
                            <a href="../AboutUs.php" class="dropdown-item">
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
                    <li><a href="index.php" class="active">Dashboard</a></li>
                    <li><a href="StartExam.php">Take Exam</a></li>
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
                    <h1>👋 Welcome, <?php echo $_SESSION['Name']; ?>!</h1>
                    <p>Ready to take your exams? Access your dashboard below.</p>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">📚</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $examCount; ?></div>
                        <div class="stat-label">Available Exams</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $completedCount; ?></div>
                        <div class="stat-label">Completed Exams</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon">📊</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $avgScore; ?>%</div>
                        <div class="stat-label">Average Score</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon">🏛️</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $studentData['department_name'] ?? 'N/A'; ?></div>
                        <div class="stat-label">Department</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="content-wrapper mt-4">
                <h2>⚡ Quick Actions</h2>
                <div class="quick-actions-grid">
                    <a href="StartExam.php" class="action-card">
                        <div class="action-icon">📝</div>
                        <div class="action-title">Take Exam</div>
                        <div class="action-desc">Start a new examination</div>
                    </a>
                    <a href="Result.php" class="action-card">
                        <div class="action-icon">📈</div>
                        <div class="action-title">View Results</div>
                        <div class="action-desc">Check your exam scores</div>
                    </a>
                    <a href="Profile.php" class="action-card">
                        <div class="action-icon">👤</div>
                        <div class="action-title">My Profile</div>
                        <div class="action-desc">Update your information</div>
                    </a>
                    <a href="practice-selection.php" class="action-card">
                        <div class="action-icon">🎯</div>
                        <div class="action-title">Practice</div>
                        <div class="action-desc">Practice with sample questions</div>
                    </a>
                </div>
            </div>

            <!-- Student Info -->
            <div class="grid grid-2 mt-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">👨‍🎓 Student Information</h3>
                    </div>
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-label">Student ID:</span>
                            <span class="info-value"><?php echo $studentData['student_id']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Name:</span>
                            <span class="info-value"><?php echo $studentData['full_name']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Department:</span>
                            <span class="info-value"><?php echo $studentData['department_name'] ?? 'Not Assigned'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Year:</span>
                            <span class="info-value"><?php echo $yearLevel ?: $enrollmentYear; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Semester:</span>
                            <span class="info-value"><?php echo $studentData['semester']; ?></span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📢 Announcements</h3>
                    </div>
                    <div class="announcement-list">
                        <div class="announcement-item">
                            <div class="announcement-icon">📅</div>
                            <div class="announcement-content">
                                <div class="announcement-title">Exam Schedule Updated</div>
                                <div class="announcement-time">Check the schedule page for new dates</div>
                            </div>
                        </div>
                        <div class="announcement-item">
                            <div class="announcement-icon">✅</div>
                            <div class="announcement-content">
                                <div class="announcement-title">System Ready</div>
                                <div class="announcement-time">All systems operational</div>
                            </div>
                        </div>
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
    </script>
</body>
</html>
