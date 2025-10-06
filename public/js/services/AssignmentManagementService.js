/**
 * Assignment Management Service
 * Business logic layer - Handles all assignment-related business operations
 */
class AssignmentManagementService {
    constructor(apiService) {
        this.api = apiService || window.apiService;
    }

    /**
     * Get all assignments
     */
    async getAssignments(filters = {}) {
        try {
            const params = new URLSearchParams(filters);
            const endpoint = `/assignments${params.toString() ? '?' + params.toString() : ''}`;
            return await this.api.get(endpoint);
        } catch (error) {
            console.error('Error fetching assignments:', error);
            throw error;
        }
    }

    /**
     * Get assignment by ID
     */
    async getAssignmentById(assignmentId) {
        try {
            return await this.api.get(`/assignments/${assignmentId}`);
        } catch (error) {
            console.error('Error fetching assignment:', error);
            throw error;
        }
    }

    /**
     * Create new assignment
     */
    async createAssignment(assignmentData) {
        try {
            // Validate before sending
            const assignment = new Assignment(assignmentData);
            const validation = assignment.validate();
            
            if (!validation.isValid) {
                return {
                    success: false,
                    errors: validation.errors
                };
            }

            const response = await this.api.post('/assignments/add', assignment.toJSON());
            
            return {
                success: response.status === 'success' || response.success,
                message: response.message || 'Assignment created successfully',
                assignment: response.assignment || null
            };
        } catch (error) {
            console.error('Error creating assignment:', error);
            throw error;
        }
    }

    /**
     * Update existing assignment
     */
    async updateAssignment(assignmentId, assignmentData) {
        try {
            // Validate before sending
            const assignment = new Assignment({ ...assignmentData, id: assignmentId });
            const validation = assignment.validate();
            
            if (!validation.isValid) {
                return {
                    success: false,
                    errors: validation.errors
                };
            }

            const response = await this.api.post('/assignments/edit', assignment.toJSON());
            
            return {
                success: response.status === 'success' || response.success,
                message: response.message || 'Assignment updated successfully',
                assignment: response.assignment || null
            };
        } catch (error) {
            console.error('Error updating assignment:', error);
            throw error;
        }
    }

    /**
     * Delete assignment
     */
    async deleteAssignment(assignmentId) {
        try {
            const response = await this.api.post('/assignments/delete', { assignment_id: assignmentId });
            
            return {
                success: response.status === 'success' || response.success,
                message: response.message || 'Assignment deleted successfully'
            };
        } catch (error) {
            console.error('Error deleting assignment:', error);
            throw error;
        }
    }

    /**
     * Validate assignment data (client-side)
     */
    validateAssignment(assignmentData) {
        const assignment = new Assignment(assignmentData);
        return assignment.validate();
    }

    /**
     * Get assignment statistics
     */
    async getAssignmentStatistics() {
        try {
            const response = await this.api.get('/assignments/stats');
            return response.data || response;
        } catch (error) {
            console.error('Error fetching statistics:', error);
            throw error;
        }
    }

    /**
     * Refresh assignments data
     */
    async refreshAssignments() {
        try {
            const response = await this.api.get('/assignments/refresh');
            return {
                success: response.status === 'success',
                data: response.data || []
            };
        } catch (error) {
            console.error('Error refreshing assignments:', error);
            throw error;
        }
    }

    /**
     * Group assignments by subject
     */
    groupBySubject(assignments) {
        const groups = {};
        assignments.forEach(assignment => {
            const key = assignment.subject_code || assignment.subjectCode;
            if (!groups[key]) {
                groups[key] = [];
            }
            groups[key].push(assignment);
        });
        return groups;
    }

    /**
     * Group assignments by faculty
     */
    groupByFaculty(assignments) {
        const groups = {};
        assignments.forEach(assignment => {
            const key = assignment.faculty_name || assignment.facultyName;
            if (!groups[key]) {
                groups[key] = [];
            }
            groups[key].push(assignment);
        });
        return groups;
    }

    /**
     * Filter assignments by year and section
     */
    filterByYearSection(assignments, yearLevel, section) {
        return assignments.filter(a => {
            const matchesYear = !yearLevel || a.year_level === yearLevel || a.yearLevel === yearLevel;
            const matchesSection = !section || a.section === section;
            return matchesYear && matchesSection;
        });
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AssignmentManagementService;
} else {
    window.AssignmentManagementService = AssignmentManagementService;
}
