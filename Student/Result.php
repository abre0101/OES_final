<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location: ../index.php");
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
    <?php include 'includes/modern-header-styles.php'; ?>
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .result-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 2px solid;
            transition: all 0.3s ease;
        }

        .result-card.pass {
            border-color: #28a745;
        }

        .result-card.fail {
            border-color: #dc3545;
        }

        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        }

        .result-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a2b4a;
        }

        .result-score {
            font-size: 3rem;
            font-weight: 900;
            color: #1a2b4a;
        }

        .result-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .result-detail {
            text-align: center;
            padding: 1rem;
            background: rgba(0, 0, 0, 0.02);
            border-radius: 15px;
        }

        .result-detail-value {
            font-size: 2rem;
            font-weight: 900;
            color: #1a2b4a;
        }

        .result-detail-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 0.5rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .result-details {
                grid-template-columns: 1fr;
            }

            .result-header {
                flex-direction: column;
                gap: 1rem;
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
            <div class="content-wrapper">
                <h1>📊 My Exam Results</h1>
                <p class="text-secondary">Welcome <?php echo $_SESSION['Name']; ?>! View all your examination results below.</p>

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
                        INNER JOIN exam_schedules es ON er.schedule_id = es.schedule_id
                        INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
                        INNER JOIN courses c ON es.course_id = c.course_id
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
                            <div style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.25rem;">
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
                            <div class="result-detail-value" style="color: var(--success-color);"><?php echo $Correct; ?></div>
                            <div class="result-detail-label">Correct</div>
                        </div>
                        <div class="result-detail">
                            <div class="result-detail-value" style="color: var(--danger-color);"><?php echo $Wrong; ?></div>
                            <div class="result-detail-label">Wrong</div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
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
                    <p style="color: var(--text-secondary); margin: 1rem 0 2rem 0;">
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
                        <strong style="color: var(--primary-color); font-size: 1.1rem;">
                            Total Exams Completed: <?php echo $records; ?>
                        </strong>
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
