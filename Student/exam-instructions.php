<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location: ../index.php");
    exit();
}

// Get schedule information
$scheduleId = isset($_GET['exam_id']) ? $_GET['exam_id'] : null;
$examInfo = null;

if ($scheduleId) {
    $con = require_once(__DIR__ . "/../Connections/OES.php"); $con;
    $stmt = $con->prepare("SELECT es.*, ec.category_name as exam_type_name, c.course_name, c.course_code
                           FROM exams es 
                           LEFT JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id 
                           LEFT JOIN courses c ON es.course_id = c.course_id
                           WHERE es.exam_id = ?");
    $stmt->bind_param("i", $scheduleId);
    $stmt->execute();
    $result = $stmt->get_result();
    $examInfo = $result->fetch_assoc();
    $stmt->close();
    mysqli_close($con);
}

if (!$examInfo) {
    header("Location: StartExam.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Instructions - Debre Markos University Health Campus</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/exam-modern.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="exam-instructions-container">
        <div class="instructions-card">
            <div class="instructions-header">
                <img src="../images/logo1.png" alt="Logo" class="instructions-logo" onerror="this.style.display='none'">
                <h1>📋 Examination Instructions</h1>
                <p><?php echo htmlspecialchars($examInfo['exam_name']); ?></p>
                <p style="font-size: 0.9rem; opacity: 0.8;">
                    <?php echo htmlspecialchars($examInfo['course_code']); ?> - <?php echo htmlspecialchars($examInfo['course_name']); ?> | 
                    <?php echo htmlspecialchars($examInfo['exam_type_name']); ?>
                </p>
                <p style="font-size: 0.9rem; opacity: 0.8;">
                    Date: <?php echo date('M d, Y', strtotime($examInfo['exam_date'])); ?> | 
                    Duration: <?php echo $examInfo['duration_minutes']; ?> minutes
                </p>
            </div>

            <div class="instructions-body">
                <div class="alert alert-warning">
                    <strong>⚠️ Important:</strong> Once you start the exam, you cannot pause or exit. Make sure you're ready!
                </div>

                <h3>📝 General Instructions:</h3>
                <ul class="instructions-list">
                    <li>⏱️ The exam will start immediately after you click "Agree and Proceed"</li>
                    <li>⏰ You will have a fixed time limit to complete the exam</li>
                    <li>🔒 The exam will auto-submit when time expires</li>
                    <li>✅ Each question has only one correct answer</li>
                    <li>🔄 You can navigate between questions using the question panel</li>
                    <li>🟢 Answered questions will be marked in green</li>
                    <li>⚪ Skipped questions will remain white</li>
                </ul>

                <h3>📊 Marking Scheme:</h3>
                <div class="marking-scheme">
                    <div class="marking-item positive">
                        <span class="marking-icon">✅</span>
                        <div>
                            <strong>Correct Answer</strong>
                            <p>+2 Marks</p>
                        </div>
                    </div>
                    <div class="marking-item negative">
                        <span class="marking-icon">❌</span>
                        <div>
                            <strong>Wrong Answer</strong>
                            <p>-1 Mark</p>
                        </div>
                    </div>
                    <div class="marking-item neutral">
                        <span class="marking-icon">⚪</span>
                        <div>
                            <strong>Unanswered</strong>
                            <p>0 Marks</p>
                        </div>
                    </div>
                </div>

                <h3>⚠️ Important Rules:</h3>
                <ul class="instructions-list">
                    <li>🚫 Do not refresh the page during the exam</li>
                    <li>🚫 Do not close the browser window</li>
                    <li>🚫 Do not use back button</li>
                    <li>📶 Ensure stable internet connection</li>
                    <li>📤 Submit your exam before time runs out</li>
                </ul>

                <div class="student-info">
                    <h3>👤 Student Information:</h3>
                    <p><strong>Name:</strong> <?php echo $_SESSION['Name']; ?></p>
                    <p><strong>ID:</strong> <?php echo $_SESSION['ID']; ?></p>
                    <p><strong>Department:</strong> <?php echo $_SESSION['Dept']; ?></p>
                </div>

                <div class="agreement-section">
                    <label class="checkbox-container">
                        <input type="checkbox" id="agreeCheckbox">
                        <span class="checkmark"></span>
                        <span class="checkbox-label">I have read and understood all the instructions</span>
                    </label>
                </div>

                <div class="instructions-actions">
                    <a href="StartExam.php" class="btn btn-secondary">Cancel</a>
                    <button id="proceedBtn" class="btn btn-success" disabled onclick="startExam()">
                        Agree and Proceed to Exam ➡️
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const agreeCheckbox = document.getElementById('agreeCheckbox');
        const proceedBtn = document.getElementById('proceedBtn');

        agreeCheckbox.addEventListener('change', function() {
            proceedBtn.disabled = !this.checked;
        });

        function startExam() {
            // Redirect to exam interface with exam_id
            window.location.href = 'exam-interface.php?exam_id=<?php echo $scheduleId; ?>';
        }
    </script>
</body>
</html>
