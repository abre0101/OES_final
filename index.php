<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debre Markos University Health Campus - Online Examination System</title>
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
                    <a href="student-login.php" class="btn btn-primary btn-sm">Login</a>
                </div>
            </div>
        </div>
        <nav class="main-nav">
            <div class="container">
                <ul class="nav-menu">
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="AboutUs.php">About Us</a></li>
                    <li><a href="Help.php">Help</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Hero Section -->
            <section class="hero-section">
                <div class="hero-content">
                    <h1>Welcome to Online Examination System</h1>
                    <p>Secure, Efficient, and Modern Examination Platform</p>
                    
                    <div class="hero-features">
                        <div class="feature-card">
                            <div class="feature-icon">👨‍🎓</div>
                            <span class="stat-number">1000+</span>
                            <h3>Students</h3>
                            <p>Active learners taking exams online</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">👨‍🏫</div>
                            <span class="stat-number">50+</span>
                            <h3>Instructors</h3>
                            <p>Expert faculty managing assessments</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">📊</div>
                            <span class="stat-number">98%</span>
                            <h3>Success Rate</h3>
                            <p>Reliable and secure exam delivery</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Login Call-to-Action -->
            <section id="login" class="login-container">
                <div class="login-card" style="text-align: center; padding: 4rem 3rem;">
                    <div style="font-size: 4rem; margin-bottom: 1.5rem;">🎓</div>
                    <h2 style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem;">Ready to Start?</h2>
                    <p style="font-size: 1.25rem; color: var(--text-secondary); margin-bottom: 3rem;">
                        Access your portal to take exams, view results, and manage your profile
                    </p>
                    <div style="display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap;">
                        <a href="student-login.php" class="btn btn-success btn-lg">
                            👨‍🎓 Student Login
                        </a>
                        <a href="institute-login.php" class="btn btn-primary btn-lg">
                            👨‍💼 Institute Login
                        </a>
                    </div>
                    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid var(--border-color);">
                        <p style="color: var(--text-secondary); margin: 0;">
                            <a href="forgot-password.php" style="color: var(--secondary-color); font-weight: 600;">Forgot Your Password?</a>
                        </p>
                    </div>
                </div>
            </section>
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
        // Form validation
        document.querySelector('form[name="form1"]').addEventListener('submit', function(e) {
            const username = document.getElementById('txtUserName').value.trim();
            const password = document.getElementById('txtPassword').value.trim();
            const userType = document.getElementById('cmbType').value;

            if (!username) {
                alert('Please enter your username');
                e.preventDefault();
                return false;
            }

            if (!password) {
                alert('Please enter your password');
                e.preventDefault();
                return false;
            }

            if (!userType) {
                alert('Please select a user type');
                e.preventDefault();
                return false;
            }
        });

        // Smooth scroll to login
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
