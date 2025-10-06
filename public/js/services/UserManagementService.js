/**
 * UserManagementService - Handles user-related business logic and API calls
 * Service layer - Contains business rules, NO UI manipulation
 */
class UserManagementService {
    constructor(apiService) {
        this.api = apiService || window.apiService;
    }

    /**
     * Get all users (server-side filtering preferred)
     */
    async getUsers(filters = {}) {
        try {
            const params = new URLSearchParams(filters);
            const endpoint = `/users${params.toString() ? '?' + params.toString() : ''}`;
            return await this.api.get(endpoint);
        } catch (error) {
            console.error('Error fetching users:', error);
            throw error;
        }
    }

    /**
     * Get user by ID
     */
    async getUserById(userId) {
        try {
            return await this.api.get(`/users/${userId}`);
        } catch (error) {
            console.error('Error fetching user:', error);
            throw error;
        }
    }

    /**
     * Create new user
     */
    async createUser(userData) {
        try {
            console.log('🟢 Creating user with data:', userData);
            
            // Validate before sending
            const user = new User(userData);
            const validation = user.validate();
            
            console.log('🔍 Validation result:', validation);
            
            if (!validation.isValid) {
                return {
                    success: false,
                    errors: validation.errors
                };
            }

            const userJSON = user.toJSON();
            console.log('📦 Sending user data:', userJSON);
            
            const response = await this.api.post('/users/add', userJSON);
            
            console.log('📥 Server response:', response);
            
            return {
                success: response.status === 'success' || response.success,
                message: response.message || 'User created successfully',
                user: response.user || null
            };
        } catch (error) {
            console.error('Error creating user:', error);
            throw error;
        }
    }

    /**
     * Update existing user
     */
    async updateUser(userId, userData) {
        try {
            // Validate before sending
            const user = new User({ ...userData, user_id: userId });
            const validation = user.validate();
            
            if (!validation.isValid) {
                return {
                    success: false,
                    errors: validation.errors
                };
            }

            const response = await this.api.post(`/users/edit/${userId}`, user.toJSON());
            
            return {
                success: response.status === 'success' || response.success,
                message: response.message || 'User updated successfully',
                user: response.user || null
            };
        } catch (error) {
            console.error('Error updating user:', error);
            throw error;
        }
    }

    /**
     * Delete user
     */
    async deleteUser(userId) {
        try {
            console.log('🗑️ Service: Deleting user ID:', userId);
            
            const response = await this.api.post(`/users/delete/${userId}`, { user_id: userId });
            
            console.log('🗑️ Service: Server response:', response);
            
            const result = {
                success: response.status === 'success' || response.success,
                message: response.message || 'User deleted successfully'
            };
            
            console.log('🗑️ Service: Returning result:', result);
            
            return result;
        } catch (error) {
            console.error('❌ Service: Error deleting user:', error);
            throw error;
        }
    }

    /**
     * Validate user data (client-side)
     */
    validateUser(userData) {
        const user = new User(userData);
        return user.validate();
    }

    /**
     * Get users by role (prefer server-side filtering)
     */
    async getUsersByRole(role) {
        return await this.getUsers({ role: role });
    }

    /**
     * Search users (prefer server-side search)
     */
    async searchUsers(searchTerm) {
        return await this.getUsers({ search: searchTerm });
    }

    /**
     * Get user statistics
     */
    async getUserStatistics() {
        try {
            const response = await this.api.get('/users/statistics');
            return response.data || response;
        } catch (error) {
            console.error('Error fetching statistics:', error);
            throw error;
        }
    }

    /**
     * Bulk create users
     */
    async bulkCreateUsers(usersData) {
        try {
            const response = await this.api.post('/users/bulk-create', { users: usersData });
            
            return {
                success: response.status === 'success' || response.success,
                message: response.message || 'Users created successfully',
                created: response.created || 0,
                failed: response.failed || 0,
                errors: response.errors || []
            };
        } catch (error) {
            console.error('Error bulk creating users:', error);
            throw error;
        }
    }

    /**
     * Export users to CSV
     */
    async exportUsers(filters = {}) {
        try {
            const users = await this.getUsers(filters);
            return this.generateCSV(users);
        } catch (error) {
            console.error('Error exporting users:', error);
            throw error;
        }
    }

    /**
     * Generate CSV from users data
     */
    generateCSV(users) {
        const headers = ['School ID', 'Full Name', 'Role', 'Year Level', 'Section', 'Created At'];
        const rows = users.map(user => [
            user.school_id || '',
            user.full_name || '',
            user.role || '',
            user.year_level || '',
            user.section || '',
            user.created_at || ''
        ]);

        const csvContent = [
            headers.join(','),
            ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
        ].join('\n');

        return csvContent;
    }

    /**
     * Download CSV file
     */
    downloadCSV(csvContent, filename = 'users.csv') {
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        URL.revokeObjectURL(url);
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UserManagementService;
} else {
    window.UserManagementService = UserManagementService;
}
