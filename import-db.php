<?php
// Simple database import script for Railway
// Access: https://deployoes-production.up.railway.app/import-db.php

$con = require_once('Connections/OES.php');

echo "<h1>Database Import</h1>";
echo "<pre>";

// Read SQL file
$sqlFile = 'database/oes_professional.sql';
if (!file_exists($sqlFile)) {
    die("SQL file not found: $sqlFile\n");
}

echo "Found SQL file: $sqlFile\n";
echo "File size: " . round(filesize($sqlFile) / 1024, 2) . " KB\n\n";

// Read and execute SQL
$sql = file_get_contents($sqlFile);

// Remove comments
$sql = preg_replace('/--.*$/m', '', $sql);
$sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

// Split into queries
$queries = array_filter(array_map('trim', explode(';', $sql)));

echo "Processing " . count($queries) . " SQL statements...\n\n";

$success = 0;
$errors = 0;

// Disable foreign key checks
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

echo "\n<strong>IMPORTANT: Delete this file (import-db.php) after import!</strong>\n";
echo "</pre>";

$con->close();
?>
