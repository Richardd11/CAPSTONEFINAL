/**
 * Assignment Form Controller
 * Handles assignment creation/editing form
 * Controller layer - Orchestrates Model, View, and Service
 */
class AssignmentFormController {
    constructor(assignmentService) {
        this.service = assignmentService;
        this.assignmentToDelete = null;
        
        this.initialize();
    }

    /**
     * Initialize controller
     */
    initialize() {
        this.setupEventListeners();
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Form submission
        const assignmentForm = document.getElementById('assignmentForm');
        if (assignmentForm) {
            assignmentForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit();
            });
        }
    }

    // ========================================================================
    // MODAL OPERATIONS
    // ========================================================================

    /**
     * Show add assignment modal
     */
    showAddAssignmentModal() {
        document.getElementById('assignmentModalTitle').textContent = 'Create Assignment';
        document.getElementById('assignmentSubmitText').textContent = 'Create Assignment';
        document.getElementById('assignmentAction').value = 'add';
        document.getElementById('assignmentForm').reset();
        document.getElementById('assignmentId').value = '';
        document.getElementById('assignmentAcademicYear').value = '2024-2025';
        document.getElementById('assignmentModal').classList.remove('hidden');
    }

    /**
     * Edit assignment
     */
    editAssignment(assignment) {
        document.getElementById('assignmentModalTitle').textContent = 'Edit Assignment';
        document.getElementById('assignmentSubmitText').textContent = 'Update Assignment';
        document.getElementById('assignmentAction').value = 'edit';
        
        document.getElementById('assignmentId').value = assignment.id;
        document.getElementById('subjectId').value = assignment.subject_id;
        document.getElementById('facultyId').value = assignment.faculty_id;
        document.getElementById('assignmentYearLevel').value = assignment.year_level;
        document.getElementById('assignmentSection').value = assignment.section;
        document.getElementById('assignmentStatus').value = assignment.status;
        document.getElementById('assignmentAcademicYear').value = assignment.academic_year;
        document.getElementById('assignmentSemester').value = assignment.semester;
        document.getElementById('assignmentNotes').value = assignment.notes || '';
        
        document.getElementById('assignmentModal').classList.remove('hidden');
    }

    /**
     * Close assignment modal
     */
    closeAssignmentModal() {
        document.getElementById('assignmentModal').classList.add('hidden');
    }

    // ========================================================================
    // DELETE OPERATIONS
    // ========================================================================

    /**
     * Delete assignment
     */
    deleteAssignment(assignmentId) {
        this.assignmentToDelete = { id: assignmentId };
        document.getElementById('deleteAssignmentInfo').textContent = `Assignment #${assignmentId}`;
        document.getElementById('deleteAssignmentModal').classList.remove('hidden');
    }

    /**
     * Close delete modal
     */
    closeDeleteAssignmentModal() {
        document.getElementById('deleteAssignmentModal').classList.add('hidden');
        this.assignmentToDelete = null;
    }

    /**
     * Confirm delete assignment
     */
    confirmDeleteAssignment() {
        if (!this.assignmentToDelete) return;
        
        const deleteBtn = document.querySelector('#deleteAssignmentModal button[onclick="confirmDeleteAssignment()"]');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';
        
        const formData = new FormData();
        formData.append('assignment_id', this.assignmentToDelete.id);
        
        const basePath = window.location.pathname.split('/').slice(0, -1).join('/');
        
        fetch(basePath + '/assignments/delete', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.status === 'success' || data.success) {
                this.showToast('Assignment deleted successfully!', 'success');
                this.closeDeleteAssignmentModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(data.message || 'Failed to delete assignment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showToast('Error: ' + error.message, 'error');
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalText;
        });
    }

    // ========================================================================
    // FORM SUBMISSION
    // ========================================================================

    /**
     * Handle form submission
     */
    handleFormSubmit() {
        const formData = new FormData(document.getElementById('assignmentForm'));
        const action = formData.get('action');
        
        const basePath = window.location.pathname.split('/').slice(0, -1).join('/');
        let url = basePath + '/assignments/';
        if (action === 'add') {
            url += 'add';
        } else if (action === 'edit') {
            url += 'edit';
        }
        
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.status === 'success' || data.success) {
                const actionText = action === 'add' ? 'created' : 'updated';
                this.showToast(`Assignment ${actionText} successfully!`, 'success');
                this.closeAssignmentModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                this.showToast('Error: ' + (data.message || 'Unknown error occurred'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showToast('An error occurred while processing your request.', 'error');
        });
    }

    // ========================================================================
    // TOAST NOTIFICATION
    // ========================================================================

    /**
     * Show toast notification
     */
    showToast(message, type = 'success') {
        const existingToast = document.getElementById('toast');
        if (existingToast) existingToast.remove();

        const toast = document.createElement('div');
        toast.id = 'toast';
        toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-medium transform transition-all duration-300 translate-x-full ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        }`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(toast);
        setTimeout(() => toast.classList.remove('translate-x-full'), 100);
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => { if (toast.parentNode) toast.remove(); }, 300);
        }, 3000);
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AssignmentFormController;
} else {
    window.AssignmentFormController = AssignmentFormController;
}
