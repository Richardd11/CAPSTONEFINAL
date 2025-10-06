/**
 * Faculty Dashboard - Extracted Inline JavaScript
 * All business logic preserved exactly as it was
 * No changes to functionality - only moved from inline to separate file
 */

// Faculty-specific functions
document.addEventListener('DOMContentLoaded', function() {
    console.log('Faculty dashboard loaded');
});

// Open export dashboard
async function exportAllData() {
    // Show the export dashboard modal
    document.getElementById('exportDashboardModal').classList.remove('hidden');
    await loadExamsForExport();
}

// Export single exam data
async function exportSingleExamData(exam) {
    try {
        // Get base path
        const basePath = window.location.pathname.split('/').slice(0, -1).join('/');
        
        // Fetch exam results
        const response = await fetch(`${basePath}/api/exam/${exam.id}/results`);
        const data = await response.json();
        
        if (!data.success || !data.results || data.results.length === 0) {
            return false; // No results to export
        }
        
        const results = data.results;
        
        // Prepare CSV data
        const csvData = [];
        
        // Add header information
        csvData.push(['Exam Results Export']);
        csvData.push(['']);
        csvData.push(['Exam Title:', exam.title || 'N/A']);
        csvData.push(['Subject:', exam.subject || 'N/A']);
        csvData.push(['Date:', exam.date ? new Date(exam.date).toLocaleDateString() : 'N/A']);
        csvData.push(['Total Students:', results.length]);
        
        // Calculate statistics
        const totalStudents = results.length;
        const averageScore = results.reduce((sum, student) => sum + (parseFloat(student.score) || 0), 0) / totalStudents;
        const highestScore = Math.max(...results.map(s => parseFloat(s.score) || 0));
        const lowestScore = Math.min(...results.map(s => parseFloat(s.score) || 0));
        const passRate = (results.filter(s => (parseFloat(s.score) || 0) >= 75).length / totalStudents * 100);
        
        csvData.push(['Average Score:', averageScore.toFixed(2) + '%']);
        csvData.push(['Highest Score:', highestScore.toFixed(2) + '%']);
        csvData.push(['Lowest Score:', lowestScore.toFixed(2) + '%']);
        csvData.push(['Pass Rate (≥75%):', passRate.toFixed(1) + '%']);
        csvData.push(['Export Date:', new Date().toLocaleString()]);
        csvData.push(['']);
        
        // Add table headers
        csvData.push(['Rank', 'Student ID', 'Student Name', 'Score (%)', 'Grade', 'Status', 'Completion Date']);
        
        // Sort results by score (highest first)
        const sortedResults = [...results].sort((a, b) => (parseFloat(b.score) || 0) - (parseFloat(a.score) || 0));
        
        // Add student data with improved handling
        sortedResults.forEach((student, index) => {
            const score = parseFloat(student.score) || 0;
            const grade = getGradeForExport(score);
            const status = score >= 75 ? 'Satisfactory' : 'Needs Improvement';
            
            // Better handling of student name (API returns 'name' field)
            let studentName = 'Unknown Student';
            if (student.name && student.name !== 'N/A' && student.name !== 'Unknown Student') {
                studentName = student.name;
            } else if (student.student_name && student.student_name !== 'N/A') {
                studentName = student.student_name;
            } else if (student.full_name && student.full_name !== 'N/A') {
                studentName = student.full_name;
            }
            
            // Better handling of completion date (API returns 'completed_at' field)
            let completionDate = 'Not Available';
            if (student.completed_at && student.completed_at !== 'N/A') {
                try {
                    completionDate = new Date(student.completed_at).toLocaleString();
                } catch (e) {
                    completionDate = student.completed_at;
                }
            } else if (student.end_time && student.end_time !== 'N/A') {
                try {
                    completionDate = new Date(student.end_time).toLocaleString();
                } catch (e) {
                    completionDate = student.end_time;
                }
            } else if (student.completion_date && student.completion_date !== 'N/A') {
                try {
                    completionDate = new Date(student.completion_date).toLocaleString();
                } catch (e) {
                    completionDate = student.completion_date;
                }
            }
            
            // Better handling of student ID (API returns 'student_id' field)
            let studentId = 'Unknown ID';
            if (student.student_id && student.student_id !== 'N/A') {
                studentId = student.student_id;
            } else if (student.school_id && student.school_id !== 'N/A') {
                studentId = student.school_id;
            } else if (student.user_id && student.user_id !== 'N/A') {
                studentId = student.user_id;
            }
            
            csvData.push([
                index + 1, // Rank
                studentId,
                studentName,
                score.toFixed(2),
                grade,
                status,
                completionDate
            ]);
        });
        
        // Convert to CSV string
        const csvContent = csvData.map(row => 
            row.map(cell => {
                const cellStr = String(cell || '');
                if (cellStr.includes(',') || cellStr.includes('"') || cellStr.includes('\n')) {
                    return '"' + cellStr.replace(/"/g, '""') + '"';
                }
                return cellStr;
            }).join(',')
        ).join('\n');
        
        // Create and download file
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        
        // Generate filename
        const examTitle = (exam.title || 'Exam').replace(/[^a-zA-Z0-9]/g, '_');
        const subject = (exam.subject || 'Subject').replace(/[^a-zA-Z0-9]/g, '_');
        const dateStr = exam.date ? new Date(exam.date).toISOString().split('T')[0] : new Date().toISOString().split('T')[0];
        const filename = `${subject}_${examTitle}_Results_${dateStr}.csv`;
        
        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }
        
        return true; // Successfully exported
        
    } catch (error) {
        console.error('Error exporting exam:', exam.title, error);
        throw error;
    }
}

// Grade calculation function for export
function getGradeForExport(score) {
    if (score >= 95) return 'A+';
    if (score >= 90) return 'A';
    if (score >= 85) return 'B+';
    if (score >= 80) return 'B';
    if (score >= 75) return 'C+';
    if (score >= 70) return 'C';
    if (score >= 65) return 'D';
    return 'F';
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-2xl shadow-lg transform transition-all duration-300 translate-x-full`;
    
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
    notification.className += ` ${bgColor} text-white`;
    
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'} mr-3"></i>
            <span class="font-medium">${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Animate out and remove
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Enhanced subject modal functionality
function showSubjectDetails(subjectData) {
    currentSubjectId = subjectData.subject_id;
    
    document.getElementById('modalSubjectTitle').textContent = 
        `${subjectData.subject_code} - ${subjectData.subject_name}`;
    
    const content = `
        <!-- Subject Header Card -->
        <div class="relative bg-gradient-to-br from-blue-50 via-white to-indigo-50 rounded-3xl p-8 mb-8 overflow-hidden">
            <!-- Decorative background -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-400/10 to-purple-400/10 rounded-full blur-2xl transform translate-x-8 -translate-y-8"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-green-400/10 to-blue-400/10 rounded-full blur-xl transform -translate-x-4 translate-y-4"></div>
            
            <div class="relative">
                <div class="flex items-center mb-6">
                    <div class="relative w-16 h-16 mr-6">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-lg transform rotate-3"></div>
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl flex items-center justify-center transform -rotate-3">
                            <i class="fas fa-book text-white text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                            ${subjectData.subject_code}
                        </h3>
                        <p class="text-xl text-gray-700 font-medium">${subjectData.subject_name}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Information Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Subject Information -->
            <div class="relative bg-gradient-to-br from-white to-blue-50/30 rounded-2xl p-6 border border-blue-100/50 shadow-lg">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-info-circle text-white text-lg"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800">Subject Details</h4>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center p-3 bg-white/60 rounded-xl">
                        <span class="text-sm font-medium text-gray-600 w-20">Code:</span>
                        <span class="text-lg font-bold text-gray-800">${subjectData.subject_code}</span>
                    </div>
                    <div class="flex items-center p-3 bg-white/60 rounded-xl">
                        <span class="text-sm font-medium text-gray-600 w-20">Name:</span>
                        <span class="text-base font-semibold text-gray-800">${subjectData.subject_name}</span>
                    </div>
                </div>
            </div>
            
            <!-- Class Information -->
            <div class="relative bg-gradient-to-br from-white to-green-50/30 rounded-2xl p-6 border border-green-100/50 shadow-lg">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-graduation-cap text-white text-lg"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800">Class Details</h4>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center p-3 bg-white/60 rounded-xl">
                        <span class="text-sm font-medium text-gray-600 w-24">Year:</span>
                        <span class="text-base font-semibold text-gray-800">${subjectData.year_level}</span>
                    </div>
                    <div class="flex items-center p-3 bg-white/60 rounded-xl">
                        <span class="text-sm font-medium text-gray-600 w-24">Section:</span>
                        <span class="text-base font-semibold text-gray-800">${subjectData.section}</span>
                    </div>
                    <div class="flex items-center p-3 bg-white/60 rounded-xl">
                        <span class="text-sm font-medium text-gray-600 w-24">Semester:</span>
                        <span class="text-base font-semibold text-gray-800">${subjectData.semester}</span>
                    </div>
                    <div class="flex items-center p-3 bg-white/60 rounded-xl">
                        <span class="text-sm font-medium text-gray-600 w-24">Year:</span>
                        <span class="text-base font-semibold text-gray-800">${subjectData.academic_year}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="relative bg-gradient-to-br from-white to-purple-50/30 rounded-2xl p-6 border border-purple-100/50 shadow-lg">
            <div class="flex items-center mb-6">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mr-4">
                    <i class="fas fa-bolt text-white text-lg"></i>
                </div>
                <h4 class="text-xl font-bold text-gray-800">Quick Actions</h4>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="${window.location.pathname.split('/').slice(0, -1).join('/')}/create-exam?subject_id=${subjectData.subject_id}" 
                   class="group relative bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-4 rounded-2xl font-semibold transition-all duration-200 transform hover:scale-105 hover:shadow-lg shadow-blue-500/25 text-center">
                    <i class="fas fa-plus mr-2"></i>Create Exam
                    <div class="absolute inset-0 bg-white/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                </a>
                <a href="${window.location.pathname.split('/').slice(0, -1).join('/')}/exams" 
                   class="group relative bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-6 py-4 rounded-2xl font-semibold transition-all duration-200 transform hover:scale-105 hover:shadow-lg shadow-green-500/25 text-center">
                    <i class="fas fa-list mr-2"></i>View All Exams
                    <div class="absolute inset-0 bg-white/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                </a>
                <button onclick="viewSubjectStudents(${subjectData.subject_id})" 
                        class="group relative bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white px-6 py-4 rounded-2xl font-semibold transition-all duration-200 transform hover:scale-105 hover:shadow-lg shadow-purple-500/25">
                    <i class="fas fa-users mr-2"></i>View Students
                    <div class="absolute inset-0 bg-white/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                </button>
            </div>
        </div>
    `;
    
    // Update content
    const contentArea = document.getElementById('modalSubjectContent');
    contentArea.innerHTML = content;
    
    // Show modal with ultra-smooth animation
    const modal = document.getElementById('subjectModal');
    const modalContent = modal.querySelector('.relative.bg-gradient-to-br');
    const backdrop = modal.querySelector('.fixed.inset-0.bg-black\\/0');
    
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

let currentSubjectId = null;

// Modal close functions
function closeSubjectModal() {
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
        currentSubjectId = null;
        modalContent.style.transform = 'scale(0.75) translateY(48px) rotate(1deg)';
        modalContent.style.opacity = '0';
        modalContent.style.filter = 'blur(0px)';
    }, 700);
}

function viewSubjectStudents(subjectId) {
    // Navigate to students page filtered by subject
    const basePath = window.location.pathname.split('/').slice(0, -1).join('/');
    window.location.href = `${basePath}/students?subject=${subjectId}`;
}

// Navigation functions (scores modal removed - now using direct navigation)
function viewSubjectScores(subjectId, subjectCode) {
    // Redirect to exam results page filtered by subject
    const basePath = window.location.pathname.split('/').slice(0, -1).join('/');
    window.location.href = `${basePath}/exam-results?subject=${subjectId}&code=${encodeURIComponent(subjectCode)}`;
}

// Export Dashboard Functions
let availableExams = [];
let selectedExams = new Set();

async function loadExamsForExport() {
    const container = document.getElementById('exportExamsList');
    
    try {
        const basePath = window.location.pathname.split('/').slice(0, -1).join('/');
        const response = await fetch(`${basePath}/api/exams`);
        const data = await response.json();
        
        if (!data.success || !data.exams || data.exams.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-file-alt text-gray-400 text-xl"></i>
                    </div>
                    <p class="text-gray-500">No exams found</p>
                </div>
            `;
            return;
        }
        
        availableExams = data.exams;
        displayExamsForExport(data.exams);
        
    } catch (error) {
        console.error('Error loading exams:', error);
        container.innerHTML = `
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                </div>
                <p class="text-red-500">Error loading exams</p>
            </div>
        `;
    }
}

function displayExamsForExport(exams) {
    const container = document.getElementById('exportExamsList');
    
    const html = exams.map(exam => `
        <div class="exam-export-card p-4 border border-gray-200 rounded-xl hover:border-blue-300 transition-colors" data-exam-id="${exam.id}">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input type="checkbox" id="exam-${exam.id}" class="exam-checkbox w-5 h-5 text-blue-600 rounded mr-4" onchange="toggleExamSelection(${exam.id})">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-2">
                            <h4 class="font-semibold text-gray-800">${exam.title || 'Untitled Exam'}</h4>
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">${exam.subject || 'No Subject'}</span>
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">${exam.exam_type || 'exam'}</span>
                        </div>
                        <div class="flex items-center space-x-4 text-sm text-gray-600">
                            <span><i class="fas fa-calendar mr-1"></i>${exam.date ? new Date(exam.date).toLocaleDateString() : 'No date'}</span>
                            <span><i class="fas fa-clock mr-1"></i>${exam.time_limit || 60} minutes</span>
                            <span id="student-count-${exam.id}"><i class="fas fa-users mr-1"></i>Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="previewExamResults(${exam.id})" class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50 transition-colors" title="Preview Results">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="exportSingleExam(${exam.id})" class="text-green-600 hover:text-green-800 p-2 rounded-lg hover:bg-green-50 transition-colors" title="Export This Exam">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
    
    // Load student counts for each exam
    exams.forEach(exam => {
        loadExamStudentCount(exam.id);
    });
}

async function loadExamStudentCount(examId) {
    try {
        const basePath = window.location.pathname.split('/').slice(0, -1).join('/');
        const response = await fetch(`${basePath}/api/exam/${examId}/results`);
        const data = await response.json();
        
        const count = data.success && data.results ? data.results.length : 0;
        const countElement = document.getElementById(`student-count-${examId}`);
        if (countElement) {
            countElement.innerHTML = `<i class="fas fa-users mr-1"></i>${count} student${count !== 1 ? 's' : ''}`;
            
            // Disable checkbox if no results
            const checkbox = document.getElementById(`exam-${examId}`);
            if (checkbox && count === 0) {
                checkbox.disabled = true;
                checkbox.parentElement.parentElement.classList.add('opacity-50');
            }
        }
    } catch (error) {
        console.error(`Error loading student count for exam ${examId}:`, error);
        const countElement = document.getElementById(`student-count-${examId}`);
        if (countElement) {
            countElement.innerHTML = '<i class="fas fa-users mr-1"></i>Error';
        }
    }
}

function toggleExamSelection(examId) {
    const checkbox = document.getElementById(`exam-${examId}`);
    if (checkbox.checked) {
        selectedExams.add(examId);
    } else {
        selectedExams.delete(examId);
    }
    updateSelectionUI();
}

function selectAllExams() {
    const checkboxes = document.querySelectorAll('.exam-checkbox:not(:disabled)');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
        selectedExams.add(parseInt(checkbox.id.replace('exam-', '')));
    });
    updateSelectionUI();
}

function deselectAllExams() {
    const checkboxes = document.querySelectorAll('.exam-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    selectedExams.clear();
    updateSelectionUI();
}

function updateSelectionUI() {
    const count = selectedExams.size;
    document.getElementById('selectedCount').textContent = `${count} exam${count !== 1 ? 's' : ''} selected`;
    
    const exportBtn = document.getElementById('exportSelectedBtn');
    exportBtn.disabled = count === 0;
}

async function exportSelectedExams() {
    if (selectedExams.size === 0) return;
    
    const button = document.getElementById('exportSelectedBtn');
    const originalContent = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Exporting...';
    button.disabled = true;
    
    let totalExported = 0;
    const failedExports = [];
    
    for (const examId of selectedExams) {
        const exam = availableExams.find(e => e.id == examId);
        if (!exam) continue;
        
        try {
            const hasResults = await exportSingleExamData(exam);
            if (hasResults) totalExported++;
            
            // Small delay between exports
            await new Promise(resolve => setTimeout(resolve, 300));
        } catch (error) {
            console.error(`Failed to export exam ${exam.title}:`, error);
            failedExports.push(exam.title);
        }
    }
    
    // Show completion status
    button.innerHTML = '<i class="fas fa-check mr-2"></i>Exported!';
    
    let message = `Successfully exported ${totalExported} exam(s)`;
    if (failedExports.length > 0) {
        message += `. Failed: ${failedExports.length} exam(s)`;
    }
    
    showNotification(message, totalExported > 0 ? 'success' : 'warning');
    
    // Reset button after delay
    setTimeout(() => {
        button.innerHTML = originalContent;
        button.disabled = selectedExams.size === 0;
    }, 3000);
}

async function exportSingleExam(examId) {
    const exam = availableExams.find(e => e.id == examId);
    if (!exam) return;
    
    try {
        const hasResults = await exportSingleExamData(exam);
        if (hasResults) {
            showNotification(`Exported ${exam.title} successfully!`, 'success');
        } else {
            showNotification(`No results found for ${exam.title}`, 'warning');
        }
    } catch (error) {
        showNotification(`Failed to export ${exam.title}`, 'error');
    }
}

function previewExamResults(examId) {
    // Navigate to exam results page for this specific exam
    const basePath = window.location.pathname.split('/').slice(0, -1).join('/');
    window.open(`${basePath}/exam-results`, '_blank');
}

function closeExportDashboard() {
    document.getElementById('exportDashboardModal').classList.add('hidden');
    selectedExams.clear();
    updateSelectionUI();
}

// Dashboard initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Faculty dashboard loaded successfully');
});
