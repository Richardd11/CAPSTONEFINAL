/**
 * FacultyExamResultsView - View Layer
 * Handles all DOM manipulation and rendering
 * NO BUSINESS LOGIC - Pure presentation
 */
class FacultyExamResultsView {
    constructor(service) {
        this.service = service;
    }

    /**
     * Render exams list in sidebar
     */
    renderExamsList(exams, currentExamId, subjectCode) {
        const container = document.getElementById('examsList');
        
        if (!exams || exams.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clipboard-list text-gray-400 text-xl"></i>
                    </div>
                    <p class="text-gray-500 text-sm">${subjectCode ? `No exams found for ${subjectCode}` : 'No exams found'}</p>
                </div>
            `;
            return;
        }

        // Group by subject
        const grouped = this.service.groupExamsBySubject(exams);

        let html = '';
        Object.keys(grouped).sort().forEach(subject => {
            html += this._renderSubjectGroup(subject, grouped[subject], currentExamId);
        });

        container.innerHTML = html;
    }

    /**
     * Render subject group (private)
     */
    _renderSubjectGroup(subject, exams, currentExamId) {
        return `
            <div class="mb-6">
                <div class="subject-header p-4 mb-3 relative">
                    <div class="flex items-center relative z-10">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-book text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800">${subject}</h3>
                            <p class="text-xs text-gray-600">${exams.length} exam${exams.length !== 1 ? 's' : ''}</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    ${exams.map(exam => this._renderExamCard(exam, currentExamId)).join('')}
                </div>
            </div>
        `;
    }

    /**
     * Render exam card (private)
     */
    _renderExamCard(exam, currentExamId) {
        const isSelected = currentExamId === exam.id;
        return `
            <button onclick="facultyExamResults.selectExam(${exam.id})" 
                    class="exam-card w-full text-left p-4 transition-all ${isSelected ? 'border-blue-300 bg-blue-50' : 'hover:bg-gray-50'}">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-semibold text-gray-800 mb-1">${exam.title}</div>
                        <div class="text-xs text-gray-500 flex items-center">
                            <i class="fas fa-users mr-1"></i>
                            ${exam.students || 0} students
                            ${exam.date ? `<span class="mx-2">•</span><i class="fas fa-calendar mr-1"></i>${new Date(exam.date).toLocaleDateString()}` : ''}
                        </div>
                    </div>
                    ${isSelected ? '<i class="fas fa-check-circle text-blue-600"></i>' : '<i class="fas fa-chevron-right text-gray-400"></i>'}
                </div>
            </button>
        `;
    }

    /**
     * Show loading state
     */
    showLoading(container) {
        container.innerHTML = `
            <div class="text-center py-8">
                <div class="loading-spinner mx-auto mb-4"></div>
                <p class="text-gray-600">Loading results...</p>
            </div>
        `;
    }

    /**
     * Show no results message
     */
    showNoResults(container) {
        container.innerHTML = `
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-users-slash text-4xl mb-4"></i>
                <p>No results found for this exam</p>
            </div>
        `;
    }

    /**
     * Show error message
     */
    showError(container, message = 'Error loading results') {
        container.innerHTML = `
            <div class="text-center py-12 text-red-600">
                <i class="fas fa-exclamation-circle text-4xl mb-4"></i>
                <p>${message}</p>
            </div>
        `;
    }

    /**
     * Render results with statistics
     */
    renderResults(results) {
        const container = document.getElementById('resultsContainer');
        
        if (!results || results.length === 0) {
            container.innerHTML = `
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-users-slash text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Results Yet</h3>
                    <p class="text-gray-500">No students have taken this exam yet</p>
                </div>
            `;
            return;
        }

        const sortedResults = this.service.sortResultsByScore(results);
        const stats = this.service.calculateStatistics(results);

        container.innerHTML = `
            <div class="fade-in">
                ${this._renderStatisticsHeader(stats)}
                ${this._renderResultsGrid(sortedResults)}
            </div>
        `;
    }

    /**
     * Render statistics header (private)
     */
    _renderStatisticsHeader(stats) {
        return `
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Student Results</h2>
                    <p class="text-gray-600">Performance analysis and detailed breakdown</p>
                </div>
                <div class="flex items-center space-x-6">
                    <button onclick="facultyExamResults.exportExamResults()" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-4 py-2 rounded-xl font-semibold transition-all duration-200 transform hover:scale-105 inline-flex items-center">
                        <i class="fas fa-download mr-2"></i>Export CSV
                    </button>
                    <div class="flex space-x-4">
                        <div class="text-center p-3 bg-blue-50 rounded-xl">
                            <div class="text-lg font-bold text-blue-600">${stats.totalStudents}</div>
                            <div class="text-xs text-gray-600">Students</div>
                        </div>
                        <div class="text-center p-3 bg-green-50 rounded-xl">
                            <div class="text-lg font-bold text-green-600">${stats.averageScore.toFixed(1)}%</div>
                            <div class="text-xs text-gray-600">Average</div>
                        </div>
                        <div class="text-center p-3 bg-purple-50 rounded-xl">
                            <div class="text-lg font-bold text-purple-600">${stats.passRate.toFixed(0)}%</div>
                            <div class="text-xs text-gray-600">Pass Rate</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Render results grid (private)
     */
    _renderResultsGrid(results) {
        return `
            <div class="space-y-3">
                ${results.map((student, index) => this._renderStudentCard(student, index)).join('')}
            </div>
        `;
    }

    /**
     * Render student card (private)
     */
    _renderStudentCard(student, index) {
        const score = parseFloat(student.score) || 0;
        const grade = this.service.getGrade(score);
        const gradeColor = this.service.getGradeColor(score);
        const status = this.service.getStatus(score);
        const date = new Date(student.completed_at).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
        
        const rankIcon = index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : `#${index + 1}`;
        
        return `
            <div class="exam-card p-6 hover:shadow-lg transition-all">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-full ${index < 3 ? 'bg-gradient-to-br from-yellow-400 to-yellow-600' : 'bg-gray-100'} flex items-center justify-center font-bold ${index < 3 ? 'text-white' : 'text-gray-600'}">
                            ${index < 3 ? rankIcon : index + 1}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800 text-lg">${student.name || 'Unknown'}</div>
                            <div class="text-sm text-gray-500">${student.student_id || 'N/A'} • Completed ${date}</div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold ${score >= 75 ? 'text-green-600' : score >= 50 ? 'text-yellow-600' : 'text-red-600'}">
                                ${score.toFixed(1)}%
                            </div>
                            <div class="text-xs text-gray-500">Score</div>
                        </div>
                        <div class="text-center">
                            <span class="px-3 py-1 rounded-full text-sm font-semibold ${gradeColor}">
                                ${grade}
                            </span>
                            <div class="text-xs text-gray-500 mt-1">Grade</div>
                        </div>
                        <div class="text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-medium ${score >= 75 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                                ${status}
                            </span>
                            <div class="text-xs text-gray-500 mt-1">Status</div>
                        </div>
                        <button onclick="facultyExamResults.viewDetails(${student.id})" 
                                class="ios-button px-6 py-3 text-sm">
                            <i class="fas fa-eye mr-2"></i>View Details
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg text-white font-semibold transition-all duration-300 transform translate-x-full`;
        
        if (type === 'success') {
            toast.className += ' bg-green-500';
            toast.innerHTML = `<i class="fas fa-check mr-2"></i>${message}`;
        } else if (type === 'error') {
            toast.className += ' bg-red-500';
            toast.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i>${message}`;
        } else {
            toast.className += ' bg-blue-500';
            toast.innerHTML = `<i class="fas fa-info-circle mr-2"></i>${message}`;
        }
        
        document.body.appendChild(toast);
        
        setTimeout(() => toast.classList.remove('translate-x-full'), 100);
        
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

    /**
     * Open modal
     */
    openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    /**
     * Close modal
     */
    closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    /**
     * Update header for subject filter
     */
    updateHeaderForSubject(subjectCode) {
        const headerTitle = document.querySelector('.header-gradient h1');
        const headerSubtitle = document.querySelector('.header-gradient p');
        
        if (headerTitle && headerSubtitle) {
            headerTitle.textContent = `${subjectCode} - Exam Results`;
            headerSubtitle.textContent = `View and analyze student performance for ${subjectCode}`;
        }
    }

    /**
     * Populate override modal
     */
    populateOverrideModal(currentScore, maxPoints) {
        const form = document.getElementById('overrideForm');
        form.reset();
        document.getElementById('currentScoreDisplay').textContent = `${currentScore}/${maxPoints}`;
        document.getElementById('newScore').max = maxPoints;
        document.getElementById('newScore').value = currentScore;
    }

    /**
     * Download CSV file
     */
    downloadCSV(csvContent, filename) {
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        
        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            return true;
        }
        return false;
    }
}
