# Add User Testing Checklist

## Before Testing
1. ✅ Clear browser cache (Ctrl+Shift+Delete)
2. ✅ Hard refresh the page (Ctrl+F5)
3. ✅ Open browser console (F12)

## Test Case 1: Add Student
1. Click "Add User" button
2. Fill in the form:
   - **School ID**: `2024001`
   - **Full Name**: `John Doe`
   - **Role**: Select `Student`
   - **Year Level**: Select `1st Year`
   - **Section**: Select `A`
3. Click "Create User"

### Expected Console Output:
```
📋 Form field values: {schoolId: "2024001", fullName: "John Doe", role: "student", yearLevel: "1", section: "A"}
🟢 Creating user with data: {school_id: "2024001", full_name: "John Doe", role: "student", year_level: "1", section: "A"}
🔍 Validation result: {isValid: true, errors: []}
📦 Sending user data: {user_id: null, school_id: "2024001", full_name: "John Doe", role: "student", year_level: "1", section: "A"}
🔵 POST Request: /exam-main/public/admin/users/add {user_id: null, school_id: "2024001", ...}
📤 FormData entries: [["user_id", "null"], ["school_id", "2024001"], ...]
📥 Server response: {success: true, message: "User created successfully!", user_id: 123}
```

### Expected Result:
- ✅ Success toast appears
- ✅ Page reloads
- ✅ New student appears in the dashboard

## Test Case 2: Add Faculty
1. Click "Add User" button
2. Fill in the form:
   - **School ID**: `FAC001`
   - **Full Name**: `Jane Smith`
   - **Role**: Select `Faculty`
3. Click "Create User"

### Expected Console Output:
```
📋 Form field values: {schoolId: "FAC001", fullName: "Jane Smith", role: "faculty", yearLevel: null, section: null}
🟢 Creating user with data: {school_id: "FAC001", full_name: "Jane Smith", role: "faculty", year_level: null, section: null}
🔍 Validation result: {isValid: true, errors: []}
...
📥 Server response: {success: true, message: "User created successfully!", user_id: 124}
```

### Expected Result:
- ✅ Success toast appears
- ✅ Page reloads
- ✅ New faculty member appears in the dashboard

## Test Case 3: Validation Error (Empty Form)
1. Click "Add User" button
2. Select role "Student" but leave other fields empty
3. Click "Create User"

### Expected Console Output:
```
📋 Form field values: {schoolId: "", fullName: "", role: "student", yearLevel: "", section: ""}
🟢 Creating user with data: {school_id: "", full_name: "", role: "student", year_level: "", section: ""}
🔍 Validation result: {isValid: false, errors: ["School ID is required", "Full name is required", "Year level is required for students", "Section is required for students"]}
```

### Expected Result:
- ✅ Error toast appears with validation messages
- ✅ No server request is made
- ✅ Form stays open

## Test Case 4: Duplicate School ID
1. Try to add a user with an existing School ID
2. Click "Create User"

### Expected Console Output:
```
📥 Server response: {success: false, message: "School ID already exists."}
```

### Expected Result:
- ✅ Error toast appears: "School ID already exists."
- ✅ Form stays open

## Server-Side Verification

Check PHP error logs (usually in `storage/logs` or Apache/PHP error log):

```
=== ADD USER REQUEST ===
POST data: {"school_id":"2024001","full_name":"John Doe","role":"student","year_level":"1","section":"A"}
Result: {"success":true,"message":"User created successfully!","user_id":123,"default_password":"2024001John Doe"}
```

## Troubleshooting

### If form data is empty:
- Check console for "📋 Form field values" - all should have values
- Verify you filled in all required fields
- Check that JavaScript files are loaded (no 404 errors in Network tab)

### If validation fails:
- Check "🔍 Validation result" in console
- Ensure all required fields are filled
- For students: Year level and section are required

### If no server response:
- Check Network tab (F12 → Network)
- Look for the POST request to `/admin/users/add`
- Check response status (should be 200)
- Check response body (should be JSON)

### If page doesn't reload:
- Check for JavaScript errors in console
- Verify success toast appears
- Check that `location.reload()` is called after 1 second

## Success Criteria

All tests pass when:
1. ✅ Form fields are read correctly
2. ✅ Client-side validation works
3. ✅ AJAX request is sent with correct data
4. ✅ Server returns JSON response
5. ✅ Success/error toasts appear
6. ✅ Page reloads on success
7. ✅ New user appears in the list
