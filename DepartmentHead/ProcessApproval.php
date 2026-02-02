<?php
session_start();
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");

// Get parameters
$schedule_id = $_POST['schedule_id'] ?? $_GET['schedule_id'] ?? 0;
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$comments = $_POST['comments'] ?? $_GET['comments'] ?? '';

if(!$schedule_id || !$action) {
    $_SESSION['error'] = "Invalid request parameters.";
    header("Location: PendingApprovals.php");
    exit();
}

// Get exam details - exclude draft exams
$exam = $con->query("SELECT es.*, c.course_name
    FROM exam_schedules es
    INNER JOIN courses c ON es.course_id = c.course_id
    WHERE es.schedule_id = $schedule_id AND es.approval_status != 'draft'
    LIMIT 1")->fetch_assoc();

if(!$exam) {
    $_SESSION['error'] = "Exam not found or not available for review.";
    header("Location: PendingApprovals.php");
    exit();
}

$reviewer_name = $_SESSION['Name'];
$reviewer_id = $_SESSION['ID'] ?? 1; // Default to 1 if no ID in session
$review_date = date('Y-m-d H:i:s');

try {
    switch($action) {
        case 'approve':
            // Update exam status to approved
            $stmt = $con->prepare("UPDATE exam_schedules SET 
                approval_status = 'approved',
                approved_by = ?,
                approval_date = ?,
                reviewer_comments = ?
                WHERE schedule_id = ?");
            $stmt->bind_param("sssi", $reviewer_name, $review_date, $comments, $schedule_id);
            
            if($stmt->execute()) {
                // Log approval in history
                $log_stmt = $con->prepare("INSERT INTO exam_approval_history 
                    (schedule_id, action, performed_by, performed_by_type, comments, previous_status, new_status, created_at) 
                    VALUES (?, 'approved', ?, 'committee', ?, ?, 'approved', ?)");
                $prev_status = $exam['approval_status'];
                $log_stmt->bind_param("iisss", $schedule_id, $reviewer_id, $comments, $prev_status, $review_date);
                $log_stmt->execute();
                
                $_SESSION['success'] = "✓ Exam approved successfully!";
            } else {
                $_SESSION['error'] = "Failed to approve exam: " . $con->error;
            }
            break;
            
        case 'revision':
            if(empty($comments)) {
                $_SESSION['error'] = "Please provide revision comments.";
                header("Location: PendingApprovals.php");
                exit();
            }
            
            // Update exam status to revision
            $revision_count = $exam['revision_count'] + 1;
            $stmt = $con->prepare("UPDATE exam_schedules SET 
                approval_status = 'revision',
                reviewer_comments = ?,
                reviewed_by = ?,
                reviewed_at = ?,
                revision_count = ?
                WHERE schedule_id = ?");
            $stmt->bind_param("sssii", $comments, $reviewer_name, $review_date, $revision_count, $schedule_id);
            
            if($stmt->execute()) {
                // Log revision request in history
                $log_stmt = $con->prepare("INSERT INTO exam_approval_history 
                    (schedule_id, action, performed_by, performed_by_type, comments, previous_status, new_status, created_at) 
                    VALUES (?, 'revision_requested', ?, 'committee', ?, ?, 'revision', ?)");
                $prev_status = $exam['approval_status'];
                $log_stmt->bind_param("iisss", $schedule_id, $reviewer_id, $comments, $prev_status, $review_date);
                $log_stmt->execute();
                
                $_SESSION['success'] = "✏️ Revision request sent to instructor.";
            } else {
                $_SESSION['error'] = "Failed to request revision: " . $con->error;
            }
            break;
            
        case 'reject':
            if(empty($comments)) {
                $_SESSION['error'] = "Please provide a reason for rejection.";
                header("Location: PendingApprovals.php");
                exit();
            }
            
            // Update exam status to rejected
            $stmt = $con->prepare("UPDATE exam_schedules SET 
                approval_status = 'rejected',
                reviewer_comments = ?,
                reviewed_by = ?,
                reviewed_at = ?
                WHERE schedule_id = ?");
            $stmt->bind_param("sssi", $comments, $reviewer_name, $review_date, $schedule_id);
            
            if($stmt->execute()) {
                // Log rejection in history
                $log_stmt = $con->prepare("INSERT INTO exam_approval_history 
                    (schedule_id, action, performed_by, performed_by_type, comments, previous_status, new_status, created_at) 
                    VALUES (?, 'rejected', ?, 'committee', ?, ?, 'rejected', ?)");
                $prev_status = $exam['approval_status'];
                $log_stmt->bind_param("iisss", $schedule_id, $reviewer_id, $comments, $prev_status, $review_date);
                $log_stmt->execute();
                
                $_SESSION['success'] = "✗ Exam rejected.";
            } else {
                $_SESSION['error'] = "Failed to reject exam: " . $con->error;
            }
            break;
            
        default:
            $_SESSION['error'] = "Invalid action.";
    }
} catch(Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

$con->close();
header("Location: PendingApprovals.php");
exit();
?>
