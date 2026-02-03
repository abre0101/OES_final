<?php
require_once(__DIR__ . "/../utils/session_manager.php");
SessionManager::startSession('DepartmentHead');

if(!isset($_SESSION['Name']) || $_SESSION['UserType'] !== 'DepartmentHead'){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$deptId = $_SESSION['DeptId'] ?? null;

$scheduleQuery = "SELECT e.exam_name, e.exam_date, e.start_time, e.duration_minutes,
    e.approval_status, e.is_active,
    c.course_code, c.course_name,
    i.full_name as instructor_name,
    ec.category_name,
    COUNT(DISTINCT sc.student_id) as enrolled_students
    FROM exams e
    INNER JOIN courses c ON e.course_id = c.course_id
    LEFT JOIN instructors i ON e.created_by = i.instructor_id
    LEFT JOIN exam_categories ec ON e.exam_category_id = ec.exam_category_id
    LEFT JOIN student_courses sc ON c.course_id = sc.course_id
    WHERE c.department_id = ? AND e.exam_date >= CURDATE()
    GROUP BY e.exam_id
    ORDER BY e.exam_date, e.start_time";
$stmt = $con->prepare($scheduleQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$exams = $stmt->get_result();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=examination_schedule_report_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, ['Date', 'Time', 'Exam Name', 'Course', 'Category', 'Instructor', 'Duration (min)', 'Enrolled', 'Approval Status', 'Published']);

while($exam = $exams->fetch_assoc()) {
    fputcsv($output, [
        date('M d, Y', strtotime($exam['exam_date'])),
        date('h:i A', strtotime($exam['start_time'])),
        $exam['exam_name'],
        $exam['course_code'],
        $exam['category_name'],
        $exam['instructor_name'] ?? 'N/A',
        $exam['duration_minutes'],
        $exam['enrolled_students'],
        ucfirst($exam['approval_status']),
        $exam['is_active'] ? 'Yes' : 'No'
    ]);
}

fclose($output);
$con->close();
exit();
?>
