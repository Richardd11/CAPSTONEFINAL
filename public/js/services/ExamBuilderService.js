/**
 * ExamBuilderService - Handles business logic and API calls
 * Service layer - Contains business rules and data operations
 */
class ExamBuilderService {
    constructor() {
        this.api = window.apiService || this.createFallbackApi();
        this.basePath = this.getBasePath();
    }

    /**
     * Get base path for API calls
     */
    getBasePath() {
        // Return empty string to use absolute paths from root
        return '';
    }

    /**
     * Create fallback API if apiService not available
     */
    createFallbackApi() {
        return {
            post: async (endpoint, data) => {
                const response = await fetch(`${this.basePath}${endpoint}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                return await response.json();
            },
            get: async (endpoint) => {
                const response = await fetch(`${this.basePath}${endpoint}`);
                return await response.json();
            }
        };
    }

    /**
     * Load exam from server for editing
     */
    async loadExam(examId) {
        try {
            const response = await this.api.get(`/faculty/api/exam/${examId}`);
            
            if (response.success || response.status === 'success') {
                return response.data || response.exam;
            } else {
                throw new Error(response.message || 'Failed to load exam');
            }
        } catch (error) {
            console.error('Error loading exam:', error);
            throw error;
        }
    }

    /**
     * Save exam to server
     */
    async saveExam(examData) {
        try {
            // Prepare data for API
            const payload = this.prepareExamPayload(examData);
            
            console.log('🚀 FRONTEND SERVICE - Starting saveExam process');
            console.log('🔍 FRONTEND SERVICE - Exam data received:', examData);
            console.log('🔍 FRONTEND SERVICE - Is edit mode?', !!examData.id);
            console.log('🔍 FRONTEND SERVICE - Exam ID:', examData.id);
            console.log('🔍 FRONTEND SERVICE - Payload prepared:', payload);
            
            // Determine endpoint based on whether it's new or edit
            const endpoint = examData.id 
                ? `/faculty/exam/${examData.id}/update`
                : '/faculty/save-exam';
            
            console.log('🌐 FRONTEND SERVICE - Selected endpoint:', endpoint);
            console.log('🔒 FRONTEND SERVICE - Assignment data in payload:', {
                year_level: payload.year_level,
                section: payload.section,
                academic_year: payload.academic_year,
                semester: payload.semester,
                faculty_id: payload.faculty_id,
                exam_id: payload.exam_id
            });
            
            if (examData.id) {
                console.log('✅ FRONTEND SERVICE - This is an UPDATE request for exam ID:', examData.id);
            } else {
                console.log('⚠️ FRONTEND SERVICE - This is a CREATE request (no exam ID)');
            }
            
            const response = await this.api.post(endpoint, payload);
            
            console.log('📡 Server response:', response);
            console.log('📡 Response type:', typeof response);
            console.log('📡 Response keys:', Object.keys(response || {}));
            
            if (response.status === 'success' || response.success) {
                return {
                    success: true,
                    examId: response.exam_id || response.examId || examData.id,
                    message: response.message || (examData.id ? 'Exam updated successfully' : 'Exam saved successfully')
                };
            } else {
                console.error('❌ Server returned error:', {
                    status: response.status,
                    success: response.success,
                    message: response.message,
                    errors: response.errors,
                    fullResponse: response
                });
                throw new Error(response.message || 'Failed to save exam');
            }
        } catch (error) {
            console.error('❌ Error saving exam:', error);
            throw error;
        }
    }

    /**
     * Validate exam on server
     */
    async validateExam(examData) {
        try {
            const payload = this.prepareExamPayload(examData);
            const response = await this.api.post('/faculty/exams/validate', payload);
            
            return {
                isValid: response.valid || response.isValid,
                errors: response.errors || [],
                warnings: response.warnings || []
            };
        } catch (error) {
            console.error('Error validating exam:', error);
            // Return client-side validation as fallback
            return this.clientSideValidation(examData);
        }
    }

    /**
     * Prepare exam payload for API
     * Following the business logic from the old exam builder
     */
    prepareExamPayload(examData) {
        const payload = {
            exam_id: examData.id, // CRITICAL: This must be set for updates
            title: examData.title,
            description: examData.description,
            subject_id: examData.subjectId,
            exam_type: examData.examType,
            time_limit: examData.timeLimit,
            start_date: examData.startDate,
            end_date: examData.endDate,
            is_active: examData.isActive ? 1 : 0,
            instructions: examData.instructions,
            passing_score: examData.passingScore || 75,
            // Required fields from subject assignment - with format conversion
            year_level: this.formatYearLevel(examData.yearLevel),
            section: examData.section,
            academic_year: examData.academicYear,
            semester: this.formatSemester(examData.semester),
            faculty_id: examData.facultyId, // Add faculty_id
            questions: examData.questions.map(q => this.prepareQuestionPayload(q)),
            metadata: examData.metadata || {}
        };
        
        console.log('📦 Prepared payload:', payload);
        return payload;
    }

    /**
     * Prepare question payload for API
     */
    prepareQuestionPayload(question) {
        const payload = {
            question_id: question.id,
            question_type: question.type,
            question_text: question.text,
            points: question.points
        };

        switch (question.type) {
            case 'multiple_choice':
                // Transform options to backend format
                payload.options = question.options.map(opt => ({
                    option_text: opt.text,
                    is_correct: opt.isCorrect ? 1 : 0
                }));
                payload.correct_answer = question.options.findIndex(opt => opt.isCorrect);
                break;

            case 'true_false':
                payload.correct_answer = question.correctAnswer;
                break;

            case 'enumeration':
                payload.correct_answer = Array.isArray(question.correctAnswer) 
                    ? question.correctAnswer.join('\n')
                    : question.correctAnswer;
                payload.expected_count = question.metadata?.expectedCount || 3;
                break;

            case 'essay':
                payload.rubric = question.metadata?.rubric || {};
                payload.key_concepts = question.metadata?.keyConcepts || [];
                break;
        }

        return payload;
    }

    /**
     * Parse exam data from API response
     */
    parseExamData(data) {
        return {
            id: data.exam_id || data.id,
            title: data.title || data.exam_title,
            description: data.description || data.exam_description,
            subjectId: data.subject_id || data.subjectId,
            timeLimit: data.time_limit || data.timeLimit || 60,
            passingScore: data.passing_score || data.passingScore || 75,
            // Additional fields from the exam model
            yearLevel: data.year_level || data.yearLevel,
            section: data.section,
            academicYear: data.academic_year || data.academicYear,
            semester: data.semester,
            examType: data.exam_type || data.examType,
            startDate: data.start_date || data.startDate,
            endDate: data.end_date || data.endDate,
            isActive: data.is_active !== undefined ? data.is_active : data.isActive,
            instructions: data.instructions,
            questions: (data.questions || []).map(q => this.parseQuestionData(q)),
            metadata: data.metadata || {}
        };
    }

    /**
     * Parse question data from API response
     */
    parseQuestionData(data) {
        const question = {
            id: data.question_id || data.id,
            type: data.question_type || data.type,
            text: data.question_text || data.text,
            points: data.points || 1,
            options: [],
            correctAnswer: null,
            metadata: {}
        };

        switch (question.type) {
            case 'multiple_choice':
                question.options = data.options || [];
                // Ensure each option has isCorrect flag
                question.options = question.options.map((opt, index) => ({
                    text: opt.option_text || opt.text || '',
                    isCorrect: opt.is_correct || (index === data.correct_answer)
                }));
                break;

            case 'true_false':
                question.correctAnswer = data.correct_answer;
                break;

            case 'enumeration':
                const answers = data.correct_answer || '';
                question.correctAnswer = typeof answers === 'string' 
                    ? answers.split('\n').filter(a => a.trim())
                    : answers;
                question.metadata.expectedCount = data.expected_count || 3;
                break;

            case 'essay':
                question.metadata.rubric = data.rubric || {
                    content: 40,
                    organization: 30,
                    grammar: 20,
                    creativity: 10
                };
                question.metadata.keyConcepts = data.key_concepts || [];
                break;
        }

        return question;
    }

    /**
     * Client-side validation (fallback)
     */
    clientSideValidation(examData) {
        const errors = [];
        const warnings = [];

        // Validate exam metadata
        if (!examData.title || examData.title.trim().length === 0) {
            errors.push('Exam title is required');
        }

        if (!examData.subjectId) {
            errors.push('Subject is required');
        }

        if (examData.questions.length === 0) {
            errors.push('Exam must have at least one question');
        }

        // Validate each question
        examData.questions.forEach((question, index) => {
            const questionNum = index + 1;

            if (!question.text || question.text.trim().length === 0) {
                errors.push(`Question ${questionNum}: Question text is required`);
            }

            if (question.points < 0 || question.points > 100) {
                errors.push(`Question ${questionNum}: Points must be between 0 and 100`);
            }

            // Type-specific validation
            switch (question.type) {
                case 'multiple_choice':
                    if (!question.options || question.options.length < 2) {
                        errors.push(`Question ${questionNum}: Must have at least 2 options`);
                    }
                    if (!question.options.some(opt => opt.isCorrect)) {
                        errors.push(`Question ${questionNum}: Must have a correct answer`);
                    }
                    break;

                case 'true_false':
                    if (question.correctAnswer === null || question.correctAnswer === undefined) {
                        errors.push(`Question ${questionNum}: Must have a correct answer`);
                    }
                    break;

                case 'enumeration':
                    if (!question.correctAnswer || question.correctAnswer.length === 0) {
                        errors.push(`Question ${questionNum}: Must have correct answers`);
                    }
                    break;

                case 'essay':
                    const rubric = question.metadata?.rubric;
                    if (rubric) {
                        const total = Object.values(rubric).reduce((a, b) => a + b, 0);
                        if (total !== 100) {
                            warnings.push(`Question ${questionNum}: Rubric weights should total 100% (currently ${total}%)`);
                        }
                    }
                    break;
            }
        });

        // Check time limit
        const estimatedTime = examData.questions.length * 2;
        if (examData.timeLimit < estimatedTime) {
            warnings.push(`Time limit may be too short. Estimated time needed: ${estimatedTime} minutes`);
        }

        return {
            isValid: errors.length === 0,
            errors: errors,
            warnings: warnings
        };
    }

    /**
     * Get subjects for dropdown
     */
    async getSubjects() {
        try {
            const response = await this.api.get('/faculty/subjects');
            return response.subjects || response.data || [];
        } catch (error) {
            console.error('Error loading subjects:', error);
            return [];
        }
    }

    /**
     * Auto-save exam (draft)
     */
    async autoSave(examData) {
        try {
            const payload = this.prepareExamPayload(examData);
            payload.is_draft = true;
            
            await this.api.post('/faculty/exams/autosave', payload);
            return true;
        } catch (error) {
            console.error('Auto-save failed:', error);
            return false;
        }
    }

    /**
     * Calculate exam statistics
     */
    calculateStatistics(examData) {
        const stats = {
            totalQuestions: examData.questions.length,
            totalPoints: examData.questions.reduce((sum, q) => sum + q.points, 0),
            questionsByType: {
                multiple_choice: 0,
                true_false: 0,
                enumeration: 0,
                essay: 0
            },
            averagePointsPerQuestion: 0,
            estimatedDuration: examData.timeLimit
        };

        // Count questions by type
        examData.questions.forEach(q => {
            if (stats.questionsByType.hasOwnProperty(q.type)) {
                stats.questionsByType[q.type]++;
            }
        });

        // Calculate average
        if (stats.totalQuestions > 0) {
            stats.averagePointsPerQuestion = stats.totalPoints / stats.totalQuestions;
        }

        return stats;
    }

    /**
     * Format year level for backend compatibility
     */
    formatYearLevel(yearLevel) {
        if (!yearLevel) return '';
        
        // Convert number or string to proper format
        const yearMap = {
            '1': '1st Year',
            '2': '2nd Year', 
            '3': '3rd Year',
            '4': '4th Year',
            'first': '1st Year',
            'second': '2nd Year',
            'third': '3rd Year',
            'fourth': '4th Year'
        };
        
        // If already in correct format, return as is
        if (yearLevel.includes('Year')) {
            return yearLevel;
        }
        
        // Convert from number/string format
        return yearMap[yearLevel.toString().toLowerCase()] || yearLevel;
    }

    /**
     * Format semester for backend compatibility
     */
    formatSemester(semester) {
        if (!semester) return '';
        
        // Convert number or string to proper format
        const semesterMap = {
            '1': '1st Semester',
            '2': '2nd Semester',
            'summer': 'Summer',
            'first': '1st Semester',
            'second': '2nd Semester'
        };
        
        // If already in correct format, return as is
        if (semester.includes('Semester') || semester === 'Summer') {
            return semester;
        }
        
        // Convert from number/string format
        return semesterMap[semester.toString().toLowerCase()] || semester;
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ExamBuilderService;
} else {
    window.ExamBuilderService = ExamBuilderService;
}
