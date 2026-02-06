<?php
// Import practice questions into database
$con = require_once(__DIR__ . "/Connections/OES.php");

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

echo "<h2>Importing Practice Questions</h2>";

// Read and execute practice questions SQL
$sql_file = __DIR__ . '/database/insert_practice_questions.sql';
if (file_exists($sql_file)) {
    $sql = file_get_contents($sql_file);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (!empty($statement) && substr($statement, 0, 2) !== '--' && stripos($statement, 'USE') !== 0) {
            if ($con->query($statement) === TRUE) {
                $success_count++;
                echo "<p style='color: green;'>✓ Statement executed</p>";
            } else {
                $error_count++;
                echo "<p style='color: red;'>✗ Error: " . $con->error . "</p>";
            }
        }
    }
    
    echo "<hr>";
    echo "<h3>Import Summary:</h3>";
    echo "<p>✓ Successful: $success_count</p>";
    echo "<p>✗ Errors: $error_count</p>";
    
    if ($error_count === 0) {
        echo "<p style='color: green; font-weight: bold;'>✓ Practice questions imported successfully!</p>";
        
        // Show count
        $count_result = $con->query("SELECT COUNT(*) as total FROM practice_questions");
        if ($count_result) {
            $count = $count_result->fetch_assoc()['total'];
            echo "<p style='font-size: 1.2rem;'><strong>Total Practice Questions:</strong> $count</p>";
        }
    }
} else {
    echo "<p style='color: red;'>SQL file not found!</p>";
}

$con->close();

echo "<hr>";
echo "<p><a href='Student/practice-selection.php'>Go to Practice Questions</a> | <a href='Admin/index.php'>Admin Dashboard</a></p>";
?>
