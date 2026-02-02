<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "View Student";

// Get student ID from URL
$studentId = $_GET['id'] ?? null;

if (!$studentId) {
    header("Location: Students.php");
    exit();
}

// Get department ID from session
$deptId = $_SESSION['DeptId'] ?? null;

// Get student details - ensure they belong to this department
$stmt = $con->prepare("SELECT s.*, d.department_name, f.faculty_name 
    FROM students s 
    LEFT JOIN departments d ON s.department_id = d.department_id 
    LEFT JOIN faculties f ON d.faculty_id = f.faculty_id 
    WHERE s.student_id = ? AND s.department_id = ?");
$stmt->bind_param("ii", $studentId, $deptId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: Students.php");
    exit();
}

$student = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .profile-header {
            text-align: center;
            padding: 2.5rem 2rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, #0056b3 100%);
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            color: white;
            margin: -1.5rem -1.5rem 0 -1.5rem;
        }
        .profile-avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        .profile-header h2 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: 700;
        }
        .profile-header p {
            margin: 0;
            opacity: 0.95;
            font-size: 1.1rem;
        }
        .info-section {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .info-section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }
        .info-section-icon {
            font-size: 1.75rem;
        }
        .info-section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }
        .info-grid {
            display: grid;
            gap: 1.25rem;
        }
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }
        .info-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 1.05rem;
            color: var(--text-primary);
            font-weight: 500;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border-color);
        }
        
        @media (max-width: 968px) {
            .profile-header {
                margin: -1rem -1rem 0 -1rem;
                padding: 2rem 1.5rem;
            }
            
            .info-section {
                padding: 1.25rem;
            }
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>
    
    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>
        
        <div class="admin-content">
            <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: var(--primary-color); font-weight: 700;">Student Profile</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 1.05rem;">
                    Detailed information for <?php echo htmlspecialchars($student['full_name']); ?>
                </p>
            </div>

            <div class="card">
                <div class="profile-header">
                    <div class="profile-avatar-large">
                        <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                    </div>
                    <h2 style="color: #FFD700;"><?php echo htmlspecialchars($student['full_name']); ?></h2>
                    <p style="color: #90EE90; font-weight: 600;"><?php echo htmlspecialchars($student['student_code']); ?></p>
                </div>

                <div style="padding: 2rem;">
                    <!-- Personal & Academic Information Side by Side -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                        <!-- Personal Information -->
                        <div class="info-section">
                            <div class="info-section-header">
                                <span class="info-section-icon">👤</span>
                                <h3 class="info-section-title">Personal Information</h3>
                            </div>
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-label">Student ID</span>
                                    <span class="info-value"><?php echo htmlspecialchars($student['student_code']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Full Name</span>
                                    <span class="info-value"><?php echo htmlspecialchars($student['full_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Gender</span>
                                    <span class="info-value"><?php echo htmlspecialchars($student['gender'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Email Address</span>
                                    <span class="info-value" style="word-break: break-word;"><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Phone Number</span>
                                    <span class="info-value"><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Information -->
                        <div class="info-section">
                            <div class="info-section-header">
                                <span class="info-section-icon">🎓</span>
                                <h3 class="info-section-title">Academic Information</h3>
                            </div>
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-label">Department</span>
                                    <span class="info-value"><?php echo htmlspecialchars($student['department_name'] ?? 'Not Assigned'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Faculty</span>
                                    <span class="info-value"><?php echo htmlspecialchars($student['faculty_name'] ?? 'Not Assigned'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Academic Year</span>
                                    <span class="info-value" style="color: var(--primary-color); font-weight: 700; font-size: 1.25rem;">
                                        <?php echo $student['academic_year'] ? 'Year ' . htmlspecialchars($student['academic_year']) : 'N/A'; ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Current Semester</span>
                                    <span class="info-value">Semester <?php echo htmlspecialchars($student['semester']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Enrollment Date</span>
                                    <span class="info-value"><?php echo date('F j, Y', strtotime($student['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Information Full Width -->
                    <div class="info-section">
                        <div class="info-section-header">
                            <span class="info-section-icon">🔐</span>
                            <h3 class="info-section-title">Account Information</h3>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem;">
                            <div class="info-item">
                                <span class="info-label">Username</span>
                                <span class="info-value"><?php echo htmlspecialchars($student['username']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Account Status</span>
                                <span class="info-value">
                                    <?php if($student['is_active']): ?>
                                    <span class="badge badge-success" style="font-size: 0.9rem; padding: 0.4rem 0.8rem;">✓ Active</span>
                                    <?php else: ?>
                                    <span class="badge badge-danger" style="font-size: 0.9rem; padding: 0.4rem 0.8rem;">✗ Inactive</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Last Login</span>
                                <span class="info-value" style="font-size: 0.95rem;">
                                    <?php echo $student['last_login'] ? date('M j, Y g:i A', strtotime($student['last_login'])) : 'Never logged in'; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Last Updated</span>
                                <span class="info-value" style="font-size: 0.95rem;"><?php echo date('M j, Y g:i A', strtotime($student['updated_at'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <a href="Students.php" class="btn btn-secondary" style="padding: 0.75rem 2rem; font-size: 1rem;">
                            ← Back to Students
                        </a>
                        <a href="EditStudent.php?id=<?php echo $student['student_id']; ?>" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1rem;">
                            ✏️ Edit Student
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $con->close(); ?>
    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
