/**
 * Question Model - Represents a single exam question
 * Follows MVC Model pattern - contains data and business logic
 */
class Question {
    constructor(type, data = {}) {
        this.id = data.id || this.generateId();
        this.type = type;
        this.text = data.text || '';
        this.points = data.points || this.getDefaultPoints(type);
        this.options = data.options || this.getDefaultOptions(type);
        this.correctAnswer = data.correctAnswer || this.getDefaultCorrectAnswer(type);
        this.metadata = data.metadata || this.getDefaultMetadata(type);
    }

    /**
     * Generate unique question ID
     */
    generateId() {
        return `question_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    /**
     * Get default points based on question type
     */
    getDefaultPoints(type) {
        const defaults = {
            'multiple_choice': 1,
            'true_false': 1,
            'enumeration': 2,
            'essay': 5
        };
        return defaults[type] || 1;
    }

    /**
     * Get default options based on question type
     */
    getDefaultOptions(type) {
        if (type === 'multiple_choice') {
            return [
                { text: 'Option A', isCorrect: true },
                { text: 'Option B', isCorrect: false },
                { text: 'Option C', isCorrect: false },
                { text: 'Option D', isCorrect: false }
            ];
        }
        return [];
    }

    /**
     * Get default correct answer based on question type
     */
    getDefaultCorrectAnswer(type) {
        switch (type) {
            case 'true_false':
                return 'true';
            case 'enumeration':
                return [];
            case 'essay':
                return null;
            default:
                return null;
        }
    }

    /**
     * Get default metadata based on question type
     */
    getDefaultMetadata(type) {
        switch (type) {
            case 'enumeration':
                return { expectedCount: 3 };
            case 'essay':
                return {
                    rubric: {
                        content: 40,
                        organization: 30,
                        grammar: 20,
                        creativity: 10
                    },
                    keyConcepts: ['']
                };
            default:
                return {};
        }
    }

    /**
     * Validate question data
     */
    validate() {
        const errors = [];

        // Validate question text
        if (!this.text || this.text.trim().length === 0) {
            errors.push('Question text is required');
        }

        // Validate points
        if (this.points < 0 || this.points > 100) {
            errors.push('Points must be between 0 and 100');
        }

        // Type-specific validation
        switch (this.type) {
            case 'multiple_choice':
                if (!this.options || this.options.length < 2) {
                    errors.push('Multiple choice questions must have at least 2 options');
                }
                if (!this.options.some(opt => opt.isCorrect)) {
                    errors.push('Multiple choice questions must have a correct answer');
                }
                // Check for empty option text
                const emptyOptions = this.options.filter(opt => !opt.text || opt.text.trim() === '');
                if (emptyOptions.length > 0) {
                    errors.push('All options must have text');
                }
                break;

            case 'true_false':
                if (this.correctAnswer === null) {
                    errors.push('True/False questions must have a correct answer');
                }
                break;

            case 'enumeration':
                if (!this.correctAnswer || this.correctAnswer.length === 0) {
                    errors.push('Enumeration questions must have correct answers');
                }
                break;

            case 'essay':
                if (!this.metadata.rubric || Object.keys(this.metadata.rubric).length === 0) {
                    errors.push('Essay questions should have a rubric');
                }
                break;
        }

        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }

    /**
     * Add option to multiple choice question
     */
    addOption(text, isCorrect = false) {
        if (this.type !== 'multiple_choice') {
            throw new Error('Can only add options to multiple choice questions');
        }

        this.options.push({
            id: this.generateId(),
            text: text,
            isCorrect: isCorrect
        });
    }

    /**
     * Remove option from multiple choice question
     */
    removeOption(optionId) {
        if (this.type !== 'multiple_choice') {
            throw new Error('Can only remove options from multiple choice questions');
        }

        this.options = this.options.filter(opt => opt.id !== optionId);
    }

    /**
     * Set correct answer
     */
    setCorrectAnswer(answer) {
        switch (this.type) {
            case 'multiple_choice':
                // Mark one option as correct
                this.options.forEach((opt, index) => {
                    opt.isCorrect = (index === answer);
                });
                break;

            case 'true_false':
                this.correctAnswer = answer;
                break;

            case 'enumeration':
                this.correctAnswer = answer;
                break;

            case 'essay':
                // Essays don't have a single correct answer
                this.metadata.sampleAnswer = answer;
                break;
        }
    }

    /**
     * Auto-fix empty options for multiple choice questions
     */
    autoFixOptions() {
        if (this.type === 'multiple_choice' && this.options) {
            this.options.forEach((option, index) => {
                if (!option.text || option.text.trim() === '') {
                    option.text = `Option ${String.fromCharCode(65 + index)}`;
                }
            });
        }
    }

    /**
     * Convert to JSON for API
     */
    toJSON() {
        // Auto-fix empty options before converting to JSON
        this.autoFixOptions();
        
        return {
            id: this.id,
            type: this.type,
            text: this.text,
            points: this.points,
            options: this.options,
            correctAnswer: this.correctAnswer,
            metadata: this.metadata
        };
    }

    /**
     * Create Question from JSON
     */
    static fromJSON(data) {
        const question = new Question(data.type, data);
        // Ensure all properties are properly set
        question.id = data.id || question.id;
        question.text = data.text || '';
        question.points = data.points || 1;
        question.options = data.options || question.options;
        question.correctAnswer = data.correctAnswer || question.correctAnswer;
        return question;
    }

    /**
     * Clone question
     */
    clone() {
        return Question.fromJSON(this.toJSON());
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Question;
} else {
    window.Question = Question;
}
