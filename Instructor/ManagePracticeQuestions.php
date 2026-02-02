<?php
session_start();
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$instructor_id = $_SESSION['ID'];

// Get instructor's courses
$coursesQuery = $con->prepare("SELECT DISTINCT c.course_id, c.course_name, c.course_code
    FROM courses c
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ? AND ic.is_active = TRUE
    ORDER BY c.course_name");
$coursesQuery->bind_param("i", $instructor_id);
$coursesQuery->execute();
$instructorCourses = $coursesQuery->get_result();
$coursesQuery->close();

// Get practice questions for instructor's courses
$selectedCourse = isset($_GET['course_id']) ? $_GET['course_id'] : null;

$message = '';
$messageType = '';

// Handle delete
if(isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $stmt = $con->prepare("DELETE FROM practice_questions WHERE practice_question_id = ? AND created_by = ?");
    $stmt->bind_param("ii", $deleteId, $instructor_id);
    if($stmt->execute()) {
        $message = 'Practice question deleted successfully!';
        $messageType = 'success';
    }
    $stmt->close();
}

// Handle toggle active status
if(isset($_GET['toggle_id'])) {
    $toggleId = $_GET['toggle_id'];
    $stmt = $con->prepare("UPDATE practice_questions SET is_active = NOT is_active WHERE practice_question_id = ? AND created_by = ?");
    $stmt->bind_param("ii", $toggleId, $instructor_id);
    if($stmt->execute()) {
        $message = 'Practice question status updated!';
        $messageType = 'success';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Practice Questions - Instructor Dashboard</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <!-- Page Header -->
            <div class="page-header-actions">
                <div class="page-title-section">
                    <h1><span>🎯</span> Manage Practice Questions</h1>
                    <p>Create and manage practice questions for your courses</p>
                </div>
                <div class="header-actions-group">
                    <a href="AddPracticeQuestion.php" class="btn btn-primary">
                        <span>➕ Add Practice Question</span>
                    </a>
                </div>
            </div>

            <?php if($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 2rem;">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <!-- Course Filter -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3 class="card-title">📚 Filter by Course</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <form method="GET" style="display: flex; gap: 1rem; align-items: end;">
                        <div class="form-group" style="flex: 1; margin: 0;">
                            <label>Select Course</label>
                            <select name="course_id" class="form-control" onchange="this.form.submit()">
                                <option value="">-- All My Courses --</option>
                                <?php 
                                $instructorCourses->data_seek(0);
                                while($course = $instructorCourses->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $course['course_id']; ?>" <?php echo $selectedCourse == $course['course_id'] ? 'selected' : ''; ?>>
                                    <?php echo $course['course_name']; ?> (<?php echo $course['course_code']; ?>)
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <?php if($selectedCourse): ?>
                        <a href="ManagePracticeQuestions.php" class="btn btn-secondary">Clear Filter</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Practice Questions List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📝 Practice Questions</h3>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Course</th>
                                <th>Question</th>
                                <th>Difficulty</th>
                                <th>Topic</th>
                                <th>Points</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT pq.*, c.course_name, c.course_code
                                    FROM practice_questions pq
                                    INNER JOIN courses c ON pq.course_id = c.course_id
                                    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
                                    WHERE ic.instructor_id = ? AND ic.is_active = TRUE";
                            
                            if($selectedCourse) {
                                $sql .= " AND pq.course_id = ?";
                            }
                            
                            $sql .= " ORDER BY pq.created_at DESC";
                            
                            $stmt = $con->prepare($sql);
                            if($selectedCourse) {
                                $stmt->bind_param("ii", $instructor_id, $selectedCourse);
                            } else {
                                $stmt->bind_param("i", $instructor_id);
                            }
                            $stmt->execute();
                            $questions = $stmt->get_result();
                            
                            if($questions->num_rows > 0):
                                while($q = $questions->fetch_assoc()):
                            ?>
                            <tr>
                                <td><strong>#<?php echo $q['practice_question_id']; ?></strong></td>
                                <td>
                                    <div style="font-weight: 600;"><?php echo $q['course_name']; ?></div>
                                    <div style="font-size: 0.85rem; color: #6c757d;"><?php echo $q['course_code']; ?></div>
                                </td>
                                <td style="max-width: 300px;">
                                    <?php echo substr(strip_tags($q['question_text']), 0, 100); ?>
                                    <?php if(strlen($q['question_text']) > 100) echo '...'; ?>
                                </td>
                                <td>
                                    <span class="badge <?php 
                                        echo $q['difficulty_level'] == 'easy' ? 'badge-success' : 
                                            ($q['difficulty_level'] == 'medium' ? 'badge-warning' : 'badge-danger'); 
                                    ?>">
                                        <?php echo ucfirst($q['difficulty_level']); ?>
                                    </span>
                                </td>
                                <td><?php echo $q['topic'] ?: 'General'; ?></td>
                                <td><strong><?php echo $q['points']; ?></strong></td>
                                <td>
                                    <?php if($q['is_active']): ?>
                                    <span class="status-badge status-active">Active</span>
                                    <?php else: ?>
                                    <span class="status-badge status-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.85rem; color: #6c757d;">
                                    <?php echo date('M d, Y', strtotime($q['created_at'])); ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="EditPracticeQuestion.php?id=<?php echo $q['practice_question_id']; ?>" 
                                           class="btn btn-sm btn-primary" title="Edit">
                                            ✏️
                                        </a>
                                        <a href="?toggle_id=<?php echo $q['practice_question_id']; ?><?php echo $selectedCourse ? '&course_id='.$selectedCourse : ''; ?>" 
                                           class="btn btn-sm btn-warning" 
                                           title="<?php echo $q['is_active'] ? 'Deactivate' : 'Activate'; ?>"
                                           onclick="return confirm('Toggle status?')">
                                            <?php echo $q['is_active'] ? '👁️' : '🔒'; ?>
                                        </a>
                                        <a href="?delete_id=<?php echo $q['practice_question_id']; ?><?php echo $selectedCourse ? '&course_id='.$selectedCourse : ''; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this practice question?')">
                                            🗑️
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 3rem; color: #6c757d;">
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                                    <h3>No Practice Questions Yet</h3>
                                    <p>Create your first practice question to help students learn!</p>
                                    <a href="AddPracticeQuestion.php" class="btn btn-primary" style="margin-top: 1rem;">
                                        ➕ Add Practice Question
                                    </a>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3 class="card-title">ℹ️ About Practice Questions</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <ul style="line-height: 2;">
                        <li><strong>Purpose:</strong> Practice questions help students learn without the pressure of exams</li>
                        <li><strong>No Approval:</strong> Practice questions don't require approval - they're available immediately</li>
                        <li><strong>Unlimited Attempts:</strong> Students can practice as many times as they want</li>
                        <li><strong>Separate from Exams:</strong> Practice questions are completely separate from exam questions</li>
                        <li><strong>Add Explanations:</strong> Include explanations to help students understand the correct answers</li>
                        <li><strong>Set Difficulty:</strong> Mark questions as Easy, Medium, or Hard to help students progress</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>
