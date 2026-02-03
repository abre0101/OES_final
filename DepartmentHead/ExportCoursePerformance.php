<?php
require_once(__DIR__ . "/../utils/session_manager.php");
SessionManager::startSession('DepartmentHead');

if(!isset($_SESSION['Name']) || $_SESSION['UserType'] !== 'DepartmentHead'){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$deptId = $_SESSION['DeptId'] ?? null;

$performanceQuery = "SELECT c.course_code, c.course_name, c.credit_hours,
    i.full_name as instructor_name,
    COUNT(DISTINCT sc.student_id) as enrolled_students,
    COUNT(DISTINCT e.exam_id) as total_exams,
    COUNT(DISTINCT er.result_id) as total_attempts,
    AVG(er.total_points_earned) as avg_score,
    SUM(CASE WHEN er.total_points_earned >= e.pass_marks THEN 1 ELSE 0 END) as passed_count,
    COUNT(DISTINCT er.result_id) as attempt_count
    FROM courses c
    LEFT JOIN student_courses sc ON c.course_id = sc.course_id
    LEFT JOIN instructor_courses ic ON c.course_id = ic.course_id
    LEFT JOIN instructors i ON ic.instructor_id = i.instructor_id
    LEFT JOIN exams e ON c.course_id = e.course_id
    LEFT JOIN exam_results er ON e.exam_id = er.exam_id
    WHERE c.department_id = ? AND c.is_active = 1
    GROUP BY c.course_id
    ORDER BY c.course_code";
$stmt = $con->prepare($performanceQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$courses = $stmt->get_result();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=course_performance_report_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, ['Course Code', 'Course Name', 'Instructor', 'Enrolled Students', 'Total Exams', 'Attempts', 'Avg Score', 'Pass Rate %']);

while($course = $courses->fetch_assoc()) {
    $pass_rate = $course['attempt_count'] > 0 ? round(($course['passed_count'] / $course['attempt_count']) * 100, 2) : 0;
    
    fputcsv($output, [
        $course['course_code'],
        $course['course_name'],
        $course['instructor_name'] ?? 'Not Assigned',
        $course['enrolled_students'],
        $course['total_exams'],
        $course['total_attempts'],
        $course['avg_score'] ? round($course['avg_score'], 2) : 'N/A',
        $pass_rate
    ]);
}

fclose($output);
$con->close();
exit();
?>
