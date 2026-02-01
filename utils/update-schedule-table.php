<?php
// Script to UPDATE exam_schedules table with duration and end_time columns

$con = mysqli_connect("localhost","root","","oes");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "<h2>Updating Schedule Table Structure...</h2>";

// Add duration_minutes column (exam duration in minutes)
$sql1 = "ALTER TABLE exam_schedules ADD COLUMN IF NOT EXISTS duration_minutes INT DEFAULT 60";
if (mysqli_query($con, $sql1)) {
    echo "✓ Added duration_minutes column<br>";
} else {
    if (strpos(mysqli_error($con), 'Duplicate column') !== false) {
        echo "ℹ️ duration_minutes column already exists<br>";
    } else {
        echo "✗ Error adding duration_minutes: " . mysqli_error($con) . "<br>";
    }
}

// Add end_time column
$sql2 = "ALTER TABLE exam_schedules ADD COLUMN IF NOT EXISTS end_time TIME";
if (mysqli_query($con, $sql2)) {
    echo "✓ Added end_time column<br>";
} else {
    if (strpos(mysqli_error($con), 'Duplicate column') !== false) {
        echo "ℹ️ end_time column already exists<br>";
    } else {
        echo "✗ Error adding end_time: " . mysqli_error($con) . "<br>";
    }
}

// Update existing records to set end_time (2 hours after start time by default)
$sql3 = "UPDATE exam_schedules SET end_time = ADDTIME(exam_time, '02:00:00') WHERE end_time IS NULL";
if (mysqli_query($con, $sql3)) {
    echo "✓ Updated existing records with end_time<br>";
} else {
    echo "✗ Error updating end_time: " . mysqli_error($con) . "<br>";
}

// Update duration_minutes for existing records (120 minutes = 2 hours)
$sql4 = "UPDATE exam_schedules SET duration_minutes = 60 WHERE duration_minutes IS NULL OR duration_minutes = 0";
if (mysqli_query($con, $sql4)) {
    echo "✓ Updated existing records with duration_minutes<br>";
} else {
    echo "✗ Error updating duration_minutes: " . mysqli_error($con) . "<br>";
}

mysqli_close($con);

echo "<br><strong>✅ Schedule table updated successfully!</strong><br><br>";
echo "<a href='create-test-schedule.php' style='padding: 10px 20px; background: #1a2b4a; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>Recreate Test Schedules</a>";
?>
