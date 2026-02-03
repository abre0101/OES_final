<?php
require_once(__DIR__ . "/utils/session_manager.php");

// Start default session for public pages
SessionManager::startSession();

$isLoggedIn = isset($_SESSION['Name']);
$userRole = '';
if ($isLoggedIn) {
    // Determine user role based on session variables
    if (isset($_SESSION['ID']) && !isset($_SESSION['instructor_id']) && !isset($_SESSION['committee_member_id'])) {
        $userRole = 'student';
    } elseif (isset($_SESSION['instructor_id'])) {
        $userRole = 'instructor';
    } elseif (isset($_SESSION['committee_member_id'])) {
        $userRole = 'department_head';
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* Match Home Page Styles */
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
            background: url('images/exam.webp') center/cover no-repeat;
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
            width: 80px;
            height: 80px;
            object-fit: contain;
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.1));
        }

        .university-name h1 {
            font-size: 1.85rem;
            font-weight: 900;
            color: #ffffff;
            margin: 0;
            line-height: 1.2;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.5px;
        }

        .university-name p {
            font-size: 1.15rem;
            color: #ffd700;
            font-weight: 700;
            margin: 0.35rem 0 0 0;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            letter-spacing: 0.5px;
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
            padding: 1rem 2rem;
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
            padding: 3rem 0;
        }

        .content-wrapper {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 3rem 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            border: 2px solid rgba(212, 175, 55, 0.4);
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

        .content-wrapper > h1 {
            font-size: 2.75rem;
            font-weight: 900;
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 2rem;
            text-align: center;
        }

        /* Cards */
        .card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 2px solid rgba(212, 175, 55, 0.2);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: #d4af37;
        }

        .card-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(212, 175, 55, 0.3);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a2b4a;
            margin: 0;
        }

        /* Grid */
        .grid {
            display: grid;
            gap: 2rem;
        }

        .grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }

        .grid-3 {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }

        .mt-3 {
            margin-top: 1.5rem;
        }

        .mt-4 {
            margin-top: 2rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 43, 74, 0.4);
        }

        .btn-sm {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
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
                font-size: 1.25rem;
            }

            .nav-menu {
                flex-direction: column;
            }

            .nav-menu li a {
                padding: 0.75rem 1.5rem;
            }

            .content-wrapper > h1 {
                font-size: 2rem;
            }

            .grid-2, .grid-3 {
                grid-template-columns: 1fr;
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
                            elseif ($userRole == 'department_head') echo 'DepartmentHead/index.php';
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
                            <p>Separate interfaces for students, instructors, department heads, and administrators.</p>
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
