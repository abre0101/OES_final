<?php
/**
 * Database Setup Script for Railway
 * Access this file once after deployment to import the database schema
 * URL: https://your-app.up.railway.app/setup-database.php?key=dmu2026setup
 */

// Security: Only allow this to run once or with a secret key
$SETUP_KEY = 'dmu2026setup';
$provided_key = $_GET['key'] ?? '';

if ($provided_key !== $SETUP_KEY) {
    die('Access denied. Provide the correct setup key in URL: ?key=YOUR_KEY');
}

echo "<h1>Database Setup for Railway</h1>";
echo "<p>Starting database import...</p>";
echo "<pre>";

// Include database connection
$con = include('Connections/OES.php');

if (!$con) {
    die("Failed to connect to database");
}

echo "✓ Connected to database successfully\n\n";

// Read SQL file
$sqlFile = 'database/oes_professional.sql';
if (!file_exists($sqlFile)) {
    die("✗ SQL file not found: $sqlFile\n");
}

echo "✓ Found SQL file: $sqlFile\n";
echo "✓ File size: " . round(filesize($sqlFile) / 1024, 2) . " KB\n\n";

// Read and execute SQL
$sql = file_get_contents($sqlFile);

// Remove comments and split by semicolon
$sql = preg_replace('/--.*$/m', '', $sql);
$sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

// Split into queries
$queries = array_filter(array_map('trim', explode(';', $sql)));

echo "Processing " . count($queries) . " SQL statements...\n\n";

$success = 0;
$errors = 0;
$errorMessages = [];

// Disable foreign key checks temporarily
$con->query('SET FOREIGN_KEY_CHECKS=0');

foreach ($queries as $index => $query) {
    if (empty($query) || strlen($query) < 5) continue;
    
    if ($con->query($query) === TRUE) {
        $success++;
        if ($success % 20 == 0) {
            echo "✓ Processed $success queries...\n";
            flush();
        }
    } else {
        $errors++;
        $errorMessages[] = "Query " . ($index + 1) . ": " . $con->error;
        if ($errors <= 5) {
            echo "✗ Error in query " . ($index + 1) . ": " . $con->error . "\n";
        }
    }
}

// Re-enable foreign key checks
$con->query('SET FOREIGN_KEY_CHECKS=1');

echo "\n" . str_repeat("=", 60) . "\n";
echo "DATABASE IMPORT COMPLETED!\n";
echo str_repeat("=", 60) . "\n";
echo "✓ Successful queries: $success\n";
echo "✗ Failed queries: $errors\n";

if ($errors > 0 && $errors <= 5) {
    echo "\nError details:\n";
    foreach ($errorMessages as $msg) {
        echo "  - $msg\n";
    }
} elseif ($errors > 5) {
    echo "\n(Showing first 5 errors only)\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "NEXT STEPS:\n";
echo str_repeat("=", 60) . "\n";
echo "1. Delete this file (setup-database.php) for security\n";
echo "2. Visit your application: https://web-production-08e8e.up.railway.app\n";
echo "3. Login with default admin credentials:\n";
echo "   Username: admin\n";
echo "   Password: password\n";
echo "4. Change the admin password immediately!\n";
echo "\n";

$con->close();

echo "</pre>";
echo "<p><strong>Setup completed! Please delete this file now.</strong></p>";
?>
