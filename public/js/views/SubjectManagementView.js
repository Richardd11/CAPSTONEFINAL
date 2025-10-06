/**
 * Subject Management View
 * Handles all subject-related UI rendering
 * View layer - Only handles presentation, NO business logic
 */
class SubjectManagementView {
    constructor() {
        this.yearSemesterTabs = document.getElementById('yearSemesterTabs');
        this.subjectsContent = document.getElementById('subjectsContent');
        this.addModal = document.getElementById('addSubjectModal');
        this.editModal = document.getElementById('editSubjectModal');
        this.deleteModal = document.getElementById('deleteSubjectModal');
    }

    /**
     * Render subjects grouped by year and semester
     */
    renderSubjects(subjects, service) {
        const yearSemesterGroups = service.groupByYearSemester(subjects);
        
        // Generate tabs
        this.renderTabs(yearSemesterGroups);
        
        // Generate content
        this.renderContent(yearSemesterGroups);
    }

    /**
     * Render year-semester tabs
     */
    renderTabs(yearSemesterGroups) {
        if (!this.yearSemesterTabs) return;
        
        this.yearSemesterTabs.innerHTML = '';
        
        let firstTab = true;
        Object.keys(yearSemesterGroups).forEach(yearSemester => {
            const count = yearSemesterGroups[yearSemester].length;
            const tabId = 'tab-' + yearSemester.replace(/\s+/g, '-').toLowerCase();
            
            const tab = document.createElement('button');
            tab.className = `year-semester-tab ${firstTab ? 'active' : ''} bg-grey-100 border border-grey-300 text-grey-600 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-300 hover:bg-primary-600 hover:text-white hover:border-primary-600`;
            tab.setAttribute('data-section', tabId);
            tab.innerHTML = `${yearSemester} <span class="bg-green-500 text-white rounded-full w-5 h-5 inline-flex items-center justify-center text-xs font-bold ml-2">${count}</span>`;
            
            tab.addEventListener('click', () => this.showYearSemesterSection(tabId));
            
            this.yearSemesterTabs.appendChild(tab);
            firstTab = false;
        });
    }

    /**
     * Render subjects content
     */
    renderContent(yearSemesterGroups) {
        if (!this.subjectsContent) return;
        
        this.subjectsContent.innerHTML = '';
        
        let firstSection = true;
        Object.keys(yearSemesterGroups).forEach(yearSemester => {
            const subjects = yearSemesterGroups[yearSemester];
            const sectionId = 'tab-' + yearSemester.replace(/\s+/g, '-').toLowerCase();
            
            const section = document.createElement('div');
            section.className = `year-semester-section ${firstSection ? 'active' : ''} ${!firstSection ? 'hidden' : ''}`;
            section.id = sectionId;
            
            section.innerHTML = this.generateSubjectsSectionHTML(yearSemester, subjects);
            this.subjectsContent.appendChild(section);
            firstSection = false;
        });
    }

    /**
     * Generate HTML for subjects section
     */
    generateSubjectsSectionHTML(yearSemester, subjects) {
        let html = `
            <div class="bg-grey-100 p-4 rounded-lg mb-4 border-l-4 border-primary-600">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-lg font-bold text-primary-600">
                            <i class="fas fa-book mr-2"></i>
                            ${yearSemester}
                        </h6>
                    </div>
                    <div>
                        <span class="text-grey-600 text-sm">${subjects.length} subjects</span>
                    </div>
                </div>
            </div>
        `;
        
        subjects.forEach(subject => {
            const subjectObj = subject instanceof Subject ? subject : new Subject(subject);
            
            html += `
                <div class="bg-white border border-grey-200 rounded-lg p-6 mb-4 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="flex justify-between items-center">
                        <div class="flex-1">
                            <div class="text-lg font-bold text-primary-600 mb-2">
                                <i class="fas fa-book mr-2"></i>
                                ${this.escapeHtml(subject.subject_name || subject.subjectName)}
                            </div>
                            <div class="text-grey-600 text-sm mb-2">
                                <i class="fas fa-code mr-2"></i>
                                ${this.escapeHtml(subject.subject_code || subject.subjectCode)}
                            </div>
                            ${subject.description ? `
                            <div class="text-grey-500 text-sm mb-2">
                                <i class="fas fa-info-circle mr-2"></i>
                                ${this.escapeHtml(subject.description)}
                            </div>
                            ` : ''}
                            <div class="text-grey-500 text-xs">
                                <i class="fas fa-calendar mr-2"></i>
                                ${subject.units} units • Added on ${subjectObj.getFormattedDate()}
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm transition-all duration-300" onclick="editSubject(${subject.subject_id || subject.subjectId})">
                                <i class="fas fa-edit mr-1"></i>
                                Edit
                            </button>
                            <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition-all duration-300" onclick="deleteSubject(${subject.subject_id || subject.subjectId})">
                                <i class="fas fa-trash mr-1"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        return html;
    }

    /**
     * Show year-semester section
     */
    showYearSemesterSection(sectionId) {
        // Hide all sections
        document.querySelectorAll('.year-semester-section').forEach(section => {
            section.classList.add('hidden');
            section.classList.remove('active');
        });
        
        // Remove active class from all tabs
        document.querySelectorAll('.year-semester-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Show selected section
        const selectedSection = document.getElementById(sectionId);
        if (selectedSection) {
            selectedSection.classList.remove('hidden');
            selectedSection.classList.add('active');
        }
        
        // Add active class to selected tab
        const activeTab = document.querySelector(`[data-section="${sectionId}"]`);
        if (activeTab) {
            activeTab.classList.add('active');
        }
    }

    /**
     * Show add subject modal
     */
    showAddModal() {
        if (this.addModal) {
            this.addModal.classList.remove('hidden');
        }
        const form = document.getElementById('addSubjectForm');
        if (form) form.reset();
    }

    /**
     * Hide add subject modal
     */
    hideAddModal() {
        if (this.addModal) {
            this.addModal.classList.add('hidden');
        }
    }

    /**
     * Show edit subject modal
     */
    showEditModal() {
        if (this.editModal) {
            this.editModal.classList.remove('hidden');
        }
    }

    /**
     * Hide edit subject modal
     */
    hideEditModal() {
        if (this.editModal) {
            this.editModal.classList.add('hidden');
        }
    }

    /**
     * Populate edit form
     */
    populateEditForm(subject) {
        const fields = {
            'editSubjectId': subject.subject_id || subject.subjectId,
            'editSubjectCode': subject.subject_code || subject.subjectCode,
            'editSubjectName': subject.subject_name || subject.subjectName,
            'editSubjectDescription': subject.description || '',
            'editSubjectUnits': subject.units,
            'editSubjectYearLevel': subject.year_level || subject.yearLevel,
            'editSubjectSemester': subject.semester
        };

        for (const [id, value] of Object.entries(fields)) {
            const element = document.getElementById(id);
            if (element) element.value = value;
        }
    }

    /**
     * Show delete confirmation modal
     */
    showDeleteModal() {
        if (this.deleteModal) {
            this.deleteModal.classList.remove('hidden');
        }
    }

    /**
     * Hide delete confirmation modal
     */
    hideDeleteModal() {
        if (this.deleteModal) {
            this.deleteModal.classList.add('hidden');
        }
    }

    /**
     * Show success message
     */
    showSuccessMessage(message) {
        const successDiv = document.createElement('div');
        successDiv.className = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex justify-between items-center';
        successDiv.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                ${message}
            </div>
            <button type="button" class="text-green-700 hover:text-green-900" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        const subjectsContent = document.getElementById('subjects');
        if (subjectsContent) {
            subjectsContent.insertBefore(successDiv, subjectsContent.firstChild);
        }
        
        setTimeout(() => {
            if (successDiv.parentElement) {
                successDiv.remove();
            }
        }, 5000);
    }

    /**
     * Show error message
     */
    showError(message) {
        alert('Error: ' + message);
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SubjectManagementView;
} else {
    window.SubjectManagementView = SubjectManagementView;
}
