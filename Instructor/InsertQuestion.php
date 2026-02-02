<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

// Validate POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method');
}

$con = require_once(__DIR__ . "/../Connections/OES.php");

// Get form data
$instructor_id = $_POST['instructor_id'] ?? $_SESSION['ID'];
$course_id = $_POST['course_id'] ?? null;
$exam_id = $_POST['exam_id'] ?? null;
$topic_id = !empty($_POST['topic_id']) ? $_POST['topic_id'] : null;
$question_text = $_POST['question_text'] ?? '';
$option_a = $_POST['option_a'] ?? '';
$option_b = $_POST['option_b'] ?? '';
$option_c = !empty($_POST['option_c']) ? $_POST['option_c'] : null;
$option_d = !empty($_POST['option_d']) ? $_POST['option_d'] : null;
$correct_answer = $_POST['correct_answer'] ?? '';
$difficulty_level = $_POST['difficulty_level'] ?? 'Medium';
$point_value = $_POST['point_value'] ?? 1;
$save_and_add_another = isset($_POST['save_and_add_another']);

// Validate required fields
if(empty($course_id) || empty($question_text) || empty($option_a) || empty($option_b) || empty($correct_answer)) {
    echo '<script type="text/javascript">alert("Please fill all required fields");window.history.back();</script>';
    exit();
}

// If exam_id is provided, check if exam is locked
if($exam_id) {
    $checkExam = $con->prepare("SELECT approval_status FROM exams WHERE exam_id = ?");
    $checkExam->bind_param("i", $exam_id);
    $checkExam->execute();
    $examStatus = $checkExam->get_result()->fetch_assoc();
    $checkExam->close();
    
    if($examStatus && $examStatus['approval_status'] != 'draft' && $examStatus['approval_status'] != 'revision') {
        echo '<script type="text/javascript">
            alert("Cannot add questions to an exam that has been submitted for approval.");
            window.location="ViewExam.php?id=' . $exam_id . '";
        </script>';
        exit();
    }
}

// Start transaction
$con->begin_transaction();

try {
    // Insert question into questions table
    $insertQuestion = $con->prepare("INSERT INTO questions 
        (course_id, instructor_id, topic_id, question_text, option_a, option_b, option_c, option_d, 
         correct_answer, difficulty_level, point_value, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    $insertQuestion->bind_param("iiisssssssi", 
        $course_id, $instructor_id, $topic_id, $question_text, 
        $option_a, $option_b, $option_c, $option_d, 
        $correct_answer, $difficulty_level, $point_value
    );
    
    if(!$insertQuestion->execute()) {
        throw new Exception("Failed to insert question: " . $insertQuestion->error);
    }
    
    $question_id = $con->insert_id;
    $insertQuestion->close();
    
    // If exam_id is provided, link question to exam
    if($exam_id) {
        // Get the current max question order for this exam
        $orderQuery = $con->prepare("SELECT COALESCE(MAX(question_order), 0) + 1 as next_order 
            FROM exam_questions WHERE exam_id = ?");
        $orderQuery->bind_param("i", $exam_id);
        $orderQuery->execute();
        $orderResult = $orderQuery->get_result();
        $next_order = $orderResult->fetch_assoc()['next_order'];
        $orderQuery->close();
        
        // Insert into exam_questions
        $insertExamQuestion = $con->prepare("INSERT INTO exam_questions 
            (exam_id, question_id, question_order) 
            VALUES (?, ?, ?)");
        $insertExamQuestion->bind_param("iii", $exam_id, $question_id, $next_order);
        
        if(!$insertExamQuestion->execute()) {
            throw new Exception("Failed to link question to exam: " . $insertExamQuestion->error);
        }
        $insertExamQuestion->close();
    }
    
    // Commit transaction
    $con->commit();
    
    // Success message and redirect
    if($save_and_add_another) {
        $redirect = $exam_id ? "AddQuestion.php?exam_id=$exam_id" : "AddQuestion.php";
        echo '<script type="text/javascript">
            alert("Question added successfully! Add another question.");
            window.location="' . $redirect . '";
        </script>';
    } else {
        echo '<script type="text/javascript">
            alert("Question added successfully!");
            window.location="ManageQuestions.php";
        </script>';
    }
    
} catch (Exception $e) {
    // Rollback on error
    $con->rollback();
    echo '<script type="text/javascript">
        alert("Error: ' . addslashes($e->getMessage()) . '");
        window.history.back();
    </script>';
}

$con->close();
?>
