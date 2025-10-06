/**
 * FacultyDashboardController - MVC Controller for Faculty Dashboard
 * Handles all dashboard interactions, modals, and data export
 */
class FacultyDashboardController {
    constructor() {
        this.currentSubjectId = null;
        this.availableExams = [];
        this.selectedExams = new Set();
        
        this.initialize();
    }

    /**
     * Initialize controller
     */
    initialize() {
        this.setupEventListeners();
        
        // Show welcome modal if first time
        if (window.modernModal && this.isFirstVisit()) {
            this.showWelcomeModal();
        }
    }

    /**
     * Setup all event listeners
     */
    setupEventListeners() {
        // Modal close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeSubjectModal();
            }
        });
        
        // Add modern modal triggers
        this.setupModernModalTriggers();
    }

    /**
     * Show subject details modal
     */
    showSubjectDetails(subjectData) {
        const modal = document.getElementById('subjectModal');
        const modalContent = modal.querySelector('.relative.bg-gradient-to-br');
        const backdrop = modal.querySelector('.fixed.inset-0.bg-black\\/0');
        
        // Update modal title
        document.getElementById('modalSubjectTitle').textContent = 
            `${subjectData.subject_code} - ${subjectData.subject_name}`;
        
        // Populate modal content
        this.populateSubjectModalContent(subjectData);
        
        // Show modal with animation
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            backdrop.style.background = 'rgba(0, 0, 0, 0.4)';
            backdrop.style.backdropFilter = 'blur(12px)';
            
            setTimeout(() => {
                modalContent.style.transform = 'scale(1.05) translateY(-8px) rotate(0deg)';
                modalContent.style.opacity = '1';
                modalContent.style.filter = 'blur(0px)';
                
                setTimeout(() => {
                    modalContent.style.transform = 'scale(1) translateY(0) rotate(0deg)';
                }, 300);
            }, 200);
        }, 100);
        
        this.currentSubjectId = subjectData.subject_id;
    }

    /**
     * Populate subject modal content
     */
    populateSubjectModalContent(subjectData) {
        const content = document.getElementById('modalSubjectContent');
        
        content.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Subject Information -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-200">
                    <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        Subject Information
                    </h4>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Code:</span>
                            <span class="font-semibold text-gray-800">${subjectData.subject_code}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Name:</span>
                            <span class="font-semibold text-gray-800">${subjectData.subject_name}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Year Level:</span>
                            <span class="font-semibold text-gray-800">${subjectData.year_level}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Section:</span>
                            <span class="font-semibold text-gray-800">${subjectData.section}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Semester:</span>
                            <span class="font-semibold text-gray-800">${subjectData.semester}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Academic Year:</span>
                            <span class="font-semibold text-gray-800">${subjectData.academic_year}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-6 border border-green-200">
                    <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-bolt text-green-600 mr-2"></i>
                        Quick Actions
                    </h4>
                    <div class="space-y-3">
                        <button onclick="facultyDashboard.viewSubjectStudents('${subjectData.subject_id}')" 
                                class="w-full bg-white hover:bg-blue-50 text-left px-4 py-3 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-200 flex items-center">
                            <i class="fas fa-users text-blue-600 mr-3"></i>
                            <span class="font-medium text-gray-700">View Students</span>
                        </button>
                        <button onclick="facultyDashboard.viewSubjectScores('${subjectData.subject_id}', '${subjectData.subject_code}')" 
                                class="w-full bg-white hover:bg-green-50 text-left px-4 py-3 rounded-xl border border-gray-200 hover:border-green-300 transition-all duration-200 flex items-center">
                            <i class="fas fa-chart-bar text-green-600 mr-3"></i>
                            <span class="font-medium text-gray-700">View Scores</span>
                        </button>
                        <a href="/faculty/create-exam?subject_id=${subjectData.subject_id}" 
                           class="w-full bg-white hover:bg-purple-50 text-left px-4 py-3 rounded-xl border border-gray-200 hover:border-purple-300 transition-all duration-200 flex items-center">
                            <i class="fas fa-plus text-purple-600 mr-3"></i>
                            <span class="font-medium text-gray-700">Create Exam</span>
                        </a>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Close subject modal
     */
    closeSubjectModal() {
        const modal = document.getElementById('subjectModal');
        const modalContent = modal.querySelector('.relative.bg-gradient-to-br');
        const backdrop = modal.querySelector('.fixed.inset-0.bg-black\\/0');
        
        modalContent.style.transform = 'scale(0.8) translateY(30px) rotate(-2deg)';
        modalContent.style.opacity = '0';
        modalContent.style.filter = 'blur(4px)';
        backdrop.style.background = 'rgba(0, 0, 0, 0)';
        backdrop.style.backdropFilter = 'blur(0px)';
        
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            this.currentSubjectId = null;
            modalContent.style.transform = 'scale(0.75) translateY(48px) rotate(1deg)';
            modalContent.style.opacity = '0';
            modalContent.style.filter = 'blur(0px)';
        }, 700);
    }

    /**
     * Navigate to students page filtered by subject
     */
    viewSubjectStudents(subjectId) {
        window.location.href = `/faculty/students?subject=${subjectId}`;
    }

    /**
     * Navigate to exam results page filtered by subject
     */
    viewSubjectScores(subjectId, subjectCode) {
        window.location.href = `/faculty/exam-results?subject=${subjectId}&code=${encodeURIComponent(subjectCode)}`;
    }

    /**
     * Show modern notification (replaced old toast system)
     */
    showNotification(message, type = 'success') {
        if (window.modernModal) {
            switch(type) {
                case 'success':
                    window.modernModal.success('Success', message, { autoClose: 3000 });
                    break;
                case 'error':
                    window.modernModal.error('Error', message);
                    break;
                case 'info':
                    window.modernModal.info('Information', message, { autoClose: 3000 });
                    break;
                default:
                    window.modernModal.info('Notification', message, { autoClose: 3000 });
            }
        }
    }
    
    /**
     * Setup modern modal triggers
     */
    setupModernModalTriggers() {
        // Add click handlers for modern modals
        document.addEventListener('click', (e) => {
            // Handle exam delete buttons
            if (e.target.closest('.delete-exam-btn')) {
                e.preventDefault();
                const examId = e.target.closest('.delete-exam-btn').dataset.examId;
                const examTitle = e.target.closest('.delete-exam-btn').dataset.examTitle;
                this.confirmDeleteExam(examId, examTitle);
            }
            
            // Handle export buttons with modern feedback
            if (e.target.closest('.export-btn')) {
                e.preventDefault();
                this.showExportModal();
            }
        });
    }
    
    /**
     * Check if this is first visit
     */
    isFirstVisit() {
        const visited = localStorage.getItem('faculty_dashboard_visited');
        if (!visited) {
            localStorage.setItem('faculty_dashboard_visited', 'true');
            return true;
        }
        return false;
    }
    
    /**
     * Show welcome modal for new faculty
     */
    showWelcomeModal() {
        window.modernModal.info(
            'Welcome to Faculty Dashboard!',
            'Here you can manage your exams, view student results, and export data. Click on any subject card to get started.',
            {
                confirmText: 'Get Started',
                icon: 'fas fa-graduation-cap',
                autoClose: 5000
            }
        );
    }
    
    /**
     * Confirm exam deletion with modern modal
     */
    async confirmDeleteExam(examId, examTitle) {
        const confirmed = await window.modernModal.confirmDelete(examTitle, 'exam');
        if (confirmed) {
            this.deleteExam(examId, examTitle);
        }
    }
    
    /**
     * Delete exam with modern feedback
     */
    async deleteExam(examId, examTitle) {
        const loadingModal = window.modernModal.loading(
            'Deleting Exam...',
            'Please wait while we delete the exam and all associated data.'
        );
        
        try {
            const response = await fetch(`/faculty/exam/${examId}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            loadingModal.close();
            
            if (response.ok) {
                window.modernModal.examDeleted(examTitle);
                // Refresh the page or remove the exam element
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                throw new Error('Failed to delete exam');
            }
        } catch (error) {
            loadingModal.close();
            window.modernModal.error(
                'Delete Failed',
                'There was an error deleting the exam. Please try again.',
                {
                    confirmText: 'Retry',
                    onConfirm: () => this.deleteExam(examId, examTitle)
                }
            );
        }
    }
    
    /**
     * Show export modal
     */
    showExportModal() {
        window.modernModal.info(
            'Export Data',
            'Choose the data you want to export. The file will be downloaded automatically.',
            {
                confirmText: 'Start Export',
                onConfirm: () => this.startExport()
            }
        );
    }
    
    /**
     * Start export process
     */
    startExport() {
        const exportModal = window.modernModal.loading(
            'Preparing Export...',
            'We are preparing your data for download. This may take a few moments.',
            {
                icon: 'fas fa-download fa-spin'
            }
        );
        
        // Simulate export process
        setTimeout(() => {
            exportModal.close();
            window.modernModal.success(
                'Export Complete!',
                'Your data has been exported successfully. The download should start automatically.',
                {
                    confirmText: 'Done',
                    autoClose: 3000
                }
            );
        }, 2000);
    }
}

// Initialize controller when DOM is ready
let facultyDashboard;
document.addEventListener('DOMContentLoaded', () => {
    facultyDashboard = new FacultyDashboardController();
});

// Global functions for onclick handlers (backward compatibility)
function showSubjectDetails(subjectData) {
    facultyDashboard.showSubjectDetails(subjectData);
}

function closeSubjectModal() {
    facultyDashboard.closeSubjectModal();
}

function viewSubjectStudents(subjectId) {
    facultyDashboard.viewSubjectStudents(subjectId);
}

function viewSubjectScores(subjectId, subjectCode) {
    facultyDashboard.viewSubjectScores(subjectId, subjectCode);
}

// exportAllData is defined in faculty-dashboard-inline.js
// Don't override it here
