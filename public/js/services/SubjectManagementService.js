/**
 * Subject Management Service
 * Business logic layer - Handles all subject-related business operations
 */
class SubjectManagementService {
    constructor(apiService) {
        this.api = apiService || window.apiService;
    }

    /**
     * Get all subjects
     */
    async getSubjects(filters = {}) {
        try {
            const params = new URLSearchParams(filters);
            const endpoint = `/subjects${params.toString() ? '?' + params.toString() : ''}`;
            return await this.api.get(endpoint);
        } catch (error) {
            console.error('Error fetching subjects:', error);
            throw error;
        }
    }

    /**
     * Get subject by ID
     */
    async getSubjectById(subjectId) {
        try {
            return await this.api.get(`/subjects/${subjectId}`);
        } catch (error) {
            console.error('Error fetching subject:', error);
            throw error;
        }
    }

    /**
     * Create new subject
     */
    async createSubject(subjectData) {
        try {
            // Validate before sending
            const subject = new Subject(subjectData);
            const validation = subject.validate();
            
            if (!validation.isValid) {
                return {
                    success: false,
                    errors: validation.errors
                };
            }

            const response = await this.api.post('/subjects/add', subject.toJSON());
            
            return {
                success: response.status === 'success' || response.success,
                message: response.message || 'Subject created successfully',
                subject: response.subject || null
            };
        } catch (error) {
            console.error('Error creating subject:', error);
            throw error;
        }
    }

    /**
     * Update existing subject
     */
    async updateSubject(subjectId, subjectData) {
        try {
            // Validate before sending
            const subject = new Subject({ ...subjectData, subject_id: subjectId });
            const validation = subject.validate();
            
            if (!validation.isValid) {
                return {
                    success: false,
                    errors: validation.errors
                };
            }

            const response = await this.api.post('/subjects/edit', subject.toJSON());
            
            return {
                success: response.status === 'success' || response.success,
                message: response.message || 'Subject updated successfully',
                subject: response.subject || null
            };
        } catch (error) {
            console.error('Error updating subject:', error);
            throw error;
        }
    }

    /**
     * Delete subject
     */
    async deleteSubject(subjectId) {
        try {
            const response = await this.api.post('/subjects/delete', { subject_id: subjectId });
            
            return {
                success: response.status === 'success' || response.success,
                message: response.message || 'Subject deleted successfully'
            };
        } catch (error) {
            console.error('Error deleting subject:', error);
            throw error;
        }
    }

    /**
     * Validate subject data (client-side)
     */
    validateSubject(subjectData) {
        const subject = new Subject(subjectData);
        return subject.validate();
    }

    /**
     * Refresh subjects data
     */
    async refreshSubjects() {
        try {
            const response = await this.api.get('/subjects/refresh');
            return {
                success: response.status === 'success',
                data: response.data || []
            };
        } catch (error) {
            console.error('Error refreshing subjects:', error);
            throw error;
        }
    }

    /**
     * Group subjects by year level and semester
     */
    groupByYearSemester(subjects) {
        const groups = {};
        subjects.forEach(subject => {
            const subjectObj = subject instanceof Subject ? subject : new Subject(subject);
            const key = subjectObj.getYearSemesterKey();
            if (!groups[key]) {
                groups[key] = [];
            }
            groups[key].push(subject);
        });
        return groups;
    }

    /**
     * Filter subjects by search term
     */
    filterBySearch(subjects, searchTerm) {
        if (!searchTerm) return subjects;
        
        const term = searchTerm.toLowerCase();
        return subjects.filter(subject => {
            const code = (subject.subject_code || subject.subjectCode || '').toLowerCase();
            const name = (subject.subject_name || subject.subjectName || '').toLowerCase();
            const desc = (subject.description || '').toLowerCase();
            
            return code.includes(term) || name.includes(term) || desc.includes(term);
        });
    }

    /**
     * Filter subjects by year level
     */
    filterByYearLevel(subjects, yearLevel) {
        if (!yearLevel) return subjects;
        return subjects.filter(s => (s.year_level || s.yearLevel) === yearLevel);
    }

    /**
     * Filter subjects by semester
     */
    filterBySemester(subjects, semester) {
        if (!semester) return subjects;
        return subjects.filter(s => s.semester === semester);
    }

    /**
     * Apply all filters
     */
    applyFilters(subjects, filters = {}) {
        let filtered = subjects;
        
        if (filters.search) {
            filtered = this.filterBySearch(filtered, filters.search);
        }
        
        if (filters.yearLevel) {
            filtered = this.filterByYearLevel(filtered, filters.yearLevel);
        }
        
        if (filters.semester) {
            filtered = this.filterBySemester(filtered, filters.semester);
        }
        
        return filtered;
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SubjectManagementService;
} else {
    window.SubjectManagementService = SubjectManagementService;
}
