# 🐛 Debug: Buttons Not Working

## 🔍 **Step-by-Step Debugging**

### **Step 1: Open Browser Console**

1. Open any admin page (e.g., `http://localhost:8000/admin/manage-users`)
2. Press **F12** to open Developer Tools
3. Go to **Console** tab

---

### **Step 2: Check for Errors**

Look for these types of errors:

#### **❌ Type 1: 404 Errors (Files Not Found)**
```
GET http://localhost:8000/js/services/APIService.js 404 (Not Found)
```

**This means:** JavaScript files aren't loading

**Fix:** Path issue - see Fix #1 below

---

#### **❌ Type 2: Undefined Function**
```
Uncaught ReferenceError: deleteStudent is not defined
```

**This means:** MVC didn't initialize properly

**Fix:** See Fix #2 below

---

#### **❌ Type 3: Controller Not Found**
```
Cannot read property 'deleteStudent' of undefined
```

**This means:** Controller didn't load

**Fix:** See Fix #3 below

---

### **Step 3: Test Controller Loading**

In the console, type:

```javascript
// Test 1: Check if window has controllers
console.log('User Controller:', window.userController);
console.log('Assignment Controller:', window.assignmentController);
console.log('Subject Controller:', window.subjectController);

// Test 2: Check if classes exist
console.log('ManageUsersController:', typeof ManageUsersController);
console.log('APIService:', typeof APIService);
```

**Expected Output:**
```
User Controller: ManageUsersController {service: ..., view: ...}
Assignment Controller: AssignmentManagementController {...}
Subject Controller: SubjectManagementController {...}
ManageUsersController: "function"
APIService: "function"
```

**If you see `undefined`:** MVC files didn't load

---

## 🔧 **Fixes**

### **Fix #1: Correct the Script Paths**

The issue is likely the base path. Update your PHP files to use absolute paths:

#### **Current (might be wrong):**
```php
<script src="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/js/services/APIService.js"></script>
```

#### **Fix Option A: Use /exam-main/public/js/**
```php
<script src="/exam-main/public/js/services/APIService.js"></script>
```

#### **Fix Option B: Use Relative Path**
```php
<script src="../js/services/APIService.js"></script>
```

#### **Fix Option C: Dynamic Base Path**
```php
<?php
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<script src="<?= $basePath ?>/js/services/APIService.js"></script>
```

---

### **Fix #2: Check Script Load Order**

Scripts must load in this order:

1. ✅ Services (APIService first)
2. ✅ Models
3. ✅ Views (if any)
4. ✅ Controllers
5. ✅ MVC Initializer (last)

**Current order in manage-users.php:**
```php
<!-- 1. Services -->
<script src=".../APIService.js"></script>
<script src=".../UserManagementService.js"></script>

<!-- 2. Models -->
<script src=".../User.js"></script>

<!-- 3. Controllers -->
<script src=".../ManageUsersController.js"></script>

<!-- 4. Initializer -->
<script src=".../manage-users-mvc.js"></script>
```

This order is ✅ **CORRECT**

---

### **Fix #3: Add Error Handling to MVC Initializer**

Update `manage-users-mvc.js` to show errors:

```javascript
document.addEventListener('DOMContentLoaded', function() {
    try {
        console.log('🔄 Initializing Manage Users MVC...');
        
        // Check if classes exist
        if (typeof APIService === 'undefined') {
            throw new Error('APIService not loaded');
        }
        if (typeof UserManagementService === 'undefined') {
            throw new Error('UserManagementService not loaded');
        }
        if (typeof ManageUsersController === 'undefined') {
            throw new Error('ManageUsersController not loaded');
        }
        
        // Initialize
        const apiService = new APIService();
        const userService = new UserManagementService(apiService);
        const userView = {};
        const userController = new ManageUsersController(userService, userView);
        
        // Initialize data
        if (typeof studentsData !== 'undefined' && typeof facultyData !== 'undefined') {
            userController.initializeData(studentsData, facultyData);
        }
        
        // Make global
        window.userController = userController;
        
        console.log('✅ Manage Users MVC initialized successfully');
        console.log('Controller:', window.userController);
        
    } catch (error) {
        console.error('❌ MVC Initialization Error:', error);
        alert('Error loading page. Check console for details.');
    }
});
```

---

## 🧪 **Quick Test Script**

Add this to the bottom of manage-users.php temporarily:

```php
<script>
// Diagnostic script
console.log('=== DIAGNOSTIC START ===');
console.log('Current URL:', window.location.href);
console.log('Script path:', '<?= dirname($_SERVER['SCRIPT_NAME']) ?>');
console.log('Base path:', window.location.pathname.split('/').slice(0, -1).join('/'));

// Check if scripts loaded
setTimeout(() => {
    console.log('APIService loaded?', typeof APIService !== 'undefined');
    console.log('UserManagementService loaded?', typeof UserManagementService !== 'undefined');
    console.log('ManageUsersController loaded?', typeof ManageUsersController !== 'undefined');
    console.log('User Controller exists?', typeof window.userController !== 'undefined');
    console.log('=== DIAGNOSTIC END ===');
}, 1000);
</script>
```

---

## 📊 **Common Scenarios**

### **Scenario 1: All Scripts Show 404**

**Problem:** Base path is wrong

**Solution:** 
```php
<!-- Change from: -->
<script src="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/js/..."></script>

<!-- To: -->
<script src="/js/..."></script>
```

---

### **Scenario 2: Scripts Load But Controller Undefined**

**Problem:** Initialization didn't run

**Solution:** Check if `DOMContentLoaded` fired:
```javascript
console.log('DOM loaded?', document.readyState);
```

---

### **Scenario 3: Controller Exists But Functions Don't Work**

**Problem:** Global function wrappers not working

**Solution:** Check onclick attributes in HTML:
```html
<!-- Should be: -->
<button onclick="deleteStudent(123)">Delete</button>

<!-- NOT: -->
<button onclick="window.userController.deleteStudent(123)">Delete</button>
```

---

## 🎯 **Action Plan**

### **Do This Now:**

1. **Open manage-users.php in browser**
2. **Press F12 → Console**
3. **Look for errors**
4. **Copy/paste ALL errors here**
5. **Run the test scripts above**
6. **Tell me what you see**

---

## 💡 **Most Likely Issue**

Based on your setup, the most likely issue is:

### **Path Problem**

Your scripts are trying to load from:
```
http://localhost:8000/exam-main/public/js/services/APIService.js
```

But they should load from:
```
http://localhost:8000/js/services/APIService.js
```

**Quick Fix:** Update all script tags to use `/js/` instead of full path.

---

## 🚀 **Quick Fix to Try First**

Update manage-users.php script tags:

```php
<!-- Change ALL script tags from: -->
<script src="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/js/services/APIService.js"></script>

<!-- To: -->
<script src="/js/services/APIService.js"></script>
```

Then refresh and test!

---

**What errors do you see in the console?** Copy them here and I'll help you fix them! 🔍
