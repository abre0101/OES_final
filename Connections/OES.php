<?php
// Database connection configuration
// Support both Railway (production) and local development

// Try MYSQL_PUBLIC_URL first (accessible), then MYSQL_URL (internal network)
$mysql_url = getenv('MYSQL_PUBLIC_URL') ?: getenv('MYSQL_URL');

if ($mysql_url) {
    // Parse the MySQL URL (format: mysql://user:pass@host:port/database)
    $url_parts = parse_url($mysql_url);
    $hostname_OES = $url_parts['host'];
    $username_OES = $url_parts['user'];
    $password_OES = $url_parts['pass'];
    $database_OES = ltrim($url_parts['path'], '/');
    $port_OES = $url_parts['port'] ?? 3306;
} else {
    // Fallback to individual environment variables or local defaults
    $hostname_OES = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: 'localhost';
    $database_OES = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'oes_professional';
    $username_OES = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
    $password_OES = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';
    $port_OES = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: 3306;
}

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
