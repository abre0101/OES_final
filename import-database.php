<?php
// Database Import Script for Railway
// This script imports the SQL files into Railway MySQL

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 minutes

echo "<h2>Database Import Script</h2>";
echo "<pre>";

// Get database credentials from environment
// Try MYSQL_PUBLIC_URL first (accessible from anywhere), then MYSQL_URL (internal network)
$mysql_url = $_ENV['MYSQL_PUBLIC_URL'] ?? getenv('MYSQL_PUBLIC_URL') ?: $_ENV['MYSQL_URL'] ?? getenv('MYSQL_URL');

if ($mysql_url) {
    echo "Using MySQL connection string\n";
    $url_parts = parse_url($mysql_url);
    $host = $url_parts['host'];
    $port = $url_parts['port'] ?? 3306;
    $username = $url_parts['user'];
    $password = $url_parts['pass'];
    $database = ltrim($url_parts['path'], '/');
} else {
    // Fallback to individual environment variables
    $host = $_ENV['MYSQLHOST'] ?? getenv('MYSQLHOST') ?: $_ENV['MYSQL_HOST'] ?? getenv('MYSQL_HOST');
    $port = $_ENV['MYSQLPORT'] ?? getenv('MYSQLPORT') ?: $_ENV['MYSQL_PORT'] ?? getenv('MYSQL_PORT') ?: 3306;
    $database = $_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE') ?: $_ENV['MYSQL_DATABASE'] ?? getenv('MYSQL_DATABASE') ?: 'railway';
    $username = $_ENV['MYSQLUSER'] ?? getenv('MYSQLUSER') ?: $_ENV['MYSQL_USER'] ?? getenv('MYSQL_USER');
    $password = $_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD') ?: $_ENV['MYSQL_PASSWORD'] ?? getenv('MYSQL_PASSWORD');
}

echo "Connecting to MySQL...\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "Database: $database\n";
echo "User: $username\n\n";

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
