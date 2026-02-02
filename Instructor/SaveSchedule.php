<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ManageSchedules.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$instructor_id = $_SESSION['ID'];

// Get form data
$exam_name = $_POST['exam_name'] ?? '';
$course_id = $_POST['course_id'] ?? 0;
$exam_category_id = $_POST['exam_category_id'] ?? 0;
$topic_id = !empty($_POST['topic_id']) ? intval($_POST['topic_id']) : null; // Optional topic filter for quizzes
$exam_date = $_POST['exam_date'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';
$duration_minutes = $_POST['duration_minutes'] ?? 0;
$total_marks = $_POST['total_marks'] ?? 0; // Can be 0, will be calculated from questions
$pass_percentage = $_POST['pass_marks'] ?? 50; // This is actually percentage now
$instructions = $_POST['instructions'] ?? '';

// Validate required fields with specific messages
$errors = [];
if(empty($exam_name)) {
    $errors[] = "Exam Name is required";
}
if($course_id == 0) {
    $errors[] = "Course selection is required";
}
if($exam_category_id == 0) {
    $errors[] = "Exam Category is required";
}
if(empty($exam_date)) {
    $errors[] = "Exam Date is required";
}
if(empty($start_time)) {
    $errors[] = "Start Time is required";
}
if(empty($end_time)) {
    $errors[] = "End Time is required";
}
if($duration_minutes == 0) {
    $errors[] = "Duration is required";
}
if($pass_percentage == 0) {
    $errors[] = "Pass Percentage is required";
}

if(!empty($errors)) {
    $_SESSION['error'] = "Missing required fields: " . implode(", ", $errors);
    header("Location: CreateSchedule.php");
    exit();
}

// Verify instructor has access to this course
$verifyQuery = $con->prepare("SELECT c.course_id, c.semester FROM courses c
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ? AND c.course_id = ? AND ic.is_active = TRUE");
$verifyQuery->bind_param("ii", $instructor_id, $course_id);
$verifyQuery->execute();
$courseResult = $verifyQuery->get_result();
if($courseResult->num_rows == 0) {
    $_SESSION['error'] = "You don't have access to this course";
    header("Location: CreateSchedule.php");
    exit();
}
$courseData = $courseResult->fetch_assoc();
$semester = $courseData['semester'];

// Get category name to check limits
$categoryQuery = $con->prepare("SELECT category_name FROM exam_categories WHERE exam_category_id = ?");
$categoryQuery->bind_param("i", $exam_category_id);
$categoryQuery->execute();
$categoryResult = $categoryQuery->get_result();
if($categoryResult->num_rows == 0) {
    $_SESSION['error'] = "Invalid exam category";
    header("Location: CreateSchedule.php");
    exit();
}
$categoryName = $categoryResult->fetch_assoc()['category_name'];

// Check if Midterm or Final already exists for this course/semester
if($categoryName === 'Midterm' || $categoryName === 'Final') {
    $checkQuery = $con->prepare("SELECT es.exam_id FROM exams es
        INNER JOIN courses c ON es.course_id = c.course_id
        INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
        WHERE es.course_id = ? AND c.semester = ? AND ec.category_name = ?");
    $checkQuery->bind_param("iis", $course_id, $semester, $categoryName);
    $checkQuery->execute();
    if($checkQuery->get_result()->num_rows > 0) {
        $_SESSION['error'] = "This course already has a $categoryName exam scheduled for Semester $semester. Only one $categoryName exam is allowed per course per semester.";
        header("Location: CreateSchedule.php");
        exit();
    }
}

// Insert exam schedule with 'draft' status (will be submitted for approval later)
$temp_pass_marks = 0; // Temporary, will be calculated from percentage
$insertQuery = $con->prepare("INSERT INTO exams 
    (exam_name, course_id, exam_category_id, exam_date, start_time, end_time, duration_minutes, total_marks, pass_marks, instructions, approval_status, is_active, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', TRUE, NOW())");
$insertQuery->bind_param("siisssiiss", $exam_name, $course_id, $exam_category_id, $exam_date, $start_time, $end_time, $duration_minutes, $total_marks, $temp_pass_marks, $instructions);

if($insertQuery->execute()) {
    $exam_id = $con->insert_id;
    
    // Auto-add questions from this course (with optional topic filter)
    // Get all active questions for this course (no approval needed for questions)
    $sql = "SELECT question_id, point_value 
        FROM questions 
        WHERE course_id = ? 
        AND instructor_id = ?";
    
    // Add topic filter if provided (for quizzes)
    if($topic_id !== null) {
        $sql .= " AND topic_id = ?";
    }
    
    $sql .= " ORDER BY created_at ASC";
    
    $questionsQuery = $con->prepare($sql);
    
    if($topic_id !== null) {
        $questionsQuery->bind_param("iii", $course_id, $instructor_id, $topic_id);
    } else {
        $questionsQuery->bind_param("ii", $course_id, $instructor_id);
    }
    
    $questionsQuery->execute();
    $availableQuestions = $questionsQuery->get_result();
    
    // Add questions to the exam
    $questionOrder = 1;
    $calculatedTotal = 0;
    $addedCount = 0;
    
    while($question = $availableQuestions->fetch_assoc()) {
        $addQuestionQuery = $con->prepare("INSERT INTO exam_questions 
            (exam_id, question_id, question_order) 
            VALUES (?, ?, ?)");
        $addQuestionQuery->bind_param("iii", $exam_id, $question['question_id'], $questionOrder);
        
        if($addQuestionQuery->execute()) {
            $calculatedTotal += $question['point_value'];
            $questionOrder++;
            $addedCount++;
        }
    }
    
    // Calculate pass marks from percentage
    $pass_marks = round(($calculatedTotal * $pass_percentage) / 100);
    
    // Update total marks and pass marks based on added questions
    if($addedCount > 0) {
        $updateMarksQuery = $con->prepare("UPDATE exams SET total_marks = ?, pass_marks = ? WHERE exam_id = ?");
        $updateMarksQuery->bind_param("iii", $calculatedTotal, $pass_marks, $exam_id);
        $updateMarksQuery->execute();
    } else {
        // No questions added, just update pass marks based on percentage
        $pass_marks = round(($total_marks * $pass_percentage) / 100);
        $updateMarksQuery = $con->prepare("UPDATE exams SET pass_marks = ? WHERE exam_id = ?");
        $updateMarksQuery->bind_param("ii", $pass_marks, $exam_id);
        $updateMarksQuery->execute();
    }
    
    $_SESSION['success'] = "Exam schedule created successfully! $addedCount questions automatically added.";
    header("Location: ViewExam.php?id=" . $exam_id);
} else {
    $_SESSION['error'] = "Failed to create exam schedule: " . $con->error;
    header("Location: CreateSchedule.php");
}

$con->close();
exit();
?>
