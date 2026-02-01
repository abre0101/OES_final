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
    <title>Help - Debre Markos University Health Campus</title>
    <link href="assets/css/modern-v2.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .faq-item {
            background: var(--bg-secondary);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .faq-item:hover {
            background: white;
            box-shadow: var(--shadow-md);
        }
        .faq-question {
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .faq-answer {
            margin-top: 1rem;
            color: var(--text-secondary);
            display: none;
        }
        .faq-item.active .faq-answer {
            display: block;
        }
        .faq-icon {
            transition: transform 0.3s ease;
        }
        .faq-item.active .faq-icon {
            transform: rotate(180deg);
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
                                <p>To login, go to the home page and enter your username, password, and select your user type (Student, Instructor, Exam Committee, or Administrator). Click the "Login to System" button to access your dashboard.</p>
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
