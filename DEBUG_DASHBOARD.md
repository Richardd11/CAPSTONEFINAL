# 🐛 Dashboard Debugging Guide

## 🔍 **Current Issue:**

```
Cannot read properties of undefined (reading 'showUsersModal')
```

This means `window.userController` is undefined when the button is clicked.

---

## 🧪 **Add This Debug Script to Dashboard.php**

Add this RIGHT BEFORE the closing `</body>` tag in dashboard.php:

```php
<!-- TEMPORARY DEBUG SCRIPT -->
<script>
console.log('=== DASHBOARD DEBUG START ===');

// Check if scripts loaded
setTimeout(() => {
    console.log('1. Classes loaded?');
    console.log('   - APIService:', typeof APIService);
    console.log('   - UserManagementService:', typeof UserManagementService);
    console.log('   - UserManagementView:', typeof UserManagementView);
    console.log('   - UserManagementController:', typeof UserManagementController);
    console.log('   - ScoreService:', typeof ScoreService);
    console.log('   - ScoreView:', typeof ScoreView);
    console.log('   - ScoreController:', typeof ScoreController);
    
    console.log('2. Controllers initialized?');
    console.log('   - window.userController:', window.userController);
    console.log('   - window.scoreController:', window.scoreController);
    
    console.log('3. Methods available?');
    if (window.userController) {
        console.log('   - showUsersModal:', typeof window.userController.showUsersModal);
        console.log('   - showAddUserModal:', typeof window.userController.showAddUserModal);
    } else {
        console.error('   ❌ userController is undefined!');
    }
    
    console.log('4. Global functions?');
    console.log('   - showUsersModal:', typeof showUsersModal);
    console.log('   - showAddUserModal:', typeof showAddUserModal);
    
    console.log('=== DASHBOARD DEBUG END ===');
}, 2000);
</script>
```

---

## 📊 **Expected Output:**

### **If Everything Works:**
```
=== DASHBOARD DEBUG START ===
1. Classes loaded?
   - APIService: "function"
   - UserManagementService: "function"
   - UserManagementView: "function"
   - UserManagementController: "function"
   - ScoreService: "function"
   - ScoreView: "function"
   - ScoreController: "function"

2. Controllers initialized?
   - window.userController: UserManagementController {view: ..., service: ...}
   - window.scoreController: ScoreController {...}

3. Methods available?
   - showUsersModal: "function"
   - showAddUserModal: "function"

4. Global functions?
   - showUsersModal: "function"
   - showAddUserModal: "function"

=== DASHBOARD DEBUG END ===
```

### **If Something's Wrong:**
```
❌ One of the classes shows "undefined"
❌ window.userController is undefined
❌ Methods are not "function"
```

---

## 🔧 **Common Issues & Fixes:**

### **Issue 1: Classes Show "undefined"**

**Cause:** Script files not loading in correct order

**Fix:** Check Network tab - are all JS files loading with 200 OK?

---

### **Issue 2: window.userController is undefined**

**Cause:** DOMContentLoaded fired before scripts loaded

**Fix:** Add delay or check if scripts loaded:

```javascript
// In admin-dashboard-mvc.js
window.addEventListener('load', function() {
    // Initialize here instead of DOMContentLoaded
});
```

---

### **Issue 3: ScoreView or ScoreController undefined**

**Cause:** File doesn't exist or wrong name

**Check:** 
```
public/js/views/ScoreView.js exists?
public/js/controllers/ScoreController.js exists?
```

---

## 🎯 **Quick Test Commands**

Open Console (F12) and run:

```javascript
// Test 1: Check if controller exists
console.log(window.userController);

// Test 2: Try to call method directly
if (window.userController) {
    window.userController.showUsersModal();
}

// Test 3: Check all window properties
console.log(Object.keys(window).filter(k => k.includes('Controller')));

// Test 4: Force initialize
if (typeof UserManagementController !== 'undefined') {
    window.userController = new UserManagementController();
    console.log('Manually initialized:', window.userController);
}
```

---

## 🔍 **Diagnostic Steps:**

### **Step 1: Check Script Loading Order**

In Network tab, verify this order:
1. ✅ APIService.js (200 OK)
2. ✅ UserManagementService.js (200 OK)
3. ✅ DashboardService.js (200 OK)
4. ✅ ScoreService.js (200 OK)
5. ✅ User.js (200 OK)
6. ✅ Score.js (200 OK)
7. ✅ UserManagementView.js (200 OK)
8. ✅ ScoreManagementView.js (200 OK)
9. ✅ UserManagementController.js (200 OK)
10. ✅ AdminDashboardController.js (200 OK)
11. ✅ ScoreManagementController.js (200 OK)
12. ✅ admin-dashboard-mvc.js (200 OK) - LAST

---

### **Step 2: Check Console Messages**

Look for:
```
✅ "🔄 Initializing Admin Dashboard MVC..."
✅ "✅ Admin Dashboard MVC initialized successfully"
✅ "✅ User Controller: UserManagementController {...}"
```

If you see:
```
❌ "APIService not loaded"
❌ "UserManagementService not loaded"
❌ Any error in red
```

Then scripts aren't loading properly.

---

### **Step 3: Check File Names**

Verify exact file names match:
```
views/ScoreManagementView.js  ← Dashboard loads this
views/ScoreView.js             ← Might be wrong name!

controllers/ScoreManagementController.js  ← Dashboard loads this
controllers/ScoreController.js            ← Might be wrong name!
```

---

## 🛠️ **Potential Fixes:**

### **Fix 1: Change Event Listener**

In `admin-dashboard-mvc.js`, change:

```javascript
// From:
document.addEventListener('DOMContentLoaded', function() {

// To:
window.addEventListener('load', function() {
```

This waits for ALL scripts to load.

---

### **Fix 2: Check File Names**

Dashboard.php loads:
```php
<script src=".../ScoreManagementView.js"></script>
```

But file might be named:
```
ScoreView.js
```

**Solution:** Rename file or update dashboard.php

---

### **Fix 3: Add Fallback**

In global functions, add fallback:

```javascript
function showUsersModal() {
    if (!window.userController) {
        console.error('Controller not ready, initializing...');
        window.userController = new UserManagementController();
    }
    window.userController.showUsersModal();
}
```

---

## 📝 **Action Plan:**

1. ✅ Add debug script to dashboard.php
2. ✅ Refresh page
3. ✅ Check console output
4. ✅ Copy ALL console messages
5. ✅ Tell me what you see

---

**Add the debug script and tell me what the console shows!** 🔍
