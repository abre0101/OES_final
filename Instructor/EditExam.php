<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
$pageTitle = "Edit Exam";

$exam_id = $_GET['id'] ?? 0;

// Get exam details
$exam = $con->query("SELECT * FROM exam_category WHERE exam_id = '$exam_id'")->fetch_assoc();

if(!$exam) {
    header("Location: ManageExams.php");
    exit();
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $exam_name = $_POST['exam_name'];
    
    $update = $con->prepare("UPDATE exam_category SET exam_name = ? WHERE exam_id = ?");
    $update->bind_param("si", $exam_name, $exam_id);
    
    if($update->execute()) {
        header("Location: ViewExam.php?id=$exam_id&success=1");
        exit();
    }
    $update->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Exam - Instructor</title>
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
                <h1>✏️ Edit Exam</h1>
                <p>Update exam details</p>
            </div>

            <div class="form-wrapper">
                <form method="POST">
                    <div class="form-section">
                        <h3 class="form-section-title">Exam Information</h3>
                        
                        <div class="form-group">
                            <label>Exam ID</label>
                            <input type="text" class="form-control" value="<?php echo $exam['exam_id']; ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label>Exam Name *</label>
                            <input type="text" name="exam_name" class="form-control" value="<?php echo htmlspecialchars($exam['exam_name']); ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Exam Type</label>
                                <select name="exam_type" class="form-control">
                                    <option value="Midterm">Midterm Exam</option>
                                    <option value="Final">Final Exam</option>
                                    <option value="Quiz">Quiz</option>
                                    <option value="Assignment">Assignment</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>is_active</label>
                                <select name='is_active' class="form-control">
                                    <option value="Active">Active</option>
                                    <option value="Draft">Draft</option>
                                    <option value="Archived">Archived</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Duration (minutes)</label>
                                <input type="number" name="duration" class="form-control" placeholder="60">
                            </div>
                            
                            <div class="form-group">
                                <label>Passing Score (%)</label>
                                <input type="number" name="passing_score" class="form-control" placeholder="50">
                            </div>
                            
                            <div class="form-group">
                                <label>Attempt Limit</label>
                                <input type="number" name="attempts" class="form-control" placeholder="1">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Exam description..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="checkbox" name="randomize">
                                <span>Randomize question order</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Questions Management</h3>
                        
                        <div style="background: var(--bg-light); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1rem;">
                            <p style="margin: 0 0 1rem 0;">
                                <strong>Total Questions:</strong> 
                                <?php 
                                $count = $con->query("SELECT COUNT(*) as count FROM question_page WHERE exam_id='$exam_id'")->fetch_assoc()['count'];
                                echo $count ?? 0;
                                ?>
                            </p>
                            <div style="display: flex; gap: 1rem;">
                                <a href="AddQuestion.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-primary btn-sm">
                                    ➕ Add Questions
                                </a>
                                <a href="ViewExam.php?id=<?php echo $exam_id; ?>" class="btn btn-secondary btn-sm">
                                    👁️ View All Questions
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            💾 Save Changes
                        </button>
                        <a href="ViewExam.php?id=<?php echo $exam_id; ?>" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="button" class="btn btn-danger" onclick="deleteExam()" style="margin-left: auto;">
                            🗑️ Delete Exam
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        function deleteExam() {
            if(confirm('Are you sure you want to delete this exam? This will also delete all questions in this exam.')) {
                window.location.href = 'DeleteExam.php?id=<?php echo $exam_id; ?>';
            }
        }
    </script>
</body>
</html>
<?php $con->close(); ?>
