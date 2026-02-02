<?php
session_start();
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
require_once('../utils/NotificationSystem.php');

$notificationSystem = new NotificationSystem($con);

// Add approval_status column if it doesn't exist
$con->query("ALTER TABLE question_page ADD COLUMN IF NOT EXISTS approval_status ENUM('pending', 'approved', 'revision', 'rejected') DEFAULT 'pending'");
$con->query("ALTER TABLE question_page ADD COLUMN IF NOT EXISTS approved_by VARCHAR(100)");
$con->query("ALTER TABLE question_page ADD COLUMN IF NOT EXISTS approval_date DATETIME");
$con->query("ALTER TABLE question_page ADD COLUMN IF NOT EXISTS revision_comments TEXT");
$con->query("ALTER TABLE question_page ADD COLUMN IF NOT EXISTS reviewed_by VARCHAR(100)");
$con->query("ALTER TABLE question_page ADD COLUMN IF NOT EXISTS review_date DATETIME");
$con->query("ALTER TABLE question_page ADD COLUMN IF NOT EXISTS instructor_id VARCHAR(100)");

$response = ['success' => false, 'message' => ''];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $questionId = $_POST['question_id'];
    $reviewerName = $_SESSION['Name'];
    $reviewDate = date('Y-m-d H:i:s');
    
    // Get question details for notification
    $questionData = $con->query("SELECT qp.*, ec.exam_name, qp.instructor_id 
        FROM question_page qp 
        LEFT JOIN exam_categories ec ON qp.exam_id = ec.exam_id 
        WHERE qp.Question_ID = $questionId")->fetch_assoc();
    
    $instructorId = $questionData['instructor_id'] ?? null;
    $examName = $questionData['exam_name'] ?? 'Exam';
    
    switch($action) {
        case 'approve':
            $stmt = $con->prepare("UPDATE question_page SET approval_status = 'approved', approved_by = ?, approval_date = ?, reviewed_by = ?, review_date = ? WHERE Question_ID = ?");
            $stmt->bind_param("ssssi", $reviewerName, $reviewDate, $reviewerName, $reviewDate, $questionId);
            
            if($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Question approved successfully!';
                
                // Send notification to instructor
                if($instructorId) {
                    $notificationSystem->notifyQuestionApproved($instructorId, $questionId, $examName);
                }
            } else {
                $response['message'] = 'Error approving question: ' . $stmt->error;
            }
            break;
            
        case 'revision':
            $comments = $_POST['comments'];
            $stmt = $con->prepare("UPDATE question_page SET approval_status = 'revision', revision_comments = ?, reviewed_by = ?, review_date = ? WHERE Question_ID = ?");
            $stmt->bind_param("sssi", $comments, $reviewerName, $reviewDate, $questionId);
            
            if($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Revision request sent to instructor!';
                
                // Send notification to instructor
                if($instructorId) {
                    $notificationSystem->notifyRevisionRequested($instructorId, $questionId, $comments);
                }
            } else {
                $response['message'] = 'Error requesting revision: ' . $stmt->error;
            }
            break;
            
        case 'reject':
            $reason = $_POST['reason'];
            $stmt = $con->prepare("UPDATE question_page SET approval_status = 'rejected', revision_comments = ?, reviewed_by = ?, review_date = ? WHERE Question_ID = ?");
            $stmt->bind_param("sssi", $reason, $reviewerName, $reviewDate, $questionId);
            
            if($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Question rejected!';
                
                // Send notification to instructor
                if($instructorId) {
                    $title = "Question Rejected";
                    $message = "Your question for '{$examName}' (ID: {$questionId}) was rejected. Reason: {$reason}";
                    $link = "../Instructor/ManageQuestions.php";
                    $notificationSystem->createNotification($instructorId, 'instructor', 'question_rejected', $title, $message, $link);
                }
            } else {
                $response['message'] = 'Error rejecting question: ' . $stmt->error;
            }
            break;
    }
}

$con->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
