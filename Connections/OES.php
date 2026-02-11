<?php
// Database connection configuration
// Supports both local development and Railway deployment

// Check if running on Railway (environment variables set)
// Railway provides multiple variable formats: MYSQL_*, MYSQLHOST, DB_*
if (getenv('MYSQL_HOST') || getenv('MYSQLHOST') || getenv('DB_HOST')) {
    // Railway MySQL configuration - try all possible variable names
    $hostname_OES = getenv('MYSQL_HOST') ?: getenv('MYSQLHOST') ?: getenv('DB_HOST');
    $database_OES = getenv('MYSQL_DATABASE') ?: getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'railway';
    $username_OES = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
    $password_OES = getenv('MYSQL_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: '';
    $port_OES = getenv('MYSQL_PORT') ?: getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306;
} else {
    // Local development configuration
    $hostname_OES = 'localhost';
    $database_OES = 'oes_professional';
    $username_OES = 'root';
    $password_OES = '';
    $port_OES = 3306;
}

// Create connection
$con = new mysqli($hostname_OES, $username_OES, $password_OES, $database_OES, $port_OES);

// Check connection
if ($con->connect_error) {
    // Log error for debugging (in production, log to file instead of displaying)
    error_log("Database connection failed: " . $con->connect_error);
    die("Connection failed. Please check database configuration.");
}

// Set charset to utf8mb4 for better Unicode support
$con->set_charset("utf8mb4");
// Return connection object
return $con;
?>
