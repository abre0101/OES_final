<?php
// Determine active page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <img src="../images/logo1.png" alt="Logo" class="sidebar-logo" onerror="this.style.display='none'">
        <div class="sidebar-title">OES Portal</div>
        <div class="sidebar-subtitle">Student Dashboard</div>
    </div>
    
    <!-- User Profile -->
    <div class="sidebar-user">
        <div class="user-avatar-large">
            <?php echo strtoupper(substr($_SESSION['Name'], 0, 1)); ?>
        </div>
        <div class="user-name"><?php echo $_SESSION['Name']; ?></div>
        <div class="user-id">ID: <?php echo $_SESSION['ID']; ?></div>
    </div>
    
    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Main Menu</div>
            <a href="index.php" class="nav-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <span class="nav-icon">🏠</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="Profile-modern.php" class="nav-item <?php echo ($current_page == 'Profile-modern.php') ? 'active' : ''; ?>">
                <span class="nav-icon">👤</span>
                <span class="nav-text">My Profile</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Examinations</div>
            <a href="StartExam-modern.php" class="nav-item <?php echo ($current_page == 'StartExam-modern.php') ? 'active' : ''; ?>">
                <span class="nav-icon">📝</span>
                <span class="nav-text">Take Exam</span>
            </a>
            <a href="Result-modern.php" class="nav-item <?php echo ($current_page == 'Result-modern.php') ? 'active' : ''; ?>">
                <span class="nav-icon">📊</span>
                <span class="nav-text">My Results</span>
            </a>
            <a href="practice-selection.php" class="nav-item <?php echo ($current_page == 'practice-selection.php' || $current_page == 'practice-modern.php') ? 'active' : ''; ?>">
                <span class="nav-icon">✏️</span>
                <span class="nav-text">Practice</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Support</div>
            <a href="../Help-modern.php" class="nav-item">
                <span class="nav-icon">❓</span>
                <span class="nav-text">Help Center</span>
            </a>
            <a href="../AboutUs-modern.php" class="nav-item">
                <span class="nav-icon">ℹ️</span>
                <span class="nav-text">About Us</span>
            </a>
        </div>
    </nav>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <a href="Logout.php" class="logout-btn">
            <span class="nav-icon">🚪</span>
            <span>Logout</span>
        </a>
    </div>
</div>
