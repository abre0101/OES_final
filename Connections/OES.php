<?php
// Database connection configuration
// Support both Railway (production) and local development

// Railway environment variables (production)
$hostname_OES = getenv('MYSQL_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
$database_OES = getenv('MYSQL_DATABASE') ?: getenv('MYSQLDATABASE') ?: 'oes_professional';
$username_OES = getenv('MYSQL_USER') ?: getenv('MYSQLUSER') ?: 'root';
$password_OES = getenv('MYSQL_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';
$port_OES = getenv('MYSQL_PORT') ?: getenv('MYSQLPORT') ?: 3306;

// Create connection with port support
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
