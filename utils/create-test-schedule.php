<?php
// Quick script to create test exam schedules

$con = require_once(__DIR__ . "/../Connections/OES.php"); $con;

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get exam categories (1=Quiz, 2=Mid Exam, 3=Final Exam)
$exam_result = mysqli_query($con, "SELECT * FROM exam_categories ORDER BY exam_id");
$exams = [];
while($exam = mysqli_fetch_assoc($exam_result)) {
    $exams[] = $exam;
}

// Check what courses exist
$course_result = mysqli_query($con, "SELECT * FROM courses LIMIT 1");
$course = mysqli_fetch_assoc($course_result);

if (empty($exams) || !$course) {
    echo "Error: No exam categories or courses found. Please create them first.<br>";
    echo "<a href='Admin/index.php'>Go to Admin Panel</a>";
    exit();
}

// Clear existing schedules (optional)
mysqli_query($con, "DELETE FROM exam_schedules");

// Create sample schedules with proper exam_id references and time windows
$schedules = [
    [
        'schedule_id' => 1,
        'exam_name' => 1, // Quiz Exam
        'course_name' => $course['course_name'],
        'exam_date' => date('Y-m-d'),
        'exam_time' => '08:00:00',
        'end_time' => '23:59:00',
        'duration_minutes' => 30,
        'semester' => 1
    ],
    [
        'schedule_id' => 2,
        'exam_name' => 2, // Mid Exam
        'course_name' => $course['course_name'],
        'exam_date' => date('Y-m-d', strtotime('+2 days')),
        'exam_time' => '09:00:00',
        'end_time' => '17:00:00',
        'duration_minutes' => 90,
        'semester' => 1
    ],
    [
        'schedule_id' => 3,
        'exam_name' => 3, // Final Exam
        'course_name' => $course['course_name'],
        'exam_date' => date('Y-m-d', strtotime('+5 days')),
        'exam_time' => '10:00:00',
        'end_time' => '16:00:00',
        'duration_minutes' => 120,
        'semester' => 2
    ]
];

echo "<h2>Creating Exam Schedules...</h2>";

$success_count = 0;
foreach ($schedules as $schedule) {
    $sql = "INSERT INTO exam_schedules (schedule_id, exam_name, course_name, exam_date, exam_time, end_time, duration_minutes, semester) 
            VALUES (
                {$schedule['schedule_id']}, 
                {$schedule['exam_name']}, 
                '{$schedule['course_name']}', 
                '{$schedule['exam_date']}', 
                '{$schedule['exam_time']}',
                '{$schedule['end_time']}',
                {$schedule['duration_minutes']},
                {$schedule['semester']}
            )";
    
    if (mysqli_query($con, $sql)) {
        $success_count++;
        // Get exam type name
        $exam_type = '';
        foreach($exams as $e) {
            if($e['exam_id'] == $schedule['exam_name']) {
                $exam_type = $e['exam_name'];
                break;
            }
        }
        echo "✓ Schedule {$schedule['schedule_id']} created: <strong>{$exam_type}</strong> - {$schedule['course_name']} on {$schedule['exam_date']} ({$schedule['exam_time']} - {$schedule['end_time']}, {$schedule['duration_minutes']} min)<br>";
    } else {
        echo "✗ Error creating schedule {$schedule['schedule_id']}: " . mysqli_error($con) . "<br>";
    }
}

mysqli_close($con);

echo "<br><strong>Summary: {$success_count} schedules created successfully!</strong><br><br>";
echo "<a href='Student/StartExam-modern.php' style='padding: 10px 20px; background: #1a2b4a; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>View Exam Schedule</a>";
?>
