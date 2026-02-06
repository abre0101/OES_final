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

$Id = $_SESSION['ID'];
require_once(__DIR__ . "/../Connections/OES.php");
$stmt = $con->prepare("SELECT s.*, d.department_name, f.faculty_name 
    FROM students s 
    LEFT JOIN departments d ON s.department_id = d.department_id 
    LEFT JOIN faculties f ON d.faculty_id = f.faculty_id 
    WHERE s.student_id=?");
$stmt->bind_param("i", $Id);
$stmt->execute();
$result = $stmt->get_result();
$row = mysqli_fetch_array($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Debre Markos University Health Campus</title>
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

        .profile-form {
            animation: fadeInUp 0.8s ease;
        }

        .profile-header {
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 3rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
            border: 2px solid rgba(212, 175, 55, 0.3);
            text-align: center;
        }

        .profile-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #d4af37 0%, #f4d03f 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            font-weight: 900;
            color: #1a2b4a;
            margin: 0 auto 1.5rem;
            border: 5px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .profile-header h1 {
            font-size: 2.5rem;
            font-weight: 900;
            color: #ffffff;
            margin: 0 0 0.5rem 0;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .profile-header p {
            color: #ffd700 !important;
            font-size: 1.25rem;
            font-weight: 600;
        }

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

        .card {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(212, 175, 55, 0.3);
            overflow: hidden;
            opacity: 1 !important;
            animation: fadeInUp 0.8s ease;
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

        .info-list {
            padding: 2rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 0;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 700;
            color: #1a2b4a;
            font-size: 1rem;
        }

        .info-value {
            color: #1a2b4a;
            font-weight: 600;
            opacity: 0.8;
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
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
            margin: 0 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 43, 74, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
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

            .grid-2 {
                grid-template-columns: 1fr;
            }

            .profile-header h1 {
                font-size: 2rem;
            }

            .profile-avatar-large {
                width: 100px;
                height: 100px;
                font-size: 3rem;
            }

            .btn {
                margin: 0.5rem 0;
                width: 100%;
            }

            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .info-value {
                text-align: left;
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
                    <li><a href="Result.php">Results</a></li>
                    <li><a href="practice-selection.php">Practice</a></li>
                    <li><a href="Profile.php" class="active">Profile</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="profile-form">
                <div class="profile-header">
                    <div class="profile-avatar-large">
                        <?php echo strtoupper(substr($_SESSION['Name'], 0, 1)); ?>
                    </div>
                    <h1><?php echo $row['full_name']; ?></h1>
                    <p style="color: var(--text-secondary); font-size: 1.1rem; margin: 0;">Student Profile</p>
                </div>

                <div class="grid grid-2">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">👤 Personal Information</h3>
                        </div>
                        <div class="info-list">
                            <div class="info-item">
                                <span class="info-label">Student ID</span>
                                <span class="info-value"><?php echo $row['student_id']; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Full Name</span>
                                <span class="info-value"><?php echo $row['full_name']; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Gender</span>
                                <span class="info-value"><?php echo ucfirst($row['gender']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">🎓 Academic Information</h3>
                        </div>
                        <div class="info-list">
                            <div class="info-item">
                                <span class="info-label">Department</span>
                                <span class="info-value"><?php echo $row['department_name'] ?? 'Not Assigned'; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Faculty</span>
                                <span class="info-value"><?php echo $row['faculty_name'] ?? 'Not Assigned'; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Enrollment Year</span>
                                <span class="info-value"><?php echo date('Y', strtotime($row['created_at'])); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Semester</span>
                                <span class="info-value"><?php echo $row['semester']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">🔐 Account Information</h3>
                    </div>
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-label">Username</span>
                            <span class="info-value"><?php echo $row['username']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Password</span>
                            <span class="info-value">••••••••</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Account Status</span>
                            <span class="info-value">
                                <?php if($row['is_active'] == 1): ?>
                                <span class="status-badge status-active">Active</span>
                                <?php else: ?>
                                <span class="status-badge status-inactive">Inactive</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 3rem;">
                    <a href="index.php" class="btn btn-secondary">
                        ← Back to Dashboard
                    </a>
                    <a href="EditProfile.php" class="btn btn-primary">
                        ✏️ Edit Profile
                    </a>
                </div>
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
<?php
mysqli_close($con);
?>
