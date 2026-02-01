<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institute Login - Debre Markos University Health Campus</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
                        <p>Online Examination System</p>
                    </div>
                </div>
                <div class="header-actions">
                    <a href="../index.php" class="btn btn-secondary btn-sm">Student Login</a>
                </div>
            </div>
        </div>
        <nav class="main-nav">
            <div class="container">
                <ul class="nav-menu">
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../AboutUs-modern.php">About Us</a></li>
                    <li><a href="../Help-modern.php">Help</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Login Section -->
            <section class="login-container">
                <div class="login-card">
                    <div class="login-header">
                        <h2>Institute Sign In</h2>
                        <p>Login to access your account</p>
                    </div>
                    <div class="login-body">
                        <form name="form1" method="post" action="institute-login-process.php">
                            <div class="form-group">
                                <label for="txtUserName">Username</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="txtUserName" 
                                       id="txtUserName" 
                                       placeholder="Enter your username"
                                       required>
                            </div>

                            <div class="form-group">
                                <label for="txtPassword">Password</label>
                                <input type="password" 
                                       class="form-control" 
                                       name="txtPassword" 
                                       id="txtPassword" 
                                       placeholder="Enter your password"
                                       required>
                            </div>

                            <button type="submit" name="logined" class="btn btn-primary btn-block">
                                Login to System
                            </button>

                            <div style="text-align: center; margin-top: 1rem;">
                                <a href="../forgot-password-request.php" style="color: var(--primary-color); font-weight: 600;">
                                    🔐 Forgot Password?
                                </a>
                            </div>

                            <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                                <p style="color: var(--text-secondary); margin: 0;">
                                    Are you a student? <a href="index.php" style="color: var(--primary-color); font-weight: 600;">Go to Student login</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="modern-footer">
        <div class="container">
            <div class="footer-content">
                <p>&copy; 2022 Debre Markos University Health Campus Online Examination System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Form validation
        document.querySelector('form[name="form1"]').addEventListener('submit', function(e) {
            const username = document.getElementById('txtUserName').value.trim();
            const password = document.getElementById('txtPassword').value.trim();

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
        });
    </script>
</body>
</html>
