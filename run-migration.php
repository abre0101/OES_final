<?php
// Run database migration for True/False questions
$con = require_once(__DIR__ . "/Connections/OES.php");

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

echo "<h2>Running Migration: Add True/False Question Support</h2>";

$migration_file = __DIR__ . '/database/migrations/add_true_false_questions.sql';

if (file_exists($migration_file)) {
    $sql = file_get_contents($migration_file);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (!empty($statement) && substr($statement, 0, 2) !== '--' && stripos($statement, 'USE') !== 0) {
            if ($con->query($statement) === TRUE) {
                $success_count++;
                echo "<p style='color: green;'>✓ Statement executed successfully</p>";
            } else {
                $error_count++;
                echo "<p style='color: red;'>✗ Error: " . $con->error . "</p>";
                echo "<pre>" . htmlspecialchars(substr($statement, 0, 200)) . "...</pre>";
            }
        }
    }
    
    echo "<hr>";
    echo "<h3>Migration Summary:</h3>";
    echo "<p>✓ Successful: $success_count</p>";
    echo "<p>✗ Errors: $error_count</p>";
    
    if ($error_count === 0) {
        echo "<p style='color: green; font-weight: bold;'>✓ Migration completed successfully!</p>";
        echo "<p>The system now supports both Multiple Choice and True/False questions.</p>";
    }
} else {
    echo "<p style='color: red;'>Migration file not found!</p>";
}

$con->close();

echo "<hr>";
echo "<p><a href='Admin/index.php'>Go to Admin Dashboard</a></p>";
?>
