<?php
// Database connection configuration
// Use environment variables for Railway deployment, fallback to local values
$hostname_OES = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: "localhost";
$database_OES = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: "oes_professional";
$username_OES = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: "root";
$password_OES = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: "";
$port_OES = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: "3306";

// Create connection
$con = new mysqli($hostname_OES, $username_OES, $password_OES, $database_OES, $port_OES);

// Check connection
if ($con->connect_error) {
    error_log("Database connection failed: " . $con->connect_error);
    die("Connection failed. Please check your database configuration.");
}

// Set charset to utf8
$con->set_charset("utf8mb4");

// Return connection object
return $con;
?>
