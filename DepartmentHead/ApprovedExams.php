<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Department Head session
SessionManager::startSession('DepartmentHead');

// Check if user is logged in
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

// Validate user role
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'DepartmentHead'){
    SessionManager::destroySession();
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");

// Get filter parameters
$departmentFilter = $_GET['department'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Build query
$query = "SELECT es.*, c.course_name, c.course_code, d.department_name, ec.category_name,
    (SELECT COUNT(*) FROM exam_questions eq WHERE eq.exam_id = es.exam_id) as question_count
    FROM exams es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN departments d ON c.department_id = d.department_id
    INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    WHERE es.approval_status = 'approved'";

if($departmentFilter) {
    $query .= " AND d.department_id = " . intval($departmentFilter);
}

if($searchQuery) {
    $query .= " AND (es.exam_name LIKE '%" . $con->real_escape_string($searchQuery) . "%' 
                OR c.course_name LIKE '%" . $con->real_escape_string($searchQuery) . "%'
                OR c.course_code LIKE '%" . $con->real_escape_string($searchQuery) . "%')";
}

$query .= " ORDER BY es.approved_at DESC";
$approvedExams = $con->query($query);

// Get departments
$departments = $con->query("SELECT DISTINCT d.department_id, d.department_name 
    FROM departments d 
    INNER JOIN courses c ON d.department_id = c.department_id
    INNER JOIN exams es ON c.course_id = es.course_id
    WHERE es.approval_status = 'approved'
    ORDER BY d.department_name");

// Get statistics
$stats = $con->query("SELECT 
    COUNT(*) as total_approved,
    SUM(CASE WHEN DATE(es.approved_at) = CURDATE() THEN 1 ELSE 0 END) as approved_today,
    SUM(CASE WHEN es.exam_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming
    FROM exams es
    WHERE es.approval_status = 'approved'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Exams</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .compact-stat {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .compact-stat-icon {
            width: 50px;
            height: 50px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .exam-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.25rem;
        }
        .exam-card-compact {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid var(--success-color);
            transition: all 0.2s;
        }
        .exam-card-compact:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 1.75rem; margin: 0 0 0.25rem 0; color: var(--primary-color);">✅ Approved Exams</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.95rem;">View all approved examinations</p>
            </div>

            <!-- Stats -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <div class="compact-stat">
                    <div class="compact-stat-icon" style="background: rgba(40, 167, 69, 0.1); color: var(--success-color);">✅</div>
                    <div>
                        <div style="font-size: 1.75rem; font-weight: 700; color: var(--success-color);"><?php echo $stats['total_approved']; ?></div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">Total Approved</div>
                    </div>
                </div>
                <div class="compact-stat">
                    <div class="compact-stat-icon" style="background: rgba(0, 123, 255, 0.1); color: var(--primary-color);">📅</div>
                    <div>
                        <div style="font-size: 1.75rem; font-weight: 700; color: var(--primary-color);"><?php echo $stats['approved_today']; ?></div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">Approved Today</div>
                    </div>
                </div>
                <div class="compact-stat">
                    <div class="compact-stat-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">⏰</div>
                    <div>
                        <div style="font-size: 1.75rem; font-weight: 700; color: #ffc107;"><?php echo $stats['upcoming']; ?></div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">Upcoming</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card" style="margin-bottom: 2rem;">
                <div style="padding: 1.25rem;">
                    <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
                        <div style="flex: 1; min-width: 200px;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem;">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Exam name, course..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                        </div>
                        <div style="flex: 1; min-width: 200px;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem;">Department</label>
                            <select name="department" class="form-control">
                                <option value="">All Departments</option>
                                <?php while($dept = $departments->fetch_assoc()): ?>
                                <option value="<?php echo $dept['department_id']; ?>" <?php echo $departmentFilter == $dept['department_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">🔍 Filter</button>
                        <?php if($departmentFilter || $searchQuery): ?>
                        <a href="ApprovedExams.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Exams Grid -->
            <?php if($approvedExams && $approvedExams->num_rows > 0): ?>
            <div class="exam-grid">
                <?php while($exam = $approvedExams->fetch_assoc()): ?>
                <div class="exam-card-compact">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <h3 style="margin: 0; font-size: 1.1rem; color: var(--primary-color); flex: 1;">
                            <?php echo htmlspecialchars($exam['exam_name']); ?>
                        </h3>
                        <span style="background: #d4edda; color: #155724; padding: 0.25rem 0.6rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; white-space: nowrap;">✓ Approved</span>
                    </div>
                    
                    <div style="background: var(--bg-light); padding: 0.75rem; border-radius: var(--radius-md); margin-bottom: 1rem; font-size: 0.85rem;">
                        <div style="margin-bottom: 0.5rem;"><strong>Course:</strong> <?php echo htmlspecialchars($exam['course_code']); ?></div>
                        <div style="margin-bottom: 0.5rem;"><strong>Department:</strong> <?php echo htmlspecialchars($exam['department_name']); ?></div>
                        <div><strong>Category:</strong> <?php echo htmlspecialchars($exam['category_name']); ?></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem; font-size: 0.85rem;">
                        <div>
                            <div style="color: var(--text-secondary); font-size: 0.75rem;">Exam Date</div>
                            <div style="font-weight: 600;"><?php echo date('M d, Y', strtotime($exam['exam_date'])); ?></div>
                        </div>
                        <div>
                            <div style="color: var(--text-secondary); font-size: 0.75rem;">Duration</div>
                            <div style="font-weight: 600;"><?php echo $exam['duration_minutes']; ?> min</div>
                        </div>
                        <div>
                            <div style="color: var(--text-secondary); font-size: 0.75rem;">Questions</div>
                            <div style="font-weight: 600;"><?php echo $exam['question_count']; ?></div>
                        </div>
                        <div>
                            <div style="color: var(--text-secondary); font-size: 0.75rem;">Approved</div>
                            <div style="font-weight: 600;"><?php echo $exam['approved_at'] ? date('M d', strtotime($exam['approved_at'])) : 'N/A'; ?></div>
                        </div>
                    </div>
                    
                    <a href="ViewExamDetails.php?exam_id=<?php echo $exam['exam_id']; ?>" class="btn btn-primary btn-sm" style="width: 100%;">
                        👁️ View Details
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="card" style="padding: 3rem; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;">✅</div>
                <h3 style="color: var(--text-secondary); margin-bottom: 0.5rem;">No Approved Exams Found</h3>
                <p style="color: var(--text-secondary); margin: 0;">Try adjusting your filters</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php $con->close(); ?>
    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
