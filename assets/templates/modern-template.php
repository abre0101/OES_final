<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Title - Debre Markos University Health Campus</title>
    <link href="../assets/css/modern.css" rel="stylesheet">
    <link href="../assets/css/admin-modern.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                    <?php if(isset($_SESSION['username'])): ?>
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                        </div>
                        <div>
                            <div style="font-weight: 600;"><?php echo $_SESSION['username']; ?></div>
                            <div style="font-size: 0.75rem; opacity: 0.8;">User Type</div>
                        </div>
                    </div>
                    <a href="Logout.php" class="btn btn-danger btn-sm">Logout</a>
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
                    <li><a href="AboutUs-modern.php">About Us</a></li>
                    <li><a href="Shedule-modern.php">Schedule</a></li>
                    <li><a href="Help-modern.php">Help</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="content-wrapper">
                <h1>Page Title</h1>
                
                <!-- Your content here -->
                
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="modern-footer">
        <div class="container">
            <div class="footer-content">
                <p>&copy; 2022 Samara University Online Examination System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Optional JavaScript -->
    <script>
        // Your JavaScript here
    </script>
</body>
</html>
