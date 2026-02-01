<?php
if (!isset($_SESSION)) {
    session_start();
}

// If already logged in, redirect to dashboard
if(isset($_SESSION['Name'])){
    header("Location: ../Student/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - Debre Markos University Health Campus</title>
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
                    <a href="institute-login.php" class="btn btn-secondary btn-sm">Institute Login</a>
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
                        <h2>👨‍🎓 Student Sign In</h2>
                        <p>Login to access your student portal</p>
                    </div>
                    <div class="login-body">
                        <form name="form1" method="post" action="login.php">
                            <input type="hidden" name="cmbType" value="Student">
                            <div class="form-group">
                                <label for="txtUserName">Student ID</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="txtUserName" 
                                       id="txtUserName" 
                                       placeholder="Enter your student ID"
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

                            <button type="submit" name="logined" class="btn btn-success btn-block">
                                Login to Student Portal
                            </button>

                            <div style="text-align: center; margin-top: 1rem;">
                                <a href="../forgot-password-request.php" style="color: var(--primary-color); font-weight: 600;">
                                    🔐 Forgot Password?
                                </a>
                            </div>

                            <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid var(--border-color);">
                                <p style="color: var(--text-secondary); margin: 0;">
                                    Are you an instructor or admin? <a href="institute-login.php" style="color: var(--primary-color); font-weight: 600;">Go to Institute login</a>
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
                <p>&copy; 2024 Debre Markos University Health Campus Online Examination System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Form validation
        document.querySelector('form[name="form1"]').addEventListener('submit', function(e) {
            const username = document.getElementById('txtUserName').value.trim();
            const password = document.getElementById('txtPassword').value.trim();

            if (!username) {
                alert('Please enter your student ID');
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
