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
        $userRole = 'departmenthead';
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
    <title>Help - Debre Markos University Health Campus</title>
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
            margin-bottom: 1rem;
            text-align: center;
        }

        .text-secondary {
            color: #6c757d;
            text-align: center;
            font-size: 1.15rem;
            margin-bottom: 2rem;
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

        .text-center {
            text-align: center;
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

        /* FAQ Items */
        .faq-item {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .faq-item:hover {
            background: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-color: #d4af37;
            transform: translateX(5px);
        }

        .faq-question {
            font-weight: 700;
            color: #1a2b4a;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1.1rem;
        }

        .faq-answer {
            margin-top: 1rem;
            color: #6c757d;
            display: none;
            line-height: 1.8;
        }

        .faq-item.active {
            background: white;
            border-color: #d4af37;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .faq-item.active .faq-answer {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .faq-icon {
            transition: transform 0.3s ease;
            color: #d4af37;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .faq-item.active .faq-icon {
            transform: rotate(180deg);
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
                            elseif ($userRole == 'departmenthead') echo 'DepartmentHead/index.php';
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
                    <li><a href="AboutUs.php">About Us</a></li>
                    <li><a href="Help.php" class="active">Help</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="content-wrapper">
                <h1>❓ Help & Support</h1>
                <p class="text-secondary">Find answers to common questions and get help with the Online Examination System.</p>

                <div class="grid grid-3 mt-4">
                    <div class="card text-center">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📚</div>
                        <h3>User Guides</h3>
                        <p>Step-by-step instructions for using the system</p>
                    </div>
                    <div class="card text-center">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🎥</div>
                        <h3>Video Tutorials</h3>
                        <p>Watch video guides for common tasks</p>
                    </div>
                    <div class="card text-center">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">💬</div>
                        <h3>Contact Support</h3>
                        <p>Get in touch with our support team</p>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">🔍 Frequently Asked Questions</h3>
                    </div>
                    
                    <div class="mt-3">
                        <div class="faq-item">
                            <div class="faq-question">
                                <span>How do I login to the system?</span>
                                <span class="faq-icon">▼</span>
                            </div>
                            <div class="faq-answer">
                                <p>To login, go to the home page and enter your username, password, and select your user type (Student, Instructor, Department Head, or Administrator). Click the "Login to System" button to access your dashboard.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>What should I do if I forget my password?</span>
                                <span class="faq-icon">▼</span>
                            </div>
                            <div class="faq-answer">
                                <p>Contact your department administrator or the IT support team to reset your password. You will need to provide your student/employee ID for verification.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>What are the system requirements?</span>
                                <span class="faq-icon">▼</span>
                            </div>
                            <div class="faq-answer">
                                <p>You need a computer or mobile device with:</p>
                                <ul>
                                    <li>Modern web browser (Chrome, Firefox, Safari, or Edge)</li>
                                    <li>Stable internet connection (minimum 2 Mbps)</li>
                                    <li>JavaScript enabled</li>
                                    <li>Cookies enabled</li>
                                </ul>
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>How do I take an exam?</span>
                                <span class="faq-icon">▼</span>
                            </div>
                            <div class="faq-answer">
                                <p>After logging in as a student:</p>
                                <ol>
                                    <li>Go to your dashboard</li>
                                    <li>Check the available exams</li>
                                    <li>Click on the exam you want to take</li>
                                    <li>Read the instructions carefully</li>
                                    <li>Click "Start Exam" when ready</li>
                                    <li>Answer all questions before the time expires</li>
                                    <li>Submit your exam</li>
                                </ol>
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>What happens if my internet connection drops during an exam?</span>
                                <span class="faq-icon">▼</span>
                            </div>
                            <div class="faq-answer">
                                <p>The system automatically saves your progress. When your connection is restored, login again and continue from where you left off. However, the timer continues running, so try to maintain a stable connection.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>How can I view my exam results?</span>
                                <span class="faq-icon">▼</span>
                            </div>
                            <div class="faq-answer">
                                <p>Login to your student account and navigate to the "Results" or "My Exams" section. Results are typically available within 24-48 hours after exam submission, depending on the exam type.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>Can I use my mobile phone to take exams?</span>
                                <span class="faq-icon">▼</span>
                            </div>
                            <div class="faq-answer">
                                <p>Yes, the system is mobile-responsive. However, we recommend using a computer or tablet for a better experience, especially for exams with complex questions or file uploads.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>Who do I contact for technical support?</span>
                                <span class="faq-icon">▼</span>
                            </div>
                            <div class="faq-answer">
                                <p>For technical support, contact:</p>
                                <ul>
                                    <li>Email: support@dmu.edu.et</li>
                                    <li>Phone: +251-58-771-xxxx</li>
                                    <li>Office Hours: Monday - Friday, 8:00 AM - 5:00 PM</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-2 mt-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">📧 Contact Support</h3>
                        </div>
                        <p><strong>Email:</strong> support@dmu.edu.et</p>
                        <p><strong>Phone:</strong> +251-58-771-xxxx</p>
                        <p><strong>Office Hours:</strong> Monday - Friday, 8:00 AM - 5:00 PM</p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">🏢 Visit Us</h3>
                        </div>
                        <p><strong>Location:</strong><br>
                        IT Support Office<br>
                        Debre Markos University Health Campus<br>
                        Debre Markos, Ethiopia</p>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">💡 Quick Tips</h3>
                    </div>
                    <div class="grid grid-2 mt-3">
                        <div>
                            <h4>Before the Exam:</h4>
                            <ul>
                                <li>Test your internet connection</li>
                                <li>Close unnecessary applications</li>
                                <li>Have your student ID ready</li>
                                <li>Login 15 minutes early</li>
                            </ul>
                        </div>
                        <div>
                            <h4>During the Exam:</h4>
                            <ul>
                                <li>Read all instructions carefully</li>
                                <li>Manage your time wisely</li>
                                <li>Save your answers regularly</li>
                                <li>Don't refresh the page</li>
                            </ul>
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

    <script>
        // FAQ accordion functionality
        document.querySelectorAll('.faq-item').forEach(item => {
            item.addEventListener('click', function() {
                // Close all other items
                document.querySelectorAll('.faq-item').forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });
                // Toggle current item
                this.classList.toggle('active');
            });
        });
    </script>
</body>
</html>
