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
$export_type = $_GET['type'] ?? 'departmental';

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $export_type . '_report_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 support
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

if($export_type == 'departmental') {
    // Export departmental performance report
    fputcsv($output, ['Department Performance Report - ' . date('F Y')]);
    fputcsv($output, ['Generated on: ' . date('Y-m-d H:i:s')]);
    fputcsv($output, ['Department: ' . $_SESSION['Dept']]);
    fputcsv($output, []);
    
    fputcsv($output, ['Course Code', 'Course Name', 'Total Exams', 'Total Students', 'Avg Score', 'Pass Rate']);
    
    $query = "SELECT c.course_code, c.course_name,
              COUNT(DISTINCT es.exam_id) as total_exams,
              COUNT(DISTINCT sc.student_id) as total_students,
              AVG(er.marks_obtained) as avg_score,
              (COUNT(DISTINCT CASE WHEN er.marks_obtained >= es.passing_marks THEN er.result_id END) / 
               NULLIF(COUNT(DISTINCT er.result_id), 0) * 100) as pass_rate
              FROM courses c
              LEFT JOIN exams es ON c.course_id = es.course_id
              LEFT JOIN student_courses sc ON c.course_id = sc.course_id
              LEFT JOIN exam_results er ON es.exam_id = er.exam_id
              WHERE c.department_id = ? AND c.is_active = 1
              GROUP BY c.course_id
              ORDER BY c.course_code";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $deptId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['course_code'],
            $row['course_name'],
            $row['total_exams'] ?? 0,
            $row['total_students'] ?? 0,
            $row['avg_score'] ? number_format($row['avg_score'], 2) : 'N/A',
            $row['pass_rate'] ? number_format($row['pass_rate'], 2) . '%' : 'N/A'
        ]);
    }
    
} elseif($export_type == 'students') {
    // Export student list
    fputcsv($output, ['Student List - ' . $_SESSION['Dept'] . ' Department']);
    fputcsv($output, ['Generated on: ' . date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    
    fputcsv($output, ['Student ID', 'Full Name', 'Email', 'Phone', 'Enrollment Date', 'Status']);
    
    $query = "SELECT s.student_id, s.full_name, s.email, s.phone_number, s.enrollment_date, s.is_active
              FROM students s
              WHERE s.department_id = ?
              ORDER BY s.full_name";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $deptId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['student_id'],
            $row['full_name'],
            $row['email'],
            $row['phone_number'] ?? 'N/A',
            $row['enrollment_date'] ? date('Y-m-d', strtotime($row['enrollment_date'])) : 'N/A',
            $row['is_active'] ? 'Active' : 'Inactive'
        ]);
    }
    
} elseif($export_type == 'exams') {
    // Export exam schedules
    fputcsv($output, ['Exam Schedules - ' . $_SESSION['Dept'] . ' Department']);
    fputcsv($output, ['Generated on: ' . date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    
    fputcsv($output, ['Exam Name', 'Course', 'Category', 'Date', 'Time', 'Duration', 'Total Marks', 'Status']);
    
    $query = "SELECT es.exam_name, c.course_code, ec.category_name, es.exam_date, es.start_time, 
              es.duration_minutes, es.total_marks, es.approval_status
              FROM exams es
              LEFT JOIN courses c ON es.course_id = c.course_id
              LEFT JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
              WHERE c.department_id = ? AND es.is_active = 1
              ORDER BY es.exam_date DESC";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $deptId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['exam_name'],
            $row['course_code'],
            $row['category_name'],
            $row['exam_date'] ? date('Y-m-d', strtotime($row['exam_date'])) : 'Not scheduled',
            $row['start_time'] ? date('H:i', strtotime($row['start_time'])) : 'N/A',
            $row['duration_minutes'] . ' min',
            $row['total_marks'],
            ucfirst($row['approval_status'])
        ]);
    }
}

fclose($output);
exit();
?>
