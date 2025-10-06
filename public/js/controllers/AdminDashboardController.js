/**
 * AdminDashboardController - Main controller for admin dashboard
 * Controller layer - Coordinates all dashboard functionality
 */
class AdminDashboardController {
    constructor() {
        // Initialize API service
        this.api = new APIService();
        console.log('🔧 API Service basePath:', this.api.basePath);
        this.modal = window.modernModal;
        
        // Initialize sub-controllers
        this.userController = null;
        this.scoresController = null;
        
        this.initialize();
    }

    /**
     * Initialize dashboard
     */
    initialize() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initializeComponents());
        } else {
            this.initializeComponents();
        }
    }

    /**
     * Initialize all components
     */
    initializeComponents() {
        console.log('🔄 Initializing Admin Dashboard components...');
        
        // Initialize user management
        if (window.UserManagementController) {
            this.userController = new UserManagementController(this.api);
            console.log('✅ User Management Controller initialized');
        } else {
            console.warn('⚠️ UserManagementController not found');
        }

        // Initialize scores management
        if (window.ScoreController && window.ScoreService && window.ScoreView) {
            const scoreService = new ScoreService(this.api);
            const scoreView = new ScoreView();
            this.scoresController = new ScoreController(scoreService, scoreView);
            console.log('✅ Score Controller initialized');
        } else {
            console.warn('⚠️ Score components not available');
        }

        // Initialize logout functionality
        this.initializeLogout();
        
        // Handle session messages
        this.handleSessionMessages();
        
        // Initialize statistics
        this.loadStatistics();
        
        console.log('✅ Admin Dashboard initialized successfully');
        console.log('📊 Dashboard instance:', this);
    }

    /**
     * Initialize logout functionality
     */
    initializeLogout() {
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => this.openLogoutModal());
        }

        // Check if logout modal should be shown
        const showLogoutModal = document.body.dataset.showLogoutModal;
        if (showLogoutModal === 'true') {
            this.openLogoutModal();
        }
    }

    /**
     * Handle session messages
     */
    handleSessionMessages() {
        const successMessage = document.body.dataset.successMessage;
        const errorMessage = document.body.dataset.errorMessage;
        
        if (successMessage && this.toast) {
            this.toast.success(successMessage);
        }
        
        if (errorMessage && this.toast) {
            this.toast.error(errorMessage);
        }
    }

    /**
     * Load dashboard statistics
     */
    async loadStatistics() {
        try {
            // Don't include /admin in endpoint - basePath already has it
            const stats = await this.api.get('/statistics');
            this.updateStatistics(stats);
        } catch (error) {
            console.error('Failed to load statistics:', error);
        }
    }

    /**
     * Update statistics display
     */
    updateStatistics(stats) {
        // Update total users
        const totalUsersEl = document.getElementById('totalUsers');
        if (totalUsersEl && stats.totalUsers !== undefined) {
            totalUsersEl.textContent = stats.totalUsers;
        }

        // Update students count
        const studentsEl = document.getElementById('studentsCount');
        if (studentsEl && stats.students !== undefined) {
            studentsEl.textContent = stats.students;
        }

        // Update faculty count
        const facultyEl = document.getElementById('facultyCount');
        if (facultyEl && stats.faculty !== undefined) {
            facultyEl.textContent = stats.faculty;
        }

        // Update admins count
        const adminsEl = document.getElementById('adminsCount');
        if (adminsEl && stats.admins !== undefined) {
            adminsEl.textContent = stats.admins;
        }
    }

    /**
     * Open logout modal
     */
    openLogoutModal() {
        const modal = document.getElementById('logoutModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    /**
     * Close logout modal
     */
    closeLogoutModal() {
        const modal = document.getElementById('logoutModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    /**
     * Confirm logout
     */
    confirmLogout() {
        const logoutBtn = document.querySelector('#logoutModal button[onclick*="confirmLogout"]');
        if (logoutBtn) {
            const originalText = logoutBtn.innerHTML;
            logoutBtn.disabled = true;
            logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Logging out...';
        }
        
        // Use the API service basePath to construct the logout URL
        const logoutUrl = this.api.basePath + '/logout?confirm=true';
        
        console.log('🚪 API basePath:', this.api.basePath);
        console.log('🚪 Logging out to:', logoutUrl);
        
        // Small delay for UI feedback
        setTimeout(() => {
            window.location.href = logoutUrl;
        }, 500);
    }

    /**
     * Navigate to subjects management
     */
    navigateToSubjects() {
        window.location.href = `${this.api.basePath}/subjects`;
    }

    /**
     * Navigate to assignments management
     */
    navigateToAssignments() {
        window.location.href = `${this.api.basePath}/assignments`;
    }

    /**
     * Refresh dashboard
     */
    async refresh() {
        await this.loadStatistics();
        if (this.userController) {
            await this.userController.refreshUserList();
        }
    }
}

// Global functions for backward compatibility
window.openLogoutModal = () => {
    if (window.adminDashboard) {
        window.adminDashboard.openLogoutModal();
    }
};

window.closeLogoutModal = () => {
    if (window.adminDashboard) {
        window.adminDashboard.closeLogoutModal();
    }
};

window.confirmLogout = () => {
    if (window.adminDashboard) {
        window.adminDashboard.confirmLogout();
    }
};

// User management global functions
window.showAddUserModal = () => {
    if (window.adminDashboard?.userController) {
        window.adminDashboard.userController.showAddUserModal();
    }
};

window.closeAddUserModal = () => {
    if (window.adminDashboard?.userController) {
        window.adminDashboard.userController.closeAddUserModal();
    }
};

window.showUsersModal = () => {
    if (window.adminDashboard?.userController) {
        window.adminDashboard.userController.showUsersModal();
    }
};

window.closeUsersModal = () => {
    if (window.adminDashboard?.userController) {
        window.adminDashboard.userController.closeUsersModal();
    }
};

window.toggleStudentFields = () => {
    if (window.adminDashboard?.userController) {
        const roleSelect = document.getElementById('userRole');
        const isStudent = roleSelect?.value === 'student';
        window.adminDashboard.userController.view.toggleStudentFields(isStudent);
    }
};

window.filterUsers = (role) => {
    if (window.adminDashboard?.userController) {
        window.adminDashboard.userController.filterUsers(role);
    }
};

window.editUser = (userData) => {
    if (window.adminDashboard?.userController) {
        window.adminDashboard.userController.editUser(userData.user_id || userData);
    }
};

window.deleteUser = (userId, userName, userRole) => {
    if (window.adminDashboard?.userController) {
        window.adminDashboard.userController.deleteUser(userId, userName, userRole);
    }
};

window.closeDeleteUserModal = () => {
    if (window.adminDashboard?.userController) {
        window.adminDashboard.userController.closeDeleteUserModal();
    }
};

window.confirmDeleteUser = () => {
    if (window.adminDashboard?.userController) {
        window.adminDashboard.userController.confirmDeleteUser();
    }
};

// Scores management global functions
window.showScoresModal = () => {
    if (window.adminDashboard?.scoresController) {
        window.adminDashboard.scoresController.showScoresModal();
    }
};

window.closeScoresModal = () => {
    if (window.adminDashboard?.scoresController) {
        window.adminDashboard.scoresController.closeScoresModal();
    }
};

window.showScoreAnalytics = () => {
    if (window.adminDashboard?.scoresController) {
        window.adminDashboard.scoresController.showAnalytics();
    }
};

// Initialize dashboard when scripts are loaded
window.adminDashboard = new AdminDashboardController();

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdminDashboardController;
} else {
    window.AdminDashboardController = AdminDashboardController;
}
