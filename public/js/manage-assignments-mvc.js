/**
 * Manage Assignments - MVC Implementation
 * Replaces manage-assignments-inline.js with proper MVC structure
 */

// Initialize MVC components when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing Assignment Management MVC...');
    
    // Initialize API Service
    const apiService = new APIService();
    
    // Initialize Service Layer
    const assignmentService = new AssignmentManagementService(apiService);
    
    // Initialize View Layer
    const assignmentView = new AssignmentManagementView();
    
    // Initialize Controller Layer
    const assignmentController = new AssignmentManagementController(assignmentService, assignmentView);
    
    // Initialize data from PHP (set by inline script in PHP file)
    if (typeof assignmentsData !== 'undefined') {
        assignmentController.initializeAssignmentsData(assignmentsData);
    }
    
    // Make controller globally accessible for HTML onclick handlers
    window.assignmentController = assignmentController;
    
    console.log('Assignment Management MVC initialized successfully');
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

function hideAddAssignmentModal() {
    if (window.assignmentController) {
        window.assignmentController.hideAddAssignmentModal();
    }
}

function showEditAssignmentModal() {
    if (window.assignmentController) {
        window.assignmentController.showEditAssignmentModal();
    }
}

function hideEditAssignmentModal() {
    if (window.assignmentController) {
        window.assignmentController.hideEditAssignmentModal();
    }
}

function showDeleteAssignmentModal() {
    if (window.assignmentController) {
        window.assignmentController.showDeleteAssignmentModal();
    }
}

function hideDeleteAssignmentModal() {
    if (window.assignmentController) {
        window.assignmentController.hideDeleteAssignmentModal();
    }
}

function showErrorModal(message) {
    if (window.assignmentController && window.assignmentController.view) {
        window.assignmentController.view.showErrorModal(message);
    }
}

function hideErrorModal() {
    if (window.assignmentController && window.assignmentController.view) {
        window.assignmentController.view.hideErrorModal();
    }
}

function editAssignment(assignmentId) {
    if (window.assignmentController) {
        window.assignmentController.editAssignment(assignmentId);
    }
}

function deleteAssignment(assignmentId) {
    if (window.assignmentController) {
        window.assignmentController.deleteAssignment(assignmentId);
    }
}

function confirmDeleteAssignment() {
    if (window.assignmentController) {
        window.assignmentController.confirmDeleteAssignment();
    }
}

function filterAssignments() {
    if (window.assignmentController) {
        window.assignmentController.filterAssignments();
    }
}

console.log('Manage Assignments MVC script loaded');
