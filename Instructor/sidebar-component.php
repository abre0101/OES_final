<!-- Compact Modern Instructor Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand-wrapper">
            <img src="../images/logo1.png" alt="Logo" class="brand-logo" onerror="this.style.display='none'">
            <div class="brand-text">
                <h2 class="brand-title">Instructor</h2>
                <span class="brand-subtitle">DMU</span>
            </div>
        </div>
        <button class="sidebar-toggle-btn" onclick="toggleSidebarMinimize()" title="Toggle Sidebar" id="sidebarToggleBtn">
            <span id="toggleIcon">◀</span>
        </button>
    </div>

    <nav class="sidebar-nav">
        <!-- Main -->
        <a href="index.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" data-tooltip="Dashboard">
            <span class="sidebar-nav-icon">📊</span>
            <span>Dashboard</span>
        </a>
        <a href="MyCourses.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'MyCourses.php') ? 'active' : ''; ?>" data-tooltip="My Courses">
            <span class="sidebar-nav-icon">📚</span>
            <span>My Courses</span>
        </a>
        
        <div class="sidebar-divider"></div>
        
        <!-- Questions & Exams -->
        <a href="ManageQuestions.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'ManageQuestions.php' || basename($_SERVER['PHP_SELF']) == 'AddQuestion.php' || basename($_SERVER['PHP_SELF']) == 'EditQuestion.php') ? 'active' : ''; ?>" data-tooltip="Questions">
            <span class="sidebar-nav-icon">📝</span>
            <span>Questions</span>
        </a>
        <a href="MyExams.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'MyExams.php' || basename($_SERVER['PHP_SELF']) == 'CreateExam.php' || basename($_SERVER['PHP_SELF']) == 'ManageExamQuestions.php' || basename($_SERVER['PHP_SELF']) == 'SubmitExamForApproval.php') ? 'active' : ''; ?>" data-tooltip="My Exams">
            <span class="sidebar-nav-icon">📋</span>
            <span>My Exams</span>
        </a>
        <a href="ViewStudents.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'ViewStudents.php') ? 'active' : ''; ?>" data-tooltip="Students">
            <span class="sidebar-nav-icon">👨‍🎓</span>
            <span>Students</span>
        </a>
        
        <div class="sidebar-divider"></div>
        
        <!-- Results & Analytics -->
        <a href="ResultsOverview.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'ResultsOverview.php' || basename($_SERVER['PHP_SELF']) == 'ViewStudentResult.php') ? 'active' : ''; ?>" data-tooltip="Results">
            <span class="sidebar-nav-icon">📈</span>
            <span>Results</span>
        </a>
        <a href="Reports.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'Reports.php') ? 'active' : ''; ?>" data-tooltip="Reports">
            <span class="sidebar-nav-icon">📊</span>
            <span>Reports</span>
        </a>
        <a href="Analytics.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'Analytics.php') ? 'active' : ''; ?>" data-tooltip="Analytics">
            <span class="sidebar-nav-icon">📉</span>
            <span>Analytics</span>
        </a>
        
        <div class="sidebar-divider"></div>
        
        <!-- Settings -->
        <a href="Settings.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'Settings.php') ? 'active' : ''; ?>" data-tooltip="Settings">
            <span class="sidebar-nav-icon">⚙️</span>
            <span>Settings</span>
        </a>
        <a href="Help.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'Help.php') ? 'active' : ''; ?>" data-tooltip="Help">
            <span class="sidebar-nav-icon">❓</span>
            <span>Help</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar"><?php echo strtoupper(substr($_SESSION['Name'], 0, 1)); ?></div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?php echo htmlspecialchars(substr($_SESSION['Name'], 0, 20)); ?></div>
                <div class="sidebar-user-role">Instructor</div>
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
