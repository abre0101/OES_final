<?php
session_start();
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$instructor_id = $_SESSION['ID'];
$instructor_name = $_SESSION['Name'];

// Get filter parameters
$courseFilter = $_GET['course'] ?? '';
$semesterFilter = $_GET['semester'] ?? '';

// Get courses taught by this instructor
$coursesQuery = "SELECT DISTINCT c.course_id, c.course_name, c.semester 
    FROM courses c 
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id 
    WHERE ic.instructor_id = ? AND ic.is_active = 1 
    ORDER BY c.course_name";

$stmt = $con->prepare($coursesQuery);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$courses = $stmt->get_result();

// Build student query based on filters
$studentQuery = "SELECT DISTINCT 
    s.student_code,
    s.full_name,
    s.email,
    s.department_id,
    s.semester as student_semester,
    c.course_name,
    c.course_id,
    c.course_code,
    sc.semester as enrolled_semester,
    d.department_name
FROM students s
INNER JOIN student_courses sc ON s.student_id = sc.student_id
INNER JOIN courses c ON sc.course_id = c.course_id
INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
LEFT JOIN departments d ON s.department_id = d.department_id
WHERE ic.instructor_id = ? AND ic.is_active = 1 AND sc.is_active = 1";

$params = [$instructor_id];
$types = "i";

if ($courseFilter) {
    $studentQuery .= " AND c.course_id = ?";
    $params[] = $courseFilter;
    $types .= "i";
}

if ($semesterFilter) {
    $studentQuery .= " AND sc.semester = ?";
    $params[] = $semesterFilter;
    $types .= "i";
}

$studentQuery .= " ORDER BY c.course_name, s.full_name";

$stmt = $con->prepare($studentQuery);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$students = $stmt->get_result();

// Get statistics
$statsQuery = "SELECT 
    COUNT(DISTINCT s.student_id) as total_students,
    COUNT(DISTINCT c.course_id) as total_courses,
    COUNT(*) as total_enrollments
FROM students s
INNER JOIN student_courses sc ON s.student_id = sc.student_id
INNER JOIN courses c ON sc.course_id = c.course_id
INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
WHERE ic.instructor_id = ? AND ic.is_active = 1 AND sc.is_active = 1";

$stmt = $con->prepare($statsQuery);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Students - Instructor</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        .page-header-modern { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2); margin-bottom: 2rem; }
        .page-header-modern h1 { margin: 0 0 0.5rem 0; font-size: 2.2rem; font-weight: 800; display: flex; align-items: center; gap: 1rem; color: white; }
        .page-header-modern h1 span { color: white; }
        .page-header-modern p { margin: 0; opacity: 0.95; font-size: 1.05rem; color: white; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; border-radius: 12px; padding: 1.75rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); border-left: 5px solid; transition: transform 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card.primary { border-left-color: #007bff; }
        .stat-card.success { border-left-color: #28a745; }
        .stat-card.info { border-left-color: #17a2b8; }
        .stat-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
        .stat-value { font-size: 2.5rem; font-weight: 900; color: #003366; margin-bottom: 0.5rem; }
        .stat-label { font-size: 0.95rem; color: #6c757d; font-weight: 500; }
        .filter-section { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); margin-bottom: 2rem; }
        .filter-section h3 { margin: 0 0 1.5rem 0; font-size: 1.5rem; font-weight: 700; color: #003366; }
        .filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem; }
        .filter-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #003366; font-size: 0.95rem; }
        .filter-group select { width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 0.95rem; transition: all 0.3s ease; font-family: 'Poppins', sans-serif; }
        .filter-group select:focus { outline: none; border-color: #003366; box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1); }
        .btn-filter { padding: 0.85rem 1.75rem; border-radius: 8px; font-weight: 600; font-size: 0.95rem; border: none; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; }
        .btn-filter:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3); }
        .data-table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); }
        .data-table thead { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); }
        .data-table th { padding: 1rem; text-align: left; color: white; font-weight: 600; font-size: 0.9rem; white-space: nowrap; }
        .data-table td { padding: 0.85rem 1rem; border-bottom: 1px solid #e8eef3; font-size: 0.9rem; }
        .data-table tbody tr:hover { background: #f8f9fa; }
        .course-badge { display: inline-block; padding: 0.4rem 0.9rem; background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        @media (max-width: 768px) { .stats-grid { grid-template-columns: 1fr; } .filter-grid { grid-template-columns: 1fr; } .data-table { font-size: 0.8rem; } .data-table th, .data-table td { padding: 0.5rem; } }
    </style>
            opacity: 0.9;
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-secondary);
        }
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php 
        $pageTitle = 'My Students';
        include 'header-component.php'; 
        ?>

        <div class="admin-content">
            <div class="page-header-modern">
                <h1><span>👨‍🎓</span> My Students</h1>
                <p>Students enrolled in courses you teach</p>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-value"><?php echo number_format($stats['total_students']); ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon">📚</div>
                    <div class="stat-value"><?php echo number_format($stats['total_courses']); ?></div>
                    <div class="stat-label">Courses Teaching</div>
                </div>
                <div class="stat-card info">
                    <div class="stat-icon">📝</div>
                    <div class="stat-value"><?php echo number_format($stats['total_enrollments']); ?></div>
                    <div class="stat-label">Total Enrollments</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-section">
                <h3>🔍 Filter Students</h3>
                <form method="GET" action="">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label>Filter by Course</label>
                            <select name="course">
                                <option value="">All Courses</option>
                                <?php 
                                $courses->data_seek(0);
                                while($course = $courses->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $course['course_id']; ?>" <?php echo $courseFilter == $course['course_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['course_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Filter by Semester</label>
                            <select name="semester">
                                <option value="">All Semesters</option>
                                <option value="1" <?php echo $semesterFilter == '1' ? 'selected' : ''; ?>>Semester 1</option>
                                <option value="2" <?php echo $semesterFilter == '2' ? 'selected' : ''; ?>>Semester 2</option>
                                <option value="3" <?php echo $semesterFilter == '3' ? 'selected' : ''; ?>>Semester 3</option>
                                <option value="4" <?php echo $semesterFilter == '4' ? 'selected' : ''; ?>>Semester 4</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-filter"><span>🔍</span> Apply Filters</button>
                    <?php if($courseFilter || $semesterFilter): ?>
                    <a href="ViewStudents.php" class="btn-filter" style="background: #6c757d; margin-left: 1rem;">Clear Filters</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Students List -->
            <div style="background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);">
                <h3 style="margin: 0 0 1.5rem 0; font-size: 1.4rem; font-weight: 700; color: #003366;">
                    📋 Student List
                    <?php if($courseFilter || $semesterFilter): ?>
                    <span style="font-size: 0.9rem; color: #6c757d; font-weight: normal;">(Filtered)</span>
                    <?php endif; ?>
                </h3>
                <?php if($students->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Student Code</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Course</th>
                                <th>Semester</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        $students->data_seek(0);
                        while($student = $students->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_code']); ?></td>
                            <td><strong><?php echo htmlspecialchars($student['full_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo htmlspecialchars($student['department_name'] ?? 'N/A'); ?></td>
                            <td><span class="course-badge"><?php echo htmlspecialchars($student['course_name']); ?></span></td>
                            <td>Semester <?php echo $student['student_semester']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 3rem; color: #6c757d;">
                        <div style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;">🎓</div>
                        <h3 style="margin-bottom: 0.5rem;">No Students Found</h3>
                        <p>
                            <?php if($courseFilter || $semesterFilter): ?>
                                No students match your filter criteria. Try adjusting the filters.
                            <?php else: ?>
                                No students are currently enrolled in your courses.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>
