<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Department Head session
SessionManager::startSession('DepartmentHead');

// Check if user is logged in
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

// Validate user role
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'DepartmentHead'){
    SessionManager::destroySession();
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");

// Get parameters
$exam_id = $_POST['exam_id'] ?? $_GET['exam_id'] ?? 0;
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$comments = $_POST['comments'] ?? $_GET['comments'] ?? '';

if(!$exam_id || !$action) {
    $_SESSION['error'] = "Invalid request parameters.";
    header("Location: PendingApprovals.php");
    exit();
}

// Get exam details - exclude draft exams
$exam = $con->query("SELECT es.*, c.course_name
    FROM exams es
    INNER JOIN courses c ON es.course_id = c.course_id
    WHERE es.exam_id = $exam_id AND es.approval_status != 'draft'
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
            $stmt = $con->prepare("UPDATE exams SET 
                approval_status = 'approved',
                approved_by = ?,
                approved_at = ?,
                approval_comments = ?
                WHERE exam_id = ?");
            $stmt->bind_param("issi", $reviewer_id, $review_date, $comments, $exam_id);
            
            if($stmt->execute()) {
                // Log approval in history
                $log_stmt = $con->prepare("INSERT INTO exam_approval_history 
                    (exam_id, action, performed_by, performed_by_type, comments, previous_status, new_status, created_at) 
                    VALUES (?, 'approved', ?, 'department_head', ?, ?, 'approved', ?)");
                $prev_status = $exam['approval_status'];
                $log_stmt->bind_param("iisss", $exam_id, $reviewer_id, $comments, $prev_status, $review_date);
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
            $stmt = $con->prepare("UPDATE exams SET 
                approval_status = 'revision',
                approval_comments = ?,
                approved_by = ?,
                approved_at = ?,
                revision_count = ?
                WHERE exam_id = ?");
            $stmt->bind_param("sisii", $comments, $reviewer_id, $review_date, $revision_count, $exam_id);
            
            if($stmt->execute()) {
                // Log revision request in history
                $log_stmt = $con->prepare("INSERT INTO exam_approval_history 
                    (exam_id, action, performed_by, performed_by_type, comments, previous_status, new_status, created_at) 
                    VALUES (?, 'revision_requested', ?, 'department_head', ?, ?, 'revision', ?)");
                $prev_status = $exam['approval_status'];
                $log_stmt->bind_param("iisss", $exam_id, $reviewer_id, $comments, $prev_status, $review_date);
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
            $stmt = $con->prepare("UPDATE exams SET 
                approval_status = 'rejected',
                approval_comments = ?,
                approved_by = ?,
                approved_at = ?
                WHERE exam_id = ?");
            $stmt->bind_param("sisi", $comments, $reviewer_id, $review_date, $exam_id);
            
            if($stmt->execute()) {
                // Log rejection in history
                $log_stmt = $con->prepare("INSERT INTO exam_approval_history 
                    (exam_id, action, performed_by, performed_by_type, comments, previous_status, new_status, created_at) 
                    VALUES (?, 'rejected', ?, 'department_head', ?, ?, 'rejected', ?)");
                $prev_status = $exam['approval_status'];
                $log_stmt->bind_param("iisss", $exam_id, $reviewer_id, $comments, $prev_status, $review_date);
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
