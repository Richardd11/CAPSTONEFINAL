/**
 * UserManagementController - Coordinates user management operations
 * Controller layer - Orchestrates Model, View, and Service
 */
class UserManagementController {
    constructor(apiService) {
        this.view = new UserManagementView();
        this.service = new UserManagementService(apiService);
        this.currentEditingUserId = null;
        this.userToDelete = null;
        
        this.initialize();
    }

    /**
     * Initialize controller
     */
    initialize() {
        this.setupEventListeners();
        this.setupSearchDebounce();
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Add user button
        const addBtn = document.getElementById('addUserBtn');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.showAddUserModal());
        }

        // View users button
        const viewBtn = document.getElementById('viewUsersBtn');
        if (viewBtn) {
            viewBtn.addEventListener('click', () => this.showUsersModal());
        }

        // Role change handler
        const roleSelect = document.getElementById('userRole');
        if (roleSelect) {
            roleSelect.addEventListener('change', (e) => {
                this.view.toggleStudentFields(e.target.value === 'student');
            });
        }

        // Form submission
        const userForm = document.getElementById('userForm');
        if (userForm) {
            userForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit();
            });
        }

        // Filter buttons
        document.querySelectorAll('[id^="filter"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const role = e.target.id.replace('filter', '').toLowerCase();
                this.filterUsers(role);
            });
        });

        // Search input
        const searchInput = document.getElementById('userSearch');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.handleSearch(e.target.value);
            });
        }

        // Delete modal buttons
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        if (cancelDeleteBtn) {
            cancelDeleteBtn.addEventListener('click', () => this.view.closeDeleteModal());
        }

        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', () => this.confirmDeleteUser());
        }
    }

    /**
     * Setup search debounce
     */
    setupSearchDebounce() {
        this.searchTimeout = null;
    }

    /**
     * Show add user modal
     */
    showAddUserModal() {
        this.currentEditingUserId = null;
        this.view.showAddUserModal();
    }

    /**
     * Close add user modal
     */
    closeAddUserModal() {
        this.view.closeAddUserModal();
    }

    /**
     * Show users modal
     */
    showUsersModal() {
        this.view.showUsersModal();
    }

    /**
     * Close users modal
     */
    closeUsersModal() {
        this.view.closeUsersModal();
    }

    /**
     * Edit user
     */
    async editUser(userId) {
        try {
            this.currentEditingUserId = userId;
            
            // Get user data from DOM or fetch from server
            const userData = await this.getUserData(userId);
            const user = new User(userData);
            
            this.view.closeUsersModal();
            this.view.showEditUserModal(user);
        } catch (error) {
            this.view.showError('Failed to load user data: ' + error.message);
        }
    }

    /**
     * Get user data (from DOM or server)
     */
    async getUserData(userId) {
        // Try to get from DOM first
        const userCard = document.querySelector(`[data-user-id="${userId}"]`);
        if (userCard) {
            return this.extractUserDataFromCard(userCard);
        }
        
        // Fallback to server
        return await this.service.getUserById(userId);
    }

    /**
     * Extract user data from card element
     */
    extractUserDataFromCard(card) {
        // This would extract data from the card's data attributes or content
        // Implementation depends on your HTML structure
        return {
            user_id: card.dataset.userId,
            school_id: card.querySelector('.school-id')?.textContent || '',
            full_name: card.querySelector('.full-name')?.textContent || '',
            role: card.dataset.role,
            year_level: card.dataset.yearLevel,
            section: card.dataset.section
        };
    }

    /**
     * Delete user - accepts userId or full user data object
     */
    async deleteUser(userIdOrData, userName = null, userRole = null) {
        try {
            // Handle both formats: deleteUser(userId, name, role) or deleteUser(userObject)
            let userId, userData;
            
            if (typeof userIdOrData === 'object') {
                // Called with user object
                userData = userIdOrData;
                userId = userData.user_id;
            } else {
                // Called with separate parameters (from dashboard buttons)
                userId = userIdOrData;
                
                // If we have name and role, use them directly
                if (userName && userRole) {
                    userData = {
                        user_id: userId,
                        full_name: userName,
                        role: userRole
                    };
                } else {
                    // Fetch user data
                    userData = await this.getUserData(userId);
                }
            }
            
            this.userToDelete = userId;
            const user = new User(userData);
            this.view.showDeleteModal(user);
        } catch (error) {
            console.error('Error preparing delete modal:', error);
            this.view.showError('Failed to load user data');
        }
    }

    /**
     * Close delete user modal
     */
    closeDeleteUserModal() {
        this.view.closeDeleteModal();
    }

    /**
     * Confirm delete user
     */
    async confirmDeleteUser() {
        if (!this.userToDelete) {
            console.error('No user selected for deletion');
            return;
        }

        console.log('🗑️ Deleting user:', this.userToDelete);
        
        let result = null;

        try {
            this.view.showButtonLoading('confirmDeleteBtn', 'Deleting...');
            
            result = await this.service.deleteUser(this.userToDelete);
            
            console.log('🗑️ Delete result:', result);
            
            if (result.success) {
                this.view.showSuccess(result.message || 'User deleted successfully!');
                this.view.closeDeleteModal();
                
                // Reload page after showing success
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                this.view.showError(result.message || 'Failed to delete user');
                this.view.resetButton('confirmDeleteBtn');
                this.userToDelete = null;
            }
        } catch (error) {
            console.error('❌ Error deleting user:', error);
            this.view.showError('Error deleting user: ' + error.message);
            this.view.resetButton('confirmDeleteBtn');
            this.userToDelete = null;
        }
    }

    /**
     * Handle form submission
     */
    async handleFormSubmit() {
        const formData = this.view.getFormData();
        
        try {
            this.view.showButtonLoading('userSubmitBtn', 'Processing...');
            
            let result;
            if (this.currentEditingUserId) {
                // Update existing user
                result = await this.service.updateUser(this.currentEditingUserId, formData);
            } else {
                // Create new user
                result = await this.service.createUser(formData);
            }
            
            if (result.success) {
                this.view.showSuccess(result.message);
                this.view.closeAddUserModal();
                
                // Reload page or update user list
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else if (result.errors) {
                this.view.showValidationErrors(result.errors);
            } else {
                this.view.showError('Operation failed');
            }
        } catch (error) {
            this.view.showError('Error: ' + error.message);
        } finally {
            this.view.resetButton('userSubmitBtn');
        }
    }

    /**
     * Filter users by role
     */
    filterUsers(role) {
        this.view.filterUserCards(role);
    }

    /**
     * Handle search with debounce
     */
    handleSearch(searchTerm) {
        // Clear previous timeout
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }

        // Set new timeout
        this.searchTimeout = setTimeout(() => {
            this.view.searchUsers(searchTerm);
        }, 300); // 300ms debounce
    }

    /**
     * Export users
     */
    async exportUsers() {
        try {
            const csvContent = await this.service.exportUsers();
            this.service.downloadCSV(csvContent, `users_${new Date().toISOString().split('T')[0]}.csv`);
            this.view.showSuccess('Users exported successfully');
        } catch (error) {
            this.view.showError('Failed to export users: ' + error.message);
        }
    }

    /**
     * Refresh user list
     */
    async refreshUserList() {
        try {
            const users = await this.service.getUsers();
            // Update UI with new user list
            this.renderUserList(users);
        } catch (error) {
            this.view.showError('Failed to refresh user list: ' + error.message);
        }
    }

    /**
     * Render user list
     */
    renderUserList(usersData) {
        const container = document.getElementById('usersGrid');
        if (!container) return;

        const html = usersData.map(userData => {
            const user = new User(userData);
            return this.view.renderUserCard(user);
        }).join('');

        container.innerHTML = html;
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UserManagementController;
} else {
    window.UserManagementController = UserManagementController;
}
