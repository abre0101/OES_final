<?php
require_once(__DIR__ . "/../utils/session_manager.php");
SessionManager::startSession('DepartmentHead');

if(!isset($_SESSION['Name']) || $_SESSION['UserType'] !== 'DepartmentHead'){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$deptId = $_SESSION['DeptId'] ?? null;

$qualityQuery = "SELECT c.course_code, c.course_name,
    COUNT(DISTINCT q.question_id) as total_questions,
    COUNT(DISTINCT e.exam_id) as total_exams,
    AVG(q.point_value) as avg_points,
    COUNT(DISTINCT CASE WHEN e.approval_status = 'approved' THEN e.exam_id END) as approved_exams
    FROM courses c
    LEFT JOIN exams e ON c.course_id = e.course_id
    LEFT JOIN exam_questions eq ON e.exam_id = eq.exam_id
    LEFT JOIN questions q ON eq.question_id = q.question_id
    WHERE c.department_id = ? AND c.is_active = 1
    GROUP BY c.course_id
    ORDER BY c.course_code";
$stmt = $con->prepare($qualityQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$courses = $stmt->get_result();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=question_bank_quality_report_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, ['Course Code', 'Course Name', 'Total Questions', 'Total Exams', 'Avg Points', 'Approved Exams', 'Quality Rate %']);

while($course = $courses->fetch_assoc()) {
    $quality_rate = $course['total_exams'] > 0 ? 
        round(($course['approved_exams'] / $course['total_exams']) * 100, 2) : 0;
    
    fputcsv($output, [
        $course['course_code'],
        $course['course_name'],
        $course['total_questions'],
        $course['total_exams'],
        $course['avg_points'] ? round($course['avg_points'], 2) : 'N/A',
        $course['approved_exams'],
        $quality_rate
    ]);
}

fclose($output);
$con->close();
exit();
?>
