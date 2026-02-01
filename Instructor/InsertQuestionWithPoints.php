<?php
session_start();
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;

// Add point_value column if it doesn't exist
$con->query("ALTER TABLE question_page ADD COLUMN IF NOT EXISTS point_value INT DEFAULT 1 AFTER Answer");
$con->query("ALTER TABLE truefalse_question ADD COLUMN IF NOT EXISTS point_value INT DEFAULT 1 AFTER Answer");

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $questionType = $_POST['question_type'] ?? 'mcq'; // mcq or truefalse
    $examId = $_POST['exam_id'];
    $courseName = $_POST['course_name'];
    $semester = $_POST['semester'];
    $question = $_POST['question'];
    $pointValue = isset($_POST['point_value']) ? intval($_POST['point_value']) : 1;
    
    if($questionType == 'mcq') {
        // Multiple Choice Question
        $option1 = $_POST['option1'];
        $option2 = $_POST['option2'];
        $option3 = $_POST['option3'];
        $option4 = $_POST['option4'];
        $answer = $_POST['answer'];
        
        $stmt = $con->prepare("INSERT INTO question_page (exam_id, course_name, semester, Question, Option1, Option2, Option3, Option4, Answer, point_value, approval_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("isssssssi", $examId, $courseName, $semester, $question, $option1, $option2, $option3, $option4, $answer, $pointValue);
        
    } else {
        // True/False Question
        $option1 = $_POST['option1']; // True
        $option2 = $_POST['option2']; // False
        $answer = $_POST['answer'];
        
        $stmt = $con->prepare("INSERT INTO truefalse_question (exam_id, course_name, semester, question, Answer1, Answer2, Answer, point_value) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isisssi", $examId, $courseName, $semester, $question, $option1, $option2, $answer, $pointValue);
    }
    
    if($stmt->execute()) {
        echo '<script>alert("Question added successfully with ' . $pointValue . ' points!"); window.location="ManageQuestions.php";</script>';
    } else {
        echo '<script>alert("Error: ' . $stmt->error . '"); window.history.back();</script>';
    }
}

$con->close();
?>
