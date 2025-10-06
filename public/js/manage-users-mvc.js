/**
 * Manage Users - MVC Implementation
 * Replaces manage-users-inline.js with proper MVC structure
 */

// Initialize MVC components when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing Manage Users MVC...');
    
    // Initialize API Service
    const apiService = new APIService();
    
    // Initialize Service Layer
    const userService = new UserManagementService(apiService);
    
    // Initialize View Layer (simple view, most DOM is in HTML)
    const userView = { /* placeholder for future view methods */ };
    
    // Initialize Controller Layer
    const userController = new ManageUsersController(userService, userView);
    
    // Initialize data from PHP (set by inline script in PHP file)
    if (typeof studentsData !== 'undefined' && typeof facultyData !== 'undefined') {
        userController.initializeData(studentsData, facultyData);
    }
    
    // Make controller globally accessible for HTML onclick handlers
    window.userController = userController;
    
    console.log('Manage Users MVC initialized successfully');
});

// ============================================================================
// GLOBAL FUNCTION WRAPPERS
// These functions maintain backward compatibility with existing HTML
// They delegate to the MVC controller
// ============================================================================

function showUsersSubtab(which) {
    if (window.userController) {
        window.userController.showUsersSubtab(which);
    }
}

function showAddStudentModal() {
    if (window.userController) {
        window.userController.showAddStudentModal();
    }
}

function showAddFacultyModal() {
    if (window.userController) {
        window.userController.showAddFacultyModal();
    }
}

function editStudent(studentId) {
    if (window.userController) {
        window.userController.editStudent(studentId);
    }
}

function editFaculty(facultyId) {
    if (window.userController) {
        window.userController.editFaculty(facultyId);
    }
}

function deleteStudent(studentId) {
    if (window.userController) {
        window.userController.deleteStudent(studentId);
    }
}

function deleteFaculty(facultyId) {
    if (window.userController) {
        window.userController.deleteFaculty(facultyId);
    }
}

function cancelDeleteStudent() {
    if (window.userController) {
        window.userController.cancelDeleteStudent();
    }
}

function cancelDeleteFaculty() {
    if (window.userController) {
        window.userController.cancelDeleteFaculty();
    }
}

function confirmDeleteStudent() {
    if (window.userController) {
        window.userController.confirmDeleteStudent();
    }
}

function confirmDeleteFaculty() {
    if (window.userController) {
        window.userController.confirmDeleteFaculty();
    }
}

function closeModal(modalId) {
    if (window.userController) {
        window.userController.closeModal(modalId);
    }
}

function resetForm(formId) {
    if (window.userController) {
        window.userController.resetForm(formId);
    }
}

function submitForm() {
    if (window.userController) {
        window.userController.submitStudentForm();
    }
}

function submitEditForm() {
    if (window.userController) {
        window.userController.submitEditStudentForm();
    }
}

function submitFacultyForm() {
    if (window.userController) {
        window.userController.submitFacultyForm();
    }
}

function submitEditFacultyForm() {
    if (window.userController) {
        window.userController.submitEditFacultyForm();
    }
}

console.log('Manage Users MVC script loaded');
