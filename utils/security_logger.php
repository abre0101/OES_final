<?php
/**
 * Security Logger Utility
 * Logs security events to the security_logs table
 */

function logSecurityEvent($user_id, $user_type, $action, $status, $details = '') {
    try {
        $con = require(__DIR__ . "/../Connections/OES.php");
        
        // Get user's IP address
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        // Get user agent
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        // Prepare and execute insert
        $stmt = $con->prepare("INSERT INTO security_logs (user_id, user_type, action, ip_address, user_agent, is_active, details) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $user_id, $user_type, $action, $ip_address, $user_agent, $status, $details);
        $stmt->execute();
        $stmt->close();
        $con->close();
        
        return true;
    } catch (Exception $e) {
        // Silently fail - don't break the application if logging fails
        error_log("Security logging failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Log successful login
 */
function logSuccessfulLogin($user_id, $user_type) {
    return logSecurityEvent($user_id, $user_type, 'Login Successful', 'success', 'User logged in successfully');
}

/**
 * Log failed login
 */
function logFailedLogin($user_id, $user_type, $reason = 'Invalid credentials') {
    return logSecurityEvent($user_id, $user_type, 'Login Failed', 'failed', $reason);
}

/**
 * Log logout
 */
function logLogout($user_id, $user_type) {
    return logSecurityEvent($user_id, $user_type, 'Logout', 'success', 'User logged out');
}

/**
 * Log data modification
 */
function logDataModification($user_id, $user_type, $action, $details) {
    return logSecurityEvent($user_id, $user_type, $action, 'success', $details);
}

/**
 * Log suspicious activity
 */
function logSuspiciousActivity($user_id, $user_type, $action, $details) {
    return logSecurityEvent($user_id, $user_type, $action, 'warning', $details);
}
?>
