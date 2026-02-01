<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name']) || !isset($_SESSION['ID'])){
    header("Location: ../index.php");
    exit();
}

// Get POST data
$correct = isset($_POST['correct']) ? intval($_POST['correct']) : 0;
$wrong = isset($_POST['wrong']) ? intval($_POST['wrong']) : 0;
$total = isset($_POST['total']) ? intval($_POST['total']) : 0;
$score = isset($_POST['score']) ? intval($_POST['score']) : 0;
$answers = isset($_POST['answers']) ? $_POST['answers'] : '{}';

$studentID = $_SESSION['ID'];

// Connect to database
$con = require_once(__DIR__ . "/../Connections/OES.php"); $con;

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get exam and course information from the questions taken
$sql = "SELECT q.exam_id, q.course_name, c.course_id 
        FROM question_page q
        LEFT JOIN courses c ON TRIM(q.course_name) = TRIM(c.course_name)
        LIMIT 1";
$result = mysqli_query($con, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $examId = $row['exam_id'] ? $row['exam_id'] : 1;
    
    // Ensure we use course_id, not course_name
    if (!empty($row['course_id'])) {
        $courseId = $row['course_id'];
    } else {
        // Fallback: try to find course_id by name
        $courseName = $row['course_name'];
        $courseQuery = mysqli_query($con, "SELECT course_id FROM courses WHERE course_name LIKE '%{$courseName}%' LIMIT 1");
        if ($courseQuery && mysqli_num_rows($courseQuery) > 0) {
            $courseRow = mysqli_fetch_assoc($courseQuery);
            $courseId = $courseRow['course_id'];
        } else {
            $courseId = 'CS101'; // Ultimate fallback
        }
    }
    $courseName = $row['course_name'];
} else {
    // Default values if no questions found
    $examId = 1;
    $courseId = 'CS101';
    $courseName = 'General Exam';
}

// Insert result into database
$stmt = $con->prepare("INSERT INTO exam_results (exam_id, course_id, student_id, Total, Correct, Wrong, Result) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issiiii", $examId, $courseId, $studentID, $total, $correct, $wrong, $score);


if ($stmt->execute()) {
    $resultId = $stmt->insert_id;
    $stmt->close();
    
    // Save individual answers for review
    $answersArray = json_decode($answers, true);
    if($answersArray && is_array($answersArray)) {
        // First, create the table if it doesn't exist
        $createTableSQL = "CREATE TABLE IF NOT EXISTS `student_answers` (
            `answer_id` INT AUTO_INCREMENT PRIMARY KEY,
            `result_id` INT NOT NULL,
            `student_id` VARCHAR(50) NOT NULL,
            `question_id` INT NOT NULL,
            `selected_answer` CHAR(1),
            `is_correct` TINYINT(1) DEFAULT 0,
            `answered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_result` (`result_id`),
            INDEX `idx_student` (`student_id`),
            INDEX `idx_question` (`question_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        mysqli_query($con, $createTableSQL);
        
        // Prepare statement for inserting answers
        $answerStmt = $con->prepare("INSERT INTO student_answers (result_id, student_id, question_id, selected_answer, is_correct) VALUES (?, ?, ?, ?, ?)");
        
        foreach($answersArray as $questionId => $selectedAnswer) {
            // Get correct answer for this question
            $correctQuery = $con->prepare("SELECT Answer FROM question_page WHERE question_id = ?");
            $correctQuery->bind_param("i", $questionId);
            $correctQuery->execute();
            $correctResult = $correctQuery->get_result();
            $correctData = $correctResult->fetch_assoc();
            $correctQuery->close();
            
            $isCorrect = ($correctData && $correctData['Answer'] == $selectedAnswer) ? 1 : 0;
            
            // Insert answer
            $answerStmt->bind_param("isisi", $resultId, $studentID, $questionId, $selectedAnswer, $isCorrect);
            $answerStmt->execute();
        }
        
        $answerStmt->close();
    }
    
    mysqli_close($con);
    
    // Store result data in session for display
    $_SESSION['last_exam_result'] = [
        'exam_name' => $examId,
        'course_name' => $courseName,
        'correct' => $correct,
        'wrong' => $wrong,
        'total' => $total,
        'score' => $score
    ];
    
    // Redirect to result page
    if ($resultId > 0) {
        header("Location: exam-result.php?id=" . $resultId);
    } else {
        header("Location: exam-result.php");
    }
    exit();
} else {
    // Store error and data in session
    $_SESSION['last_exam_result'] = [
        'exam_name' => $examId,
        'course_name' => $courseName,
        'correct' => $correct,
        'wrong' => $wrong,
        'total' => $total,
        'score' => $score,
        'error' => mysqli_error($con)
    ];
    
    $stmt->close();
    mysqli_close($con);
    
    // Show results even if insert fails
    header("Location: exam-result.php");
    exit();
}
?>
