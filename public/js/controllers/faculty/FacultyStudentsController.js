/**
 * FacultyStudentsController - MVC Controller for Faculty Students Page
 * Handles student list display, search, filtering, and group management
 */
class FacultyStudentsController {
    constructor() {
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
        // Search functionality
        const searchInput = document.getElementById('studentSearch');
        if (searchInput) {
            searchInput.addEventListener('input', () => this.handleSearch());
        }

        // Filter functionality
        const yearFilter = document.getElementById('yearFilter');
        const sectionFilter = document.getElementById('sectionFilter');
        
        if (yearFilter) {
            yearFilter.addEventListener('change', () => this.filterStudents());
        }
        
        if (sectionFilter) {
            sectionFilter.addEventListener('change', () => this.filterStudents());
        }
    }

    /**
     * Handle search input
     */
    handleSearch() {
        const searchInput = document.getElementById('studentSearch');
        const searchTerm = searchInput.value.toLowerCase();
        const studentRows = document.querySelectorAll('.student-row');
        
        studentRows.forEach(row => {
            const name = row.dataset.name;
            const id = row.dataset.id;
            const matches = name.includes(searchTerm) || id.includes(searchTerm);
            row.style.display = matches ? '' : 'none';
        });
        
        // Hide empty groups
        this.updateGroupVisibility();
    }

    /**
     * Filter students by year and section
     */
    filterStudents() {
        const yearFilter = document.getElementById('yearFilter').value;
        const sectionFilter = document.getElementById('sectionFilter').value;
        const groups = document.querySelectorAll('.group-section');
        
        groups.forEach(group => {
            const groupYear = group.dataset.year;
            const groupSection = group.dataset.section;
            
            const yearMatch = !yearFilter || groupYear === yearFilter;
            const sectionMatch = !sectionFilter || groupSection === sectionFilter;
            
            group.style.display = (yearMatch && sectionMatch) ? '' : 'none';
        });
    }

    /**
     * Update group visibility based on visible rows
     */
    updateGroupVisibility() {
        const groups = document.querySelectorAll('.group-section');
        
        groups.forEach(group => {
            const visibleRows = group.querySelectorAll('.student-row:not([style*="display: none"])');
            group.style.display = visibleRows.length > 0 ? '' : 'none';
        });
    }

    /**
     * Toggle group collapse/expand
     */
    toggleGroup(header) {
        const content = header.nextElementSibling;
        const icon = header.querySelector('.group-icon');
        
        if (content.style.display === 'none') {
            content.style.display = '';
            icon.style.transform = 'rotate(0deg)';
        } else {
            content.style.display = 'none';
            icon.style.transform = 'rotate(-90deg)';
        }
    }

    /**
     * Export section data
     */
    exportSection(year, section) {
        console.log('Exporting section:', year, section);
        
        // Collect student data for this section
        const groupSection = document.querySelector(`[data-year="${year}"][data-section="${section}"]`);
        if (!groupSection) return;
        
        const studentRows = groupSection.querySelectorAll('.student-row');
        const students = [];
        
        studentRows.forEach(row => {
            const id = row.querySelector('.font-mono').textContent.trim();
            const name = row.querySelector('.font-semibold.text-gray-800').textContent.trim();
            const subjectInfo = row.querySelector('td:last-child .text-sm');
            
            students.push({
                id,
                name,
                subject: subjectInfo ? subjectInfo.textContent.trim() : 'N/A'
            });
        });
        
        // Create CSV content
        const csvContent = this.generateCSV(students, year, section);
        
        // Download CSV
        this.downloadCSV(csvContent, `Students_${year}_Section${section}.csv`);
        
        // Show success toast
        this.showToast(`Exported ${students.length} students from ${year} - Section ${section}`, 'success');
    }

    /**
     * Generate CSV content
     */
    generateCSV(students, year, section) {
        let csv = `Year Level,Section,Student ID,Full Name,Subject\n`;
        
        students.forEach(student => {
            csv += `"${year}","${section}","${student.id}","${student.name}","${student.subject}"\n`;
        });
        
        return csv;
    }

    /**
     * Download CSV file
     */
    downloadCSV(content, filename) {
        const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        
        if (navigator.msSaveBlob) {
            // IE 10+
            navigator.msSaveBlob(blob, filename);
        } else {
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'success') {
        const toast = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' : 
                       type === 'error' ? 'bg-red-500' : 'bg-blue-500';
        
        toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-2xl shadow-lg z-50 transform translate-x-full transition-all duration-300`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
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
let facultyStudents;
document.addEventListener('DOMContentLoaded', () => {
    facultyStudents = new FacultyStudentsController();
});

// Global functions for onclick handlers (backward compatibility)
function toggleGroup(header) {
    facultyStudents.toggleGroup(header);
}

function exportSection(year, section) {
    facultyStudents.exportSection(year, section);
}
