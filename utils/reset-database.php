<?php
/**
 * Database Reset Script
 * This script will reset the OES database to a fresh state
 * WARNING: This will delete ALL data!
 */

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "oes";

// Connect to database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>OES Database Reset</h2>";
echo "<p>Starting database reset...</p>";

// Read the SQL file
$sqlFile = 'reset-database.sql';
if (!file_exists($sqlFile)) {
    die("Error: SQL file not found!");
}

$sql = file_get_contents($sqlFile);

// Split into individual queries
$queries = array_filter(array_map('trim', explode(';', $sql)));

$successCount = 0;
$errorCount = 0;
$errors = [];

// Execute each query
foreach ($queries as $query) {
    // Skip empty queries and comments
    if (empty($query) || strpos($query, '--') === 0 || strpos($query, '/*') === 0) {
        continue;
    }
    
    if ($conn->query($query) === TRUE) {
        $successCount++;
    } else {
        $errorCount++;
        $errors[] = "Error: " . $conn->error . "<br>Query: " . substr($query, 0, 100) . "...";
    }
}

// Display results
echo "<h3>Reset Complete!</h3>";
echo "<p><strong>Successful queries:</strong> $successCount</p>";
echo "<p><strong>Failed queries:</strong> $errorCount</p>";

if ($errorCount > 0) {
    echo "<h4>Errors:</h4>";
    echo "<div style='color: red;'>";
    foreach ($errors as $error) {
        echo "<p>$error</p>";
    }
    echo "</div>";
}

echo "<hr>";
echo "<h3>Default Credentials:</h3>";
echo "<ul>";
echo "<li><strong>Admin:</strong> username = <code>admin</code>, password = <code>admin123</code></li>";
echo "<li><strong>Students:</strong> username = <code>student1/student2/student3</code>, password = <code>pass123</code></li>";
echo "<li><strong>Instructors:</strong> username = <code>inst1/inst2/inst3/inst4</code>, password = <code>pass123</code></li>";
echo "</ul>";

echo "<hr>";
echo "<h3>Sample Data Inserted:</h3>";
echo "<ul>";
echo "<li>3 Departments (Computer Science, IT, Software Engineering)</li>";
echo "<li>3 Faculties (Engineering, Science, Health Sciences)</li>";
echo "<li>4 Exam Categories (Midterm, Final, Quiz, Practice Test)</li>";
echo "<li>4 Courses with sample content</li>";
echo "<li>4 Instructors</li>";
echo "<li>3 Students</li>";
echo "<li>16 Practice Questions across all courses</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='index.php' style='padding: 10px 20px; background: #1a2b4a; color: white; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Reset - OES</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h2 {
            color: #1a2b4a;
        }
        code {
            background: #e0e0e0;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        ul {
            line-height: 1.8;
        }
    </style>
</head>
<body>
</body>
</html>
