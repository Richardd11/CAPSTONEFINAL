/**
 * Subject List Controller
 * Handles subject listing and filtering
 * Controller layer - Orchestrates Model, View, and Service
 */
class SubjectListController {
    constructor(subjectService) {
        this.service = subjectService;
        this.subjectToDelete = null;
        
        this.initialize();
    }

    /**
     * Initialize controller
     */
    initialize() {
        this.setupEventListeners();
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Search and filter functionality
        const searchInput = document.getElementById('subjectSearch');
        if (searchInput) {
            searchInput.addEventListener('input', () => this.filterSubjects());
        }
        
        const yearFilter = document.getElementById('yearFilter');
        if (yearFilter) {
            yearFilter.addEventListener('change', () => this.filterSubjects());
        }
        
        const semesterFilter = document.getElementById('semesterFilter');
        if (semesterFilter) {
            semesterFilter.addEventListener('change', () => this.filterSubjects());
        }

        // Form submission
        const subjectForm = document.getElementById('subjectForm');
        if (subjectForm) {
            subjectForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit();
            });
        }
    }

    // ========================================================================
    // FILTER OPERATIONS
    // ========================================================================

    /**
     * Filter subjects
     */
    filterSubjects() {
        const searchTerm = document.getElementById('subjectSearch')?.value.toLowerCase() || '';
        const selectedYear = document.getElementById('yearFilter')?.value || '';
        const selectedSemester = document.getElementById('semesterFilter')?.value || '';
        
        const yearSections = document.querySelectorAll('[data-year-section]');
        
        yearSections.forEach(section => {
            const yearLevel = section.getAttribute('data-year-section');
            const subjectCards = section.querySelectorAll('.subject-card');
            let visibleCards = 0;
            
            subjectCards.forEach(card => {
                const subjectCode = card.querySelector('h3')?.textContent.toLowerCase() || '';
                const subjectName = card.querySelector('p')?.textContent.toLowerCase() || '';
                const semester = card.getAttribute('data-semester') || '';
                
                const matchesSearch = subjectCode.includes(searchTerm) || subjectName.includes(searchTerm);
                const matchesYear = !selectedYear || yearLevel === selectedYear;
                const matchesSemester = !selectedSemester || semester === selectedSemester;
                
                if (matchesSearch && matchesYear && matchesSemester) {
                    card.style.display = 'block';
                    visibleCards++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            section.style.display = visibleCards > 0 ? 'block' : 'none';
        });
    }

    // ========================================================================
    // MODAL OPERATIONS
    // ========================================================================

    /**
     * Show add subject modal
     */
    showAddSubjectModal() {
        document.getElementById('modalTitle').textContent = 'Add New Subject';
        document.getElementById('submitText').textContent = 'Add Subject';
        document.getElementById('formAction').value = 'add';
        document.getElementById('subjectForm').reset();
        document.getElementById('subjectId').value = '';
        document.getElementById('subjectModal').classList.remove('hidden');
    }

    /**
     * Edit subject
     */
    editSubject(subject) {
        document.getElementById('modalTitle').textContent = 'Edit Subject';
        document.getElementById('submitText').textContent = 'Update Subject';
        document.getElementById('formAction').value = 'edit';
        
        document.getElementById('subjectId').value = subject.subject_id;
        document.getElementById('subjectCode').value = subject.subject_code;
        document.getElementById('subjectName').value = subject.subject_name;
        document.getElementById('description').value = subject.description || '';
        document.getElementById('units').value = subject.units;
        document.getElementById('yearLevel').value = subject.year_level;
        document.getElementById('semester').value = subject.semester;
        
        document.getElementById('subjectModal').classList.remove('hidden');
    }

    /**
     * Close subject modal
     */
    closeSubjectModal() {
        document.getElementById('subjectModal').classList.add('hidden');
    }

    // ========================================================================
    // DELETE OPERATIONS
    // ========================================================================

    /**
     * Delete subject
     */
    deleteSubject(subjectId, subjectCode) {
        this.subjectToDelete = { id: subjectId, code: subjectCode };
        document.getElementById('deleteSubjectCode').textContent = subjectCode;
        document.getElementById('deleteSubjectModal').classList.remove('hidden');
    }

    /**
     * Close delete modal
     */
    closeDeleteSubjectModal() {
        document.getElementById('deleteSubjectModal').classList.add('hidden');
        this.subjectToDelete = null;
    }

    /**
     * Confirm delete subject
     */
    confirmDeleteSubject() {
        if (!this.subjectToDelete) return;
        
        const deleteBtn = document.querySelector('#deleteSubjectModal button[onclick="confirmDeleteSubject()"]');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';
        
        const formData = new FormData();
        formData.append('subject_id', this.subjectToDelete.id);
        
        const basePath = window.location.pathname.split('/').slice(0, -1).join('/');
        
        fetch(basePath + '/subjects/delete', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.status === 'success' || data.success) {
                this.showToast('Subject deleted successfully!', 'success');
                this.closeDeleteSubjectModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(data.message || 'Failed to delete subject');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showToast('Error: ' + error.message, 'error');
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalText;
        });
    }

    // ========================================================================
    // FORM SUBMISSION
    // ========================================================================

    /**
     * Handle form submission
     */
    handleFormSubmit() {
        const formData = new FormData(document.getElementById('subjectForm'));
        const action = formData.get('action');
        
        const basePath = window.location.pathname.split('/').slice(0, -1).join('/');
        let url = basePath + '/subjects/';
        if (action === 'add') {
            url += 'add';
        } else if (action === 'edit') {
            url += 'edit';
        }
        
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.status === 'success' || data.success) {
                const actionText = action === 'add' ? 'created' : 'updated';
                this.showToast(`Subject ${actionText} successfully!`, 'success');
                this.closeSubjectModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                this.showToast('Error: ' + (data.message || 'Unknown error occurred'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showToast('An error occurred while processing your request.', 'error');
        });
    }

    // ========================================================================
    // TOAST NOTIFICATION
    // ========================================================================

    /**
     * Show toast notification
     */
    showToast(message, type = 'success') {
        const existingToast = document.getElementById('toast');
        if (existingToast) existingToast.remove();

        const toast = document.createElement('div');
        toast.id = 'toast';
        toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-medium transform transition-all duration-300 translate-x-full ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        }`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(toast);
        setTimeout(() => toast.classList.remove('translate-x-full'), 100);
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => { if (toast.parentNode) toast.remove(); }, 300);
        }, 3000);
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SubjectListController;
} else {
    window.SubjectListController = SubjectListController;
}
