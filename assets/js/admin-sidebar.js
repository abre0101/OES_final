// Real-time clock
function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });
    const timeElement = document.getElementById('currentTime');
    if (timeElement) {
        timeElement.textContent = timeString;
    }
}
updateTime();
setInterval(updateTime, 1000);

// Toggle sidebar minimize/maximize
function toggleSidebarMinimize() {
    const sidebar = document.getElementById('adminSidebar');
    const toggleIcon = document.getElementById('toggleIcon');

    if (!sidebar || !toggleIcon) return;

    sidebar.classList.toggle('minimized');

    if (sidebar.classList.contains('minimized')) {
        toggleIcon.textContent = '▶';
        localStorage.setItem('sidebarMinimized', 'true');
    } else {
        toggleIcon.textContent = '◀';
        localStorage.setItem('sidebarMinimized', 'false');
    }
}

// Toggle sidebar for mobile
function toggleSidebar() {
    const sidebar = document.querySelector('.admin-sidebar');
    if (sidebar) {
        // On mobile, toggle the 'open' class
        if (window.innerWidth <= 1024) {
            sidebar.classList.toggle('open');
        } else {
            // On desktop, use the minimize function
            toggleSidebarMinimize();
        }
    }
}

// Tab switching function for pages with tabs
function switchTab(index) {
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach((tab, i) => {
        if (i === index) {
            tab.classList.add('active');
            contents[i].classList.add('active');
        } else {
            tab.classList.remove('active');
            contents[i].classList.remove('active');
        }
    });
}

// Restore sidebar state from localStorage
window.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('adminSidebar');
    const toggleIcon = document.getElementById('toggleIcon');

    if (sidebar && toggleIcon) {
        const isMinimized = localStorage.getItem('sidebarMinimized') === 'true';

        if (isMinimized) {
            sidebar.classList.add('minimized');
            toggleIcon.textContent = '▶';
        }
    }

    // Initialize search functionality
    const searchInput = document.querySelector('.search-input');

    if (searchInput) {
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                const searchTerm = this.value.trim();
                if (searchTerm) {
                    performSearch(searchTerm);
                }
            }
        });
    }
});

// Search functionality
function performSearch(searchTerm) {
    const term = searchTerm.toLowerCase();

    const searchMap = {
        'student': 'Student-modern.php',
        'students': 'Student-modern.php',
        'instructor': 'Instructor.php',
        'instructors': 'Instructor.php',
        'teacher': 'Instructor.php',
        'teachers': 'Instructor.php',
        'course': 'Course.php',
        'courses': 'Course.php',
        'department': 'Department.php',
        'departments': 'Department.php',
        'college': 'Faculty.php',
        'faculty': 'Faculty.php',
        'exam': 'ECommittee.php',
        'committee': 'ECommittee.php',
        'exam committee': 'ECommittee.php',
        'dashboard': 'index.php',
        'home': 'index.php',
        'profile': 'Profile.php',
        'settings': 'SystemSettings.php',
        'system settings': 'SystemSettings.php'
    };

    if (searchMap[term]) {
        window.location.href = searchMap[term];
    } else {
        for (let key in searchMap) {
            if (key.includes(term) || term.includes(key)) {
                window.location.href = searchMap[key];
                return;
            }
        }

        alert('No results found for "' + searchTerm + '". Try: students, instructors, courses, departments, etc.');
    }
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function (event) {
    const sidebar = document.querySelector('.admin-sidebar');
    const menuBtn = document.querySelector('.mobile-menu-btn');

    if (!sidebar || !menuBtn) return;

    if (window.innerWidth <= 1024) {
        if (!sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
            sidebar.classList.remove('open');
        }
    }
});

// Toggle profile dropdown
function toggleProfileDropdown(event) {
    if (event) {
        event.stopPropagation();
    }
    const profile = event ? event.currentTarget : document.querySelector('.header-profile');
    if (profile) {
        profile.classList.toggle('active');
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function (event) {
    const profile = document.querySelector('.header-profile');
    if (profile && !profile.contains(event.target)) {
        profile.classList.remove('active');
    }
});
