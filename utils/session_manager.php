<?php
/**
 * Session Manager - Handles session isolation by user type
 * This allows different user types to be logged in simultaneously in different tabs
 */

class SessionManager {
    // Session names for different user types
    const SESSION_STUDENT = 'OES_STUDENT_SESSION';
    const SESSION_INSTRUCTOR = 'OES_INSTRUCTOR_SESSION';
    const SESSION_ADMIN = 'OES_ADMIN_SESSION';
    const SESSION_DEPT_HEAD = 'OES_DEPT_HEAD_SESSION';
    
    /**
     * Start session for a specific user type
     * @param string $userType - 'Student', 'Instructor', 'Administrator', or 'DepartmentHead'
     */
    public static function startSession($userType = null) {
        // If session is already started, close it first
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        
        // Determine session name based on user type
        $sessionName = self::getSessionName($userType);
        
        // Set the session name and start
        session_name($sessionName);
        session_start();
    }
    
    /**
     * Get session name for a user type
     * @param string $userType
     * @return string
     */
    private static function getSessionName($userType) {
        if ($userType === null && isset($_SESSION['UserType'])) {
            $userType = $_SESSION['UserType'];
        }
        
        switch ($userType) {
            case 'Student':
                return self::SESSION_STUDENT;
            case 'Instructor':
                return self::SESSION_INSTRUCTOR;
            case 'Administrator':
                return self::SESSION_ADMIN;
            case 'DepartmentHead':
                return self::SESSION_DEPT_HEAD;
            default:
                // Default session for login pages
                return 'OES_DEFAULT_SESSION';
        }
    }
    
    /**
     * Destroy session for current user type
     */
    public static function destroySession() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = array();
            
            // Delete session cookie
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }
            
            session_destroy();
        }
    }
    
    /**
     * Check if user is logged in
     * @return bool
     */
    public static function isLoggedIn() {
        return isset($_SESSION['ID']) && isset($_SESSION['UserType']);
    }
    
    /**
     * Get current user type
     * @return string|null
     */
    public static function getUserType() {
        return $_SESSION['UserType'] ?? null;
    }
    
    /**
     * Redirect to appropriate dashboard based on user type
     */
    public static function redirectToDashboard() {
        if (!self::isLoggedIn()) {
            return;
        }
        
        $basePath = self::getBasePath();
        
        switch (self::getUserType()) {
            case 'Student':
                header("Location: {$basePath}Student/index.php");
                exit();
            case 'Instructor':
                header("Location: {$basePath}Instructor/index.php");
                exit();
            case 'Administrator':
                header("Location: {$basePath}Admin/index.php");
                exit();
            case 'DepartmentHead':
                header("Location: {$basePath}DepartmentHead/index.php");
                exit();
        }
    }
    
    /**
     * Get base path relative to current file
     * @return string
     */
    private static function getBasePath() {
        // Determine if we're in a subdirectory
        $scriptPath = $_SERVER['SCRIPT_NAME'];
        $pathParts = explode('/', $scriptPath);
        
        // Count how many levels deep we are
        $depth = count($pathParts) - 2; // -2 for empty first element and filename
        
        if ($depth <= 1) {
            return './';
        } else {
            return '../';
        }
    }
}
