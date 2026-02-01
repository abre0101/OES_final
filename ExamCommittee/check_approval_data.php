<?php
session_start();
$con = require_once(__DIR__ . "/../Connections/OES.php");

echo "<h2>Approval History Data Check</h2>";

// Check if table exists
$table_check = $con->query("SHOW TABLES LIKE 'exam_approval_history'");
echo "<h3>1. Table exists: " . ($table_check && $table_check->num_rows > 0 ? "YES ✓" : "NO ✗") . "</h3>";

// Check total records
$count = $con->query("SELECT COUNT(*) as count FROM exam_approval_history")->fetch_assoc()['count'];
echo "<h3>2. Total records in exam_approval_history: " . $count . "</h3>";

// Show all records
if($count > 0) {
    echo "<h3>3. All Records:</h3>";
    $records = $con->query("SELECT * FROM exam_approval_history ORDER BY created_at DESC");
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Schedule ID</th><th>Action</th><th>Performed By</th><th>Type</th><th>Comments</th><th>Created At</th></tr>";
    while($row = $records->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['history_id'] . "</td>";
        echo "<td>" . $row['schedule_id'] . "</td>";
        echo "<td>" . $row['action'] . "</td>";
        echo "<td>" . $row['performed_by'] . "</td>";
        echo "<td>" . $row['performed_by_type'] . "</td>";
        echo "<td>" . substr($row['comments'], 0, 50) . "...</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check if exams exist for these records
    echo "<h3>4. Checking if related exams exist:</h3>";
    $check = $con->query("SELECT eah.history_id, eah.schedule_id, es.exam_name, c.course_name
        FROM exam_approval_history eah
        LEFT JOIN exam_schedules es ON eah.schedule_id = es.schedule_id
        LEFT JOIN courses c ON es.course_id = c.course_id
        ORDER BY eah.created_at DESC");
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>History ID</th><th>Schedule ID</th><th>Exam Name</th><th>Course Name</th></tr>";
    while($row = $check->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['history_id'] . "</td>";
        echo "<td>" . $row['schedule_id'] . "</td>";
        echo "<td>" . ($row['exam_name'] ?? '<span style="color:red;">NOT FOUND</span>') . "</td>";
        echo "<td>" . ($row['course_name'] ?? '<span style="color:red;">NOT FOUND</span>') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No records found in exam_approval_history table!</p>";
    echo "<p>This means no exams have been approved/rejected/revised yet.</p>";
    echo "<p>Go to <a href='PendingApprovals.php'>Pending Approvals</a> and approve an exam to create history records.</p>";
}

// Check exam_schedules
echo "<h3>5. Exam Schedules Status:</h3>";
$exams = $con->query("SELECT approval_status, COUNT(*) as count FROM exam_schedules GROUP BY approval_status");
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Status</th><th>Count</th></tr>";
while($row = $exams->fetch_assoc()) {
    echo "<tr><td>" . $row['approval_status'] . "</td><td>" . $row['count'] . "</td></tr>";
}
echo "</table>";

$con->close();
?>
<br><br>
<a href="ApprovalHistory.php">← Back to Approval History</a>
