<?php
if (!isset($_SESSION)) {
    session_start();
}

// Set JSON header first
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);

try {
    // Check authentication
    if(!isset($_SESSION['Name'])){
        echo json_encode(['error' => 'Unauthorized', 'topics' => []]);
        exit();
    }

    $con = require_once(__DIR__ . "/../Connections/OES.php");
    
    if(!$con) {
        echo json_encode(['error' => 'Database connection failed', 'topics' => []]);
        exit();
    }
    
    $instructor_id = $_SESSION['ID'];
    $course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

    // Validate course_id
    if($course_id == 0) {
        echo json_encode(['error' => 'Invalid course ID', 'topics' => []]);
        exit();
    }

    // Verify instructor has access to this course
    $accessCheck = $con->prepare("SELECT COUNT(*) as has_access 
        FROM instructor_courses 
        WHERE instructor_id = ? AND course_id = ? AND is_active = TRUE");
    
    if(!$accessCheck) {
        echo json_encode(['error' => 'Database query failed', 'topics' => []]);
        exit();
    }
    
    $accessCheck->bind_param("ii", $instructor_id, $course_id);
    $accessCheck->execute();
    $access = $accessCheck->get_result()->fetch_assoc();
    $accessCheck->close();

    if($access['has_access'] == 0) {
        echo json_encode(['error' => 'Access denied to this course', 'topics' => []]);
        exit();
    }

    // Get topics for this course
    $topicsQuery = $con->prepare("SELECT 
        topic_id,
        topic_name,
        chapter_number
        FROM question_topics
        WHERE course_id = ?
        ORDER BY chapter_number, topic_name");
    
    if(!$topicsQuery) {
        echo json_encode(['error' => 'Failed to prepare topics query', 'topics' => []]);
        exit();
    }
    
    $topicsQuery->bind_param("i", $course_id);
    $topicsQuery->execute();
    $result = $topicsQuery->get_result();

    $topics = [];
    while($row = $result->fetch_assoc()) {
        $topics[] = $row;
    }

    $topicsQuery->close();
    $con->close();

    // Always return topics array, even if empty
    echo json_encode(['topics' => $topics, 'count' => count($topics)]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage(), 'topics' => []]);
}
?>
