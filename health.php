<?php
header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
];

// Check database connection
try {
    $con = require_once(__DIR__ . "/Connections/OES.php");
    if ($con && !$con->connect_error) {
        $health['database'] = 'connected';
        $con->close();
    } else {
        $health['database'] = 'disconnected';
        $health['status'] = 'unhealthy';
    }
} catch (Exception $e) {
    $health['database'] = 'error';
    $health['database_error'] = $e->getMessage();
    $health['status'] = 'unhealthy';
}

http_response_code($health['status'] === 'healthy' ? 200 : 503);
echo json_encode($health, JSON_PRETTY_PRINT);
?>
