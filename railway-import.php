<?php
// One-time database import script for Railway
// Run this with: railway run php railway-import.php

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(600); // 10 minutes

echo "=== Railway Database Import ===\n\n";

// Get MySQL URL from environment
$mysql_url = getenv('MYSQL_URL');

if (!$mysql_url) {
    die("ERROR: MYSQL_URL not found in environment\n");
}

echo "Parsing MySQL URL...\n";
$url_parts = parse_url($mysql_url);
$host = $url_parts['host'];
$port = $url_parts['port'] ?? 3306;
$username = $url_parts['user'];
$password = $url_parts['pass'];
$database = ltrim($url_parts['path'], '/');

echo "Host: $host\n";
echo "Port: $port\n";
echo "Database: $database\n";
echo "User: $username\n\n";

echo "Connecting to MySQL...\n";
$con = new mysqli($host, $username, $password, $database, $port);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error . "\n");
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
    
    // Remove comments and split by semicolon
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Split into statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) { return !empty($stmt); }
    );
    
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        if ($con->query($statement) === TRUE) {
            $success++;
            if ($success % 10 == 0) {
                echo "  Executed $success statements...\n";
            }
        } else {
            $errors++;
            echo "  Error: " . $con->error . "\n";
            if ($errors > 10) {
                echo "  Too many errors, stopping...\n";
                return false;
            }
        }
    }
    
    echo "  ✓ Completed: $success statements executed";
    if ($errors > 0) {
        echo " ($errors errors)";
    }
    echo "\n\n";
    
    return true;
}

// Import main schema
echo "Step 1: Importing database schema...\n";
importSQL($con, 'database/oes_professional.sql');

// Import sample data
echo "Step 2: Importing sample data...\n";
importSQL($con, 'database/insert_sample_data.sql');

echo "=== Database import complete! ===\n";

$con->close();
?>
