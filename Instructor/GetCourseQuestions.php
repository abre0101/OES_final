<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    http_response_code(403);
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$instructor_id = $_SESSION['ID'];
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$topic_id = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;

if($course_id == 0) {
    echo json_encode(['questions' => []]);
    exit();
}

// Verify instructor has access to this course
$verifyQuery = $con->prepare("SELECT course_id FROM instructor_courses 
    WHERE instructor_id = ? AND course_id = ? AND is_active = TRUE");
$verifyQuery->bind_param("ii", $instructor_id, $course_id);
$verifyQuery->execute();
if($verifyQuery->get_result()->num_rows == 0) {
    http_response_code(403);
    exit();
}

// Get all questions for this course (with optional topic filter)
$sql = "SELECT q.question_id, q.question_text, q.point_value, q.difficulty_level, qt.topic_name
    FROM questions q
    LEFT JOIN question_topics qt ON q.topic_id = qt.topic_id
    WHERE q.course_id = ? 
    AND q.instructor_id = ?";

// Add topic filter if provided
if($topic_id > 0) {
    $sql .= " AND q.topic_id = ?";
}

$sql .= " ORDER BY q.created_at ASC";

$questionsQuery = $con->prepare($sql);

if($topic_id > 0) {
    $questionsQuery->bind_param("iii", $course_id, $instructor_id, $topic_id);
} else {
    $questionsQuery->bind_param("ii", $course_id, $instructor_id);
}

$questionsQuery->execute();
$result = $questionsQuery->get_result();

$questions = [];
while($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

header('Content-Type: application/json');
echo json_encode(['questions' => $questions]);

$con->close();
?>
