<?php
// Database Import Script for Railway
// This script imports the SQL files into Railway MySQL

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 minutes

echo "<h2>Database Import Script</h2>";
echo "<pre>";

// Get database credentials from environment
$host = getenv('MYSQL_HOST') ?: getenv('DB_HOST');
$port = getenv('MYSQL_PORT') ?: 3306;
$database = getenv('MYSQL_DATABASE') ?: getenv('DB_NAME');
$username = getenv('MYSQL_USER') ?: getenv('DB_USER');
$password = getenv('MYSQL_PASSWORD') ?: getenv('DB_PASSWORD');

echo "Connecting to MySQL...\n";
echo "Host: $host\n";
echo "Database: $database\n\n";

// Connect to MySQL
$con = new mysqli($host, $username, $password, $database, $port);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

echo "✓ Connected successfully!\n\n";

// Function to import SQL file
function importSQL($con, $filename) {
    echo "Importing $filename...\n";
    
    if (!file_exists($filename)) {
        echo "✗ File not found: $filename\n";
        return false;
    }
    
    $sql = file_get_contents($filename);
    
    if ($sql === false) {
        echo "✗ Could not read file: $filename\n";
        return false;
    }
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', 
            preg_split('/;[\r\n]+/', $sql)
        )
    );
    
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || substr($statement, 0, 2) === '--') {
            continue;
        }
        
        if ($con->query($statement) === TRUE) {
            $success++;
        } else {
            $errors++;
            echo "  Error: " . $con->error . "\n";
            echo "  Statement: " . substr($statement, 0, 100) . "...\n";
        }
    }
    
    echo "  ✓ Executed $success statements";
    if ($errors > 0) {
        echo " ($errors errors)";
    }
    echo "\n\n";
    
    return true;
}

// Import main schema
importSQL($con, 'database/oes_professional.sql');

// Import sample data
importSQL($con, 'database/insert_sample_data.sql');

echo "Database import complete!\n";
echo "</pre>";

$con->close();
?>
