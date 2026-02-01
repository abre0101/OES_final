<?php
/**
 * Security Logger Utility
 * Use this to log security events throughout the system
 */

function logSecurityEvent($userId, $userType, $action, $is_active, $details = '') {
    $con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
    
    // Create table if doesn't exist
    $createTableSQL = "CREATE TABLE IF NOT EXISTS `security_logs` (
        `log_id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` VARCHAR(50),
        `user_type` VARCHAR(20),
        `action` VARCHAR(100),
        `ip_address` VARCHAR(45),
        `user_agent` TEXT,
        `is_active` VARCHAR(20),
        `details` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_user` (`user_id`),
        INDEX `idx_action` (`action`),
        INDEX `idx_date` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $con->query($createTableSQL);
    
    // Get IP address
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    // Get user agent
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    // Insert log
    $stmt = $con->prepare("INSERT INTO security_logs (user_id, user_type, action, ip_address, user_agent, is_active, details) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $userId, $userType, $action, $ipAddress, $userAgent, $is_active, $details);
    $stmt->execute();
    $stmt->close();
    $con->close();
}

// Example usage:
// logSecurityEvent('admin123', 'admin', 'Login Attempt', 'success', 'Successful login from dashboard');
// logSecurityEvent('student456', 'student', 'Login Attempt', 'failed', 'Invalid password');
// logSecurityEvent('inst789', 'instructor', 'Password Change', 'success', 'Password updated successfully');
?>
