<?php
require_once(__DIR__ . "/../utils/session_manager.php");
SessionManager::startSession('DepartmentHead');

if(!isset($_SESSION['Name']) || $_SESSION['UserType'] !== 'DepartmentHead'){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$deptId = $_SESSION['DeptId'] ?? null;

$complianceQuery = "SELECT i.full_name, i.email,
    COUNT(DISTINCT e.exam_id) as total_exams,
    COUNT(DISTINCT CASE WHEN e.approval_status = 'approved' THEN e.exam_id END) as approved_exams,
    COUNT(DISTINCT CASE WHEN e.approval_status = 'pending' THEN e.exam_id END) as pending_exams,
    COUNT(DISTINCT CASE WHEN e.approval_status = 'rejected' THEN e.exam_id END) as rejected_exams,
    COUNT(DISTINCT eq.question_id) as total_questions,
    AVG(DATEDIFF(e.exam_date, e.created_at)) as avg_prep_days
    FROM instructors i
    LEFT JOIN instructor_courses ic ON i.instructor_id = ic.instructor_id
    LEFT JOIN courses c ON ic.course_id = c.course_id
    LEFT JOIN exams e ON c.course_id = e.course_id AND e.created_by = i.instructor_id
    LEFT JOIN exam_questions eq ON e.exam_id = eq.exam_id
    WHERE i.department_id = ? AND i.is_active = 1
    GROUP BY i.instructor_id
    ORDER BY i.full_name";
$stmt = $con->prepare($complianceQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$instructors = $stmt->get_result();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=instructor_compliance_report_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, ['Instructor Name', 'Email', 'Total Exams', 'Approved', 'Pending', 'Rejected', 'Questions Created', 'Avg Prep Days', 'Compliance Rate %']);

while($instructor = $instructors->fetch_assoc()) {
    $compliance_rate = $instructor['total_exams'] > 0 ? 
        round(($instructor['approved_exams'] / $instructor['total_exams']) * 100, 2) : 0;
    
    fputcsv($output, [
        $instructor['full_name'],
        $instructor['email'],
        $instructor['total_exams'],
        $instructor['approved_exams'],
        $instructor['pending_exams'],
        $instructor['rejected_exams'],
        $instructor['total_questions'],
        $instructor['avg_prep_days'] ? round($instructor['avg_prep_days']) : 'N/A',
        $compliance_rate
    ]);
}

fclose($output);
$con->close();
exit();
?>
