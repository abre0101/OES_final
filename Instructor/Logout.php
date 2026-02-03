<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Instructor session to access session data
SessionManager::startSession('Instructor');

// Store session info before destroying
$user_name = $_SESSION['Name'] ?? 'User';
$user_role = 'Instructor';
$session_duration = isset($_SESSION['login_time']) ? time() - $_SESSION['login_time'] : 0;

// Destroy the session using SessionManager
SessionManager::destroySession();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Instructor</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #003366 0%, #001a33 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 1rem;
        }
        
        .logout-container {
            background: white;
            border-radius: 16px;
            padding: 3rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.5s ease;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .logout-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            animation: wave 1s ease infinite;
        }
        
        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(20deg); }
            75% { transform: rotate(-20deg); }
        }
        
        .session-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: left;
        }
        
        .session-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .session-row:last-child {
            border-bottom: none;
        }
        
        .btn {
            display: block;
            width: 100%;
            padding: 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            margin-bottom: 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #003366 0%, #0055aa 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .countdown {
            font-size: 0.9rem;
            color: #666;
            margin-top: 1rem;
        }
        
        .security-note {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e0e0e0;
            font-size: 0.85rem;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-icon">👋</div>
        <h1 style="color: #003366; font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem;">
            Goodbye, <?php echo htmlspecialchars($user_name); ?>!
        </h1>
        <p style="color: #666; margin-bottom: 2rem; line-height: 1.6;">
            You have been successfully logged out. Thank you for using the Online Examination System.
        </p>
        
        <div class="session-info">
            <div class="session-row">
                <span><strong>Session Duration:</strong></span>
                <span><?php echo gmdate("H:i:s", $session_duration); ?></span>
            </div>
            <div class="session-row">
                <span><strong>Logout Time:</strong></span>
                <span><?php echo date('h:i A'); ?></span>
            </div>
            <div class="session-row">
                <span><strong>Date:</strong></span>
                <span><?php echo date('M d, Y'); ?></span>
            </div>
        </div>
        
        <div>
            <a href="../auth/institute-login.php" class="btn btn-primary">
                🔐 Login Again
            </a>
            <a href="../index.php" class="btn btn-secondary">
                🏠 Go to Homepage
            </a>
        </div>
        
        <div class="countdown">
            <p>Redirecting to login page in <span id="countdown">10</span> seconds...</p>
        </div>
        
        <div class="security-note">
            <p>
                🔒 For security reasons, please close your browser if you're on a shared computer.
            </p>
        </div>
    </div>

    <script>
        let seconds = 10;
        const countdownElement = document.getElementById('countdown');
        
        const interval = setInterval(() => {
            seconds--;
            countdownElement.textContent = seconds;
            
            if(seconds <= 0) {
                clearInterval(interval);
                window.location.href = '../auth/institute-login.php';
            }
        }, 1000);
    </script>
</body>
</html>
