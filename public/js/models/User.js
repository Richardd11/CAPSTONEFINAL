/**
 * User Model - Represents a user entity
 * Model layer - Contains data structure and validation
 */
class User {
    constructor(data = {}) {
        this.userId = data.user_id || data.userId || null;
        this.schoolId = data.school_id || data.schoolId || '';
        this.fullName = data.full_name || data.fullName || '';
        this.role = data.role || 'student';
        this.yearLevel = data.year_level || data.yearLevel || null;
        this.section = data.section || null;
        this.createdAt = data.created_at || data.createdAt || null;
    }

    /**
     * Validate user data
     */
    validate() {
        const errors = [];

        if (!this.schoolId || this.schoolId.trim().length === 0) {
            errors.push('School ID is required');
        }

        if (!this.fullName || this.fullName.trim().length === 0) {
            errors.push('Full name is required');
        }

        if (!['student', 'faculty', 'admin'].includes(this.role)) {
            errors.push('Invalid role');
        }

        // Student-specific validation
        if (this.role === 'student') {
            if (!this.yearLevel) {
                errors.push('Year level is required for students');
            }
            if (!this.section) {
                errors.push('Section is required for students');
            }
        }

        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }

    /**
     * Check if user is a student
     */
    isStudent() {
        return this.role === 'student';
    }

    /**
     * Check if user is faculty
     */
    isFaculty() {
        return this.role === 'faculty';
    }

    /**
     * Check if user is admin
     */
    isAdmin() {
        return this.role === 'admin';
    }

    /**
     * Get display name
     */
    getDisplayName() {
        return this.fullName;
    }

    /**
     * Get role badge class
     */
    getRoleBadgeClass() {
        const classes = {
            'student': 'bg-blue-100 text-blue-800',
            'faculty': 'bg-green-100 text-green-800',
            'admin': 'bg-purple-100 text-purple-800'
        };
        return classes[this.role] || 'bg-gray-100 text-gray-800';
    }

    /**
     * Get role icon
     */
    getRoleIcon() {
        const icons = {
            'student': 'fa-user-graduate',
            'faculty': 'fa-chalkboard-teacher',
            'admin': 'fa-user-shield'
        };
        return icons[this.role] || 'fa-user';
    }

    /**
     * Convert to JSON for API
     */
    toJSON() {
        return {
            user_id: this.userId,
            school_id: this.schoolId,
            full_name: this.fullName,
            role: this.role,
            year_level: this.yearLevel,
            section: this.section
        };
    }

    /**
     * Create User from JSON
     */
    static fromJSON(data) {
        return new User(data);
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = User;
} else {
    window.User = User;
}
