/**
 * Assignment Management Controller
 * Coordinates assignment management operations
 * Controller layer - Orchestrates Model, View, and Service
 */
class AssignmentManagementController {
    constructor(assignmentService, assignmentView) {
        this.service = assignmentService;
        this.view = assignmentView;
        this.currentAssignments = [];
        this.currentDeleteAssignmentId = null;
        
        this.initialize();
    }

    /**
     * Initialize controller
     */
    initialize() {
        this.setupEventListeners();
        this.loadAssignments();
        this.loadAssignmentStats();
    }

    /**
     * Initialize assignments data from PHP
     */
    initializeAssignmentsData(assignments) {
        this.currentAssignments = assignments || [];
        this.view.renderAssignments(this.currentAssignments, this.service);
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Form submissions
        const addForm = document.getElementById('addAssignmentForm');
        if (addForm) {
            addForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.addAssignment();
            });
        }
        
        const editForm = document.getElementById('editAssignmentForm');
        if (editForm) {
            editForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.updateAssignment();
            });
        }

        // Keyboard support for error modal
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                const errorModal = document.getElementById('errorModal');
                if (errorModal && !errorModal.classList.contains('hidden')) {
                    this.view.hideErrorModal();
                }
            }
        });
    }

    // ========================================================================
    // DATA LOADING
    // ========================================================================

    /**
     * Load assignments
     */
    loadAssignments() {
        if (this.currentAssignments.length > 0) {
            this.view.renderAssignments(this.currentAssignments, this.service);
        }
    }

    /**
     * Load assignment statistics
     */
    async loadAssignmentStats() {
        try {
            const stats = await this.service.getAssignmentStatistics();
            this.view.updateStatistics(stats);
        } catch (error) {
            console.error('Error loading assignment stats:', error);
        }
    }

    // ========================================================================
    // MODAL OPERATIONS
    // ========================================================================

    /**
     * Show add assignment modal
     */
    showAddAssignmentModal() {
        this.view.showAddModal();
    }

    /**
     * Hide add assignment modal
     */
    hideAddAssignmentModal() {
        this.view.hideAddModal();
    }

    /**
     * Show edit assignment modal
     */
    showEditAssignmentModal() {
        this.view.showEditModal();
    }

    /**
     * Hide edit assignment modal
     */
    hideEditAssignmentModal() {
        this.view.hideEditModal();
    }

    /**
     * Show delete assignment modal
     */
    showDeleteAssignmentModal() {
        this.view.showDeleteModal();
    }

    /**
     * Hide delete assignment modal
     */
    hideDeleteAssignmentModal() {
        this.view.hideDeleteModal();
        this.currentDeleteAssignmentId = null;
    }

    // ========================================================================
    // CRUD OPERATIONS
    // ========================================================================

    /**
     * Add new assignment
     */
    async addAssignment() {
        const formData = new FormData(document.getElementById('addAssignmentForm'));
        
        try {
            const response = await this.service.createAssignment(Object.fromEntries(formData));
            
            if (response.success) {
                this.hideAddAssignmentModal();
                await this.refreshAssignmentsData();
                this.view.showSuccessMessage('Assignment added successfully!');
            } else {
                this.view.showErrorModal(response.message || 'Failed to add assignment');
            }
        } catch (error) {
            console.error('Error:', error);
            this.view.showErrorModal('An error occurred while adding the assignment.');
        }
    }

    /**
     * Edit assignment
     */
    async editAssignment(assignmentId) {
        try {
            const response = await this.service.getAssignmentById(assignmentId);
            
            if (response.status === 'success') {
                const assignment = response.data;
                this.view.populateEditForm(assignment);
                this.showEditAssignmentModal();
            } else {
                this.view.showErrorModal(response.message || 'Failed to fetch assignment data');
            }
        } catch (error) {
            console.error('Error:', error);
            this.view.showErrorModal('An error occurred while fetching assignment data.');
        }
    }

    /**
     * Update assignment
     */
    async updateAssignment() {
        const formData = new FormData(document.getElementById('editAssignmentForm'));
        const assignmentId = formData.get('id');
        
        try {
            const response = await this.service.updateAssignment(assignmentId, Object.fromEntries(formData));
            
            if (response.success) {
                this.hideEditAssignmentModal();
                await this.refreshAssignmentsData();
                this.view.showSuccessMessage('Assignment updated successfully!');
            } else {
                this.view.showErrorModal(response.message || 'Failed to update assignment');
            }
        } catch (error) {
            console.error('Error:', error);
            this.view.showErrorModal('An error occurred while updating the assignment.');
        }
    }

    /**
     * Delete assignment
     */
    deleteAssignment(assignmentId) {
        this.currentDeleteAssignmentId = assignmentId;
        this.showDeleteAssignmentModal();
    }

    /**
     * Confirm delete assignment
     */
    async confirmDeleteAssignment() {
        if (!this.currentDeleteAssignmentId) return;
        
        try {
            const response = await this.service.deleteAssignment(this.currentDeleteAssignmentId);
            
            if (response.success) {
                this.hideDeleteAssignmentModal();
                await this.refreshAssignmentsData();
                this.view.showSuccessMessage('Assignment deleted successfully!');
            } else {
                this.view.showErrorModal(response.message || 'Failed to delete assignment');
            }
        } catch (error) {
            console.error('Error:', error);
            this.view.showErrorModal('An error occurred while deleting the assignment.');
        }
    }

    // ========================================================================
    // DATA REFRESH
    // ========================================================================

    /**
     * Refresh assignments data
     */
    async refreshAssignmentsData() {
        try {
            const result = await this.service.refreshAssignments();
            
            if (result.success) {
                this.currentAssignments = result.data;
                this.loadAssignments();
                await this.loadAssignmentStats();
            } else {
                console.error('Error refreshing assignments');
                location.reload();
            }
        } catch (error) {
            console.error('Error refreshing assignments:', error);
            location.reload();
        }
    }

    // ========================================================================
    // FILTER OPERATIONS
    // ========================================================================

    /**
     * Filter assignments
     */
    async filterAssignments() {
        await this.loadAssignments();
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AssignmentManagementController;
} else {
    window.AssignmentManagementController = AssignmentManagementController;
}
