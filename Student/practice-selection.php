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

// Get available courses with practice questions
require_once(__DIR__ . "/../Connections/OES.php");
// Use practice_questions table instead of questions table
$sql = "SELECT c.course_name, COUNT(pq.practice_id) as question_count 
        FROM courses c
        INNER JOIN practice_questions pq ON c.course_id = pq.course_id
        WHERE pq.is_active = 1
        GROUP BY c.course_id, c.course_name 
        HAVING question_count > 0
        ORDER BY c.course_name";
$result = mysqli_query($con,$sql);
$courses = [];
while($row = mysqli_fetch_array($result)) {
    $courses[] = $row;
}
mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Practice Course - Debre Markos University Health Campus</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/student-modern.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <?php include 'includes/modern-header-styles.php'; ?>
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .course-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
            border: 2px solid rgba(212, 175, 55, 0.3);
        }
        
        .course-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            border-color: #d4af37;
        }
        
        .course-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        
        .course-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a2b4a;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .course-info {
            display: flex;
            justify-content: space-around;
            padding-top: 1.5rem;
            border-top: 2px solid rgba(0, 0, 0, 0.1);
        }
        
        .course-stat {
            text-align: center;
        }
        
        .course-stat .value {
            font-size: 1.75rem;
            font-weight: 900;
            color: #1a2b4a;
            margin-bottom: 0.5rem;
        }
        
        .course-stat .label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 600;
        }
        
        .no-courses {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 2px solid rgba(212, 175, 55, 0.3);
            margin-top: 2rem;
        }

        .no-courses h2 {
            color: #1a2b4a;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .no-courses p {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 2rem;
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
            <div class="content-wrapper">
                <h1>🎯 Select Practice Course</h1>
                <p class="text-secondary">Choose a subject to start practicing</p>
        
                <?php if (count($courses) > 0): ?>
                    <div class="courses-grid">
                        <?php foreach ($courses as $course): ?>
                            <a href="practice.php?course=<?php echo urlencode($course['course_name']); ?>" class="course-card">
                                <div class="course-icon">📚</div>
                                <div class="course-name"><?php echo htmlspecialchars($course['course_name']); ?></div>
                                <div class="course-info">
                                    <div class="course-stat">
                                        <div class="value"><?php echo $course['question_count']; ?></div>
                                        <div class="label">Questions</div>
                                    </div>
                                    <div class="course-stat">
                                        <div class="value">∞</div>
                                        <div class="label">No Time Limit</div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-courses">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                        <h2>No Practice Questions Available</h2>
                        <p>There are currently no practice questions in the system. Please check back later or contact your instructor.</p>
                        <a href="index.php" class="btn btn-primary">
                            ← Back to Dashboard
                        </a>
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
