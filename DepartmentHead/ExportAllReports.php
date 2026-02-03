<?php
require_once(__DIR__ . "/../utils/session_manager.php");
SessionManager::startSession('DepartmentHead');

if(!isset($_SESSION['Name']) || $_SESSION['UserType'] !== 'DepartmentHead'){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$deptId = $_SESSION['DeptId'] ?? null;
$deptName = $_SESSION['Dept'] ?? 'Department';

// Get date range
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=all_reports_' . $deptName . '_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Report Header
fputcsv($output, ['COMPREHENSIVE DEPARTMENT REPORTS']);
fputcsv($output, ['Department: ' . $deptName]);
fputcsv($output, ['Generated: ' . date('F d, Y h:i A')]);
fputcsv($output, ['Period: ' . date('M d, Y', strtotime($startDate)) . ' to ' . date('M d, Y', strtotime($endDate))]);
fputcsv($output, []);

// ===== REPORT 1: STUDENT MANAGEMENT =====
fputcsv($output, ['===== STUDENT MANAGEMENT REPORT =====']);
fputcsv($output, []);

$studentsQuery = "SELECT s.student_code, s.full_name, s.email, s.academic_year,
    s.is_active,
    COUNT(DISTINCT sc.course_id) as enrolled_courses,
    COUNT(DISTINCT er.result_id) as exam_attempts,
    AVG(er.total_points_earned) as avg_score
    FROM students s
    LEFT JOIN student_courses sc ON s.student_id = sc.student_id
    LEFT JOIN exam_results er ON s.student_id = er.student_id
    WHERE s.department_id = ?
    GROUP BY s.student_id
    ORDER BY s.student_code";
$stmt = $con->prepare($studentsQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$students = $stmt->get_result();

fputcsv($output, ['Student Code', 'Full Name', 'Email', 'Academic Year', 'Enrolled Courses', 'Exam Attempts', 'Avg Score', 'Status']);
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
fputcsv($output, []);
fputcsv($output, []);

// ===== REPORT 2: COURSE PERFORMANCE =====
fputcsv($output, ['===== COURSE PERFORMANCE & RESULTS REPORT =====']);
fputcsv($output, []);

$performanceQuery = "SELECT c.course_code, c.course_name,
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

fputcsv($output, ['Course Code', 'Course Name', 'Instructor', 'Enrolled', 'Exams', 'Attempts', 'Avg Score', 'Pass Rate %']);
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
fputcsv($output, []);
fputcsv($output, []);

// ===== REPORT 3: EXAM PARTICIPATION =====
fputcsv($output, ['===== EXAM PARTICIPATION & ATTENDANCE REPORT =====']);
fputcsv($output, []);

$participationQuery = "SELECT e.exam_name, e.exam_date, e.start_time,
    c.course_code,
    COUNT(DISTINCT sc.student_id) as enrolled_students,
    COUNT(DISTINCT er.student_id) as participated_students,
    (COUNT(DISTINCT sc.student_id) - COUNT(DISTINCT er.student_id)) as no_shows
    FROM exams e
    INNER JOIN courses c ON e.course_id = c.course_id
    LEFT JOIN student_courses sc ON c.course_id = sc.course_id
    LEFT JOIN exam_results er ON e.exam_id = er.exam_id
    WHERE c.department_id = ? AND e.exam_date BETWEEN ? AND ?
    GROUP BY e.exam_id
    ORDER BY e.exam_date DESC";
$stmt = $con->prepare($participationQuery);
$stmt->bind_param("iss", $deptId, $startDate, $endDate);
$stmt->execute();
$exams = $stmt->get_result();

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
fputcsv($output, []);
fputcsv($output, []);

// ===== REPORT 4: INSTRUCTOR COMPLIANCE =====
fputcsv($output, ['===== INSTRUCTOR COMPLIANCE REPORT =====']);
fputcsv($output, []);

$complianceQuery = "SELECT i.full_name, i.email,
    COUNT(DISTINCT e.exam_id) as total_exams,
    COUNT(DISTINCT CASE WHEN e.approval_status = 'approved' THEN e.exam_id END) as approved_exams,
    COUNT(DISTINCT CASE WHEN e.approval_status = 'pending' THEN e.exam_id END) as pending_exams,
    COUNT(DISTINCT CASE WHEN e.approval_status = 'rejected' THEN e.exam_id END) as rejected_exams
    FROM instructors i
    LEFT JOIN exams e ON i.instructor_id = e.created_by
    LEFT JOIN courses c ON e.course_id = c.course_id
    WHERE i.department_id = ? AND i.is_active = 1
    GROUP BY i.instructor_id
    ORDER BY i.full_name";
$stmt = $con->prepare($complianceQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$instructors = $stmt->get_result();

fputcsv($output, ['Instructor Name', 'Email', 'Total Exams', 'Approved', 'Pending', 'Rejected', 'Compliance Rate %']);
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
        $compliance_rate
    ]);
}
fputcsv($output, []);
fputcsv($output, []);

// ===== REPORT 5: EXAMINATION SCHEDULE =====
fputcsv($output, ['===== EXAMINATION SCHEDULE REPORT =====']);
fputcsv($output, []);

$scheduleQuery = "SELECT e.exam_name, e.exam_date, e.start_time, e.duration_minutes,
    e.approval_status, e.is_active,
    c.course_code,
    i.full_name as instructor_name
    FROM exams e
    INNER JOIN courses c ON e.course_id = c.course_id
    LEFT JOIN instructors i ON e.created_by = i.instructor_id
    WHERE c.department_id = ? AND e.exam_date >= CURDATE()
    ORDER BY e.exam_date, e.start_time";
$stmt = $con->prepare($scheduleQuery);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$schedule = $stmt->get_result();

fputcsv($output, ['Date', 'Time', 'Exam Name', 'Course', 'Instructor', 'Duration (min)', 'Approval Status', 'Published']);
while($exam = $schedule->fetch_assoc()) {
    fputcsv($output, [
        date('M d, Y', strtotime($exam['exam_date'])),
        date('h:i A', strtotime($exam['start_time'])),
        $exam['exam_name'],
        $exam['course_code'],
        $exam['instructor_name'] ?? 'N/A',
        $exam['duration_minutes'],
        ucfirst($exam['approval_status']),
        $exam['is_active'] ? 'Yes' : 'No'
    ]);
}
fputcsv($output, []);
fputcsv($output, []);

// ===== REPORT 6: QUESTION BANK QUALITY =====
fputcsv($output, ['===== QUESTION BANK & EXAM QUALITY REPORT =====']);
fputcsv($output, []);

$qualityQuery = "SELECT c.course_code, c.course_name,
    COUNT(DISTINCT q.question_id) as total_questions,
    COUNT(DISTINCT e.exam_id) as total_exams,
    AVG(q.point_value) as avg_points
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
$quality = $stmt->get_result();

fputcsv($output, ['Course Code', 'Course Name', 'Total Questions', 'Total Exams', 'Avg Points']);
while($course = $quality->fetch_assoc()) {
    fputcsv($output, [
        $course['course_code'],
        $course['course_name'],
        $course['total_questions'],
        $course['total_exams'],
        $course['avg_points'] ? round($course['avg_points'], 2) : 'N/A'
    ]);
}

fputcsv($output, []);
fputcsv($output, ['===== END OF REPORT =====']);

fclose($output);
$con->close();
exit();
?>
