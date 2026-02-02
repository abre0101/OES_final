<?php
session_start();
require_once('../Connections/config.php');

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: ../student-login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $exam_id = mysqli_real_escape_string($conn, $_POST['exam_id']);
    $issue_description = mysqli_real_escape_string($conn, $_POST['issue_description']);
    
    if (!empty($exam_id) && !empty($issue_description)) {
        $sql = "INSERT INTO technical_issues (student_id, exam_id, issue_description, status, reported_at) 
                VALUES ('$student_id', '$exam_id', '$issue_description', 'pending', NOW())";
        
        if (mysqli_query($conn, $sql)) {
            $success_message = "Issue reported successfully. Our team will look into it.";
        } else {
            $error_message = "Error reporting issue. Please try again.";
        }
    } else {
        $error_message = "Please fill in all fields.";
    }
}

// Get student's active exams
$exams_query = "SELECT DISTINCT e.exam_id, e.exam_name, c.course_name 
                FROM exams e
                INNER JOIN courses c ON e.course_id = c.course_id
                INNER JOIN student_courses sc ON c.course_id = sc.course_id
                WHERE sc.student_id = '$student_id' AND e.is_active = 1
                ORDER BY e.exam_date DESC, e.start_time DESC";
$exams_result = mysqli_query($conn, $exams_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Technical Issue</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .report-container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .form-title {
            color: #667eea;
            margin-bottom: 30px;
            text-align: center;
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 10px 30px;
            width: 100%;
        }
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 10px 30px;
            width: 100%;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="report-container">
        <h2 class="form-title">Report Technical Issue</h2>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="exam_id">Select Exam</label>
                <select class="form-control" id="exam_id" name="exam_id" required>
                    <option value="">-- Select Exam --</option>
                    <?php while ($exam = mysqli_fetch_assoc($exams_result)): ?>
                        <option value="<?php echo $exam['exam_id']; ?>">
                            <?php echo htmlspecialchars($exam['course_name'] . ' - ' . $exam['exam_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="issue_description">Describe the Issue</label>
                <textarea class="form-control" id="issue_description" name="issue_description" 
                          rows="6" required placeholder="Please describe the technical issue you're experiencing..."></textarea>
            </div>
            
            <button type="submit" class="btn btn-submit">Submit Issue Report</button>
            <a href="index.php" class="btn btn-back">Back to Dashboard</a>
        </form>
    </div>
</body>
</html>
