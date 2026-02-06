<?php
// Include session manager
require_once(__DIR__ . "/utils/session_manager.php");

// Try to detect user type from multiple sources
$userType = null;

// First, try all possible session types to find an active one
$sessionTypes = ['Student', 'Instructor', 'Administrator', 'DepartmentHead'];
foreach ($sessionTypes as $type) {
    SessionManager::startSession($type);
    if (isset($_SESSION['UserType']) && $_SESSION['UserType'] === $type) {
        $userType = $type;
        break;
    }
}

// If no session found, check referer as fallback
if (!$userType) {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if (strpos($referer, '/Student/') !== false) {
        $userType = 'Student';
    } elseif (strpos($referer, '/Instructor/') !== false) {
        $userType = 'Instructor';
    } elseif (strpos($referer, '/Admin/') !== false) {
        $userType = 'Administrator';
    } elseif (strpos($referer, '/DepartmentHead/') !== false) {
        $userType = 'DepartmentHead';
    }
}

// Start appropriate session
if ($userType) {
    SessionManager::startSession($userType);
} else {
    // Start default session for public access
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_lifetime' => 86400,
            'cookie_path' => '/',
            'cookie_secure' => false,
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax'
        ]);
    }
}

$isLoggedIn = isset($_SESSION['UserType']) && isset($_SESSION['Name']);
$userRole = '';
if ($isLoggedIn) {
    // Determine user role based on session UserType
    $userType = $_SESSION['UserType'] ?? '';
    switch($userType) {
        case 'Student':
            $userRole = 'student';
            break;
        case 'Instructor':
            $userRole = 'instructor';
            break;
        case 'DepartmentHead':
            $userRole = 'departmenthead';
            break;
        case 'Administrator':
            $userRole = 'admin';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Debre Markos University Health Campus</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #3b82f6;
            --accent-color: #d4af37;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f9fafb;
            --white: #ffffff;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background: var(--bg-light);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 4px solid var(--accent-color);
        }

        .header-top {
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
            padding: 1.5rem 0;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .header-top .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }

        .university-branding {
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

        .university-info h1 {
            font-size: 1.85rem;
            font-weight: 900;
            color: #ffffff;
            margin: 0;
            line-height: 1.2;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .university-info p {
            font-size: 1.15rem;
            color: #ffd700;
            font-weight: 700;
            margin: 0.35rem 0 0 0;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        }

        /* Navigation */
        .main-nav {
            background: linear-gradient(135deg, #d4af37 0%, #f4d03f 100%);
        }

        .nav-menu {
            list-style: none;
            display: flex;
            gap: 0;
            margin: 0;
            padding: 0;
            justify-content: center;
        }

        .nav-menu li a {
            display: block;
            padding: 1rem 2rem;
            color: #1a2b4a;
            text-decoration: none;
            font-weight: 700;
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
            background: #1a2b4a;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-menu li a:hover,
        .nav-menu li a.active {
            background: rgba(26, 43, 74, 0.15);
        }

        .nav-menu li a:hover::after,
        .nav-menu li a.active::after {
            width: 80%;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 4rem 0;
            background: var(--white);
        }

        .page-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .page-header h1 {
            font-size: 3rem;
            font-weight: 900;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .page-header p {
            font-size: 1.25rem;
            color: var(--text-light);
            max-width: 700px;
            margin: 0 auto;
        }

        /* Content Sections */
        .content-section {
            margin-bottom: 4rem;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid var(--accent-color);
        }

        .intro-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-dark);
            margin-bottom: 2rem;
        }

        /* Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .info-card {
            background: var(--white);
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-color: var(--accent-color);
        }

        .info-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .info-card p {
            color: var(--text-light);
            line-height: 1.7;
        }

        /* Features List */
        .features-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .feature-item {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 4px solid var(--accent-color);
        }

        .feature-item h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .feature-item p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        /* Stats Section */
        .stats-section {
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
            padding: 4rem 2rem;
            border-radius: 20px;
            margin: 3rem 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }

        .stat-item {
            color: var(--white);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 900;
            color: var(--accent-color);
            display: block;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.1rem;
            font-weight: 600;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .btn-outline {
            background: transparent;
            color: white;
            border-color: white;
        }

        .btn-outline:hover {
            background: white;
            color: #1a2b4a;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
            color: var(--white);
            padding: 2rem 0;
            text-align: center;
            border-top: 4px solid var(--accent-color);
        }

        .footer p {
            margin: 0;
            color: rgba(255, 255, 255, 0.9);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-top .container {
                flex-direction: column;
                text-align: center;
            }

            .university-branding {
                flex-direction: column;
            }

            .university-info h1 {
                font-size: 1.25rem;
            }

            .nav-menu {
                flex-direction: column;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .cards-grid,
            .features-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div class="university-branding">
                    <img src="images/logo1.png" alt="DMU Logo" class="university-logo" onerror="this.style.display='none'">
                    <div class="university-info">
                        <h1>Debre Markos University Health Campus</h1>
                        <p>Online Examination System</p>
                    </div>
                </div>
                <div class="header-cta">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?php 
                            if ($userRole == 'student') echo 'Student/index.php';
                            elseif ($userRole == 'instructor') echo 'Instructor/index.php';
                            elseif ($userRole == 'departmenthead') echo 'DepartmentHead/index.php';
                            elseif ($userRole == 'admin') echo 'Admin/index.php';
                            else echo 'index.php';
                        ?>" class="btn btn-outline">← Dashboard</a>
                    <?php else: ?>
                        <a href="index.php#login" class="btn btn-outline">🔐 Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <nav class="main-nav">
            <div class="container">
                <ul class="nav-menu">
                    <li><a href="index.php">🏠 Home</a></li>
                    <li><a href="AboutUs.php" class="active">ℹ️ About Us</a></li>
                    <li><a href="Help.php">❓ Help</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>About Our System</h1>
                <p>Transforming education through innovative digital examination solutions</p>
            </div>

            <!-- Introduction -->
            <section class="content-section">
                <p class="intro-text">
                    The Debre Markos University Health Campus Online Examination System is a comprehensive digital platform designed to modernize and streamline the examination process. Built with security, efficiency, and user experience at its core, our system serves students, instructors, department heads, and administrators with tailored interfaces and powerful features.
                </p>
            </section>

            <!-- Mission & Vision -->
            <section class="content-section">
                <h2 class="section-title">Our Mission & Vision</h2>
                <div class="cards-grid">
                    <div class="info-card">
                        <h3>🎯 Mission</h3>
                        <p>To provide a secure, efficient, and accessible online examination platform that enhances academic assessment while maintaining the highest standards of integrity and fairness.</p>
                    </div>
                    <div class="info-card">
                        <h3>👁️ Vision</h3>
                        <p>To be the leading digital examination system in Ethiopian higher education, setting benchmarks for innovation, reliability, and academic excellence.</p>
                    </div>
                </div>
            </section>

            <!-- Statistics -->
            <div class="stats-section">
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number">1000+</span>
                        <span class="stat-label">Active Students</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">50+</span>
                        <span class="stat-label">Faculty Members</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">98%</span>
                        <span class="stat-label">Success Rate</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">24/7</span>
                        <span class="stat-label">System Availability</span>
                    </div>
                </div>
            </div>

            <!-- Key Features -->
            <section class="content-section">
                <h2 class="section-title">System Features</h2>
                <div class="features-list">
                    <div class="feature-item">
                        <h4>🔒 Advanced Security</h4>
                        <p>Multi-layer security protocols to ensure exam integrity and prevent unauthorized access</p>
                    </div>
                    <div class="feature-item">
                        <h4>⚡ Real-Time Processing</h4>
                        <p>Instant grading and result generation for objective assessments</p>
                    </div>
                    <div class="feature-item">
                        <h4>📱 Responsive Design</h4>
                        <p>Seamless experience across all devices - desktop, tablet, and mobile</p>
                    </div>
                    <div class="feature-item">
                        <h4>👥 Role-Based Access</h4>
                        <p>Customized interfaces for students, instructors, department heads, and admins</p>
                    </div>
                    <div class="feature-item">
                        <h4>📊 Analytics Dashboard</h4>
                        <p>Comprehensive reporting tools for performance tracking and insights</p>
                    </div>
                    <div class="feature-item">
                        <h4>🌐 Cloud-Based</h4>
                        <p>Accessible anytime, anywhere with reliable cloud infrastructure</p>
                    </div>
                </div>
            </section>

            <!-- About University -->
            <section class="content-section">
                <h2 class="section-title">About Debre Markos University Health Campus</h2>
                <p class="intro-text">
                    Debre Markos University Health Campus is a premier institution dedicated to excellence in health sciences education, research, and community service. Our commitment to innovation drives us to adopt cutting-edge technologies like this online examination system to enhance the learning experience and academic outcomes for our students.
                </p>
                <p class="intro-text">
                    This system represents our dedication to digital transformation in education, reducing administrative overhead while improving accessibility and fairness in academic assessments.
                </p>
            </section>

            <!-- Development Team -->
            <section class="content-section">
                <h2 class="section-title">Development Team</h2>
                <p class="intro-text">
                    This system was developed by a dedicated team of students from Debre Markos University Health Campus as part of their commitment to advancing educational technology.
                </p>
                <div class="cards-grid">
                    <div class="info-card" style="text-align: center;">
                        <div style="width: 120px; height: 120px; margin: 0 auto 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white; font-weight: 700;">
                            FT
                        </div>
                        <h3 style="display: block; margin-bottom: 0.5rem;">Fetsum Taye</h3>
                        <p style="color: var(--accent-color); font-weight: 600; margin: 0;">Developer</p>
                    </div>
                    <div class="info-card" style="text-align: center;">
                        <div style="width: 120px; height: 120px; margin: 0 auto 1rem; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white; font-weight: 700;">
                            AA
                        </div>
                        <h3 style="display: block; margin-bottom: 0.5rem;">Amanuel Asefa</h3>
                        <p style="color: var(--accent-color); font-weight: 600; margin: 0;">Developer</p>
                    </div>
                    <div class="info-card" style="text-align: center;">
                        <div style="width: 120px; height: 120px; margin: 0 auto 1rem; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white; font-weight: 700;">
                            RA
                        </div>
                        <h3 style="display: block; margin-bottom: 0.5rem;">Rediet Ayenekulu</h3>
                        <p style="color: var(--accent-color); font-weight: 600; margin: 0;">Developer</p>
                    </div>
                    <div class="info-card" style="text-align: center;">
                        <div style="width: 120px; height: 120px; margin: 0 auto 1rem; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white; font-weight: 700;">
                            GK
                        </div>
                        <h3 style="display: block; margin-bottom: 0.5rem;">Gizachew Kumie</h3>
                        <p style="color: var(--accent-color); font-weight: 600; margin: 0;">Developer</p>
                    </div>
                    <div class="info-card" style="text-align: center;">
                        <div style="width: 120px; height: 120px; margin: 0 auto 1rem; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white; font-weight: 700;">
                            HA
                        </div>
                        <h3 style="display: block; margin-bottom: 0.5rem;">Hana Abate</h3>
                        <p style="color: var(--accent-color); font-weight: 600; margin: 0;">Developer</p>
                    </div>
                    <div class="info-card" style="text-align: center;">
                        <div style="width: 120px; height: 120px; margin: 0 auto 1rem; background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white; font-weight: 700;">
                            AT
                        </div>
                        <h3 style="display: block; margin-bottom: 0.5rem;">Askal Tariko</h3>
                        <p style="color: var(--accent-color); font-weight: 600; margin: 0;">Developer</p>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Debre Markos University Health Campus. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
