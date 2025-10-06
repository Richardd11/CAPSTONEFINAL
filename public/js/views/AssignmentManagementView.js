/**
 * Assignment Management View
 * Handles all assignment-related UI rendering
 * View layer - Only handles presentation, NO business logic
 */
class AssignmentManagementView {
    constructor() {
        this.assignmentsTableBody = document.getElementById('assignmentsTableBody');
        this.assignmentsEmptyState = document.getElementById('assignmentsEmptyState');
        this.addModal = document.getElementById('addAssignmentModal');
        this.editModal = document.getElementById('editAssignmentModal');
        this.deleteModal = document.getElementById('deleteAssignmentModal');
        this.errorModal = document.getElementById('errorModal');
    }

    /**
     * Render assignments table
     */
    renderAssignments(assignments, service) {
        if (!this.assignmentsTableBody) return;

        if (assignments.length === 0) {
            this.assignmentsTableBody.innerHTML = '';
            if (this.assignmentsEmptyState) {
                this.assignmentsEmptyState.classList.remove('hidden');
            }
            return;
        }

        if (this.assignmentsEmptyState) {
            this.assignmentsEmptyState.classList.add('hidden');
        }

        let html = '';
        assignments.forEach(assignment => {
            const statusClass = this.getStatusClass(assignment.status);
            const statusIcon = this.getStatusIcon(assignment.status);
            
            html += `
                <tr class="hover:bg-grey-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div>
                            <div class="text-sm font-medium text-grey-900">
                                ${this.escapeHtml(assignment.subject_code)} - ${this.escapeHtml(assignment.subject_name)}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-grey-900">${this.escapeHtml(assignment.faculty_name)}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-grey-900">${this.escapeHtml(assignment.year_level)} - ${this.escapeHtml(assignment.section)}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-grey-900">${this.escapeHtml(assignment.academic_year)}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-grey-900">${this.escapeHtml(assignment.semester)}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                            <i class="${statusIcon} mr-1"></i>
                            ${this.escapeHtml(assignment.status)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-3">
                            <button class="text-indigo-600 hover:text-indigo-900 p-2 rounded hover:bg-indigo-50 transition-colors" onclick="editAssignment(${assignment.id})">
                                <i class="fas fa-edit text-lg"></i>
                            </button>
                            <button class="text-red-600 hover:text-red-900 p-2 rounded hover:bg-red-50 transition-colors" onclick="deleteAssignment(${assignment.id})">
                                <i class="fas fa-trash text-lg"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        this.assignmentsTableBody.innerHTML = html;
    }

    /**
     * Show add assignment modal
     */
    showAddModal() {
        if (this.addModal) {
            this.addModal.classList.remove('hidden');
        }
        const form = document.getElementById('addAssignmentForm');
        if (form) form.reset();
    }

    /**
     * Hide add assignment modal
     */
    hideAddModal() {
        if (this.addModal) {
            this.addModal.classList.add('hidden');
        }
    }

    /**
     * Show edit assignment modal
     */
    showEditModal() {
        if (this.editModal) {
            this.editModal.classList.remove('hidden');
        }
    }

    /**
     * Hide edit assignment modal
     */
    hideEditModal() {
        if (this.editModal) {
            this.editModal.classList.add('hidden');
        }
    }

    /**
     * Populate edit form
     */
    populateEditForm(assignment) {
        const fields = {
            'editAssignmentId': assignment.id,
            'editAssignmentSubject': assignment.subject_id,
            'editAssignmentFaculty': assignment.faculty_id,
            'editAssignmentYearLevel': assignment.year_level,
            'editAssignmentSection': assignment.section,
            'editAssignmentAcademicYear': assignment.academic_year,
            'editAssignmentSemester': assignment.semester,
            'editAssignmentStatus': assignment.status,
            'editAssignmentNotes': assignment.notes || ''
        };

        for (const [id, value] of Object.entries(fields)) {
            const element = document.getElementById(id);
            if (element) element.value = value;
        }
    }

    /**
     * Show delete confirmation modal
     */
    showDeleteModal() {
        if (this.deleteModal) {
            this.deleteModal.classList.remove('hidden');
        }
    }

    /**
     * Hide delete confirmation modal
     */
    hideDeleteModal() {
        if (this.deleteModal) {
            this.deleteModal.classList.add('hidden');
        }
    }

    /**
     * Show error modal
     */
    showErrorModal(message) {
        const errorMsg = document.getElementById('errorMessage');
        if (errorMsg) errorMsg.textContent = message;
        if (this.errorModal) {
            this.errorModal.classList.remove('hidden');
            this.errorModal.focus();
        }
    }

    /**
     * Hide error modal
     */
    hideErrorModal() {
        if (this.errorModal) {
            this.errorModal.classList.add('hidden');
        }
    }

    /**
     * Show success message
     */
    showSuccessMessage(message) {
        const successDiv = document.createElement('div');
        successDiv.className = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex justify-between items-center';
        successDiv.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                ${message}
            </div>
            <button type="button" class="text-green-700 hover:text-green-900" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        const assignmentsContent = document.getElementById('assignments');
        if (assignmentsContent) {
            assignmentsContent.insertBefore(successDiv, assignmentsContent.firstChild);
        }
        
        setTimeout(() => {
            if (successDiv.parentElement) {
                successDiv.remove();
            }
        }, 5000);
    }

    /**
     * Update statistics display
     */
    updateStatistics(stats) {
        const totalEl = document.getElementById('totalAssignments');
        const activeEl = document.getElementById('activeAssignments');
        const pendingEl = document.getElementById('pendingAssignments');
        const unassignedEl = document.getElementById('unassignedSubjects');
        
        if (totalEl) totalEl.textContent = stats.total_assignments || 0;
        if (activeEl) activeEl.textContent = stats.active_assignments || 0;
        if (pendingEl) pendingEl.textContent = stats.pending_assignments || 0;
        if (unassignedEl) unassignedEl.textContent = '0';
    }

    /**
     * Show loading state
     */
    showLoading() {
        if (this.assignmentsTableBody) {
            this.assignmentsTableBody.innerHTML = '<tr><td colspan="7" class="text-center py-8"><i class="fas fa-spinner fa-spin mr-2"></i>Loading...</td></tr>';
        }
    }

    // ========================================================================
    // UTILITY FUNCTIONS
    // ========================================================================

    getStatusClass(status) {
        const classes = {
            'active': 'bg-green-100 text-green-800',
            'inactive': 'bg-red-100 text-red-800',
            'pending': 'bg-yellow-100 text-yellow-800'
        };
        return classes[status] || 'bg-grey-100 text-grey-800';
    }

    getStatusIcon(status) {
        const icons = {
            'active': 'fas fa-check-circle',
            'inactive': 'fas fa-times-circle',
            'pending': 'fas fa-clock'
        };
        return icons[status] || 'fas fa-question-circle';
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AssignmentManagementView;
} else {
    window.AssignmentManagementView = AssignmentManagementView;
}
