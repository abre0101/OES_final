<?php
require_once(__DIR__ . "/../utils/session_manager.php");
SessionManager::startSession('DepartmentHead');

if(!isset($_SESSION['Name']) || $_SESSION['UserType'] !== 'DepartmentHead'){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$deptId = $_SESSION['DeptId'] ?? null;

// Get filters
$status_filter = $_GET['status'] ?? 'all';
$year_filter = $_GET['year'] ?? 'all';

$status_condition = "";
if($status_filter == 'active') {
    $status_condition = "AND s.is_active = 1";
} elseif($status_filter == 'inactive') {
    $status_condition = "AND s.is_active = 0";
}

$year_condition = "";
if($year_filter != 'all') {
    $year_condition = "AND s.academic_year = 'Year " . intval($year_filter) . "'";
}

$studentsQuery = "SELECT s.student_code, s.full_name, s.email, s.academic_year,
    s.is_active,
    COUNT(DISTINCT sc.course_id) as enrolled_courses,
    COUNT(DISTINCT er.result_id) as exam_attempts,
    AVG(er.total_points_earned) as avg_score
    FROM students s
    LEFT JOIN student_courses sc ON s.student_id = sc.student_id
    LEFT JOIN exam_results er ON s.student_id = er.student_id
    WHERE s.department_id = ? $status_condition $year_condition
    GROUP BY s.student_id
    ORDER BY s.student_code";
$stmt = $con->prepare($studentsQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$students = $stmt->get_result();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=student_management_report_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 support
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// CSV headers
fputcsv($output, ['Student Code', 'Full Name', 'Email', 'Year Level', 'Enrolled Courses', 'Exam Attempts', 'Avg Score', 'Status']);

// CSV data
while($student = $students->fetch_assoc()) {
    fputcsv($output, [
        $student['student_code'],
        $student['full_name'],
        $student['email'],
        $student['academic_year'] ?? 'N/A',
        $student['enrolled_courses'],
        $student['exam_attempts'],
        $student['avg_score'] ? round($student['avg_score'], 2) : 'N/A',
        $student['is_active'] ? 'Active' : 'Inactive'
    ]);
}

fclose($output);
$con->close();
exit();
?>
