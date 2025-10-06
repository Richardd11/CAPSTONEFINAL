/**
 * Subjects - MVC Implementation
 * Replaces subjects-inline.js with proper MVC structure
 */

// Initialize MVC components when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing Subjects MVC...');
    
    // Initialize API Service
    const apiService = new APIService();
    
    // Initialize Service Layer (reuse from Subject Management)
    const subjectService = new SubjectManagementService(apiService);
    
    // Initialize Controller Layer
    const subjectController = new SubjectListController(subjectService);
    
    // Make controller globally accessible for HTML onclick handlers
    window.subjectController = subjectController;
    
    console.log('Subjects MVC initialized successfully');
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

function editSubject(subject) {
    if (window.subjectController) {
        window.subjectController.editSubject(subject);
    }
}

function closeSubjectModal() {
    if (window.subjectController) {
        window.subjectController.closeSubjectModal();
    }
}

function deleteSubject(subjectId, subjectCode) {
    if (window.subjectController) {
        window.subjectController.deleteSubject(subjectId, subjectCode);
    }
}

function closeDeleteSubjectModal() {
    if (window.subjectController) {
        window.subjectController.closeDeleteSubjectModal();
    }
}

function confirmDeleteSubject() {
    if (window.subjectController) {
        window.subjectController.confirmDeleteSubject();
    }
}

console.log('Subjects MVC script loaded');
