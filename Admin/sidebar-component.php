<!-- Modern Admin Sidebar with System-Specific Styling -->
<aside class="admin-sidebar admin-theme" id="adminSidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand-wrapper">
            <img src="../images/logo1.png" alt="Logo" class="brand-logo" onerror="this.style.display='none'">
            <div class="brand-text">
                <h2 class="brand-title">Administrator</h2>
                <span class="brand-subtitle">DMU</span>
            </div>
        </div>
        <button class="sidebar-toggle-btn" onclick="toggleSidebarMinimize()" title="Toggle Sidebar" id="sidebarToggleBtn">
            <span id="toggleIcon">◀</span>
        </button>
    </div>

    <nav class="sidebar-nav">
        <!-- Main Dashboard -->
        <a href="index.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" data-tooltip="Dashboard">
            <span class="sidebar-nav-icon">📊</span>
            <span>Dashboard</span>
        </a>

        <div class="sidebar-divider"></div>

        <a href="Faculty.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'Faculty.php' || basename($_SERVER['PHP_SELF']) == 'InsertFaculty.php' || basename($_SERVER['PHP_SELF']) == 'EditFaculty.php') ? 'active' : ''; ?>" data-tooltip="Colleges">
            <span class="sidebar-nav-icon">🏛️</span>
            <span>Colleges</span>
        </a>
        <a href="Department.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'Department.php' || basename($_SERVER['PHP_SELF']) == 'InsertDepartment.php' || basename($_SERVER['PHP_SELF']) == 'EditDepartment.php') ? 'active' : ''; ?>" data-tooltip="Departments">
            <span class="sidebar-nav-icon">🏢</span>
            <span>Departments</span>
        </a>
        <a href="Course.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'Course.php' || basename($_SERVER['PHP_SELF']) == 'InsertCourse.php' || basename($_SERVER['PHP_SELF']) == 'EditCourse.php') ? 'active' : ''; ?>" data-tooltip="Courses">
            <span class="sidebar-nav-icon">📚</span>
            <span>Courses</span>
        </a>

        <div class="sidebar-divider"></div>

        <a href="Student.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'Student.php' || basename($_SERVER['PHP_SELF']) == 'InsertStudent.php' || basename($_SERVER['PHP_SELF']) == 'EditStudent.php') ? 'active' : ''; ?>" data-tooltip="Students">
            <span class="sidebar-nav-icon">👨‍🎓</span>
            <span>Students</span>
        </a>
        <a href="Instructor.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'Instructor.php' || basename($_SERVER['PHP_SELF']) == 'InsertInstructor.php' || basename($_SERVER['PHP_SELF']) == 'EditInstructor.php') ? 'active' : ''; ?>" data-tooltip="Instructors">
            <span class="sidebar-nav-icon">👨‍🏫</span>
            <span>Instructors</span>
        </a>
        <a href="AcademicOfficer.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'AcademicOfficer.php' || basename($_SERVER['PHP_SELF']) == 'InsertAcademicOfficer.php' || basename($_SERVER['PHP_SELF']) == 'EditAcademicOfficer.php') ? 'active' : ''; ?>" data-tooltip="Department Heads">
            <span class="sidebar-nav-icon">👔</span>
            <span>Department Heads</span>
        </a>
        <a href="BulkImport.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'BulkImport.php') ? 'active' : ''; ?>" data-tooltip="Bulk Import">
            <span class="sidebar-nav-icon">📥</span>
            <span>Bulk Import</span>
        </a>

        <div class="sidebar-divider"></div>

        <a href="Reports.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'Reports.php') ? 'active' : ''; ?>" data-tooltip="Reports">
            <span class="sidebar-nav-icon">📈</span>
            <span>Reports</span>
        </a>
        <a href="Settings.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'Settings.php' || basename($_SERVER['PHP_SELF']) == 'SecurityLogs.php' || basename($_SERVER['PHP_SELF']) == 'ResetPassword.php' || basename($_SERVER['PHP_SELF']) == 'DatabaseBackup.php' || basename($_SERVER['PHP_SELF']) == 'SystemSettings.php') ? 'active' : ''; ?>" data-tooltip="Settings">
            <span class="sidebar-nav-icon">⚙️</span>
            <span>Settings</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar"><?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?></div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?php echo htmlspecialchars(substr($_SESSION['username'] ?? 'Admin', 0, 20)); ?></div>
                <div class="sidebar-user-role">Administrator</div>
            </div>
        </div>
        <a href="Logout.php" class="btn btn-danger btn-block">
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
