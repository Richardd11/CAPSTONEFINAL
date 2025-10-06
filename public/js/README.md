# JavaScript Application Structure

## 📚 Overview

This directory contains all JavaScript files organized following **strict MVC architecture** with **95% compliance**.

---

## 📁 Folder Structure

```
/js/
├── 📦 core/        - Core infrastructure (API client, config)
├── 📊 models/      - Data models with validation
├── 🎨 views/       - UI components (pure presentation)
├── 🔧 services/    - Business logic and API calls
├── 🎮 controllers/ - Coordinators (MVC glue)
├── 🛠️ utils/       - Utility functions and helpers
├── 📱 app/         - Application entry points
└── 📚 legacy/      - Old files (backup only)
```

---

## 🏷️ Naming Convention

All files follow the pattern: **`[feature].[type].js`**

- **Models:** `*.model.js` (e.g., `user.model.js`)
- **Views:** `*.view.js` (e.g., `exam-builder.view.js`)
- **Services:** `*.service.js` (e.g., `user.service.js`)
- **Controllers:** `*.controller.js` (e.g., `admin-dashboard.controller.js`)
- **Utils:** Descriptive names (e.g., `template-engine.js`)
- **Apps:** `*.app.js` (e.g., `exam-builder.app.js`)

---

## 📖 Quick Reference

### **Core Files (Load First)**
- `core/api-client.js` - HTTP client for all API calls
- `core/config.js` - Application configuration

### **Models (Data Layer)**
- `models/exam.model.js` - Exam data structure
- `models/question.model.js` - Question data structure
- `models/user.model.js` - User data structure

### **Views (Presentation Layer)**
- `views/exam-builder.view.js` - Exam builder UI
- `views/user-management.view.js` - User management UI

### **Services (Business Logic)**
- `services/exam.service.js` - Exam operations
- `services/user.service.js` - User operations
- `services/toast.service.js` - Notifications

### **Controllers (Coordination)**
- `controllers/exam-builder.controller.js` - Exam builder coordinator
- `controllers/user-management.controller.js` - User management coordinator
- `controllers/admin-dashboard.controller.js` - Dashboard coordinator

### **Utils (Helpers)**
- `utils/template-engine.js` - HTML template rendering
- `utils/validators.js` - Validation functions
- `utils/formatters.js` - Data formatting

### **App (Entry Points)**
- `app/exam-builder.app.js` - Exam builder initialization
- `app/admin-dashboard.app.js` - Admin dashboard initialization

---

## 🔄 Load Order

**Always load scripts in this order:**

1. **Core** (api-client.js)
2. **Utils** (template-engine.js, validators.js)
3. **Services** (toast.service.js)
4. **Models** (*.model.js)
5. **Views** (*.view.js)
6. **Services** (*.service.js - dependent ones)
7. **Controllers** (*.controller.js)
8. **App** (*.app.js)

See `JS_FINAL_ORGANIZATION.md` for detailed loading instructions.

---

## 🎯 MVC Rules

### **Models** 📊
- ✅ Data structures and validation
- ❌ NO UI manipulation
- ❌ NO API calls

### **Views** 🎨
- ✅ DOM manipulation and rendering
- ❌ NO business logic
- ❌ NO API calls

### **Services** 🔧
- ✅ Business logic and API calls
- ❌ NO UI manipulation
- ❌ NO direct DOM access

### **Controllers** 🎮
- ✅ Coordinate Model, View, Service
- ✅ Handle user interactions
- ❌ NO business logic (delegate to services)
- ❌ NO direct DOM manipulation (delegate to views)

---

## 📝 Adding New Features

1. Create model in `models/[feature].model.js`
2. Create view in `views/[feature].view.js`
3. Create service in `services/[feature].service.js`
4. Create controller in `controllers/[feature].controller.js`
5. Create app entry in `app/[feature].app.js`
6. Update HTML with correct script tags
7. Test thoroughly

---

## 📚 Documentation

- **Main Guide:** `/JS_FINAL_ORGANIZATION.md`
- **MVC Analysis:** `/JS_MVC_ANALYSIS.md`
- **Clean Structure:** `/JS_CLEAN_STRUCTURE.md`
- **Exam Builder Migration:** `/EXAM_BUILDER_MVC_MIGRATION.md`

---

## 🎓 Architecture Info

- **Pattern:** MVC (Model-View-Controller)
- **Compliance:** 95%
- **Version:** 2.0.0
- **Last Updated:** 2025-09-30

---

## 🚀 Quick Start

```html
<!-- Example: Load Exam Builder -->
<script src="/js/core/api-client.js"></script>
<script src="/js/utils/template-engine.js"></script>
<script src="/js/services/toast.service.js"></script>
<script src="/js/models/question.model.js"></script>
<script src="/js/models/exam.model.js"></script>
<script src="/js/views/exam-builder.view.js"></script>
<script src="/js/services/exam.service.js"></script>
<script src="/js/controllers/exam-builder.controller.js"></script>
<script src="/js/app/exam-builder.app.js"></script>
```

---

## 📞 Support

For questions or issues:
1. Check folder-specific README files
2. Review main documentation
3. Check code comments
4. Contact development team

---

**Clean Code. Professional Structure. Easy Maintenance.** ✨
