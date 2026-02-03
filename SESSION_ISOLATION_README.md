# Session Isolation Implementation

## Overview
This system now implements **session isolation by user type**, allowing different user types to be logged in simultaneously in different browser tabs.

## How It Works

### Session Names
Each user type has its own unique session name:
- **Students**: `OES_STUDENT_SESSION`
- **Instructors**: `OES_INSTRUCTOR_SESSION`
- **Administrators**: `OES_ADMIN_SESSION`
- **Department Heads**: `OES_DEPT_HEAD_SESSION`

### Benefits
1. **Multiple User Types**: You can now have one Student, one Instructor, one Admin, and one Department Head logged in at the same time in different tabs
2. **Testing**: Makes it easier to test different user roles without logging out
3. **Isolation**: Each session is completely isolated from the others

### Limitations
- You still **cannot** have two users of the same type logged in simultaneously (e.g., two different students)
- Sessions are still shared within the same browser for the same user type
- To test multiple users of the same type, use:
  - Different browsers (Chrome, Firefox, Edge)
  - Incognito/Private windows
  - Different browser profiles

## Files Modified

### New Files
- `utils/session_manager.php` - Central session management class
- `test-session-isolation.php` - Test page to verify session isolation

### Updated Files
- `auth/login.php` - Student login process
- `auth/institute-login-process.php` - Institute login process
- `auth/institute-login.php` - Institute login page
- `auth/student-login.php` - Student login page
- `Admin/index.php` - Admin dashboard
- `Admin/Logout.php` - Admin logout
- `Instructor/index.php` - Instructor dashboard
- `Instructor/Logout.php` - Instructor logout
- `Student/index.php` - Student dashboard
- `Student/Logout.php` - Student logout
- `DepartmentHead/index.php` - Department Head dashboard
- `DepartmentHead/Logout.php` - Department Head logout
- `AboutUs.php` - About page
- `Help.php` - Help page

## Usage

### For Developers
All session management should now use the `SessionManager` class:

```php
// Start a session for a specific user type
require_once(__DIR__ . "/utils/session_manager.php");
SessionManager::startSession('Instructor');

// Destroy a session
SessionManager::destroySession();

// Check if logged in
if (SessionManager::isLoggedIn()) {
    // User is logged in
}

// Get user type
$userType = SessionManager::getUserType();

// Redirect to appropriate dashboard
SessionManager::redirectToDashboard();
```

### Testing Session Isolation
1. Open your browser and navigate to `test-session-isolation.php`
2. This page shows all active sessions for each user type
3. Open multiple tabs and log in as different user types
4. Refresh the test page to see all active sessions

### Example Test Scenario
1. **Tab 1**: Log in as a Student at `auth/student-login.php`
2. **Tab 2**: Log in as an Instructor at `auth/institute-login.php`
3. **Tab 3**: Log in as an Admin at `auth/institute-login.php`
4. **Tab 4**: Open `test-session-isolation.php` to see all three sessions active

All three users will remain logged in simultaneously!

## Technical Details

### Session Cookie Names
Each user type gets its own session cookie:
- `OES_STUDENT_SESSION` cookie for students
- `OES_INSTRUCTOR_SESSION` cookie for instructors
- `OES_ADMIN_SESSION` cookie for administrators
- `OES_DEPT_HEAD_SESSION` cookie for department heads

### Backward Compatibility
The system maintains backward compatibility with existing code. All session variables (`$_SESSION['ID']`, `$_SESSION['Name']`, etc.) work exactly as before.

## Troubleshooting

### Issue: Still getting logged out when logging in as another user type
**Solution**: Clear your browser cookies and try again. Old session cookies might be interfering.

### Issue: Session not persisting
**Solution**: Make sure all files that use sessions have been updated to use `SessionManager::startSession()` instead of `session_start()`.

### Issue: Cannot log in
**Solution**: Check that the `utils/session_manager.php` file exists and is accessible from the login files.

## Security Notes
- Each session is isolated and secure
- Session cookies are specific to each user type
- Logging out destroys only the current user type's session
- Other user type sessions remain active
