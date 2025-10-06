/**
 * Score Service
 * Business logic for score management
 */
class ScoreService {
    constructor(apiService) {
        this.api = apiService || window.apiService;
    }

    /**
     * Get scores by subject
     */
    async getScoresBySubject(filters = {}) {
        try {
            const response = await this.api.get('/scores-by-subject');
            return {
                success: response.success || true,
                data: response.data || [],
                subjects: response.subjects || []
            };
        } catch (error) {
            console.error('Error fetching scores:', error);
            throw error;
        }
    }

    /**
     * Get score statistics
     */
    async getScoreStatistics() {
        try {
            const response = await this.api.get('/score-statistics');
            return response.data || response;
        } catch (error) {
            console.error('Error fetching score statistics:', error);
            throw error;
        }
    }

    /**
     * Group scores by subject
     */
    groupScoresBySubject(scoresData) {
        const subjectGroups = {};
        
        scoresData.forEach(score => {
            const subjectKey = `${score.subject_code} - ${score.subject_name}`;
            if (!subjectGroups[subjectKey]) {
                subjectGroups[subjectKey] = {
                    subject: score,
                    exams: []
                };
            }
            subjectGroups[subjectKey].exams.push(score);
        });

        return subjectGroups;
    }

    /**
     * Calculate average score
     */
    calculateAverage(scores) {
        if (!scores || scores.length === 0) return 0;
        const sum = scores.reduce((total, score) => total + (parseFloat(score.score) || 0), 0);
        return sum / scores.length;
    }

    /**
     * Get score color class
     */
    getScoreColorClass(score) {
        const numScore = parseFloat(score) || 0;
        if (numScore >= 75) return 'text-green-600';
        if (numScore >= 60) return 'text-yellow-600';
        return 'text-red-600';
    }

    /**
     * Get status color class
     */
    getStatusColorClass(status) {
        const statusColors = {
            'completed': 'bg-green-100 text-green-800',
            'in_progress': 'bg-yellow-100 text-yellow-800',
            'pending': 'bg-gray-100 text-gray-800'
        };
        return statusColors[status] || 'bg-gray-100 text-gray-800';
    }

    /**
     * Export scores to CSV
     */
    exportScoresToCSV(scoresData, filename = 'scores.csv') {
        const headers = ['Subject Code', 'Subject Name', 'Student ID', 'Student Name', 'Exam', 'Score', 'Status', 'Date'];
        const rows = scoresData.map(score => [
            score.subject_code || '',
            score.subject_name || '',
            score.school_id || '',
            score.student_name || '',
            score.exam_title || '',
            score.score || '0',
            score.status || '',
            score.end_time ? new Date(score.end_time).toLocaleDateString() : ''
        ]);

        const csvContent = [
            headers.join(','),
            ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
        ].join('\n');

        // Download CSV
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        URL.revokeObjectURL(url);
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ScoreService;
} else {
    window.ScoreService = ScoreService;
}
