<!-- Compact Modern Exam Committee Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand-wrapper">
            <img src="../images/logo1.png" alt="Logo" class="brand-logo" onerror="this.style.display='none'">
            <div class="brand-text">
                <h2 class="brand-title">Exam Committee</h2>
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
        
        <div class="sidebar-divider"></div>
        
        <!-- Review & Approval -->
        <a href="PendingApprovals.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'PendingApprovals.php') ? 'active' : ''; ?>" data-tooltip="Pending Approvals">
            <span class="sidebar-nav-icon">⏳</span>
            <span>Pending Approvals</span>
            <?php
            // Get pending exams count
            if(!isset($con) || !$con || $con->connect_error) {
                try {
                    $sidebar_con = new mysqli("localhost", "root", "", "oes_professional");
                    if(!$sidebar_con->connect_error) {
                        $result = $sidebar_con->query("SELECT COUNT(*) as count FROM exam_schedules WHERE approval_status = 'pending' AND submitted_for_approval = TRUE");
                        if($result) {
                            $pendingCount = $result->fetch_assoc()['count'];
                            if($pendingCount > 0):
                            ?>
                            <span class="sidebar-badge"><?php echo $pendingCount; ?></span>
                            <?php 
                            endif;
                        }
                        $sidebar_con->close();
                    }
                } catch(Exception $e) {
                    // Silently fail
                }
            } else {
                try {
                    $result = $con->query("SELECT COUNT(*) as count FROM exam_schedules WHERE approval_status = 'pending' AND submitted_for_approval = TRUE");
                    if($result) {
                        $pendingCount = $result->fetch_assoc()['count'];
                        if($pendingCount > 0):
                        ?>
                        <span class="sidebar-badge"><?php echo $pendingCount; ?></span>
                        <?php 
                        endif;
                    }
                } catch(Exception $e) {
                    // Silently fail
                }
            }
            ?>
        </a>
        
        <div class="sidebar-divider"></div>
        
        <!-- Exam Management -->
        <a href="ApprovedExams.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'ApprovedExams.php') ? 'active' : ''; ?>" data-tooltip="Approved Exams">
            <span class="sidebar-nav-icon">✅</span>
            <span>Approved Exams</span>
        </a>
        <a href="DepartmentExams.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'DepartmentExams.php') ? 'active' : ''; ?>" data-tooltip="Department Exams">
            <span class="sidebar-nav-icon">🏛️</span>
            <span>Department Exams</span>
        </a>
        <a href="ApprovalHistory.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'ApprovalHistory.php') ? 'active' : ''; ?>" data-tooltip="Approval History">
            <span class="sidebar-nav-icon">📜</span>
            <span>Approval History</span>
        </a>
        
        <div class="sidebar-divider"></div>
        
        <!-- Settings -->
        <a href="ChangePassword.php" class="sidebar-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'ChangePassword.php') ? 'active' : ''; ?>" data-tooltip="Change Password">
            <span class="sidebar-nav-icon">🔒</span>
            <span>Change Password</span>
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
                <div class="sidebar-user-role">Exam Committee</div>
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
