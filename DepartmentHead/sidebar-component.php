<!-- Modern Department Head Sidebar -->
<aside class="admin-sidebar admin-theme" id="adminSidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand-wrapper">
            <img src="../images/logo1.png" alt="Logo" class="brand-logo" onerror="this.style.display='none'">
            <div class="brand-text">
                <h2 class="brand-title">Department Head</h2>
                <span class="brand-subtitle">DMU</span>
            </div>
        </div>
        <button class="sidebar-toggle-btn" onclick="toggleSidebarMinimize()" title="Toggle Sidebar" id="sidebarToggleBtn">
            <span id="toggleIcon">◀</span>
        </button>
    </div>

    <nav class="sidebar-nav">
        <!-- Dashboard -->
        <a href="index.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" data-tooltip="Dashboard">
            <span class="sidebar-nav-icon">📊</span>
            <span>Dashboard</span>
        </a>

        <div class="sidebar-divider"></div>

        <!-- Student Management -->
        <a href="Students.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'Students.php' || basename($_SERVER['PHP_SELF']) == 'BulkImportStudents.php') ? 'active' : ''; ?>" data-tooltip="Student Management">
            <span class="sidebar-nav-icon">👨‍🎓</span>
            <span>Student Management</span>
        </a>

        <!-- Course Management -->
        <a href="Courses.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'Courses.php') ? 'active' : ''; ?>" data-tooltip="Course Management">
            <span class="sidebar-nav-icon">📚</span>
            <span>Course Management</span>
        </a>

        <!-- Instructor Assignment -->
        <a href="AssignInstructor.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'AssignInstructor.php') ? 'active' : ''; ?>" data-tooltip="Instructor Assignment">
            <span class="sidebar-nav-icon">👨‍🏫</span>
            <span>Instructor Assignment</span>
        </a>

        <div class="sidebar-divider"></div>

        <!-- Exam Scheduling -->
        <a href="ScheduleExam.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'ScheduleExam.php') ? 'active' : ''; ?>" data-tooltip="Exam Scheduling">
            <span class="sidebar-nav-icon">📅</span>
            <span>Exam Scheduling</span>
        </a>

        <!-- Exam Monitoring -->
        <a href="MonitorExams.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'MonitorExams.php') ? 'active' : ''; ?>" data-tooltip="Exam Monitoring">
            <span class="sidebar-nav-icon">👁️</span>
            <span>Exam Monitoring</span>
        </a>

        <!-- Department Exams -->
        <a href="DepartmentExams.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'DepartmentExams.php') ? 'active' : ''; ?>" data-tooltip="Department Exams">
            <span class="sidebar-nav-icon">📋</span>
            <span>Department Exams</span>
        </a>

        <div class="sidebar-divider"></div>

        <!-- Reports Center -->
        <a href="Reports.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'Reports.php' || basename($_SERVER['PHP_SELF']) == 'PerformanceReports.php') ? 'active' : ''; ?>" data-tooltip="Reports Center">
            <span class="sidebar-nav-icon">📊</span>
            <span>Reports Center</span>
        </a>

        <div class="sidebar-divider"></div>

        <!-- Settings -->
        <a href="Settings.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'Settings.php' || basename($_SERVER['PHP_SELF']) == 'Profile.php' || basename($_SERVER['PHP_SELF']) == 'EditProfile.php' || basename($_SERVER['PHP_SELF']) == 'ChangePassword.php') ? 'active' : ''; ?>" data-tooltip="Settings">
            <span class="sidebar-nav-icon">⚙️</span>
            <span>Settings</span>
        </a>

        <div class="sidebar-divider"></div>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar"><?php echo strtoupper(substr($_SESSION['Name'] ?? 'D', 0, 1)); ?></div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?php echo htmlspecialchars(substr($_SESSION['Name'] ?? 'Department Head', 0, 20)); ?></div>
                <div class="sidebar-user-role">Department Head</div>
            </div>
        </div>
        
        <!-- Logout Button -->
        <a href="Logout.php" class="sidebar-logout-btn" title="Logout">
            <span class="sidebar-nav-icon">🚪</span>
            <span>Logout</span>
        </a>
    </div>
</aside>

<script>
function toggleSidebarMinimize() {
    const sidebar = document.getElementById('adminSidebar');
    const toggleIcon = document.getElementById('toggleIcon');
    
    sidebar.classList.toggle('minimized');
    
    if (sidebar.classList.contains('minimized')) {
        toggleIcon.textContent = '▶';
        localStorage.setItem('sidebarMinimized', 'true');
    } else {
        toggleIcon.textContent = '◀';
        localStorage.setItem('sidebarMinimized', 'false');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('adminSidebar');
    const toggleIcon = document.getElementById('toggleIcon');
    const isMinimized = localStorage.getItem('sidebarMinimized') === 'true';
    
    if (isMinimized) {
        sidebar.classList.add('minimized');
        toggleIcon.textContent = '▶';
    }
});
</script>

<style>
.sidebar-logout-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    margin-top: 0.5rem;
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-weight: 600;
}

.sidebar-logout-btn:hover {
    background: #dc3545;
    color: white;
    transform: translateX(4px);
}

.sidebar-logout-btn .sidebar-nav-icon {
    font-size: 1.2rem;
}

.admin-sidebar.minimized .sidebar-logout-btn span:not(.sidebar-nav-icon) {
    display: none;
}

.admin-sidebar.minimized .sidebar-logout-btn {
    justify-content: center;
    padding: 0.75rem;
}
</style>
