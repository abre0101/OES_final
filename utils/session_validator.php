<?php
/**
 * Session Validator Utility
 * Ensures proper role-based access control across the application
 */

if (!isset($_SESSION)) {
    session_start();
}

/**
 * Validate user session and role
 * @param string $requiredRole The role required to access the page (Student, Instructor, Administrator, DepartmentHead)
 * @param string $redirectUrl The URL to redirect to if validation fails
 */
function validateUserSession($requiredRole, $redirectUrl = '../index.php') {
    // Check if user is logged in
    if (!isset($_SESSION['Name']) || !isset($_SESSION['ID'])) {
        session_destroy();
        header("Location: $redirectUrl");
        exit();
    }
    
    // Check if UserType is set and matches required role
    if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== $requiredRole) {
        // Log the unauthorized access attempt
        error_log("Unauthorized access attempt: User {$_SESSION['Name']} (Type: " . ($_SESSION['UserType'] ?? 'none') . ") tried to access $requiredRole page");
        
        // Destroy session and redirect
        session_destroy();
        header("Location: $redirectUrl");
        exit();
    }
    
    return true;
}

/**
 * Get current user role
 * @return string|null The current user's role or null if not set
 */
function getCurrentUserRole() {
    return $_SESSION['UserType'] ?? null;
}

/**
 * Check if user has specific role
 * @param string $role The role to check
 * @return bool True if user has the role, false otherwise
 */
function hasRole($role) {
    return isset($_SESSION['UserType']) && $_SESSION['UserType'] === $role;
}
?>
