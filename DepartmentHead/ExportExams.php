<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$deptId = $_SESSION['DeptId'] ?? null;

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$course_filter = $_GET['course'] ?? 'all';
$year_filter = $_GET['year'] ?? 'all';

// Build conditions
$status_condition = "";
switch($status_filter) {
    case 'upcoming':
        $status_condition = "AND es.exam_date >= CURDATE()";
        break;
    case 'past':
        $status_condition = "AND es.exam_date < CURDATE()";
        break;
    case 'today':
        $status_condition = "AND es.exam_date = CURDATE()";
        break;
    case 'pending':
        $status_condition = "AND es.approval_status = 'pending'";
        break;
    case 'approved':
        $status_condition = "AND es.approval_status = 'approved'";
        break;
}

$course_condition = "";
if($course_filter != 'all') {
    $course_condition = "AND c.course_id = " . intval($course_filter);
}

$year_condition = "";
if($year_filter != 'all') {
    $year_condition = "AND YEAR(es.exam_date) = " . intval($year_filter);
}

// Get exams data
$query = "SELECT 
          es.exam_name,
          c.course_code,
          c.course_name,
          ec.category_name,
          i.full_name as instructor_name,
          es.exam_date,
          es.start_time,
          es.duration_minutes,
          es.total_marks,
          es.pass_marks,
          es.approval_status,
          es.is_active,
          COUNT(DISTINCT sc.student_id) as enrolled_count,
          COUNT(DISTINCT er.result_id) as attempts_count,
          (SELECT COUNT(*) FROM exam_questions eq WHERE eq.exam_id = es.exam_id) as question_count
          FROM exams es
          LEFT JOIN courses c ON es.course_id = c.course_id
          LEFT JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
          LEFT JOIN instructors i ON es.created_by = i.instructor_id
          LEFT JOIN exam_results er ON es.exam_id = er.exam_id
          LEFT JOIN student_courses sc ON c.course_id = sc.course_id
          WHERE c.department_id = ?
          $status_condition $course_condition $year_condition
          GROUP BY es.exam_id
          ORDER BY es.exam_date DESC";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$result = $stmt->get_result();

// Set headers for CSV download
$filename = "department_exams_" . date('Y-m-d_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 support
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write CSV headers
fputcsv($output, [
    'Exam Name',
    'Course Code',
    'Course Name',
    'Category',
    'Instructor',
    'Exam Date',
    'Start Time',
    'Duration (min)',
    'Total Marks',
    'Pass Marks',
    'Questions',
    'Enrolled Students',
    'Attempts',
    'Approval Status',
    'Published'
]);

// Write data rows
while($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['exam_name'],
        $row['course_code'],
        $row['course_name'],
        $row['category_name'],
        $row['instructor_name'] ?? 'N/A',
        $row['exam_date'] ? date('Y-m-d', strtotime($row['exam_date'])) : 'Not Scheduled',
        $row['start_time'] ? date('H:i', strtotime($row['start_time'])) : 'N/A',
        $row['duration_minutes'],
        $row['total_marks'],
        $row['pass_marks'],
        $row['question_count'],
        $row['enrolled_count'],
        $row['attempts_count'],
        ucfirst($row['approval_status']),
        $row['is_active'] ? 'Yes' : 'No'
    ]);
}

fclose($output);
$con->close();
exit();
?>
