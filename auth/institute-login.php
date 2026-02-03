<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start default session for login page
SessionManager::startSession();

// If already logged in, redirect based on role
if(isset($_SESSION['Name']) && isset($_SESSION['UserType'])){
    SessionManager::redirectToDashboard();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institute Login - Debre Markos University Health Campus</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* Enhanced Login Page Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
            display: flex;
            align-items: center;
        }

        /* Login Section */
        .login-container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
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

        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 0;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            border: 2px solid rgba(212, 175, 55, 0.4);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
            padding: 2.5rem 2rem;
            text-align: center;
            color: white;
            border-bottom: 4px solid #d4af37;
        }

        .login-header h2 {
            font-size: 2.25rem;
            font-weight: 900;
            margin: 0 0 0.5rem 0;
            color: white;
        }

        .login-header p {
            font-size: 1.1rem;
            margin: 0;
            opacity: 0.95;
        }

        .login-body {
            padding: 2.5rem 2rem;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 700;
            color: #1a2b4a;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.25rem;
            font-size: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #1a2b4a;
            background: white;
            box-shadow: 0 0 0 4px rgba(26, 43, 74, 0.1);
        }

        .form-control::placeholder {
            color: #adb5bd;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            font-size: 1.125rem;
            font-weight: 700;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            font-family: 'Poppins', sans-serif;
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
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
        }

        .btn-block {
            width: 100%;
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

        /* Links */
        a {
            transition: all 0.3s ease;
        }

        a:hover {
            opacity: 0.8;
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

            .login-header h2 {
                font-size: 1.75rem;
            }

            .login-card {
                margin: 1rem;
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
                        <p>Online Examination System</p>
                    </div>
                </div>
                <div class="header-actions">
                    <a href="student-login.php" class="btn btn-secondary btn-sm">Student Login</a>
                </div>
            </div>
        </div>
        <nav class="main-nav">
            <div class="container">
                <ul class="nav-menu">
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../AboutUs.php">About Us</a></li>
                    <li><a href="../Help.php">Help</a></li>
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
                        <h2>👨‍💼 Institute Sign In</h2>
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
                                <a href="../forgot-password-request.php" style="color: #1a2b4a; font-weight: 600; text-decoration: none;">
                                    🔐 Forgot Password?
                                </a>
                            </div>

                            <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid #e0e0e0;">
                                <p style="color: #6c757d; margin: 0;">
                                    Are you a student? <a href="student-login.php" style="color: #1a2b4a; font-weight: 600; text-decoration: none;">Go to Student login</a>
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
                <p>&copy; 2026 Debre Markos University Health Campus Online Examination System. All rights reserved.</p>
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
