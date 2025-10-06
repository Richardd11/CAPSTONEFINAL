/**
 * UserManagementView - Handles all user management UI
 * Pure View layer - NO business logic, only presentation
 */
class UserManagementView {
    constructor() {
        this.modals = {
            addUser: document.getElementById('addUserModal'),
            usersList: document.getElementById('usersModal'),
            deleteUser: document.getElementById('deleteUserModal')
        };
    }

    /**
     * Show add user modal
     */
    showAddUserModal() {
        this.resetAddUserForm();
        this.updateModalTitle('Add New User', 'Add User');
        this.showModal('addUser');
    }

    /**
     * Show edit user modal
     */
    showEditUserModal(user) {
        this.populateUserForm(user);
        this.updateModalTitle('Edit User', 'Update User');
        this.showModal('addUser');
    }

    /**
     * Close add user modal
     */
    closeAddUserModal() {
        this.hideModal('addUser');
    }

    /**
     * Show users list modal
     */
    showUsersModal() {
        this.showModal('usersList');
    }

    /**
     * Close users list modal
     */
    closeUsersModal() {
        this.hideModal('usersList');
    }

    /**
     * Show delete confirmation modal
     */
    showDeleteModal(user) {
        const nameElement = document.getElementById('deleteUserName');
        const roleElement = document.getElementById('deleteUserRole');
        
        if (nameElement) nameElement.textContent = user.fullName;
        if (roleElement) roleElement.textContent = user.role.charAt(0).toUpperCase() + user.role.slice(1);
        
        this.showModal('deleteUser');
    }

    /**
     * Close delete confirmation modal
     */
    closeDeleteModal() {
        this.hideModal('deleteUser');
    }

    /**
     * Generic show modal
     */
    showModal(modalName) {
        const modal = this.modals[modalName];
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    /**
     * Generic hide modal
     */
    hideModal(modalName) {
        const modal = this.modals[modalName];
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    /**
     * Update modal title and button
     */
    updateModalTitle(title, buttonText) {
        const titleElement = document.getElementById('userModalTitle');
        const buttonElement = document.getElementById('userSubmitBtn');
        
        if (titleElement) titleElement.textContent = title;
        if (buttonElement) buttonElement.textContent = buttonText;
    }

    /**
     * Reset add user form
     */
    resetAddUserForm() {
        const form = document.getElementById('userForm');
        if (form) {
            form.reset();
        }
        
        const roleSelect = document.getElementById('userRole');
        if (roleSelect) {
            roleSelect.value = '';
        }
        
        // Clear all input fields explicitly
        const schoolIdInput = document.getElementById('schoolId') || document.querySelector('input[name="school_id"]');
        const fullNameInput = document.getElementById('fullName') || document.querySelector('input[name="full_name"]');
        if (schoolIdInput) schoolIdInput.value = '';
        if (fullNameInput) fullNameInput.value = '';
        
        this.toggleStudentFields(false);
    }

    /**
     * Populate user form with data
     */
    populateUserForm(user) {
        const schoolIdInput = document.getElementById('schoolId') || document.querySelector('input[name="school_id"]');
        const fullNameInput = document.getElementById('fullName') || document.querySelector('input[name="full_name"]');
        const roleSelect = document.getElementById('userRole');
        const yearLevelSelect = document.getElementById('yearLevel') || document.getElementById('userYearLevel');
        const sectionSelect = document.getElementById('section') || document.getElementById('userSection');
        
        if (schoolIdInput) schoolIdInput.value = user.schoolId || '';
        if (fullNameInput) fullNameInput.value = user.fullName || '';
        if (roleSelect) roleSelect.value = user.role || '';
        
        if (user.isStudent()) {
            if (yearLevelSelect) yearLevelSelect.value = user.yearLevel || '';
            if (sectionSelect) sectionSelect.value = user.section || '';
            this.toggleStudentFields(true);
        } else {
            this.toggleStudentFields(false);
        }
    }

    /**
     * Toggle student-specific fields
     */
    toggleStudentFields(show) {
        const yearLevelField = document.getElementById('yearLevelField');
        const sectionField = document.getElementById('sectionField');
        const yearLevelInput = document.getElementById('yearLevel') || document.getElementById('userYearLevel');
        const sectionInput = document.getElementById('section') || document.getElementById('userSection');
        
        if (yearLevelField && sectionField) {
            yearLevelField.style.display = show ? 'block' : 'none';
            sectionField.style.display = show ? 'block' : 'none';
        }
        
        if (yearLevelInput && sectionInput) {
            yearLevelInput.required = show;
            sectionInput.required = show;
        }
    }

    /**
     * Filter user cards by role
     */
    filterUserCards(role) {
        const cards = document.querySelectorAll('.user-card');
        
        cards.forEach(card => {
            const cardRole = card.dataset.role;
            if (role === 'all' || cardRole === role) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
        
        this.updateFilterButtons(role);
    }

    /**
     * Update filter button states
     */
    updateFilterButtons(activeRole) {
        const buttons = document.querySelectorAll('[id^="filter"]');
        
        buttons.forEach(btn => {
            const btnRole = btn.id.replace('filter', '').toLowerCase();
            if (btnRole === activeRole.toLowerCase()) {
                btn.classList.remove('bg-slate-100', 'text-slate-600');
                btn.classList.add('bg-blue-500', 'text-white');
            } else {
                btn.classList.remove('bg-blue-500', 'text-white');
                btn.classList.add('bg-slate-100', 'text-slate-600');
            }
        });
    }

    /**
     * Search users
     */
    searchUsers(searchTerm) {
        const cards = document.querySelectorAll('.user-card');
        const term = searchTerm.toLowerCase();
        
        cards.forEach(card => {
            const name = card.querySelector('h4')?.textContent.toLowerCase() || '';
            const schoolId = card.querySelector('p')?.textContent.toLowerCase() || '';
            
            if (name.includes(term) || schoolId.includes(term)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    /**
     * Show loading state on button
     */
    showButtonLoading(buttonId, loadingText = 'Processing...') {
        const button = document.getElementById(buttonId);
        if (button) {
            button.dataset.originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>${loadingText}`;
        }
    }

    /**
     * Reset button state
     */
    resetButton(buttonId) {
        const button = document.getElementById(buttonId);
        if (button && button.dataset.originalText) {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText;
        }
    }

    /**
     * Show success message
     */
    showSuccess(message) {
        if (window.modernModal) {
            window.modernModal.success('Success', message);
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        if (window.modernModal) {
            window.modernModal.error('Error', message);
        }
    }

    /**
     * Show validation errors
     */
    showValidationErrors(errors) {
        if (window.modernModal) {
            const errorList = errors.map(err => `• ${err}`).join('\n');
            window.modernModal.error(
                'Validation Failed',
                `The following errors were found:\n\n${errorList}`,
                {
                    confirmText: 'Fix Errors'
                }
            );
        } else {
            const errorList = errors.map(err => `• ${err}`).join('\n');
            this.showError(`Validation failed:\n${errorList}`);
        }
    }

    /**
     * Get form data
     */
    getFormData() {
        // Try both possible ID formats for compatibility
        const schoolId = document.getElementById('schoolId')?.value || 
                        document.querySelector('input[name="school_id"]')?.value || '';
        const fullName = document.getElementById('fullName')?.value || 
                        document.querySelector('input[name="full_name"]')?.value || '';
        const role = document.getElementById('userRole')?.value || '';
        const yearLevel = document.getElementById('yearLevel')?.value || 
                         document.getElementById('userYearLevel')?.value || null;
        const section = document.getElementById('section')?.value || 
                       document.getElementById('userSection')?.value || null;
        
        console.log('📋 Form field values:', {
            schoolId,
            fullName,
            role,
            yearLevel,
            section
        });
        
        return {
            school_id: schoolId,
            full_name: fullName,
            role: role,
            year_level: role === 'student' ? yearLevel : null,
            section: role === 'student' ? section : null
        };
    }

    /**
     * Render user card (for dynamic updates)
     */
    renderUserCard(user) {
        return `
            <div class="user-card bg-white border border-slate-200 rounded-xl p-6 hover:shadow-lg transition-all duration-300 hover:-translate-y-1" 
                 data-role="${user.role}"
                 data-user-id="${user.userId}">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                            <i class="fas ${user.getRoleIcon()} text-white text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-slate-900 text-lg">${user.fullName}</h4>
                            <p class="text-slate-500 text-sm">${user.schoolId}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium rounded-full ${user.getRoleBadgeClass()}">
                        ${user.role.charAt(0).toUpperCase() + user.role.slice(1)}
                    </span>
                </div>
                
                ${user.isStudent() ? `
                <div class="space-y-2 mb-4">
                    <div class="flex items-center text-sm text-slate-600">
                        <i class="fas fa-graduation-cap w-4 mr-2"></i>
                        <span>${user.yearLevel} - Section ${user.section}</span>
                    </div>
                </div>
                ` : ''}
                
                <div class="flex space-x-2">
                    <button onclick="window.userController.editUser('${user.userId}')" 
                            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <button onclick="window.userController.deleteUser('${user.userId}')" 
                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UserManagementView;
} else {
    window.UserManagementView = UserManagementView;
}
