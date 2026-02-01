<?php
// Quick Database Reset - Inserts test data directly

$conn = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Quick Database Reset</h2>";

// Clear existing data
$conn->query("DELETE FROM students");
$conn->query("DELETE FROM exam_results");
$conn->query("DELETE FROM question_page");
$conn->query("DELETE FROM courses");
$conn->query("DELETE FROM instructors");
$conn->query("DELETE FROM exam_categories");

echo "<p>✓ Cleared existing data</p>";

// Insert students
$sql = "INSERT INTO students (Id, Name, Sex, department_name, semester, username, password, is_active, email, year) VALUES 
('1', 'Abraham', 'M', 'Computer Science', 1, 'abre', 'pass123', 'Active', 'abaraham@gmail.com', '2024'),
('2', 'Fitse', 'F', 'Information Technology', 2, 'fiste', 'pass123', 'Active', 'student2@test.com', '2024')";

if ($conn->query($sql)) {
    echo "<p>✓ Inserted 2 students</p>";
} else {
    echo "<p style='color:red'>✗ Error inserting students: " . $conn->error . "</p>";
}

// Insert exam categories
$sql = "INSERT INTO exam_categories (exam_id, exam_name) VALUES 
(1, 'Midterm Exam'),
(2, 'Final Exam'),
(3, 'Quiz'),
(4, 'Practice Test')";

if ($conn->query($sql)) {
    echo "<p>✓ Inserted exam categories</p>";
} else {
    echo "<p style='color:red'>✗ Error: " . $conn->error . "</p>";
}

// Insert courses
$sql = "INSERT INTO courses (course_id, course_name, credit_hr, department_name, semester, full_name) VALUES 
('CS101', 'Introduction to Programming', 3, 'Computer Science', 1, 'Instructor A'),
('CS102', 'Data Structures', 4, 'Computer Science', 2, 'Instructor B'),
('IT101', 'Database Systems', 3, 'Information Technology', 1, 'Instructor C')";

if ($conn->query($sql)) {
    echo "<p>✓ Inserted courses</p>";
} else {
    echo "<p style='color:red'>✗ Error: " . $conn->error . "</p>";
}

// Insert practice questions
$questions = [
    [1, 1, 'Introduction to Programming', 1, 'What is a variable?', 'A storage location', 'A function', 'A loop', 'A class', 'A'],
    [2, 1, 'Introduction to Programming', 1, 'Which is a programming language?', 'HTML', 'CSS', 'Python', 'JSON', 'C'],
    [3, 1, 'Introduction to Programming', 1, 'What does IDE stand for?', 'Integrated Development Environment', 'Internet Data Exchange', 'Internal Design Engine', 'Interactive Display', 'A'],
    [4, 1, 'Introduction to Programming', 1, 'What is a loop used for?', 'To repeat code', 'To store data', 'To define functions', 'To create variables', 'A'],
    [5, 2, 'Data Structures', 2, 'What is an array?', 'A collection of elements', 'A single value', 'A function', 'A class', 'A'],
    [6, 2, 'Data Structures', 2, 'Which uses LIFO?', 'Queue', 'Stack', 'Array', 'Tree', 'B'],
    [7, 3, 'Database Systems', 1, 'What does SQL stand for?', 'Structured Query Language', 'Simple Question Language', 'System Quality Level', 'Standard Query List', 'A'],
    [8, 3, 'Database Systems', 1, 'Which retrieves data?', 'INSERT', 'UPDATE', 'SELECT', 'DELETE', 'C']
];

$success = 0;
foreach ($questions as $q) {
    $stmt = $conn->prepare("INSERT INTO question_page (question_id, exam_id, course_name, semester, question, Option1, Option2, Option3, Option4, Answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssssss", $q[0], $q[1], $q[2], $q[3], $q[4], $q[5], $q[6], $q[7], $q[8], $q[9]);
    if ($stmt->execute()) {
        $success++;
    }
}

echo "<p>✓ Inserted $success practice questions</p>";

$conn->close();

echo "<hr>";
echo "<h3>✅ Reset Complete!</h3>";
echo "<h4>Login Credentials:</h4>";
echo "<ul>";
echo "<li><strong>Student 1:</strong> username = <code>student1</code>, password = <code>pass123</code></li>";
echo "<li><strong>Student 2:</strong> username = <code>student2</code>, password = <code>pass123</code></li>";
echo "</ul>";
echo "<p><a href='index.php' style='padding: 10px 20px; background: #1a2b4a; color: white; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
?>
<style>
body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; }
code { background: #e0e0e0; padding: 2px 6px; border-radius: 3px; }
</style>
