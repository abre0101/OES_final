<?php
/**
 * Auto-update exam total marks based on questions
 * This should be called whenever questions are added/removed/updated in an exam
 */

function updateExamTotalMarks($con, $exam_id) {
    // Calculate total marks from all questions in this exam
    $query = $con->prepare("SELECT SUM(q.point_value) as total_marks
        FROM exam_questions eq
        INNER JOIN questions q ON eq.question_id = q.question_id
        WHERE eq.exam_id = ?");
    $query->bind_param("i", $exam_id);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    
    $total_marks = $result['total_marks'] ?? 0;
    
    // Update the exam schedule with calculated total marks
    $updateQuery = $con->prepare("UPDATE exams 
        SET total_marks = ? 
        WHERE exam_id = ?");
    $updateQuery->bind_param("ii", $total_marks, $exam_id);
    $updateQuery->execute();
    
    return $total_marks;
}

// If called directly (via AJAX or redirect)
if(isset($_GET['exam_id'])) {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if(!isset($_SESSION['Name'])){
        http_response_code(403);
        exit();
    }
    
    $con = require_once(__DIR__ . "/../Connections/OES.php");
    $exam_id = intval($_GET['exam_id']);
    
    $total_marks = updateExamTotalMarks($con, $exam_id);
    
    if(isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'total_marks' => $total_marks]);
    } else {
        $_SESSION['success'] = "Total marks updated to $total_marks based on questions";
        header("Location: ViewExam.php?id=" . $exam_id);
    }
    exit();
}
?>
