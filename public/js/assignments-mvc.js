/**
 * Assignments - MVC Implementation
 * Replaces assignments-inline.js with proper MVC structure
 */

// Initialize MVC components when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing Assignments MVC...');
    
    // Initialize API Service
    const apiService = new APIService();
    
    // Initialize Service Layer (reuse from Assignment Management)
    const assignmentService = new AssignmentManagementService(apiService);
    
    // Initialize Controller Layer
    const assignmentController = new AssignmentFormController(assignmentService);
    
    // Make controller globally accessible for HTML onclick handlers
    window.assignmentController = assignmentController;
    
    console.log('Assignments MVC initialized successfully');
});

// ============================================================================
// GLOBAL FUNCTION WRAPPERS
// These functions maintain backward compatibility with existing HTML
// They delegate to the MVC controller
// ============================================================================

function showAddAssignmentModal() {
    if (window.assignmentController) {
        window.assignmentController.showAddAssignmentModal();
    }
}

function editAssignment(assignment) {
    if (window.assignmentController) {
        window.assignmentController.editAssignment(assignment);
    }
}

function closeAssignmentModal() {
    if (window.assignmentController) {
        window.assignmentController.closeAssignmentModal();
    }
}

function deleteAssignment(assignmentId) {
    if (window.assignmentController) {
        window.assignmentController.deleteAssignment(assignmentId);
    }
}

function closeDeleteAssignmentModal() {
    if (window.assignmentController) {
        window.assignmentController.closeDeleteAssignmentModal();
    }
}

function confirmDeleteAssignment() {
    if (window.assignmentController) {
        window.assignmentController.confirmDeleteAssignment();
    }
}

console.log('Assignments MVC script loaded');
