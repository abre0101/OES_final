<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location: ../index.php");
    exit();
}

$Id = $_GET['Id'];

// Establish Connection with Database
require_once(__DIR__ . "/../Connections/OES.php");

// Fetch student data
$stmt = $con->prepare("SELECT * FROM students WHERE student_id=?");
$stmt->bind_param("i", $Id);
$stmt->execute();
$result = $stmt->get_result();

if($row = mysqli_fetch_array($result)) {
    $Id = $row['student_id'];
    $Department = $row['department_id'];
    $Name = $row['full_name'];
    $Semester = $row['semester'];
    $Email = $row['email'];
    $UserName = $row['username'];
    $is_active = $row['is_active'];
} else {
    header("Location: Profile-modern.php");
    exit();
}

$stmt->close();
mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Student Portal</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/student-modern.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .edit-profile-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .profile-card {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 2px solid var(--border-color);
        }

        .profile-card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            padding: 2.5rem;
            text-align: center;
            color: white;
            position: relative;
        }

        .profile-card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.2) 0%, transparent 70%);
        }

        .profile-avatar-edit {
            width: 100px;
            height: 100px;
            border-radius: var(--radius-full);
            background: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin: 0 auto 1rem;
            border: 4px solid white;
            box-shadow: var(--shadow-lg);
            position: relative;
            z-index: 1;
        }

        .profile-card-header h1 {
            font-size: 2rem;
            margin: 0;
            position: relative;
            z-index: 1;
            color: #FFFFFF;
            font-weight: 800;
            text-shadow: 3px 3px 8px rgba(0, 0, 0, 0.5), 0 0 20px rgba(0, 0, 0, 0.3);
        }

        .profile-card-header p {
            margin: 0.5rem 0 0 0;
            opacity: 1;
            position: relative;
            z-index: 1;
            font-weight: 600;
            color: #FFFFFF;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.5), 0 0 15px rgba(0, 0, 0, 0.3);
        }

        .profile-card-body {
            padding: 3rem;
        }

        .info-section {
            margin-bottom: 3rem;
        }

        .info-section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 3px solid var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-field {
            background: var(--bg-light);
            padding: 1.25rem;
            border-radius: var(--radius-md);
            border: 2px solid var(--border-color);
        }

        .info-field-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .info-field-value {
            font-size: 1.1rem;
            color: var(--primary-color);
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-label .required {
            color: var(--danger-color);
            margin-left: 0.25rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(26, 43, 74, 0.1);
        }

        .form-input.error {
            border-color: var(--danger-color);
        }

        .error-message {
            color: var(--danger-color);
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2.5rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border-color);
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 500;
        }

        .alert-info {
            background: rgba(23, 162, 184, 0.1);
            border: 2px solid var(--accent-teal);
            color: var(--accent-teal);
        }

        .alert-icon {
            font-size: 1.5rem;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .profile-card-body {
                padding: 2rem 1.5rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
            }
        }
    </style>
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
                        <p>Online Examination System - Student Portal</p>
                    </div>
                </div>
                <div class="header-actions">
                    <div class="user-dropdown">
                        <div class="user-info">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($_SESSION['Name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div style="font-weight: 600;"><?php echo $_SESSION['Name']; ?></div>
                                <div style="font-size: 0.75rem; opacity: 0.8;">Student</div>
                            </div>
                            <svg style="width: 20px; height: 20px; margin-left: 0.5rem;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="dropdown-menu">
                            <a href="Profile-modern.php" class="dropdown-item">
                                <span class="dropdown-icon">👤</span>
                                <span>My Profile</span>
                            </a>
                            <a href="EditProfile-modern.php?Id=<?php echo $_SESSION['ID']; ?>" class="dropdown-item">
                                <span class="dropdown-icon">⚙️</span>
                                <span>Account Settings</span>
                            </a>
                            <a href="../Help-modern.php" class="dropdown-item">
                                <span class="dropdown-icon">❓</span>
                                <span>Help</span>
                            </a>
                            <a href="../AboutUs-modern.php" class="dropdown-item">
                                <span class="dropdown-icon">ℹ️</span>
                                <span>About</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="Logout.php" class="dropdown-item logout">
                                <span class="dropdown-icon">🚪</span>
                                <span>Log Out</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <nav class="main-nav">
            <div class="container">
                <ul class="nav-menu">
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="StartExam-modern.php">Take Exam</a></li>
                    <li><a href="Result-modern.php">Results</a></li>
                    <li><a href="practice-selection.php">Practice</a></li>
                    <li><a href="Profile-modern.php">Profile</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="edit-profile-container">
                <div class="profile-card">
                    <div class="profile-card-header">
                        <div class="profile-avatar-edit">
                            <?php echo strtoupper(substr($Name, 0, 1)); ?>
                        </div>
                        <h1>Edit Profile</h1>
                        <p>Update your account credentials</p>
                    </div>

                    <div class="profile-card-body">
                        <div class="alert alert-info">
                            <span class="alert-icon">ℹ️</span>
                            <span>You can only change your username and password. Other information is managed by the administration.</span>
                        </div>

                        <!-- Student Information (Read-only) -->
                        <div class="info-section">
                            <div class="info-section-title">
                                <span>📋</span>
                                <span>Student Information</span>
                            </div>
                            <div class="info-grid">
                                <div class="info-field">
                                    <div class="info-field-label">Student ID</div>
                                    <div class="info-field-value"><?php echo $Id; ?></div>
                                </div>
                                <div class="info-field">
                                    <div class="info-field-label">Full Name</div>
                                    <div class="info-field-value"><?php echo $Name; ?></div>
                                </div>
                                <div class="info-field">
                                    <div class="info-field-label">Department</div>
                                    <div class="info-field-value"><?php echo $Department; ?></div>
                                </div>
                                <div class="info-field">
                                    <div class="info-field-label">Semester</div>
                                    <div class="info-field-value"><?php echo $Semester; ?></div>
                                </div>
                                <div class="info-field">
                                    <div class="info-field-label">Email</div>
                                    <div class="info-field-value"><?php echo $Email; ?></div>
                                </div>
                                <div class="info-field">
                                    <div class="info-field-label">Account Status</div>
                                    <div class="info-field-value"><?php echo $is_active ? 'Active' : 'Inactive'; ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Form -->
                        <div class="info-section">
                            <div class="info-section-title">
                                <span>🔐</span>
                                <span>Account Credentials</span>
                            </div>
                            <form method="post" action="UpdateProfile.php?Id=<?php echo $Id; ?>" id="editProfileForm">
                                <div class="form-group">
                                    <label class="form-label" for="txtUser">
                                        Username <span class="required">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-input" 
                                        id="txtUser" 
                                        name="txtUser" 
                                        value="<?php echo htmlspecialchars($UserName); ?>"
                                        required
                                        minlength="3"
                                        maxlength="50"
                                    >
                                    <div class="error-message" id="usernameError">Username must be at least 3 characters long.</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="txtPass">
                                        New Password <span class="required">*</span>
                                    </label>
                                    <input 
                                        type="password" 
                                        class="form-input" 
                                        id="txtPass" 
                                        name="txtPass" 
                                        required
                                        minlength="6"
                                        placeholder="Enter new password (min. 6 characters)"
                                    >
                                    <div class="error-message" id="passwordError">Password must be at least 6 characters long.</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="txtPassConfirm">
                                        Confirm Password <span class="required">*</span>
                                    </label>
                                    <input 
                                        type="password" 
                                        class="form-input" 
                                        id="txtPassConfirm" 
                                        name="txtPassConfirm" 
                                        required
                                        placeholder="Re-enter your password"
                                    >
                                    <div class="error-message" id="confirmError">Passwords do not match.</div>
                                </div>

                                <div class="form-actions">
                                    <a href="Profile-modern.php" class="btn btn-secondary">
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        💾 Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="modern-footer">
        <div class="container">
            <div class="footer-content">
                <p>&copy;  2026 Debre Markos University Health Campus Online Examination System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Dropdown functionality
        const userDropdown = document.querySelector('.user-dropdown');
        const userInfo = userDropdown.querySelector('.user-info');
        const dropdownMenu = userDropdown.querySelector('.dropdown-menu');

        userInfo.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
        });

        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target)) {
                userDropdown.classList.remove('active');
            }
        });

        // Form validation
        const form = document.getElementById('editProfileForm');
        const username = document.getElementById('txtUser');
        const password = document.getElementById('txtPass');
        const confirmPassword = document.getElementById('txtPassConfirm');

        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Reset errors
            document.querySelectorAll('.error-message').forEach(el => el.classList.remove('show'));
            document.querySelectorAll('.form-input').forEach(el => el.classList.remove('error'));

            // Validate username
            if (username.value.trim().length < 3) {
                document.getElementById('usernameError').classList.add('show');
                username.classList.add('error');
                isValid = false;
            }

            // Validate password
            if (password.value.length < 6) {
                document.getElementById('passwordError').classList.add('show');
                password.classList.add('error');
                isValid = false;
            }

            // Validate password confirmation
            if (password.value !== confirmPassword.value) {
                document.getElementById('confirmError').classList.add('show');
                confirmPassword.classList.add('error');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Real-time validation
        confirmPassword.addEventListener('input', function() {
            if (password.value && confirmPassword.value) {
                if (password.value === confirmPassword.value) {
                    document.getElementById('confirmError').classList.remove('show');
                    confirmPassword.classList.remove('error');
                } else {
                    document.getElementById('confirmError').classList.add('show');
                    confirmPassword.classList.add('error');
                }
            }
        });
    </script>
</body>
</html>
