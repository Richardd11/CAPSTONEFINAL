/**
 * FacultyExamsController - MVC Controller for Faculty Exams Page
 * Handles exam list display, deletion, and dropdown interactions
 */
class FacultyExamsController {
    constructor() {
        this.examToDelete = null;
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
        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('[onclick^="toggleDropdown"]')) {
                document.querySelectorAll('[id^="dropdown-"]').forEach(d => {
                    d.classList.add('hidden');
                });
            }
        });

        // Escape key to close modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeDeleteModal();
            }
        });
    }

    /**
     * Toggle dropdown menu
     */
    toggleDropdown(examId) {
        const dropdown = document.getElementById(`dropdown-${examId}`);
        
        // Close all other dropdowns
        document.querySelectorAll('[id^="dropdown-"]').forEach(d => {
            if (d.id !== `dropdown-${examId}`) {
                d.classList.add('hidden');
            }
        });
        
        dropdown.classList.toggle('hidden');
    }

    /**
     * Delete exam (show confirmation modal)
     */
    deleteExam(examId) {
        this.examToDelete = examId;
        this.showDeleteModal();
    }

    /**
     * Show delete confirmation modal
     */
    showDeleteModal() {
        const modal = document.getElementById('deleteModal');
        const modalContent = modal.querySelector('.relative');
        const backdrop = modal.querySelector('.fixed.inset-0');
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Ultra-smooth multi-stage animation
        setTimeout(() => {
            // Stage 1: Backdrop fade with blur
            backdrop.style.background = 'rgba(0, 0, 0, 0.4)';
            backdrop.style.backdropFilter = 'blur(12px)';
            
            setTimeout(() => {
                // Stage 2: Modal entrance with spring physics
                modalContent.style.transform = 'scale(1.05) translateY(-8px) rotate(0deg)';
                modalContent.style.opacity = '1';
                modalContent.style.filter = 'blur(0px)';
                
                setTimeout(() => {
                    // Stage 3: Settle with micro-bounce
                    modalContent.style.transform = 'scale(1) translateY(0) rotate(0deg)';
                    
                    setTimeout(() => {
                        // Stage 4: Final subtle pulse
                        modalContent.style.transform = 'scale(1.01) translateY(0) rotate(0deg)';
                        setTimeout(() => {
                            modalContent.style.transform = 'scale(1) translateY(0) rotate(0deg)';
                        }, 150);
                    }, 200);
                }, 300);
            }, 200);
        }, 100);
    }

    /**
     * Close delete modal
     */
    closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        const modalContent = modal.querySelector('.relative');
        const backdrop = modal.querySelector('.fixed.inset-0');
        
        // Ultra-smooth exit animation
        modalContent.style.transform = 'scale(0.8) translateY(30px) rotate(-2deg)';
        modalContent.style.opacity = '0';
        modalContent.style.filter = 'blur(4px)';
        
        // Fade out backdrop
        backdrop.style.background = 'rgba(0, 0, 0, 0)';
        backdrop.style.backdropFilter = 'blur(0px)';
        
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            this.examToDelete = null;
            
            // Reset for next time
            modalContent.style.transform = 'scale(0.75) translateY(48px) rotate(1deg)';
            modalContent.style.opacity = '0';
            modalContent.style.filter = 'blur(0px)';
            
            // Reset button state
            const deleteBtn = document.getElementById('deleteButtonText');
            if (deleteBtn) {
                deleteBtn.innerHTML = 'Delete Exam';
                deleteBtn.parentElement.disabled = false;
            }
        }, 500);
    }

    /**
     * Confirm delete exam
     */
    async confirmDelete() {
        if (!this.examToDelete) return;
        
        const deleteBtn = document.getElementById('deleteButtonText');
        const deleteButton = deleteBtn.parentElement;
        
        // Show loading state
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';
        deleteButton.disabled = true;
        deleteButton.style.opacity = '0.7';
        
        try {
            const response = await fetch(`/faculty/exam/${this.examToDelete}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Show success state
                deleteBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Deleted!';
                deleteButton.style.background = 'linear-gradient(to right, #10b981, #059669)';
                
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                this.showToast('Failed to delete exam: ' + data.message, 'error');
                this.closeDeleteModal();
            }
        } catch (error) {
            console.error('Error:', error);
            this.showToast('An error occurred while deleting the exam', 'error');
            this.closeDeleteModal();
        }
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-4 rounded-2xl shadow-lg z-50 transform translate-x-full transition-all duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>
                ${message}
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
        }, 100);
        
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
}

// Initialize controller when DOM is ready
let facultyExams;
document.addEventListener('DOMContentLoaded', () => {
    facultyExams = new FacultyExamsController();
});

// Global functions for onclick handlers (backward compatibility)
function toggleDropdown(examId) {
    facultyExams.toggleDropdown(examId);
}

function deleteExam(examId) {
    facultyExams.deleteExam(examId);
}

function closeDeleteModal() {
    facultyExams.closeDeleteModal();
}

function confirmDelete() {
    facultyExams.confirmDelete();
}
