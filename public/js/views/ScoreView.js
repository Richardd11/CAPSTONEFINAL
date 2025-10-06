/**
 * Score View
 * Handles all score-related UI rendering
 * View layer - Only handles presentation, NO business logic
 */
class ScoreView {
    constructor() {
        this.scoresModal = document.getElementById('scoresModal');
        this.scoresLoading = document.getElementById('scoresLoading');
        this.scoresData = document.getElementById('scoresData');
        this.noScoresData = document.getElementById('noScoresData');
        this.subjectFilter = document.getElementById('subjectFilter');
        this.yearFilter = document.getElementById('yearFilter');
    }

    /**
     * Show scores modal
     */
    showModal() {
        if (this.scoresModal) {
            this.scoresModal.classList.remove('hidden');
        }
    }

    /**
     * Hide scores modal
     */
    hideModal() {
        if (this.scoresModal) {
            this.scoresModal.classList.add('hidden');
        }
    }

    /**
     * Show loading state
     */
    showLoading() {
        if (this.scoresLoading) {
            this.scoresLoading.classList.remove('hidden');
        }
        if (this.scoresData) {
            this.scoresData.classList.add('hidden');
        }
        if (this.noScoresData) {
            this.noScoresData.classList.add('hidden');
        }
    }

    /**
     * Show no data state
     */
    showNoData() {
        if (this.scoresLoading) {
            this.scoresLoading.classList.add('hidden');
        }
        if (this.scoresData) {
            this.scoresData.classList.add('hidden');
        }
        if (this.noScoresData) {
            this.noScoresData.classList.remove('hidden');
        }
    }

    /**
     * Render scores by subject
     */
    renderScores(subjectGroups, scoreService) {
        if (this.scoresLoading) {
            this.scoresLoading.classList.add('hidden');
        }
        if (this.scoresData) {
            this.scoresData.classList.remove('hidden');
        }

        let html = '';
        Object.keys(subjectGroups).forEach(subjectKey => {
            const group = subjectGroups[subjectKey];
            const avgScore = scoreService.calculateAverage(group.exams);
            
            html += `
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-book text-white"></i>
                            </div>
                            <div>
                                <h5 class="text-lg font-bold text-gray-800">${group.subject.subject_code}</h5>
                                <p class="text-gray-600">${group.subject.subject_name}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Average Points</p>
                            <p class="text-2xl font-bold text-green-600">${Math.round((avgScore / 100) * (data.total_points || 100))}/${data.total_points || 100}</p>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 font-semibold text-gray-700">Student</th>
                                    <th class="text-left py-2 font-semibold text-gray-700">Exam</th>
                                    <th class="text-left py-2 font-semibold text-gray-700">Score</th>
                                    <th class="text-left py-2 font-semibold text-gray-700">Date</th>
                                    <th class="text-left py-2 font-semibold text-gray-700">Status</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            group.exams.forEach(exam => {
                const scoreColor = scoreService.getScoreColorClass(exam.score);
                const statusColor = scoreService.getStatusColorClass(exam.status);
                
                html += `
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-gray-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">${exam.student_name || 'N/A'}</p>
                                    <p class="text-xs text-gray-500">${exam.school_id || ''}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3">
                            <p class="font-medium text-gray-800">${exam.exam_title || 'N/A'}</p>
                            <p class="text-xs text-gray-500">${exam.exam_type || ''}</p>
                        </td>
                        <td class="py-3">
                            <span class="text-lg font-bold ${scoreColor}">${exam.score || 0}%</span>
                        </td>
                        <td class="py-3 text-gray-600">
                            ${exam.end_time ? new Date(exam.end_time).toLocaleDateString() : 'N/A'}
                        </td>
                        <td class="py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium ${statusColor}">
                                ${exam.status || 'Unknown'}
                            </span>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        });
        
        if (this.scoresData) {
            this.scoresData.innerHTML = html;
        }
    }

    /**
     * Populate subject filter dropdown
     */
    populateSubjectFilter(subjects) {
        if (!this.subjectFilter) return;

        // Clear existing options except "All Subjects"
        while (this.subjectFilter.children.length > 1) {
            this.subjectFilter.removeChild(this.subjectFilter.lastChild);
        }
        
        // Add subject options
        subjects.forEach(subject => {
            const option = document.createElement('option');
            option.value = subject.subject_id;
            option.textContent = `${subject.subject_code} - ${subject.subject_name}`;
            this.subjectFilter.appendChild(option);
        });
    }

    /**
     * Show analytics placeholder
     */
    showAnalyticsPlaceholder() {
        showToast('Score Analytics feature coming soon!', 'info');
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ScoreView;
} else {
    window.ScoreView = ScoreView;
}
