/**
 * Subject Model
 * Represents a subject/course entity
 */
class Subject {
    constructor(data = {}) {
        this.subjectId = data.subject_id || data.subjectId || null;
        this.subjectCode = data.subject_code || data.subjectCode || '';
        this.subjectName = data.subject_name || data.subjectName || '';
        this.description = data.description || '';
        this.units = data.units || 3;
        this.yearLevel = data.year_level || data.yearLevel || '';
        this.semester = data.semester || '';
        this.createdAt = data.created_at || data.createdAt || null;
    }

    /**
     * Validate subject data
     */
    validate() {
        const errors = [];

        if (!this.subjectCode || this.subjectCode.trim().length === 0) {
            errors.push('Subject code is required');
        }

        if (!this.subjectName || this.subjectName.trim().length === 0) {
            errors.push('Subject name is required');
        }

        if (!this.units || this.units < 1 || this.units > 6) {
            errors.push('Units must be between 1 and 6');
        }

        if (!this.yearLevel) {
            errors.push('Year level is required');
        }

        if (!['1st', '2nd', '3rd', '4th'].includes(this.yearLevel)) {
            errors.push('Invalid year level');
        }

        if (!this.semester) {
            errors.push('Semester is required');
        }

        if (!['1st Semester', '2nd Semester', 'Summer'].includes(this.semester)) {
            errors.push('Invalid semester');
        }

        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }

    /**
     * Get year-semester key for grouping
     */
    getYearSemesterKey() {
        return `${this.yearLevel} - ${this.semester}`;
    }

    /**
     * Format created date
     */
    getFormattedDate() {
        if (!this.createdAt) return 'N/A';
        const date = new Date(this.createdAt);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }

    /**
     * Convert to JSON for API
     */
    toJSON() {
        return {
            subject_id: this.subjectId,
            subject_code: this.subjectCode,
            subject_name: this.subjectName,
            description: this.description,
            units: this.units,
            year_level: this.yearLevel,
            semester: this.semester
        };
    }

    /**
     * Create Subject from JSON
     */
    static fromJSON(data) {
        return new Subject(data);
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Subject;
} else {
    window.Subject = Subject;
}
