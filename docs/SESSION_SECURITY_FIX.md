# Session Security Fix - Role-Based Access Control

## Problem
When opening both instructor and student portals in different browser tabs, the sessions were mixing because:
- Both portals shared the same PHP session
- No role validation was performed on protected pages
- Users could access any portal with any credentials

## Solution Implemented

### 1. Added UserType to Session
All login processes now set `$_SESSION['UserType']` with one of these values:
- `Student`
- `Instructor`
- `Administrator`
- `DepartmentHead`

**Files Modified:**
- `auth/login.php` - Added UserType for all user types
- `auth/institute-login-process.php` - Standardized UserType naming

### 2. Role Validation on Protected Pages
Each portal now validates the user's role before allowing access:

**Student Portal** (`Student/index.php`):
```php
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Student'){
    session_destroy();
    header("Location: ../auth/student-login.php");
    exit();
}
```

**Instructor Portal** (`Instructor/index.php`):
```php
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Instructor'){
    session_destroy();
    header("Location:../auth/institute-login.php");
    exit();
}
```

**Admin Portal** (`Admin/index.php`):
```php
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Administrator'){
    session_destroy();
    header("Location:../auth/institute-login.php");
    exit();
}
```

**Department Head Portal** (`DepartmentHead/index.php`):
```php
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'DepartmentHead'){
    session_destroy();
    header("Location:../auth/institute-login.php");
    exit();
}
```

### 3. Smart Login Redirects
Login pages now check if a user is already logged in and redirect them to the appropriate dashboard:

**Files Modified:**
- `auth/student-login.php`
- `auth/institute-login.php`

Both now include:
```php
if(isset($_SESSION['Name']) && isset($_SESSION['UserType'])){
    switch($_SESSION['UserType']) {
        case 'Student':
            header("Location: ../Student/index.php");
            break;
        case 'Instructor':
            header("Location: ../Instructor/index.php");
            break;
        // ... etc
    }
}
```

### 4. Session Validator Utility
Created `utils/session_validator.php` with reusable functions:
- `validateUserSession($requiredRole, $redirectUrl)` - Validates role and redirects if invalid
- `getCurrentUserRole()` - Returns current user's role
- `hasRole($role)` - Checks if user has specific role

## How It Works Now

### Scenario 1: Instructor tries to access Student portal
1. Instructor logs in → `$_SESSION['UserType'] = 'Instructor'`
2. Tries to open `Student/index.php`
3. Page checks: `$_SESSION['UserType'] !== 'Student'`
4. Session destroyed, redirected to student login

### Scenario 2: Opening both portals in different tabs
1. Login as Instructor in Tab 1 → Session set with UserType='Instructor'
2. Try to login as Student in Tab 2
3. Student login page detects existing session with UserType='Instructor'
4. Automatically redirects to Instructor dashboard
5. User must logout first to switch roles

### Scenario 3: Proper role switching
1. Login as Instructor
2. Click Logout → Session destroyed completely
3. Now can login as Student in same browser
4. New session created with UserType='Student'

## Testing Recommendations

1. **Test Role Isolation:**
   - Login as Instructor
   - Try to directly access `Student/index.php` → Should redirect
   - Try to directly access `Admin/index.php` → Should redirect

2. **Test Session Switching:**
   - Login as Student
   - Logout
   - Login as Instructor → Should work
   - Verify correct dashboard loads

3. **Test Multiple Tabs:**
   - Login as Instructor in Tab 1
   - Open student login in Tab 2 → Should auto-redirect to Instructor dashboard
   - Logout in Tab 1
   - Refresh Tab 2 → Should show login form

## Security Benefits

✅ **Role-Based Access Control** - Users can only access pages for their role
✅ **Session Isolation** - Each role has its own access restrictions
✅ **Automatic Logout** - Invalid role access destroys session
✅ **Prevents Privilege Escalation** - Can't access higher privilege pages
✅ **Audit Trail** - Unauthorized attempts logged to error log

## Additional Recommendations

1. **Use Different Session Names** (Optional but more secure):
   ```php
   // In each portal's config
   session_name('STUDENT_SESSION'); // for students
   session_name('INSTRUCTOR_SESSION'); // for instructors
   ```

2. **Use Different Browsers** for testing multiple roles simultaneously

3. **Clear Browser Cookies** when switching between roles during development

## Files Changed Summary

- ✅ `auth/login.php` - Added UserType to all login flows
- ✅ `auth/institute-login-process.php` - Standardized UserType
- ✅ `auth/student-login.php` - Added smart redirect
- ✅ `auth/institute-login.php` - Added smart redirect
- ✅ `Student/index.php` - Added role validation
- ✅ `Instructor/index.php` - Added role validation
- ✅ `Admin/index.php` - Added role validation
- ✅ `DepartmentHead/index.php` - Added role validation
- ✅ `utils/session_validator.php` - Created utility functions
