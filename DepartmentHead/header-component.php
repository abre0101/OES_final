<!-- Header -->
<header class="admin-header">
    <div class="header-left">
        <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
        <div class="header-breadcrumb">
            <span class="breadcrumb-item">Department Head</span>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-item active"><?php echo $pageTitle ?? 'Dashboard'; ?></span>
        </div>
    </div>
    
    <div class="header-center">
        <div class="header-search">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="Search questions, exams..." class="search-input">
        </div>
    </div>
    
    <div class="header-right">
        <div class="header-datetime">
            <div class="header-time" id="currentTime"></div>
            <div class="header-date"><?php echo date('D, M d, Y'); ?></div>
        </div>
        
        <div class="header-notifications">
            <button class="header-icon-btn" title="Notifications">
                <span class="notification-icon">🔔</span>
                <span class="notification-badge">2</span>
            </button>
        </div>
        
        <div class="header-profile" onclick="toggleProfileDropdown(event)">
            <div class="header-profile-avatar">
                <?php echo strtoupper(substr($_SESSION['Name'], 0, 1)); ?>
            </div>
            <div class="header-profile-info">
                <div class="header-profile-name"><?php echo $_SESSION['Name']; ?></div>
                <div class="header-profile-role">Department Head</div>
            </div>
            <button class="header-dropdown-btn">▼</button>
            
            <div class="profile-dropdown">
                <a href="Profile.php" class="dropdown-item">
                    <span class="dropdown-icon">👤</span>
                    <span>My Profile</span>
                </a>
                <a href="ChangePassword.php" class="dropdown-item">
                    <span class="dropdown-icon">🔒</span>
                    <span>Change Password</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="Logout.php" class="dropdown-item logout">
                    <span class="dropdown-icon">🚪</span>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</header>
