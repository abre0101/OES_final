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

// Get available courses with practice questions
require_once(__DIR__ . "/../Connections/OES.php");

// Get courses with practice questions count
$sql = "SELECT c.course_id, c.course_name, c.course_code, COUNT(pq.practice_id) as question_count 
        FROM courses c
        INNER JOIN practice_questions pq ON c.course_id = pq.course_id
        WHERE pq.is_active = 1
        GROUP BY c.course_id, c.course_name, c.course_code
        HAVING question_count > 0
        ORDER BY c.course_name";
$result = mysqli_query($con,$sql);
$courses = [];
while($row = mysqli_fetch_array($result)) {
    $courses[] = $row;
}

// Get practice history/statistics (if you have a practice_history table)
// For now, we'll create a simple version
$practiceStats = [
    'total_sessions' => 0,
    'total_questions' => 0,
    'correct_answers' => 0,
    'average_score' => 0
];

// You can add this query if you create a practice_history table
// $statsQuery = "SELECT COUNT(*) as sessions, SUM(questions_attempted) as total_q, 
//                SUM(correct_answers) as correct FROM practice_history WHERE student_id = $studentId";

mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practice Mode - Debre Markos University Health Campus</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/student-modern.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/modern-header-styles.php'; ?>
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Inter', sans-serif;
        }

        .practice-hero {
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
            padding: 3rem 0;
            margin-bottom: 3rem;
            border-radius: 20px;
            color: white;
            text-align: center;
        }

        .practice-hero h1 {
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 0.75rem;
            color: #ffffff;
        }

        .practice-hero p {
            font-size: 1.2rem;
            opacity: 0.95;
            color: #ffd700;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
            opacity: 1 !important;
            background-color: #ffffff !important;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-color: #d4af37;
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 900;
            color: #1a2b4a;
            margin-bottom: 0.5rem;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .stat-label {
            font-size: 0.95rem;
            color: #6b7280;
            font-weight: 600;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 800;
            color: #1a2b4a;
            margin-bottom: 2rem;
            padding: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid #d4af37;
            background: white;
            border-radius: 16px 16px 0 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .course-card {
            background: white;
            border-radius: 20px;
            padding: 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
            border: 2px solid #e5e7eb;
            overflow: hidden;
            opacity: 1 !important;
            background-color: #ffffff !important;
        }
        
        .course-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            border-color: #d4af37;
        }

        .course-header {
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
            padding: 2rem;
            text-align: center;
        }
        
        .course-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .course-body {
            padding: 2rem;
        }
        
        .course-name {
            font-size: 1.35rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .course-code {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            text-align: center;
            font-weight: 600;
        }
        
        .course-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .course-stat {
            text-align: center;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 12px;
        }
        
        .course-stat .value {
            font-size: 1.75rem;
            font-weight: 900;
            color: #1a2b4a;
            margin-bottom: 0.25rem;
        }
        
        .course-stat .label {
            font-size: 0.85rem;
            color: #6b7280;
            font-weight: 600;
        }

        .start-practice-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.05rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .start-practice-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 20px rgba(26, 43, 74, 0.4);
        }
        
        .no-courses {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 2px solid #e5e7eb;
            opacity: 1 !important;
            background-color: #ffffff !important;
        }

        .no-courses-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            opacity: 0.5;
        }

        .no-courses h2 {
            color: #1a2b4a;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .no-courses p {
            color: #6b7280;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .info-box {
            background: white;
            border: 2px solid #d4af37;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 3rem;
            opacity: 1 !important;
            background-color: #ffffff !important;
        }

        .course-body {
            padding: 2rem;
            background: #ffffff !important;
            opacity: 1 !important;
        }

        .info-box h3 {
            color: #1a2b4a;
            font-size: 1.2rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 800;
        }

        .info-box ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-box li {
            padding: 0.5rem 0;
            padding-left: 1.5rem;
            position: relative;
            color: #1a2b4a;
            font-weight: 500;
        }

        .info-box li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #d4af37;
            font-weight: bold;
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .practice-hero h1 {
                font-size: 2rem;
            }

            .courses-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
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
                    <img src="../images/logo1.png" alt="DMU Logo" class="university-logo" onerror="this.style.display='none'">
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
                    <li><a href="StartExam.php">Take Exam</a></li>
                    <li><a href="Result.php">Results</a></li>
                    <li><a href="practice-selection.php" class="active">Practice</a></li>
                    <li><a href="Profile.php">Profile</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Hero Section -->
            <div class="practice-hero">
                <h1>🎯 Practice Mode</h1>
                <p>Sharpen your skills with unlimited practice questions</p>
            </div>

            <!-- Practice Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-value"><?php echo count($courses); ?></div>
                    <div class="stat-label">Available Courses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">❓</div>
                    <div class="stat-value"><?php 
                        $totalQuestions = 0;
                        foreach($courses as $c) $totalQuestions += $c['question_count'];
                        echo $totalQuestions;
                    ?></div>
                    <div class="stat-label">Total Questions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏱️</div>
                    <div class="stat-value">∞</div>
                    <div class="stat-label">No Time Limit</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🎓</div>
                    <div class="stat-value">Free</div>
                    <div class="stat-label">Practice Mode</div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <h3>💡 Practice Mode Benefits</h3>
                <ul>
                    <li>Practice as many times as you want with no time pressure</li>
                    <li>Get instant feedback on your answers with detailed explanations</li>
                    <li>Track your progress and identify areas for improvement</li>
                    <li>Build confidence before taking actual exams</li>
                </ul>
            </div>

            <!-- Courses Section -->
            <h2 class="section-title">Select a Course to Practice</h2>
        
            <?php if (count($courses) > 0): ?>
                <div class="courses-grid">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card">
                            <div class="course-header">
                                <div class="course-icon">📖</div>
                                <div class="course-name"><?php echo htmlspecialchars($course['course_name']); ?></div>
                                <div class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></div>
                            </div>
                            <div class="course-body">
                                <div class="course-info">
                                    <div class="course-stat">
                                        <div class="value"><?php echo $course['question_count']; ?></div>
                                        <div class="label">Questions</div>
                                    </div>
                                    <div class="course-stat">
                                        <div class="value">∞</div>
                                        <div class="label">Unlimited</div>
                                    </div>
                                </div>
                                <a href="practice.php?course=<?php echo urlencode($course['course_name']); ?>" class="start-practice-btn">
                                    🚀 Start Practice
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-courses">
                    <div class="no-courses-icon">📭</div>
                    <h2>No Practice Questions Available</h2>
                    <p>There are currently no practice questions available in the system. Please check back later or contact your instructor for more information.</p>
                    <a href="index.php" class="btn btn-primary btn-lg">
                        ← Back to Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="modern-footer">
        <div class="container">
            <div class="footer-content">
                <p>&copy; 2026 Debre Markos University Health Campus. All rights reserved.</p>
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
