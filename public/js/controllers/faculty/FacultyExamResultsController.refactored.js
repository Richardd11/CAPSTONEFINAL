/**
 * FacultyExamResultsController - Lean Controller (MVC)
 * Orchestrates Service, View, and Model layers
 * NO BUSINESS LOGIC - Pure orchestration
 */
class FacultyExamResultsController {
    constructor(service, view) {
        this.service = service;
        this.view = view;
        
        // State management
        this.currentExamId = null;
        this.examsData = [];
        this.currentResults = [];
        this.currentOverrideData = null;
        
        this.initialize();
    }

    /**
     * Initialize controller
     */
    initialize() {
        this.loadExams();
        this.setupModalListeners();
        this.setupOverrideModalListeners();
        this.handleURLParameters();
    }

    /**
     * Handle URL parameters for filtering
     */
    handleURLParameters() {
        const urlParams = new URLSearchParams(window.location.search);
        const subjectId = urlParams.get('subject');
        const subjectCode = urlParams.get('code');
        
        if (subjectId && subjectCode) {
            this.view.updateHeaderForSubject(subjectCode);
        }
    }

    /**
     * Load exams from service
     */
    async loadExams() {
        const result = await this.service.fetchExams();
        
        if (result.success) {
            this.examsData = result.exams;
            this.displayExams();
        } else {
            this.view.showError(document.getElementById('examsList'), 'Error loading exams');
        }
    }

    /**
     * Display exams list
     */
    displayExams() {
        const urlParams = new URLSearchParams(window.location.search);
        const subjectCode = urlParams.get('code');
        
        const filteredExams = this.service.filterExamsBySubject(this.examsData, subjectCode);
        this.view.renderExamsList(filteredExams, this.currentExamId, subjectCode);
    }

    /**
     * Select exam and load results
     */
    async selectExam(examId) {
        this.currentExamId = examId;
        this.displayExams(); // Refresh to show selection
        
        const container = document.getElementById('resultsContainer');
        this.view.showLoading(container);

        const result = await this.service.fetchExamResults(examId);
        
        if (result.success && result.results.length > 0) {
            this.currentResults = result.results;
            this.view.renderResults(result.results);
        } else {
            this.currentResults = [];
            this.view.showNoResults(container);
        }
    }

    /**
     * View student details
     */
    async viewDetails(attemptId) {
        const modalBody = document.getElementById('modalBody');
        this.view.openModal('detailsModal');
        this.view.showLoading(modalBody);

        const result = await this.service.fetchStudentDetails(attemptId);
        
        if (result.success && result.data) {
            this.displayStudentDetails(result.data);
        } else {
            this.view.showError(modalBody, 'Error loading student details');
        }
    }

    /**
     * Display student details (delegated to helper file for brevity)
     */
    displayStudentDetails(data) {
        // This is complex rendering - keep in separate renderer
        const renderer = new StudentDetailsRenderer(this.service);
        renderer.render(data);
    }

    /**
     * Close details modal
     */
    closeDetailsModal() {
        this.view.closeModal('detailsModal');
    }

    /**
     * Export exam results to CSV
     */
    exportExamResults() {
        if (!this.currentExamId || !this.currentResults || this.currentResults.length === 0) {
            alert('No exam results to export');
            return;
        }
        
        const examInfo = this.examsData.find(exam => exam.id == this.currentExamId);
        if (!examInfo) {
            alert('Exam information not found');
            return;
        }
        
        // Generate CSV using service
        const csvData = this.service.generateCSVData(examInfo, this.currentResults);
        const csvContent = this.service.csvDataToString(csvData);
        const filename = this.service.generateCSVFilename(examInfo);
        
        // Download using view
        const success = this.view.downloadCSV(csvContent, filename);
        
        if (success) {
            this.view.showToast('Exam results exported successfully!', 'success');
        } else {
            alert('Your browser does not support file downloads');
        }
    }

    /**
     * Show override modal
     */
    showOverrideModal(attemptId, questionId, currentScore, maxPoints) {
        console.log('Override Modal Data:', { attemptId, questionId, currentScore, maxPoints });
        
        this.currentOverrideData = { attemptId, questionId, currentScore, maxPoints };
        
        const modal = document.getElementById('overrideModal');
        const form = document.getElementById('overrideForm');
        
        if (!modal || !form) {
            console.error('Override modal elements not found');
            this.view.showToast('Override modal not available', 'error');
            return;
        }
        
        this.view.populateOverrideModal(currentScore, maxPoints);
        this.view.openModal('overrideModal');
        document.getElementById('newScore').focus();
    }

    /**
     * Close override modal
     */
    closeOverrideModal() {
        this.view.closeModal('overrideModal');
        this.currentOverrideData = null;
    }

    /**
     * Submit override
     */
    async submitOverride() {
        if (!this.currentOverrideData) {
            this.view.showToast('No override data available', 'error');
            return;
        }
        
        const newScore = parseFloat(document.getElementById('newScore').value);
        const reason = document.getElementById('overrideReason').value.trim();
        
        // Validate using service
        const validation = this.service.validateOverride(newScore, reason, this.currentOverrideData.maxPoints);
        
        if (!validation.isValid) {
            this.view.showToast(validation.errors[0], 'error');
            return;
        }
        
        const submitBtn = document.getElementById('submitOverride');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
        submitBtn.disabled = true;
        
        try {
            const result = await this.service.submitScoreOverride(
                this.currentOverrideData.attemptId,
                this.currentOverrideData.questionId,
                newScore,
                reason
            );
            
            if (result.success) {
                this.view.showToast('Score overridden successfully!', 'success');
                this.closeOverrideModal();
                
                // Refresh details
                if (this.currentOverrideData.attemptId) {
                    this.viewDetails(this.currentOverrideData.attemptId);
                }
            } else {
                this.view.showToast(result.message || 'Failed to override score', 'error');
            }
        } catch (error) {
            this.view.showToast('An error occurred while overriding the score', 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    /**
     * Setup modal listeners
     */
    setupModalListeners() {
        const modal = document.getElementById('detailsModal');
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.closeDetailsModal();
            }
        });
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                this.closeDetailsModal();
            }
        });
    }

    /**
     * Setup override modal listeners
     */
    setupOverrideModalListeners() {
        const modal = document.getElementById('overrideModal');
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.closeOverrideModal();
            }
        });
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                this.closeOverrideModal();
            }
        });
    }
}

// Initialize controller when DOM is ready
let facultyExamResults;
document.addEventListener('DOMContentLoaded', () => {
    const service = new FacultyExamResultsService();
    const view = new FacultyExamResultsView(service);
    facultyExamResults = new FacultyExamResultsController(service, view);
});

// Global functions for onclick handlers (backward compatibility)
function selectExam(examId) {
    facultyExamResults.selectExam(examId);
}

function viewDetails(attemptId) {
    facultyExamResults.viewDetails(attemptId);
}

function closeDetailsModal() {
    facultyExamResults.closeDetailsModal();
}

function exportExamResults() {
    facultyExamResults.exportExamResults();
}

function showToast(message, type) {
    facultyExamResults.view.showToast(message, type);
}

window.showOverrideModal = function(attemptId, questionId, currentScore, maxPoints) {
    facultyExamResults.showOverrideModal(attemptId, questionId, currentScore, maxPoints);
}

window.closeOverrideModal = function() {
    facultyExamResults.closeOverrideModal();
}

window.submitOverride = function() {
    facultyExamResults.submitOverride();
}
