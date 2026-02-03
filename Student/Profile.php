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
    <?php include 'includes/modern-header-styles.php'; ?>
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
                    <a href="EditProfile.php?Id=<?php echo $Id; ?>" class="btn btn-primary">
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
