/**
 * Assignment Model
 * Represents a faculty-subject assignment entity
 */
class Assignment {
    constructor(data = {}) {
        this.id = data.id || data.assignment_id || null;
        this.subjectId = data.subject_id || data.subjectId || null;
        this.facultyId = data.faculty_id || data.facultyId || null;
        this.yearLevel = data.year_level || data.yearLevel || '';
        this.section = data.section || '';
        this.academicYear = data.academic_year || data.academicYear || '';
        this.semester = data.semester || '';
        this.status = data.status || 'active';
        this.notes = data.notes || '';
        this.createdAt = data.created_at || data.createdAt || null;
        
        // Additional display fields
        this.subjectCode = data.subject_code || data.subjectCode || '';
        this.subjectName = data.subject_name || data.subjectName || '';
        this.facultyName = data.faculty_name || data.facultyName || '';
    }

    /**
     * Validate assignment data
     */
    validate() {
        const errors = [];

        if (!this.subjectId) {
            errors.push('Subject is required');
        }

        if (!this.facultyId) {
            errors.push('Faculty is required');
        }

        if (!this.yearLevel) {
            errors.push('Year level is required');
        }

        if (!this.section) {
            errors.push('Section is required');
        }

        if (!this.academicYear) {
            errors.push('Academic year is required');
        }

        if (!this.semester) {
            errors.push('Semester is required');
        }

        if (!['active', 'inactive', 'pending'].includes(this.status)) {
            errors.push('Invalid status');
        }

        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }

    /**
     * Check if assignment is active
     */
    isActive() {
        return this.status === 'active';
    }

    /**
     * Get status badge class
     */
    getStatusClass() {
        const classes = {
            'active': 'bg-green-100 text-green-800',
            'inactive': 'bg-red-100 text-red-800',
            'pending': 'bg-yellow-100 text-yellow-800'
        };
        return classes[this.status] || 'bg-grey-100 text-grey-800';
    }

    /**
     * Get status icon
     */
    getStatusIcon() {
        const icons = {
            'active': 'fas fa-check-circle',
            'inactive': 'fas fa-times-circle',
            'pending': 'fas fa-clock'
        };
        return icons[this.status] || 'fas fa-question-circle';
    }

    /**
     * Convert to JSON for API
     */
    toJSON() {
        return {
            id: this.id,
            subject_id: this.subjectId,
            faculty_id: this.facultyId,
            year_level: this.yearLevel,
            section: this.section,
            academic_year: this.academicYear,
            semester: this.semester,
            status: this.status,
            notes: this.notes
        };
    }

    /**
     * Create Assignment from JSON
     */
    static fromJSON(data) {
        return new Assignment(data);
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Assignment;
} else {
    window.Assignment = Assignment;
}
