/**
 * Score Controller
 * Coordinates between ScoreService and ScoreView
 * Controller layer - Handles user actions and coordinates data flow
 */
class ScoreController {
    constructor(scoreService, scoreView) {
        this.service = scoreService;
        this.view = scoreView;
        this.currentScores = [];
    }

    /**
     * Show scores modal
     */
    async showScoresModal() {
        this.view.showModal();
        await this.loadScoresBySubject();
    }

    /**
     * Close scores modal
     */
    closeScoresModal() {
        this.view.hideModal();
    }

    /**
     * Load scores by subject
     */
    async loadScoresBySubject() {
        try {
            this.view.showLoading();

            const result = await this.service.getScoresBySubject();

            if (result.success && result.data && result.data.length > 0) {
                this.currentScores = result.data;
                const subjectGroups = this.service.groupScoresBySubject(result.data);
                this.view.renderScores(subjectGroups, this.service);
                this.view.populateSubjectFilter(result.subjects || []);
            } else {
                this.view.showNoData();
            }
        } catch (error) {
            console.error('Error loading scores:', error);
            this.view.showNoData();
            showToast('Error loading scores data', 'error');
        }
    }

    /**
     * Filter scores
     */
    async filterScores() {
        // Reload scores with filters
        await this.loadScoresBySubject();
    }

    /**
     * Show score analytics
     */
    showScoreAnalytics() {
        this.view.showAnalyticsPlaceholder();
    }

    /**
     * Export scores
     */
    exportScores() {
        if (this.currentScores.length === 0) {
            showToast('No scores to export', 'warning');
            return;
        }

        try {
            this.service.exportScoresToCSV(this.currentScores);
            showToast('Scores exported successfully!', 'success');
        } catch (error) {
            console.error('Error exporting scores:', error);
            showToast('Error exporting scores', 'error');
        }
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ScoreController;
} else {
    window.ScoreController = ScoreController;
}
