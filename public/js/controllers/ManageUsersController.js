/**
 * Manage Users Controller
 * Coordinates user management operations for manage-users page
 * Controller layer - Orchestrates Model, View, and Service
 */
class ManageUsersController {
    constructor(userService, userView) {
        this.service = userService;
        this.view = userView;
        this.studentsData = [];
        this.facultyData = [];
        this.studentIdToStudent = {};
        this.facultyIdToFaculty = {};
        this.pendingDeleteStudentId = null;
        this.pendingDeleteFacultyId = null;
        
        this.initialize();
    }

    /**
     * Initialize controller
     */
    initialize() {
        this.setupEventListeners();
    }

    /**
     * Initialize data from PHP
     */
    initializeData(students, faculty) {
        this.studentsData = students || [];
        this.facultyData = faculty || [];
        this.studentIdToStudent = Object.fromEntries((this.studentsData || []).map(s => [String(s.user_id), s]));
        this.facultyIdToFaculty = Object.fromEntries((this.facultyData || []).map(f => [String(f.user_id), f]));
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Year-Section tab switching will be handled by inline event listeners
        // Modal outside-click handlers
        this.setupModalClickHandlers();
    }

    /**
     * Setup modal click outside handlers
     */
    setupModalClickHandlers() {
        const modals = [
            'editStudentModal',
            'deleteFacultyModal',
            'deleteStudentModal',
            'addStudentModal',
            'editFacultyModal',
            'addFacultyModal'
        ];

        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        this.closeModal(modalId);
                    }
                });
            }
        });
    }

    // ========================================================================
    // TAB SWITCHING
    // ========================================================================

    showUsersSubtab(which) {
        const studentsBtn = document.getElementById('students-subtab-btn');
        const facultyBtn = document.getElementById('faculty-subtab-btn');
        const studentsTab = document.getElementById('students-subtab');
        const facultyTab = document.getElementById('faculty-subtab');

        if (which === 'students') {
            studentsTab?.classList.remove('hidden');
            facultyTab?.classList.add('hidden');
            studentsBtn?.classList.add('bg-primary-600', 'text-white');
            studentsBtn?.classList.remove('bg-white', 'text-grey-700', 'border', 'border-grey-300');
            facultyBtn?.classList.remove('bg-primary-600', 'text-white');
            facultyBtn?.classList.add('bg-white', 'text-grey-700', 'border', 'border-grey-300');
        } else {
            facultyTab?.classList.remove('hidden');
            studentsTab?.classList.add('hidden');
            facultyBtn?.classList.add('bg-primary-600', 'text-white');
            facultyBtn?.classList.remove('bg-white', 'text-grey-700', 'border', 'border-grey-300');
            studentsBtn?.classList.remove('bg-primary-600', 'text-white');
            studentsBtn?.classList.add('bg-white', 'text-grey-700', 'border', 'border-grey-300');
        }
    }

    // ========================================================================
    // STUDENT OPERATIONS
    // ========================================================================

    showAddStudentModal() {
        const modal = document.getElementById('addStudentModal');
        if (modal) modal.classList.remove('hidden');
    }

    editStudent(studentId) {
        const student = this.studentIdToStudent[String(studentId)];
        if (student) {
            document.getElementById('editStudentId').value = student.user_id;
            document.getElementById('edit_school_id').value = student.school_id || '';
            document.getElementById('edit_full_name').value = student.full_name || '';
            document.getElementById('edit_year_level').value = student.year_level || '';
            document.getElementById('edit_section').value = student.section || '';
        } else {
            document.getElementById('editStudentId').value = studentId;
        }
        document.getElementById('editStudentModal').classList.remove('hidden');
    }

    deleteStudent(studentId) {
        this.pendingDeleteStudentId = studentId;
        document.getElementById('deleteStudentModal').classList.remove('hidden');
    }

    cancelDeleteStudent() {
        this.pendingDeleteStudentId = null;
        document.getElementById('deleteStudentModal').classList.add('hidden');
    }

    confirmDeleteStudent() {
        if (!this.pendingDeleteStudentId) return;
        
        const basePath = window.location.pathname.split('/').slice(0, -1).join('/');
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = basePath + '/users/delete-student';
        
        const userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = this.pendingDeleteStudentId;
        
        form.appendChild(userIdInput);
        document.body.appendChild(form);
        form.submit();
    }

    submitStudentForm() {
        const form = document.getElementById('addStudentForm');
        if (form && form.checkValidity()) {
            form.submit();
            setTimeout(() => {
                this.resetForm('addStudentForm');
                this.closeModal('addStudentModal');
            }, 100);
        } else {
            form?.reportValidity();
        }
    }

    submitEditStudentForm() {
        const form = document.getElementById('editStudentForm');
        if (form && form.checkValidity()) {
            form.submit();
            setTimeout(() => {
                this.closeModal('editStudentModal');
            }, 100);
        } else {
            form?.reportValidity();
        }
    }

    // ========================================================================
    // FACULTY OPERATIONS
    // ========================================================================

    showAddFacultyModal() {
        const modal = document.getElementById('addFacultyModal');
        if (modal) modal.classList.remove('hidden');
    }

    editFaculty(facultyId) {
        const faculty = this.facultyIdToFaculty[String(facultyId)];
        if (faculty) {
            document.getElementById('editFacultyId').value = faculty.user_id;
            document.getElementById('edit_faculty_school_id').value = faculty.school_id || '';
            document.getElementById('edit_faculty_full_name').value = faculty.full_name || '';
        } else {
            document.getElementById('editFacultyId').value = facultyId;
        }
        document.getElementById('editFacultyModal').classList.remove('hidden');
    }

    deleteFaculty(facultyId) {
        this.pendingDeleteFacultyId = facultyId;
        document.getElementById('deleteFacultyModal').classList.remove('hidden');
    }

    cancelDeleteFaculty() {
        this.pendingDeleteFacultyId = null;
        document.getElementById('deleteFacultyModal').classList.add('hidden');
    }

    confirmDeleteFaculty() {
        if (!this.pendingDeleteFacultyId) return;
        
        const basePath = window.location.pathname.split('/').slice(0, -1).join('/');
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = basePath + '/users/delete-faculty';
        
        const userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = this.pendingDeleteFacultyId;
        
        form.appendChild(userIdInput);
        document.body.appendChild(form);
        form.submit();
    }

    submitFacultyForm() {
        const form = document.getElementById('addFacultyForm');
        if (form && form.checkValidity()) {
            form.submit();
            setTimeout(() => {
                this.resetForm('addFacultyForm');
                this.closeModal('addFacultyModal');
            }, 100);
        } else {
            form?.reportValidity();
        }
    }

    submitEditFacultyForm() {
        const form = document.getElementById('editFacultyForm');
        if (form && form.checkValidity()) {
            form.submit();
            setTimeout(() => {
                this.closeModal('editFacultyModal');
            }, 100);
        } else {
            form?.reportValidity();
        }
    }

    // ========================================================================
    // UTILITY FUNCTIONS
    // ========================================================================

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('hidden');
    }

    resetForm(formId) {
        const form = document.getElementById(formId);
        if (form) form.reset();
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ManageUsersController;
} else {
    window.ManageUsersController = ManageUsersController;
}
