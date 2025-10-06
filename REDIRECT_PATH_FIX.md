{{ ... }}

## Problem
When running the server with `php -S localhost:8000 -t public`, users experienced:
1. Need to access `/public/login` instead of just `/login`
2. `ERR_CACHE_MISS` error after login
3. Invalid redirect URLs like `http://login/`

## Root Cause
The application was using `dirname($_SERVER['SCRIPT_NAME'])` to calculate base paths for redirects. When the server is run with `-t public` (document root set to public directory), this calculation returns an empty string or incorrect path, causing malformed redirect URLs.

## Solution
Replaced all dynamic base path calculations with absolute paths starting with `/`. Since the router already handles path normalization (lines 66-81 in Router.php), we don't need to manually calculate base paths.

## Files Modified

### 1. public/index.php
**Line 30-33**: Root route redirect
```php
// Before
$basePath = dirname($_SERVER['SCRIPT_NAME']);
header('Location: ' . $basePath . '/login');

// After
header('Location: /login');
```

### 2. src/App/Controllers/Auth/AuthController.php
**Lines 105-126**: `redirectToDashboard()` method
- Removed `$basePath` calculation
- Changed all redirects to use absolute paths:
  - `/admin/dashboard`
  - `/faculty/dashboard`
  - `/student-success`
  - `/login`

### 3. src/App/Controllers/Student/StudentDashboardController.php
**Lines 37, 169-215**: Authentication checks and logout
- Changed `header('Location: ' . dirname($_SERVER['SCRIPT_NAME']) . '/login')` to `header('Location: /login')`
- Fixed all 3 instances in logout method

### 4. src/App/Controllers/Student/ExamTakingController.php
**Lines 35, 42, 55, 108, 121, 139, 143, 150, 159, 202**: All redirects
- Fixed 10 instances of redirect URLs:
  - `/student/dashboard`
  - `/student-success`
  - `/student/exam-result/{id}`

### 5. src/App/Controllers/Faculty/FacultyController.php
**Lines 42, 92, 450-464**: Authentication and logout
- Fixed 3 instances:
  - `/login`
  - `/faculty/dashboard`

### 6. src/App/Controllers/Admin/AdminController.php
**Lines 156-168**: Logout method
- Fixed 2 instances:
  - `/login`
  - `/admin/dashboard`

## How to Run the Server

### Correct Method (Recommended)
```bash
php -S localhost:8000 -t public
```
or
```bash
php start_server.php
```

### What This Does
- Sets the document root to the `public` directory
- Makes `localhost:8000` directly serve `public/index.php`
- All routes work with clean URLs (e.g., `/login`, `/admin/dashboard`)

## Testing Checklist

- [x] Access `localhost:8000` redirects to login page
- [x] Login form submits to correct API endpoint
- [x] Login as admin redirects to `/admin/dashboard`
- [x] Login as faculty redirects to `/faculty/dashboard`
- [x] Login as student redirects to `/student-success`
- [x] No `ERR_CACHE_MISS` errors after login
- [x] Logout redirects work correctly
- [x] All student exam navigation works
- [x] All faculty exam management works
- [x] All admin user management works
- [x] All JavaScript/CSS assets load correctly
- [x] All view files use absolute paths

## Technical Details

### Why This Works
1. **Router Path Normalization**: The `Router` class (lines 66-81) already strips base paths from URLs
2. **Absolute Paths**: Using `/login` instead of calculating paths ensures consistent behavior
3. **Document Root**: With `-t public`, the server treats `public/` as the root directory

### Previous Issues
- `dirname($_SERVER['SCRIPT_NAME'])` returned `/public` or empty string
- Concatenating this with `/login` created invalid URLs
- Browser couldn't resolve `http://login/` (missing host)
- POST-redirect-GET pattern failed, causing `ERR_CACHE_MISS`

## Benefits
✅ Clean, predictable URLs
✅ No more path calculation errors
✅ Consistent behavior across all environments
✅ Follows standard PHP application structure
✅ Easier to maintain and debug
