<?php
/**
 * Get normalized user type for audit logging
 * Converts session UserType to lowercase format for consistency
 */
function getUserTypeForAudit() {
    if (!isset($_SESSION['UserType'])) {
        return 'unknown';
    }
    
    $userType = $_SESSION['UserType'];
    
    // Normalize the user type
    switch($userType) {
        case 'Administrator':
            return 'admin';
        case 'Instructor':
            return 'instructor';
        case 'Student':
            return 'student';
        case 'DepartmentHead':
            return 'department_head';
        default:
            return 'unknown';
    }
}
?>
