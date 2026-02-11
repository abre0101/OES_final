<?php
// Database connection configuration
// Supports both local development and Railway deployment

// Check if running on Railway (environment variables set)
// Railway can use either MYSQL* or DB_* variable names
if (getenv('MYSQLHOST') || getenv('DB_HOST')) {
    // Railway MySQL configuration
    $hostname_OES = getenv('MYSQLHOST') ?: getenv('DB_HOST');
    $database_OES = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'railway';
    $username_OES = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
    $password_OES = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: '';
    $port_OES = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306;
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
