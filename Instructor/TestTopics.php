<?php
if (!isset($_SESSION)) {
    session_start();
}

// Simple test file to check topics functionality
echo "<h2>Topic System Test</h2>";

// Check if logged in
if(!isset($_SESSION['Name'])){
    echo "<p style='color: red;'>❌ Not logged in</p>";
    exit();
}

echo "<p style='color: green;'>✅ Logged in as: " . $_SESSION['Name'] . " (ID: " . $_SESSION['ID'] . ")</p>";

// Connect to database
$con = require_once(__DIR__ . "/../Connections/OES.php");

if(!$con) {
    echo "<p style='color: red;'>❌ Database connection failed</p>";
    exit();
}

echo "<p style='color: green;'>✅ Database connected</p>";

// Check if question_topics table exists
$tableCheck = $con->query("SHOW TABLES LIKE 'question_topics'");
if($tableCheck && $tableCheck->num_rows > 0) {
    echo "<p style='color: green;'>✅ question_topics table exists</p>";
    
    // Get table structure
    $structure = $con->query("DESCRIBE question_topics");
    echo "<h3>Table Structure:</h3><pre>";
    while($col = $structure->fetch_assoc()) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
    echo "</pre>";
    
    // Count total topics
    $count = $con->query("SELECT COUNT(*) as total FROM question_topics")->fetch_assoc();
    echo "<p>Total topics in database: <strong>" . $count['total'] . "</strong></p>";
    
    // Get all topics
    $topics = $con->query("SELECT qt.*, c.course_name 
        FROM question_topics qt 
        LEFT JOIN courses c ON qt.course_id = c.course_id 
        ORDER BY c.course_name, qt.chapter_number");
    
    if($topics && $topics->num_rows > 0) {
        echo "<h3>All Topics:</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Course</th><th>Chapter</th><th>Topic Name</th></tr>";
        while($topic = $topics->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $topic['topic_id'] . "</td>";
            echo "<td>" . ($topic['course_name'] ?? 'N/A') . "</td>";
            echo "<td>" . ($topic['chapter_number'] ?? '-') . "</td>";
            echo "<td>" . $topic['topic_name'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No topics found in database</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ question_topics table does NOT exist</p>";
    echo "<p>You need to create this table. Run this SQL:</p>";
    echo "<pre>
CREATE TABLE question_topics (
  topic_id INT PRIMARY KEY AUTO_INCREMENT,
  course_id INT NOT NULL,
  topic_name VARCHAR(200) NOT NULL,
  chapter_number INT,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(course_id)
);
    </pre>";
}

// Check instructor courses
$instructor_id = $_SESSION['ID'];
$courses = $con->query("SELECT c.* FROM courses c 
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id 
    WHERE ic.instructor_id = $instructor_id AND ic.is_active = TRUE");

echo "<h3>Your Courses:</h3>";
if($courses && $courses->num_rows > 0) {
    echo "<ul>";
    while($course = $courses->fetch_assoc()) {
        echo "<li>" . $course['course_name'] . " (ID: " . $course['course_id'] . ")</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: orange;'>⚠️ No courses assigned to you</p>";
}

$con->close();
?>
<hr>
<p><a href="CreateSchedule.php">← Back to Create Schedule</a></p>
