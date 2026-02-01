<?php
if (!isset($_SESSION)) {
    session_start();
}
$isLoggedIn = isset($_SESSION['Name']);
$userRole = '';
if ($isLoggedIn) {
    // Determine user role based on session variables
    if (isset($_SESSION['ID']) && !isset($_SESSION['instructor_id']) && !isset($_SESSION['committee_member_id'])) {
        $userRole = 'student';
    } elseif (isset($_SESSION['instructor_id'])) {
        $userRole = 'instructor';
    } elseif (isset($_SESSION['committee_member_id'])) {
        $userRole = 'examcommittee';
    } elseif (isset($_SESSION['Admin'])) {
        $userRole = 'admin';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Debre Markos University Health Campus</title>
    <link href="assets/css/modern-v2.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="modern-header">
        <div class="header-top">
            <div class="container">
                <div class="university-info">
                    <img src="images/logo1.png" alt="Debre Markos University Health Campus" class="university-logo" onerror="this.style.display='none'">
                    <div class="university-name">
                        <h1>Debre Markos University Health Campus</h1>
                        <p>Online Examination System</p>
                    </div>
                </div>
                <div class="header-actions">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?php 
                            if ($userRole == 'student') echo 'Student/index.php';
                            elseif ($userRole == 'instructor') echo 'Instructor/index.php';
                            elseif ($userRole == 'examcommittee') echo 'ExamCommittee/index.php';
                            elseif ($userRole == 'admin') echo 'Admin/index.php';
                            else echo 'index.php';
                        ?>" class="btn btn-primary btn-sm">← Back to Dashboard</a>
                    <?php else: ?>
                        <a href="index.php#login" class="btn btn-primary btn-sm">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <nav class="main-nav">
            <div class="container">
                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="AboutUs.php" class="active">About Us</a></li>
                    <li><a href="Help.php">Help</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="content-wrapper">
                <h1>About Debre Markos University Health Campus Online Examination System</h1>
                
                <div class="grid grid-2 mt-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">🎯 Our Mission</h3>
                        </div>
                        <p>To provide a secure, efficient, and user-friendly online examination platform that enhances the academic assessment process at Debre Markos University Health Campus.</p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">👁️ Our Vision</h3>
                        </div>
                        <p>To become the leading digital examination system in Ethiopia, setting standards for academic integrity and technological innovation.</p>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">✨ Key Features</h3>
                    </div>
                    <div class="grid grid-3 mt-3">
                        <div>
                            <h4>🔒 Secure Platform</h4>
                            <p>Advanced security measures to ensure exam integrity and prevent cheating.</p>
                        </div>
                        <div>
                            <h4>⚡ Real-time Results</h4>
                            <p>Instant grading and result processing for objective questions.</p>
                        </div>
                        <div>
                            <h4>📱 Responsive Design</h4>
                            <p>Access exams from any device - desktop, tablet, or mobile.</p>
                        </div>
                        <div>
                            <h4>👥 Multi-user Support</h4>
                            <p>Separate interfaces for students, instructors, exam committee, and administrators.</p>
                        </div>
                        <div>
                            <h4>📊 Analytics Dashboard</h4>
                            <p>Comprehensive reporting and analytics for performance tracking.</p>
                        </div>
                        <div>
                            <h4>🌐 24/7 Availability</h4>
                            <p>Take exams anytime, anywhere with internet connectivity.</p>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">🏛️ About Debre Markos University Health Campus</h3>
                    </div>
                    <p>Debre Markos University Health Campus is a leading institution of higher education in Ethiopia, committed to excellence in teaching, research, and community service. Our Online Examination System represents our dedication to embracing technology to improve educational outcomes.</p>
                    <p>The system was developed to streamline the examination process, reduce administrative burden, and provide a better experience for both students and faculty members.</p>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">📞 Contact Information</h3>
                    </div>
                    <div class="grid grid-2 mt-3">
                        <div>
                            <p><strong>Address:</strong><br>Debre Markos University Health Campus<br>Debre Markos, Ethiopia</p>
                        </div>
                        <div>
                            <p><strong>Email:</strong><br>info@dmu.edu.et</p>
                            <p><strong>Phone:</strong><br>+251-58-771-xxxx</p>
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
                <p>&copy; 2026 Debre Markos University Health Campus Online Examination System. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
