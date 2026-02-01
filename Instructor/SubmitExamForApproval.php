<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$instructor_id = $_SESSION['ID'];
$schedule_id = isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : 0;

if($schedule_id == 0) {
    $_SESSION['error'] = "Invalid exam schedule";
    header("Location: ManageSchedules.php");
    exit();
}

// Verify instructor owns this exam
$verifyQuery = $con->prepare("SELECT es.schedule_id, es.exam_name, es.approval_status
    FROM exam_schedules es
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE es.schedule_id = ? AND ic.instructor_id = ? AND ic.is_active = TRUE");
$verifyQuery->bind_param("ii", $schedule_id, $instructor_id);
$verifyQuery->execute();
$result = $verifyQuery->get_result();

if($result->num_rows == 0) {
    $_SESSION['error'] = "You don't have permission to submit this exam";
    header("Location: ManageSchedules.php");
    exit();
}

$exam = $result->fetch_assoc();

// Check if exam is in draft status
if($exam['approval_status'] != 'draft') {
    $_SESSION['error'] = "This exam has already been submitted for approval";
    header("Location: ViewExam.php?id=" . $schedule_id);
    exit();
}

// Check minimum question requirement (at least 5 questions)
$questionCountQuery = $con->prepare("SELECT COUNT(*) as question_count FROM exam_questions WHERE schedule_id = ?");
$questionCountQuery->bind_param("i", $schedule_id);
$questionCountQuery->execute();
$questionCountResult = $questionCountQuery->get_result()->fetch_assoc();
$questionCount = $questionCountResult['question_count'];

if($questionCount < 5) {
    $_SESSION['error'] = "Cannot submit exam for approval. Minimum 5 questions required to ensure exam validity. Current questions: " . $questionCount;
    header("Location: ViewExam.php?id=" . $schedule_id);
    exit();
}

// Update status to pending
$updateQuery = $con->prepare("UPDATE exam_schedules 
    SET approval_status = 'pending', submitted_for_approval = TRUE, submitted_at = NOW() 
    WHERE schedule_id = ?");
$updateQuery->bind_param("i", $schedule_id);

if($updateQuery->execute()) {
    // Note: Notifications for exam committee will be handled by their dashboard
    // They will see pending exams when they log in
    
    $_SESSION['success'] = "Exam submitted for approval successfully! The Exam Committee will review it.";
} else {
    $_SESSION['error'] = "Failed to submit exam for approval";
}

$con->close();
header("Location: ViewExam.php?id=" . $schedule_id);
exit();
?>
