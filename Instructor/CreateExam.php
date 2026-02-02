<?php
session_start();
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Create Exam";
$instructor_id = $_SESSION['ID'];

// Get instructor's courses
$coursesQuery = $con->prepare("SELECT DISTINCT c.course_id, c.course_name, c.course_code, c.semester
    FROM instructor_courses ic
    INNER JOIN courses c ON ic.course_id = c.course_id
    WHERE ic.instructor_id = ?
    ORDER BY c.course_name");
$coursesQuery->bind_param("i", $instructor_id);
$coursesQuery->execute();
$courses = $coursesQuery->get_result();

// Get exam categories
$categories = $con->query("SELECT * FROM exam_categories ORDER BY category_name");

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'];
    $category_id = $_POST['category_id'];
    $exam_name = trim($_POST['exam_name']);
    $duration = intval($_POST['duration_minutes']);
    $total_marks = intval($_POST['total_marks']);
    $pass_marks = intval($_POST['pass_marks']);
    $instructions = trim($_POST['instructions']);
    
    // Validate
    if(empty($exam_name) || $duration <= 0 || $total_marks <= 0 || $pass_marks <= 0) {
        $error = "Please fill all required fields with valid values.";
    } elseif($pass_marks > $total_marks) {
        $error = "Pass marks cannot exceed total marks.";
    } else {
        // Insert exam as draft
        $insertQuery = $con->prepare("INSERT INTO exams 
            (course_id, exam_category_id, exam_name, duration_minutes, total_marks, pass_marks, 
            instructions, approval_status, created_by, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'draft', ?, NOW())");
        
        $insertQuery->bind_param("iisiissi", 
            $course_id, $category_id, $exam_name, $duration, 
            $total_marks, $pass_marks, $instructions, $instructor_id);
        
        if($insertQuery->execute()) {
            $exam_id = $con->insert_id;
            $success = "Exam created successfully as draft! You can now add questions.";
            
            // Redirect to add questions
            header("Location: ManageExamQuestions.php?exam_id=" . $exam_id . "&new=1");
            exit();
        } else {
            $error = "Failed to create exam: " . $con->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Exam - Instructor</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        .page-header-modern { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2); margin-bottom: 2rem; }
        .page-header-modern h1 { margin: 0 0 0.5rem 0; font-size: 2.2rem; font-weight: 800; display: flex; align-items: center; gap: 1rem; color: white; }
        .page-header-modern p { margin: 0; opacity: 0.95; font-size: 1.05rem; color: white; }
        .form-card { background: white; border-radius: 12px; padding: 2.5rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #003366; font-size: 0.95rem; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 0.95rem; transition: all 0.3s ease; font-family: 'Poppins', sans-serif; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #003366; box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1); }
        .form-group textarea { min-height: 120px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
        .btn { padding: 0.85rem 1.75rem; border-radius: 8px; font-weight: 600; font-size: 0.95rem; border: none; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3); }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        .alert { padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .info-box { background: #e7f3ff; border-left: 4px solid #0066cc; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; }
        .info-box h3 { margin: 0 0 0.5rem 0; color: #003366; font-size: 1.1rem; }
        .info-box p { margin: 0; color: #555; }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header-modern">
                <h1><span>📝</span> Create New Exam</h1>
                <p>Create an exam and submit it for department head approval</p>
            </div>

            <?php if(isset($error)): ?>
            <div class="alert alert-danger">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if(isset($success)): ?>
            <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="info-box">
                <h3>📋 Exam Creation Workflow</h3>
                <p><strong>Step 1:</strong> Create exam (saved as draft) → <strong>Step 2:</strong> Add questions → <strong>Step 3:</strong> Submit for approval → <strong>Step 4:</strong> Department Head reviews and approves → <strong>Step 5:</strong> Department Head schedules the exam</p>
            </div>

            <div class="form-card">
                <h2 style="margin: 0 0 1.5rem 0; color: #003366; font-size: 1.5rem;">Exam Details</h2>
                
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Course <span style="color: red;">*</span></label>
                            <select name="course_id" required>
                                <option value="">Select Course</option>
                                <?php while($course = $courses->fetch_assoc()): ?>
                                <option value="<?php echo $course['course_id']; ?>">
                                    <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Exam Category <span style="color: red;">*</span></label>
                            <select name="category_id" required>
                                <option value="">Select Category</option>
                                <?php while($category = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $category['exam_category_id']; ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Exam Name <span style="color: red;">*</span></label>
                        <input type="text" name="exam_name" placeholder="e.g., Midterm Exam - Fall 2024" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Duration (Minutes) <span style="color: red;">*</span></label>
                            <input type="number" name="duration_minutes" min="1" placeholder="e.g., 90" required>
                        </div>

                        <div class="form-group">
                            <label>Total Marks <span style="color: red;">*</span></label>
                            <input type="number" name="total_marks" min="1" value="100" required>
                        </div>

                        <div class="form-group">
                            <label>Pass Marks <span style="color: red;">*</span></label>
                            <input type="number" name="pass_marks" min="1" value="50" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Instructions</label>
                        <textarea name="instructions" placeholder="Enter exam instructions for students..."></textarea>
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">
                            <span>💾</span> Create Exam (Draft)
                        </button>
                        <a href="ManageSchedules.php" class="btn btn-secondary">
                            <span>❌</span> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>
