<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Edit Exam Schedule";
$instructor_id = $_SESSION['ID'];
$exam_id = $_GET['id'] ?? 0;

// Get exam schedule details
$examQuery = $con->prepare("SELECT 
    es.*,
    c.course_id,
    c.course_name,
    c.course_code
    FROM exams es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE es.exam_id = ? AND ic.instructor_id = ?");
$examQuery->bind_param("ii", $exam_id, $instructor_id);
$examQuery->execute();
$exam = $examQuery->get_result()->fetch_assoc();
$examQuery->close();

if(!$exam) {
    header("Location: ManageExams.php");
    exit();
}

// Get exam categories
$categories = $con->query("SELECT * FROM exam_categories WHERE is_active = TRUE ORDER BY category_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Exam Schedule - Instructor</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header">
                <h1>⚙️ Edit Exam Schedule</h1>
                <p>Update exam date, time, and settings</p>
            </div>

            <!-- Exam Info -->
            <div style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); color: white; padding: 1.5rem; border-radius: var(--radius-lg); margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <h3 style="margin: 0; color: white;"><?php echo htmlspecialchars($exam['exam_name']); ?></h3>
                <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">
                    <?php echo htmlspecialchars($exam['course_name']); ?> (<?php echo $exam['course_code']; ?>)
                </p>
            </div>

            <!-- Edit Form -->
            <div class="form-wrapper">
                <form method="POST" action="UpdateSchedule.php">
                    <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
                    
                    <div class="form-section">
                        <h3 class="form-section-title">Exam Details</h3>
                        
                        <div class="form-group">
                            <label>Exam Name *</label>
                            <input type="text" name="exam_name" class="form-control" value="<?php echo htmlspecialchars($exam['exam_name']); ?>" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Exam Category *</label>
                                <select name="exam_category_id" class="form-control" required>
                                    <?php while($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $cat['exam_category_id']; ?>" 
                                        <?php echo ($cat['exam_category_id'] == $exam['exam_category_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Status *</label>
                                <select name="is_active" class="form-control" required>
                                    <option value="1" <?php echo $exam['is_active'] ? 'selected' : ''; ?>>Active</option>
                                    <option value="0" <?php echo !$exam['is_active'] ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Date & Time</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Exam Date *</label>
                                <input type="date" name="exam_date" class="form-control" 
                                    value="<?php echo $exam['exam_date']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Start Time *</label>
                                <input type="time" name="start_time" class="form-control" 
                                    value="<?php echo $exam['start_time']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label>End Time *</label>
                                <input type="time" name="end_time" class="form-control" 
                                    value="<?php echo $exam['end_time']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Duration (minutes) *</label>
                                <input type="number" name="duration_minutes" class="form-control" 
                                    value="<?php echo $exam['duration_minutes']; ?>" min="1" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Grading</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Total Marks *</label>
                                <input type="number" name="total_marks" class="form-control" 
                                    value="<?php echo $exam['total_marks']; ?>" min="1" required>
                            </div>

                            <div class="form-group">
                                <label>Pass Marks *</label>
                                <input type="number" name="pass_marks" class="form-control" 
                                    value="<?php echo $exam['pass_marks']; ?>" min="1" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Instructions</h3>
                        
                        <div class="form-group">
                            <label>Exam Instructions (Optional)</label>
                            <textarea name="instructions" class="form-control" rows="4" 
                                placeholder="Enter instructions for students..."><?php echo htmlspecialchars($exam['instructions'] ?? ''); ?></textarea>
                            <small style="color: var(--text-secondary);">These instructions will be shown to students before they start the exam</small>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            💾 Save Changes
                        </button>
                        <a href="ManageExams.php" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete()" style="margin-left: auto;">
                            🗑️ Delete Exam
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function confirmDelete() {
            if(confirm('Are you sure you want to delete this exam schedule? This action cannot be undone.')) {
                window.location.href = 'DeleteSchedule.php?id=<?php echo $exam_id; ?>';
            }
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
