/**
 * Subject Management Controller
 * Coordinates subject management operations
 * Controller layer - Orchestrates Model, View, and Service
 */
class SubjectManagementController {
    constructor(subjectService, subjectView) {
        this.service = subjectService;
        this.view = subjectView;
        this.currentSubjects = [];
        this.currentDeleteSubjectId = null;
        
        this.initialize();
    }

    /**
     * Initialize controller
     */
    initialize() {
        this.setupEventListeners();
        this.loadSubjects();
    }

    /**
     * Initialize subjects data from PHP
     */
    initializeSubjectsData(subjects) {
        this.currentSubjects = subjects || [];
        this.view.renderSubjects(this.currentSubjects, this.service);
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Search functionality
        const searchInput = document.getElementById('subjectSearch');
        if (searchInput) {
            searchInput.addEventListener('input', () => this.filterSubjects());
        }
        
        // Filter functionality
        const yearFilter = document.getElementById('yearLevelFilter');
        if (yearFilter) {
            yearFilter.addEventListener('change', () => this.filterSubjects());
        }
        
        const semesterFilter = document.getElementById('semesterFilter');
        if (semesterFilter) {
            semesterFilter.addEventListener('change', () => this.filterSubjects());
        }
        
        // Form submissions
        const addForm = document.getElementById('addSubjectForm');
        if (addForm) {
            addForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.addSubject();
            });
        }
        
        const editForm = document.getElementById('editSubjectForm');
        if (editForm) {
            editForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.updateSubject();
            });
        }
    }

    // ========================================================================
    // DATA LOADING
    // ========================================================================

    /**
     * Load subjects
     */
    loadSubjects() {
        if (this.currentSubjects.length > 0) {
            this.view.renderSubjects(this.currentSubjects, this.service);
        }
    }

    // ========================================================================
    // FILTER OPERATIONS
    // ========================================================================

    /**
     * Filter subjects
     */
    filterSubjects() {
        const searchInput = document.getElementById('subjectSearch');
        const yearFilter = document.getElementById('yearLevelFilter');
        const semesterFilter = document.getElementById('semesterFilter');
        
        const filters = {
            search: searchInput ? searchInput.value : '',
            yearLevel: yearFilter ? yearFilter.value : '',
            semester: semesterFilter ? semesterFilter.value : ''
        };
        
        const filteredSubjects = this.service.applyFilters(this.currentSubjects, filters);
        this.view.renderSubjects(filteredSubjects, this.service);
    }

    /**
     * Clear filters
     */
    clearFilters() {
        const searchInput = document.getElementById('subjectSearch');
        const yearFilter = document.getElementById('yearLevelFilter');
        const semesterFilter = document.getElementById('semesterFilter');
        
        if (searchInput) searchInput.value = '';
        if (yearFilter) yearFilter.value = '';
        if (semesterFilter) semesterFilter.value = '';
        
        this.loadSubjects();
    }

    // ========================================================================
    // MODAL OPERATIONS
    // ========================================================================

    /**
     * Show add subject modal
     */
    showAddSubjectModal() {
        this.view.showAddModal();
    }

    /**
     * Hide add subject modal
     */
    hideAddSubjectModal() {
        this.view.hideAddModal();
    }

    /**
     * Show edit subject modal
     */
    showEditSubjectModal() {
        this.view.showEditModal();
    }

    /**
     * Hide edit subject modal
     */
    hideEditSubjectModal() {
        this.view.hideEditModal();
    }

    /**
     * Show delete subject modal
     */
    showDeleteSubjectModal() {
        this.view.showDeleteModal();
    }

    /**
     * Hide delete subject modal
     */
    hideDeleteSubjectModal() {
        this.view.hideDeleteModal();
        this.currentDeleteSubjectId = null;
    }

    // ========================================================================
    // CRUD OPERATIONS
    // ========================================================================

    /**
     * Add new subject
     */
    async addSubject() {
        const formData = new FormData(document.getElementById('addSubjectForm'));
        
        try {
            const response = await this.service.createSubject(Object.fromEntries(formData));
            
            if (response.success) {
                this.hideAddSubjectModal();
                await this.refreshSubjectsData();
                this.view.showSuccessMessage('Subject added successfully!');
            } else {
                this.view.showError(response.message || response.errors?.join(', ') || 'Failed to add subject');
            }
        } catch (error) {
            console.error('Error:', error);
            this.view.showError('An error occurred while adding the subject.');
        }
    }

    /**
     * Edit subject
     */
    async editSubject(subjectId) {
        try {
            const response = await this.service.getSubjectById(subjectId);
            
            if (response.status === 'success') {
                const subject = response.data;
                this.view.populateEditForm(subject);
                this.showEditSubjectModal();
            } else {
                this.view.showError(response.message || 'Failed to fetch subject data');
            }
        } catch (error) {
            console.error('Error:', error);
            this.view.showError('An error occurred while fetching subject data.');
        }
    }

    /**
     * Update subject
     */
    async updateSubject() {
        const formData = new FormData(document.getElementById('editSubjectForm'));
        const subjectId = formData.get('subject_id');
        
        try {
            const response = await this.service.updateSubject(subjectId, Object.fromEntries(formData));
            
            if (response.success) {
                this.hideEditSubjectModal();
                await this.refreshSubjectsData();
                this.view.showSuccessMessage('Subject updated successfully!');
            } else {
                this.view.showError(response.message || response.errors?.join(', ') || 'Failed to update subject');
            }
        } catch (error) {
            console.error('Error:', error);
            this.view.showError('An error occurred while updating the subject.');
        }
    }

    /**
     * Delete subject
     */
    deleteSubject(subjectId) {
        this.currentDeleteSubjectId = subjectId;
        this.showDeleteSubjectModal();
    }

    /**
     * Confirm delete subject
     */
    async confirmDeleteSubject() {
        if (!this.currentDeleteSubjectId) return;
        
        try {
            const response = await this.service.deleteSubject(this.currentDeleteSubjectId);
            
            if (response.success) {
                this.hideDeleteSubjectModal();
                await this.refreshSubjectsData();
                this.view.showSuccessMessage('Subject deleted successfully!');
            } else {
                this.view.showError(response.message || 'Failed to delete subject');
            }
        } catch (error) {
            console.error('Error:', error);
            this.view.showError('An error occurred while deleting the subject.');
        }
    }

    // ========================================================================
    // DATA REFRESH
    // ========================================================================

    /**
     * Refresh subjects data
     */
    async refreshSubjectsData() {
        try {
            const result = await this.service.refreshSubjects();
            
            if (result.success) {
                this.currentSubjects = result.data;
                this.loadSubjects();
            } else {
                console.error('Error refreshing subjects');
                location.reload();
            }
        } catch (error) {
            console.error('Error refreshing subjects:', error);
            location.reload();
        }
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SubjectManagementController;
} else {
    window.SubjectManagementController = SubjectManagementController;
}
