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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Results - Debre Markos University Health Campus</title>
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

        .mt-4 {
            margin-top: 2rem;
        }

        .result-card {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(212, 175, 55, 0.3);
            transition: all 0.3s ease;
            opacity: 1 !important;
            animation: fadeInUp 0.8s ease;
        }

        .result-card.pass {
            border-left: 6px solid #d4af37;
        }

        .result-card.fail {
            border-left: 6px solid #dc3545;
        }

        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
            border-color: #d4af37;
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid rgba(212, 175, 55, 0.2);
        }

        .result-title {
            font-size: 1.75rem;
            font-weight: 900;
            color: #1a2b4a;
            letter-spacing: -0.5px;
        }

        .result-score {
            font-size: 3.5rem;
            font-weight: 900;
            color: #1a2b4a;
            line-height: 1;
        }

        .result-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .result-detail {
            text-align: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 15px;
            border: 2px solid rgba(212, 175, 55, 0.2);
            transition: all 0.3s ease;
        }

        .result-detail:hover {
            border-color: #d4af37;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .result-detail-value {
            font-size: 2.5rem;
            font-weight: 900;
            color: #1a2b4a;
        }

        .result-detail-label {
            font-size: 0.9rem;
            color: #1a2b4a;
            margin-top: 0.5rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.7;
        }

        .status-badge {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 800;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        .status-active {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .status-inactive {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 43, 74, 0.4);
        }

        .card {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(212, 175, 55, 0.3);
            opacity: 1 !important;
            animation: fadeInUp 0.8s ease;
        }

        .card h3 {
            color: #1a2b4a;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .card p {
            color: #1a2b4a;
            opacity: 0.7;
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

            .result-details {
                grid-template-columns: 1fr;
            }

            .result-header {
                flex-direction: column;
                gap: 1rem;
            }

            .result-score {
                font-size: 2.5rem;
            }

            .welcome-content h1 {
                font-size: 2rem;
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
                    <li><a href="StartExam.php">Take Exam</a></li>
                    <li><a href="Result.php" class="active">Results</a></li>
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
                    <h1>📊 My Exam Results</h1>
                    <p>Welcome <?php echo $_SESSION['Name']; ?>! View all your examination results below.</p>
                </div>
            </div>

            <?php
                require_once(__DIR__ . "/../Connections/OES.php");
                $studentId = $_SESSION['ID'];
                
                $sql = "SELECT er.result_id, ec.category_name as exam_name, 
                        c.course_name, er.student_id, 
                        s.full_name as Name, c.semester, er.total_questions as TotalQuestions,
                        er.total_points_earned as TotalPoints, 
                        er.correct_answers as Correct, er.wrong_answers as Wrong, 
                        er.percentage_score as Result
                        FROM exam_results er
                        INNER JOIN exams e ON er.exam_id = e.exam_id
                        INNER JOIN exam_categories ec ON e.exam_category_id = ec.exam_category_id
                        INNER JOIN courses c ON e.course_id = c.course_id
                        INNER JOIN students s ON er.student_id = s.student_id
                        WHERE er.student_id=?
                        ORDER BY er.result_id DESC";
                
                $stmt = $con->prepare($sql);
                $stmt->bind_param("i", $studentId);
                $stmt->execute();
                $queryResult = $stmt->get_result();
                $records = $queryResult->num_rows;
                
                if($records > 0) {
                    while($row = mysqli_fetch_array($queryResult)) {
                        $Exam = $row['exam_name'];
                        $Sem = $row['semester'];
                        $Subject = $row['course_name'];
                        $TotalQuestions = $row['TotalQuestions'];
                        $TotalPoints = $row['TotalPoints'];
                        $Correct = $row['Correct'];
                        $Wrong = $row['Wrong'];
                        $Score = $row['Result'];
                        $percentage = round($Score, 1);
                        $isPassed = $percentage >= 50;
                ?>
                <div class="result-card <?php echo $isPassed ? 'pass' : 'fail'; ?> mt-4">
                    <div class="result-header">
                        <div>
                            <div class="result-title"><?php echo $Subject; ?></div>
                            <div style="color: #1a2b4a; opacity: 0.7; font-size: 0.95rem; margin-top: 0.25rem; font-weight: 600;">
                                <?php echo $Exam; ?> - Semester <?php echo $Sem; ?>
                            </div>
                        </div>
                        <div class="result-score"><?php echo $percentage; ?>%</div>
                    </div>
                    
                    <div class="result-details">
                        <div class="result-detail">
                            <div class="result-detail-value"><?php echo $TotalQuestions; ?></div>
                            <div class="result-detail-label">Total Questions</div>
                        </div>
                        <div class="result-detail">
                            <div class="result-detail-value" style="color: #28a745;"><?php echo $Correct; ?></div>
                            <div class="result-detail-label">Correct</div>
                        </div>
                        <div class="result-detail">
                            <div class="result-detail-value" style="color: #dc3545;"><?php echo $Wrong; ?></div>
                            <div class="result-detail-label">Wrong</div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid rgba(212, 175, 55, 0.2);">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                            <div>
                                <?php if($isPassed): ?>
                                <span class="status-badge status-active" style="font-size: 1rem; padding: 0.75rem 1.5rem;">
                                    ✅ PASSED
                                </span>
                                <?php else: ?>
                                <span class="status-badge status-inactive" style="font-size: 1rem; padding: 0.75rem 1.5rem;">
                                    ❌ FAILED
                                </span>
                                <?php endif; ?>
                            </div>
                            <a href="review-answers.php?result_id=<?php echo $row['result_id']; ?>" class="btn btn-primary">
                                📝 Review Answers
                            </a>
                        </div>
                    </div>
                </div>
                <?php
                    }
                } else {
                ?>
                <div class="card mt-4" style="text-align: center; padding: 4rem 2rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                    <h3>No Results Yet</h3>
                    <p style="color: #1a2b4a; opacity: 0.7; margin: 1rem 0 2rem 0; font-weight: 500;">
                        You haven't taken any exams yet. Start your first exam to see results here.
                    </p>
                    <a href="StartExam.php" class="btn btn-primary">
                        Take Your First Exam
                    </a>
                </div>
                <?php
                }
                
                $stmt->close();
                mysqli_close($con);
                ?>

                <?php if($records > 0): ?>
                <div class="card mt-4">
                    <div style="text-align: center; padding: 1.5rem;">
                        <strong style="color: #1a2b4a; font-size: 1.25rem; font-weight: 800;">
                            Total Exams Completed: <?php echo $records; ?>
                        </strong>
                    </div>
                </div>
                <?php endif; ?>
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
