<?php
require_once(__DIR__ . "/../utils/session_manager.php");
SessionManager::startSession('Student');

if(!isset($_SESSION['Name']) || !isset($_SESSION['ID'])){
    header("Location: ../index.php");
    exit();
}

if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Student'){
    SessionManager::destroySession();
    header("Location: ../auth/student-login.php");
    exit();
}

// Get POST data
$correct = isset($_POST['correct']) ? intval($_POST['correct']) : 0;
$wrong = isset($_POST['wrong']) ? intval($_POST['wrong']) : 0;
$total = isset($_POST['total']) ? intval($_POST['total']) : 0;
$score = isset($_POST['score']) ? intval($_POST['score']) : 0;
$answers = isset($_POST['answers']) ? $_POST['answers'] : '{}';
$tab_switches = isset($_POST['tab_switches']) ? intval($_POST['tab_switches']) : 0;
$exam_id = isset($_POST['exam_id']) ? intval($_POST['exam_id']) : (isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0);

$studentID = $_SESSION['ID'];

// Connect to database
$con = require_once(__DIR__ . "/../Connections/OES.php");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get exam schedule information
$schedule_query = "SELECT es.*, c.course_name, c.course_code, ec.category_name
    FROM exams es
    LEFT JOIN courses c ON es.course_id = c.course_id
    LEFT JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    WHERE es.exam_id = ?";
$stmt = $con->prepare($schedule_query);
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exam) {
    die("Exam not found");
}

// Calculate unanswered questions
$unanswered = $total - ($correct + $wrong);

// Calculate percentage and grade
$total_points_earned = $correct * 10; // Assuming 10 points per correct answer
$total_points_possible = $total * 10;
$percentage_score = ($total_points_possible > 0) ? ($total_points_earned / $total_points_possible) * 100 : 0;

// Determine pass/fail status
$pass_status = ($percentage_score >= 50) ? 'Pass' : 'Fail';

// The session ID already contains the student_id from the students table
// No need to look it up - it's set during login
$student_db_id = intval($studentID);

// Verify student exists (optional safety check)
$verify_query = "SELECT student_id FROM students WHERE student_id = ?";
$stmt = $con->prepare($verify_query);
$stmt->bind_param("i", $student_db_id);
$stmt->execute();
$verify_result = $stmt->get_result();
$stmt->close();

if ($verify_result->num_rows == 0) {
    die("Student record not found. Please log in again.");
}

// Check if student has already taken this exam
$check_query = "SELECT result_id, percentage_score, pass_status FROM exam_results 
    WHERE student_id = ? AND exam_id = ?";
$check_stmt = $con->prepare($check_query);
$check_stmt->bind_param("ii", $student_db_id, $exam_id);
$check_stmt->execute();
$existing_result = $check_stmt->get_result()->fetch_assoc();
$check_stmt->close();

if ($existing_result) {
    // Student has already taken this exam
    $_SESSION['exam_error'] = [
        'title' => 'Exam Already Taken',
        'message' => 'You have already completed this exam.',
        'previous_score' => $existing_result['percentage_score'],
        'previous_status' => $existing_result['pass_status'],
        'result_id' => $existing_result['result_id']
    ];
    
    mysqli_close($con);
    header("Location: exam-result.php?id=" . $existing_result['result_id'] . "&already_taken=1");
    exit();
}

// Insert result into exam_results table (only marks and percentage - no letter grade)
$insert_query = "INSERT INTO exam_results (
    student_id, 
    exam_id, 
    total_questions, 
    correct_answers, 
    wrong_answers, 
    unanswered,
    total_points_earned,
    total_points_possible,
    percentage_score,
    pass_status,
    exam_started_at,
    exam_submitted_at,
    time_taken_minutes
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?)";

$time_taken = $exam['duration_minutes'] ?? 30; // Default to exam duration

$stmt = $con->prepare($insert_query);
$stmt->bind_param("iiiiiiddssi", 
    $student_db_id, 
    $exam_id, 
    $total, 
    $correct, 
    $wrong, 
    $unanswered,
    $total_points_earned,
    $total_points_possible,
    $percentage_score,
    $pass_status,
    $time_taken
);

if ($stmt->execute()) {
    $resultId = $stmt->insert_id;
    $stmt->close();
    
    // Save individual answers for review
    $answersArray = json_decode($answers, true);
    if($answersArray && is_array($answersArray)) {
        // Get questions for this exam
        $questions_query = "SELECT q.question_id, q.correct_answer, q.point_value, eq.question_order
            FROM exam_questions eq
            INNER JOIN questions q ON eq.question_id = q.question_id
            WHERE eq.exam_id = ?
            ORDER BY eq.question_order";
        $stmt = $con->prepare($questions_query);
        $stmt->bind_param("i", $exam_id);
        $stmt->execute();
        $questions_result = $stmt->get_result();
        
        $questions_map = [];
        while ($q = $questions_result->fetch_assoc()) {
            $questions_map[$q['question_order']] = [
                'question_id' => $q['question_id'],
                'correct_answer' => $q['correct_answer'],
                'point_value' => $q['point_value'] ?? 10
            ];
        }
        $stmt->close();
        
        // Insert answers into student_answers table
        // Table structure: answer_id, result_id, question_id, selected_answer, is_correct, points_earned, answered_at
        $answerStmt = $con->prepare("INSERT INTO student_answers (result_id, question_id, selected_answer, is_correct, points_earned) VALUES (?, ?, ?, ?, ?)");
        
        foreach($answersArray as $questionIndex => $selectedAnswer) {
            // JavaScript sends 0-based index, but question_order is 1-based
            $questionOrder = $questionIndex + 1;
            
            if (isset($questions_map[$questionOrder])) {
                $question_id = $questions_map[$questionOrder]['question_id'];
                $correct_answer = $questions_map[$questionOrder]['correct_answer'];
                $point_value = $questions_map[$questionOrder]['point_value'];
                $isCorrect = ($correct_answer == $selectedAnswer) ? 1 : 0;
                $points_earned = $isCorrect ? $point_value : 0;
                
                $answerStmt->bind_param("iisid", $resultId, $question_id, $selectedAnswer, $isCorrect, $points_earned);
                $answerStmt->execute();
            }
        }
        
        $answerStmt->close();
    }
    
    mysqli_close($con);
    
    // Store result data in session for display
    $_SESSION['last_exam_result'] = [
        'result_id' => $resultId,
        'exam_name' => $exam['exam_name'],
        'course_name' => $exam['course_name'],
        'course_code' => $exam['course_code'],
        'correct' => $correct,
        'wrong' => $wrong,
        'unanswered' => $unanswered,
        'total' => $total,
        'score' => $total_points_earned,
        'percentage' => $percentage_score,
        'letter_grade' => $letter_grade,
        'gpa' => $gpa,
        'pass_status' => $pass_status,
        'tab_switches' => $tab_switches
    ];
    
    // Redirect to result page
    header("Location: exam-result.php?id=" . $resultId);
    exit();
} else {
    // Store error and data in session
    $_SESSION['last_exam_result'] = [
        'exam_name' => $exam['exam_name'],
        'course_name' => $exam['course_name'],
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
