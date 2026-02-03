<?php
require_once(__DIR__ . "/../utils/session_manager.php");
SessionManager::startSession('DepartmentHead');

if(!isset($_SESSION['Name']) || $_SESSION['UserType'] !== 'DepartmentHead'){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$deptId = $_SESSION['DeptId'] ?? null;

$participationQuery = "SELECT e.exam_name, e.exam_date, e.start_time,
    c.course_code, c.course_name,
    COUNT(DISTINCT sc.student_id) as enrolled_students,
    COUNT(DISTINCT er.student_id) as participated_students,
    COUNT(DISTINCT er.result_id) as total_attempts,
    (COUNT(DISTINCT sc.student_id) - COUNT(DISTINCT er.student_id)) as no_shows
    FROM exams e
    INNER JOIN courses c ON e.course_id = c.course_id
    LEFT JOIN student_courses sc ON c.course_id = sc.course_id
    LEFT JOIN exam_results er ON e.exam_id = er.exam_id
    WHERE c.department_id = ? AND e.exam_date IS NOT NULL
    GROUP BY e.exam_id
    ORDER BY e.exam_date DESC";
$stmt = $con->prepare($participationQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$exams = $stmt->get_result();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=exam_participation_report_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, ['Exam Name', 'Course', 'Date', 'Time', 'Enrolled', 'Participated', 'No-Shows', 'Participation Rate %']);

while($exam = $exams->fetch_assoc()) {
    $participation_rate = $exam['enrolled_students'] > 0 ? 
        round(($exam['participated_students'] / $exam['enrolled_students']) * 100, 2) : 0;
    
    fputcsv($output, [
        $exam['exam_name'],
        $exam['course_code'],
        date('M d, Y', strtotime($exam['exam_date'])),
        date('h:i A', strtotime($exam['start_time'])),
        $exam['enrolled_students'],
        $exam['participated_students'],
        $exam['no_shows'],
        $participation_rate
    ]);
}

fclose($output);
$con->close();
exit();
?>
