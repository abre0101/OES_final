<?php
// Import database schema to Railway MySQL
// Run this script once after deployment

// Railway MySQL credentials
$hostname = 'yamanote.proxy.rlwy.net';
$database = 'railway';
$username = 'root';
$password = 'WVfbKCqYyoVFszxfuaEgmGkTdSkxaLWk';
$port = 25317;

// Create connection
$con = new mysqli($hostname, $username, $password, $database, $port);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

echo "Connected successfully to Railway MySQL!\n\n";

// Read SQL file
$sqlFile = 'database/oes_professional.sql';
if (!file_exists($sqlFile)) {
    die("SQL file not found: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);

// Split into individual queries
$queries = array_filter(array_map('trim', explode(';', $sql)));

$success = 0;
$errors = 0;

echo "Importing database schema...\n";
echo "Total queries: " . count($queries) . "\n\n";

foreach ($queries as $index => $query) {
    if (empty($query)) continue;
    
    if ($con->query($query) === TRUE) {
        $success++;
        if ($success % 10 == 0) {
            echo "Processed $success queries...\n";
        }
    } else {
        $errors++;
        echo "Error in query " . ($index + 1) . ": " . $con->error . "\n";
    }
}

echo "\n=================================\n";
echo "Import completed!\n";
echo "Successful: $success\n";
echo "Errors: $errors\n";
echo "=================================\n";

$con->close();
?>
