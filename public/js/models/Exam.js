/**
 * Exam Model - Represents a complete exam
 * Follows MVC Model pattern - contains exam data and business logic
 */
class Exam {
    constructor(data = {}) {
        this.id = data.id || null;
        this.title = data.title || '';
        this.description = data.description || '';
        this.subjectId = data.subjectId || null;
        this.timeLimit = data.timeLimit || 60;
        this.passingScore = data.passingScore || 75;
        this.questions = data.questions || [];
        
        // Additional fields from subject assignment
        this.yearLevel = data.yearLevel || null;
        this.section = data.section || null;
        this.academicYear = data.academicYear || null;
        this.semester = data.semester || null;
        this.examType = data.examType || null;
        this.startDate = data.startDate || null;
        this.endDate = data.endDate || null;
        this.isActive = data.isActive !== undefined ? data.isActive : true;
        this.instructions = data.instructions || '';
        
        this.metadata = data.metadata || {
            createdAt: new Date().toISOString(),
            updatedAt: new Date().toISOString(),
            version: 1
        };
    }

    /**
     * Add question to exam
     */
    addQuestion(question) {
        if (!(question instanceof Question)) {
            throw new Error('Must provide a Question instance');
        }

        this.questions.push(question);
        this.updateTimestamp();
        return question;
    }

    /**
     * Remove question from exam
     */
    removeQuestion(questionId) {
        const index = this.questions.findIndex(q => q.id === questionId);
        if (index === -1) {
            throw new Error('Question not found');
        }

        const removed = this.questions.splice(index, 1)[0];
        this.updateTimestamp();
        return removed;
    }

    /**
     * Get question by ID
     */
    getQuestion(questionId) {
        return this.questions.find(q => q.id === questionId);
    }

    /**
     * Reorder questions
     */
    reorderQuestions(newOrder) {
        const reordered = [];
        newOrder.forEach(id => {
            const question = this.getQuestion(id);
            if (question) {
                reordered.push(question);
            }
        });

        this.questions = reordered;
        this.updateTimestamp();
    }

    /**
     * Get total points for exam
     */
    getTotalPoints() {
        return this.questions.reduce((total, q) => total + q.points, 0);
    }

    /**
     * Get question count by type
     */
    getQuestionCountByType() {
        const counts = {
            multiple_choice: 0,
            true_false: 0,
            enumeration: 0,
            essay: 0
        };

        this.questions.forEach(q => {
            if (counts.hasOwnProperty(q.type)) {
                counts[q.type]++;
            }
        });

        return counts;
    }

    /**
     * Validate entire exam
     */
    validate() {
        const errors = [];

        // Validate exam metadata
        if (!this.title || this.title.trim().length === 0) {
            errors.push('Exam title is required');
        }

        if (!this.subjectId) {
            errors.push('Subject is required');
        }

        if (!this.yearLevel) {
            errors.push('Year level is required');
        }

        if (!this.section) {
            errors.push('Section is required');
        }

        if (!this.academicYear) {
            errors.push('Academic year is required');
        }

        if (!this.semester) {
            errors.push('Semester is required');
        }

        if (this.timeLimit < 1 || this.timeLimit > 300) {
            errors.push('Time limit must be between 1 and 300 minutes');
        }

        if (this.passingScore < 0 || this.passingScore > 100) {
            errors.push('Passing score must be between 0 and 100');
        }

        // Validate questions
        if (this.questions.length === 0) {
            errors.push('Exam must have at least one question');
        }

        // Validate each question
        this.questions.forEach((question, index) => {
            const validation = question.validate();
            if (!validation.isValid) {
                validation.errors.forEach(error => {
                    errors.push(`Question ${index + 1}: ${error}`);
                });
            }
        });

        // Validate total points
        const totalPoints = this.getTotalPoints();
        if (totalPoints === 0) {
            errors.push('Exam must have a total point value greater than 0');
        }

        return {
            isValid: errors.length === 0,
            errors: errors,
            warnings: this.getWarnings()
        };
    }

    /**
     * Get warnings (non-blocking issues)
     */
    getWarnings() {
        const warnings = [];

        // Check for unbalanced point distribution
        const totalPoints = this.getTotalPoints();
        const avgPoints = totalPoints / this.questions.length;
        
        this.questions.forEach((q, index) => {
            if (q.points > avgPoints * 3) {
                warnings.push(`Question ${index + 1} has significantly more points than average`);
            }
        });

        // Check time limit vs question count
        const estimatedTime = this.questions.length * 2; // 2 min per question
        if (this.timeLimit < estimatedTime) {
            warnings.push(`Time limit may be too short. Estimated time needed: ${estimatedTime} minutes`);
        }

        return warnings;
    }

    /**
     * Update timestamp
     */
    updateTimestamp() {
        this.metadata.updatedAt = new Date().toISOString();
        this.metadata.version++;
    }

    /**
     * Convert to JSON for API
     */
    toJSON() {
        return {
            id: this.id,
            title: this.title,
            description: this.description,
            subjectId: this.subjectId,
            timeLimit: this.timeLimit,
            passingScore: this.passingScore,
            yearLevel: this.yearLevel,
            section: this.section,
            academicYear: this.academicYear,
            semester: this.semester,
            examType: this.examType,
            startDate: this.startDate,
            endDate: this.endDate,
            isActive: this.isActive,
            instructions: this.instructions,
            questions: this.questions.map(q => q.toJSON()),
            metadata: this.metadata
        };
    }

    /**
     * Create Exam from JSON
     */
    static fromJSON(data) {
        const exam = new Exam(data);
        exam.questions = (data.questions || []).map(q => Question.fromJSON(q));
        return exam;
    }

    /**
     * Clone exam
     */
    clone() {
        const cloned = Exam.fromJSON(this.toJSON());
        cloned.id = null; // New exam should have new ID
        cloned.title = `${this.title} (Copy)`;
        cloned.metadata.createdAt = new Date().toISOString();
        return cloned;
    }

    /**
     * Get exam statistics
     */
    getStatistics() {
        return {
            totalQuestions: this.questions.length,
            totalPoints: this.getTotalPoints(),
            questionsByType: this.getQuestionCountByType(),
            averagePointsPerQuestion: this.getTotalPoints() / this.questions.length,
            estimatedDuration: this.timeLimit,
            passingScore: this.passingScore
        };
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Exam;
} else {
    window.Exam = Exam;
}
