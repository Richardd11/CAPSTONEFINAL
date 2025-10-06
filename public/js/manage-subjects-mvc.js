/**
 * Manage Subjects - MVC Implementation
 * Replaces manage-subjects-inline.js with proper MVC structure
 */

// Initialize MVC components when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing Subject Management MVC...');
    
    // Initialize API Service
    const apiService = new APIService();
    
    // Initialize Service Layer
    const subjectService = new SubjectManagementService(apiService);
    
    // Initialize View Layer
    const subjectView = new SubjectManagementView();
    
    // Initialize Controller Layer
    const subjectController = new SubjectManagementController(subjectService, subjectView);
    
    // Initialize data from PHP (set by inline script in PHP file)
    if (typeof subjectsData !== 'undefined') {
        subjectController.initializeSubjectsData(subjectsData);
    }
    
    // Make controller globally accessible for HTML onclick handlers
    window.subjectController = subjectController;
    
    console.log('Subject Management MVC initialized successfully');
});

// ============================================================================
// GLOBAL FUNCTION WRAPPERS
// These functions maintain backward compatibility with existing HTML
// They delegate to the MVC controller
// ============================================================================

function showAddSubjectModal() {
    if (window.subjectController) {
        window.subjectController.showAddSubjectModal();
    }
}

function hideAddSubjectModal() {
    if (window.subjectController) {
        window.subjectController.hideAddSubjectModal();
    }
}

function showEditSubjectModal() {
    if (window.subjectController) {
        window.subjectController.showEditSubjectModal();
    }
}

function hideEditSubjectModal() {
    if (window.subjectController) {
        window.subjectController.hideEditSubjectModal();
    }
}

function showDeleteSubjectModal() {
    if (window.subjectController) {
        window.subjectController.showDeleteSubjectModal();
    }
}

function hideDeleteSubjectModal() {
    if (window.subjectController) {
        window.subjectController.hideDeleteSubjectModal();
    }
}

function editSubject(subjectId) {
    if (window.subjectController) {
        window.subjectController.editSubject(subjectId);
    }
}

function deleteSubject(subjectId) {
    if (window.subjectController) {
        window.subjectController.deleteSubject(subjectId);
    }
}

function confirmDeleteSubject() {
    if (window.subjectController) {
        window.subjectController.confirmDeleteSubject();
    }
}

function clearFilters() {
    if (window.subjectController) {
        window.subjectController.clearFilters();
    }
}

console.log('Manage Subjects MVC script loaded');
