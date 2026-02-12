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

// Drop existing database and recreate
echo "Dropping existing tables...\n";
$con->query('SET FOREIGN_KEY_CHECKS=0');

// Get all tables
$tables = [];
$result = $con->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
}

// Drop all tables
foreach ($tables as $table) {
    $con->query("DROP TABLE IF EXISTS `$table`");
    echo "✓ Dropped table: $table\n";
}

echo "\nImporting database...\n\n";

// Read SQL content
$sql = file_get_contents($sqlFile);

$success = 0;
$errors = 0;

$con->query('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"');
$con->query('SET time_zone = "+00:00"');

// Execute the entire SQL file at once using multi_query
if ($con->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $con->store_result()) {
            $result->free();
        }
        
        // Check for errors
        if ($con->errno) {
            $errors++;
            if ($errors <= 10) {
                echo "✗ Error: " . $con->error . "\n";
            }
        } else {
            $success++;
            if ($success % 20 == 0) {
                echo "✓ Processed $success queries...\n";
                flush();
            }
        }
        
        // Move to next result
        if ($con->more_results()) {
            $con->next_result();
        } else {
            break;
        }
    } while (true);
}

// Check for final error
if ($con->errno) {
    echo "✗ Final error: " . $con->error . "\n";
}

// Re-enable foreign key checks
$con->query('SET FOREIGN_KEY_CHECKS=1');

echo "\n" . str_repeat("=", 60) . "\n";
echo "DATABASE IMPORT COMPLETED!\n";
echo str_repeat("=", 60) . "\n";
echo "✓ Successful operations: $success\n";
if ($errors > 0) {
    echo "✗ Errors encountered: $errors\n";
}

echo "\n<strong>IMPORTANT: Delete this file (import-db.php) after import!</strong>\n";
echo "\n<a href='/'>Go to Application</a>\n";
echo "</pre>";

$con->close();
?>
