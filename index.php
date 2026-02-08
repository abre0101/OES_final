<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debre Markos University Health Campus - Online Examination System</title>
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
            --success-color: #10b981;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f9fafb;
            --white: #ffffff;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
            --shadow-xl: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background: var(--bg-light);
        }

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 4px solid var(--accent-color);
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
            letter-spacing: -0.5px;
        }

        .university-info p {
            font-size: 1.15rem;
            color: #ffd700;
            font-weight: 700;
            margin: 0.35rem 0 0 0;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            letter-spacing: 0.5px;
        }

        .header-cta {
            display: flex;
            gap: 1rem;
            align-items: center;
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
            color: #1a2b4a;
        }

        .nav-menu li a:hover::after,
        .nav-menu li a.active::after {
            width: 80%;
        }

        /* Hero Carousel Section */
        .hero-carousel-section {
            position: relative;
            width: 100%;
        }

        .carousel-container {
            position: relative;
            width: 100%;
            height: 600px;
            overflow: hidden;
        }

        .carousel-slide {
            display: none;
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }

        .carousel-slide.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        .carousel-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.7);
        }

        .carousel-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.7) 0%, rgba(30, 64, 175, 0.6) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }

        .carousel-caption {
            text-align: center;
            color: var(--white);
            max-width: 800px;
            padding: 2rem;
        }

        .carousel-caption h2 {
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 1.5rem;
            line-height: 1.1;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            animation: fadeInUp 0.8s ease;
        }

        .carousel-caption p {
            font-size: 1.5rem;
            margin-bottom: 2.5rem;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 400;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            animation: fadeInUp 1s ease;
        }

        .carousel-caption .btn {
            animation: fadeInUp 1.2s ease;
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

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: var(--white);
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            border-radius: 50%;
            transition: all 0.3s ease;
            z-index: 20;
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .carousel-btn:hover {
            background: var(--accent-color);
            border-color: var(--accent-color);
            transform: translateY(-50%) scale(1.15);
        }

        .carousel-btn.prev { left: 30px; }
        .carousel-btn.next { right: 30px; }

        .carousel-dots {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 12px;
            z-index: 20;
        }

        .dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.6);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dot:hover,
        .dot.active {
            background: var(--accent-color);
            border-color: var(--accent-color);
            transform: scale(1.3);
        }

        /* Stats Section */
        .stats-section {
            padding: 4rem 0;
            background: var(--white);
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            padding: 2.5rem 2rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--accent-color);
        }

        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .stat-number {
            font-size: 2.75rem;
            font-weight: 900;
            color: var(--primary-color);
            display: block;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            color: var(--text-light);
            font-weight: 600;
        }

        /* Features Section */
        .features-section {
            padding: 5rem 0;
            background: var(--bg-light);
        }

        .features-section:target {
            scroll-margin-top: 80px;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-header h2 {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .section-header p {
            font-size: 1.15rem;
            color: var(--text-light);
            max-width: 700px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: var(--white);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--accent-color);
        }

        .feature-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            display: inline-block;
        }

        .feature-card h3 {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.7;
        }

        /* Login Section */
        .login-section {
            padding: 5rem 0;
            background: var(--white);
        }

        .login-card {
            max-width: 700px;
            margin: 0 auto;
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            border-radius: 24px;
            padding: 4rem 3rem;
            box-shadow: var(--shadow-xl);
            border: 2px solid var(--accent-color);
            text-align: center;
        }

        .login-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            display: inline-block;
        }

        .login-card h2 {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .login-card > p {
            font-size: 1.1rem;
            color: var(--text-light);
            margin-bottom: 3rem;
        }

        .login-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            font-size: 1.05rem;
            font-weight: 700;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-primary {
            background: linear-gradient(135deg, #1a2b4a 0%, #2c5364 100%);
            color: white;
            border-color: var(--accent-color);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2c5364 0%, #1a2b4a 100%);
            box-shadow: 0 15px 35px rgba(26, 43, 74, 0.5);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-color: #20c997;
        }

        .btn-success:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 35px rgba(40, 167, 69, 0.5);
            border-color: #ffffff;
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

        .btn-sm {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
        }

        .btn-lg {
            padding: 1.25rem 2.5rem;
            font-size: 1.15rem;
        }

        /* Forgot Password */
        .forgot-password {
            padding-top: 2rem;
            border-top: 2px solid #e5e7eb;
        }

        .forgot-password a {
            color: var(--primary-color);
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .forgot-password a:hover {
            color: var(--accent-color);
            gap: 0.75rem;
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
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.9);
        }

        /* Responsive Design */
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

            .university-info p {
                font-size: 0.95rem;
            }

            .nav-menu {
                flex-direction: column;
            }

            .nav-menu li a {
                padding: 0.75rem 1.5rem;
            }

            .hero-content h2 {
                font-size: 2rem;
            }

            .hero-content p {
                font-size: 1.1rem;
            }

            .carousel-container {
                height: 450px;
            }

            .carousel-caption h2 {
                font-size: 2.25rem;
            }

            .carousel-caption p {
                font-size: 1.15rem;
            }

            .carousel-btn {
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
            }

            .carousel-btn.prev { left: 15px; }
            .carousel-btn.next { right: 15px; }

            .section-header h2 {
                font-size: 2rem;
            }

            .login-card {
                padding: 2.5rem 1.5rem;
            }

            .login-buttons {
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
                    <a href="#login" class="btn btn-outline btn-sm">
                        <span>🔐</span>
                        <span>Login</span>
                    </a>
                </div>
            </div>
        </div>
        <nav class="main-nav">
            <div class="container">
                <ul class="nav-menu">
                    <li><a href="index.php" class="active">🏠 Home</a></li>
                    <li><a href="AboutUs.php">ℹ️ About Us</a></li>
                    <li><a href="Help.php">❓ Help</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Hero Carousel Section -->
    <section class="hero-carousel-section">
        <div class="carousel-container">
            <div class="carousel-slide active">
                <img src="images/home1.jpg" alt="Campus View 1" onerror="this.src='images/exam.webp'">
                <div class="carousel-overlay">
                    <div class="container">
                        <div class="carousel-caption">
                            <h2>Welcome to Online Examination System</h2>
                            <p>A secure, efficient, and modern platform for conducting examinations</p>
                            <a href="#login" class="btn btn-primary btn-lg">Get Started</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-slide">
                <img src="images/home2.jpg" alt="Campus View 2" onerror="this.src='images/exam.webp'">
                <div class="carousel-overlay">
                    <div class="container">
                        <div class="carousel-caption">
                            <h2>Excellence in Digital Education</h2>
                            <p>Empowering students and educators with cutting-edge technology</p>
                            <a href="AboutUs.php" class="btn btn-primary btn-lg">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-btn prev" onclick="changeSlide(-1)">❮</button>
            <button class="carousel-btn next" onclick="changeSlide(1)">❯</button>
            <div class="carousel-dots">
                <span class="dot active" onclick="currentSlide(0)"></span>
                <span class="dot" onclick="currentSlide(1)"></span>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👨‍🎓</div>
                    <span class="stat-number">1000+</span>
                    <span class="stat-label">Active Students</span>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👨‍🏫</div>
                    <span class="stat-number">50+</span>
                    <span class="stat-label">Instructors</span>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📈</div>
                    <span class="stat-number">98%</span>
                    <span class="stat-label">Success Rate</span>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🕐</div>
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">Availability</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Login Section -->
    <section id="login" class="login-section">
        <div class="container">
            <div class="login-card">
                <div class="login-icon">🎓</div>
                <h2>Ready to Get Started?</h2>
                <p>Access your portal to take exams, view results, and manage your profile</p>
                
                <div class="login-buttons">
                    <a href="student-login.php" class="btn btn-success btn-lg">
                        <span>👨‍🎓</span>
                        <span>Student Login</span>
                    </a>
                    <a href="staff-login.php" class="btn btn-primary btn-lg">
                        <span>👨‍💼</span>
                        <span>Staffs Login</span>
                    </a>
                </div>
                
                <div class="forgot-password">
                    <a href="forgot-password-request.php">
                        <span>🔑</span>
                        <span>Forgot Your Password?</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Debre Markos University Health Campus. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Carousel functionality
        let currentSlideIndex = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.dot');

        function showSlide(index) {
            if (index >= slides.length) {
                currentSlideIndex = 0;
            } else if (index < 0) {
                currentSlideIndex = slides.length - 1;
            } else {
                currentSlideIndex = index;
            }

            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));

            slides[currentSlideIndex].classList.add('active');
            dots[currentSlideIndex].classList.add('active');
        }

        function changeSlide(direction) {
            showSlide(currentSlideIndex + direction);
        }

        function currentSlide(index) {
            showSlide(index);
        }

        // Auto-advance carousel every 5 seconds
        setInterval(() => {
            changeSlide(1);
        }, 5000);

        // Smooth scroll for anchor links
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
