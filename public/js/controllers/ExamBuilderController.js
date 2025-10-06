/**
 * ExamBuilderController - Coordinates Model, View, and Service
 * Controller layer - Handles user interactions and orchestrates components
 */
class ExamBuilderController {
    constructor(examId = null) {
        // CRITICAL FIX: Prevent double initialization
        if (window.examBuilderController && window.examBuilderController.isInitialized) {
            console.log('⚠️ PREVENTING DOUBLE INITIALIZATION - Controller already exists');
            return window.examBuilderController;
        }
        
        // Initialize components
        this.exam = new Exam();
        this.view = new ExamBuilderView('questionsContainer');
        this.service = new ExamBuilderService();
        this.container = document.getElementById('questionsContainer');
        
        // State
        this.questionToDelete = null;
        this.autoSaveInterval = null;
        this.isEditMode = !!examId;
        this.examId = examId;
        this.isInitialized = false;
        this.isSaving = false; // Prevent double save
        
        // Initialize exam data for edit mode
        if (this.isEditMode && window.existingExamData) {
            console.log('📝 Initializing exam with existing data:', window.existingExamData);
            this.exam.id = window.existingExamData.id;
            this.exam.title = window.existingExamData.title;
            this.exam.description = window.existingExamData.description;
            this.exam.subjectId = window.existingExamData.subject_id;
            this.exam.examType = window.existingExamData.exam_type;
            this.exam.timeLimit = window.existingExamData.time_limit;
            this.exam.startDate = window.existingExamData.start_date;
            this.exam.endDate = window.existingExamData.end_date;
            this.exam.isActive = window.existingExamData.is_active;
            this.exam.instructions = window.existingExamData.instructions;
            this.exam.totalPoints = window.existingExamData.total_points;
            
            // CRITICAL: Set assignment data to prevent exam moving to wrong year
            this.exam.yearLevel = window.existingExamData.year_level;
            this.exam.section = window.existingExamData.section;
            this.exam.academicYear = window.existingExamData.academic_year;
            this.exam.semester = window.existingExamData.semester;
            this.exam.facultyId = window.existingExamData.faculty_id;
            
            console.log('🔒 Assignment data locked:', {
                yearLevel: this.exam.yearLevel,
                section: this.exam.section,
                academicYear: this.exam.academicYear,
                semester: this.exam.semester
            });
        }
        
        // Initialize
        this.initialize(examId);
        
        // Mark as initialized and store globally
        this.isInitialized = true;
        window.examBuilderController = this;
    }

    /**
     * Initialize controller
     */
    async initialize(examId) {
        try {
            // Setup event listeners first
            this.setupEventListeners();
            
            // Load existing questions if in edit mode
            if (this.isEditMode && window.existingQuestions && window.existingQuestions.length > 0) {
                console.log('🔄 Edit mode: Loading existing questions');
                this.loadExistingQuestions();
            }
            
            // Start auto-save
            this.startAutoSave();
            
            // Update UI
            this.updateUI();
            
            // Show modern modal for edit mode initialization
            if (this.isEditMode && window.modernModal) {
                window.modernModal.success(
                    'Exam Loaded Successfully!',
                    'Your exam is ready for editing. All questions and settings have been loaded.',
                    {
                        autoClose: 2000,
                        confirmText: 'Start Editing'
                    }
                );
            }
        } catch (error) {
            console.error('❌ Initialization error:', error);
            if (window.modernModal) {
                window.modernModal.error(
                    'Failed to Initialize Editor',
                    'There was an error loading the exam editor: ' + error.message,
                    {
                        confirmText: 'Retry',
                        onConfirm: () => window.location.reload()
                    }
                );
            }
        }
    }

    /**
     * Setup all event listeners
     */
    setupEventListeners() {
        // Add question button
        const addBtn = document.getElementById('addQuestionBtn');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.view.showQuestionTypeMenu());
        }

        // Question type buttons
        document.querySelectorAll('.question-type-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const type = e.currentTarget.dataset.type;
                this.view.showQuantityPanel(type);
            });
        });

        // Confirm quantity button
        const confirmBtn = document.getElementById('confirmQuantity');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => this.handleAddQuestions());
        }

        // Cancel quantity button
        const cancelBtn = document.getElementById('cancelQuantity');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.view.hideQuantityPanel());
        }

        // Save button
        const saveBtn = document.getElementById('saveBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.saveExam());
        }

        // Delete modal buttons
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        if (cancelDeleteBtn) {
            cancelDeleteBtn.addEventListener('click', () => this.view.hideDeleteModal());
        }

        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', () => this.confirmDeleteQuestion());
        }

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#addQuestionBtn') && !e.target.closest('#questionTypeMenu')) {
                this.view.hideQuestionTypeMenu();
            }
        });

        // Delegate events for dynamic elements
        this.setupDelegatedEvents();
    }

    /**
     * Setup delegated event listeners for dynamic elements
     */
    setupDelegatedEvents() {
        const container = this.view.container;

        // Delete question
        container.addEventListener('click', (e) => {
            const deleteBtn = e.target.closest('.delete-question');
            if (deleteBtn) {
                const questionId = deleteBtn.dataset.questionId;
                this.handleDeleteQuestion(questionId);
            }
        });

        // Update points
        container.addEventListener('change', (e) => {
            if (e.target.classList.contains('question-points')) {
                this.handlePointsChange(e.target);
            }
        });

        // Update question text
        container.addEventListener('input', (e) => {
            if (e.target.classList.contains('question-text')) {
                this.handleTextChange(e.target);
            }
        });

        // Add option (multiple choice) - Enhanced with old business logic
        container.addEventListener('click', (e) => {
            const addBtn = e.target.closest('.add-option');
            if (addBtn) {
                const questionElement = e.target.closest('.question-card');
                const questionId = questionElement ? questionElement.dataset.questionId : addBtn.dataset.questionId;
                if (questionId) {
                    this.addOptionToQuestion(questionId);
                } else {
                    this.handleAddOption(questionId);
                }
            }
        });

        // Remove option (multiple choice) - Fixed for template engine
        container.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.remove-option');
            if (removeBtn) {
                const questionElement = e.target.closest('.question-card');
                const optionItem = e.target.closest('.option-item');
                
                if (questionElement && optionItem) {
                    const questionId = questionElement.dataset.questionId;
                    const optionIndex = parseInt(optionItem.dataset.optionIndex);
                    
                    if (questionId && !isNaN(optionIndex)) {
                        this.handleRemoveOption(questionId, optionIndex);
                    }
                }
            }
        });

        // Update correct answer (multiple choice)
        container.addEventListener('change', (e) => {
            if (e.target.classList.contains('correct-answer')) {
                this.handleCorrectAnswerChange(e.target);
            }
        });

        // Update option text
        container.addEventListener('input', (e) => {
            if (e.target.classList.contains('option-text')) {
                this.handleOptionTextChange(e.target);
            }
        });

        // Update true/false answer
        container.addEventListener('change', (e) => {
            if (e.target.classList.contains('true-false-answer')) {
                console.log('🔄 True/False change detected:', e.target.value);
                this.handleTrueFalseChange(e.target);
            }
        });
        
        // Also handle clicks on True/False labels for better UX
        container.addEventListener('click', (e) => {
            const label = e.target.closest('label');
            if (label && label.querySelector('.true-false-answer')) {
                const radio = label.querySelector('.true-false-answer');
                if (radio && !radio.checked) {
                    radio.checked = true;
                    console.log('🔄 True/False clicked, triggering change:', radio.value);
                    this.handleTrueFalseChange(radio);
                }
            }
        });

        // Update enumeration answers
        container.addEventListener('input', (e) => {
            if (e.target.classList.contains('enumeration-answers')) {
                this.handleEnumerationChange(e.target);
            }
        });

        // Update rubric weights
        container.addEventListener('input', (e) => {
            if (e.target.classList.contains('rubric-weight')) {
                this.handleRubricWeightChange(e.target);
            }
        });

        // Add key concept
        container.addEventListener('click', (e) => {
            const addBtn = e.target.closest('.add-concept');
            if (addBtn) {
                const questionId = addBtn.dataset.questionId;
                this.handleAddConcept(questionId);
            }
        });

        // Remove key concept
        container.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.remove-concept');
            if (removeBtn) {
                const questionId = removeBtn.dataset.questionId;
                const conceptIndex = parseInt(removeBtn.dataset.conceptIndex);
                this.handleRemoveConcept(questionId, conceptIndex);
            }
        });

        // Update key concept
        container.addEventListener('input', (e) => {
            if (e.target.classList.contains('key-concept')) {
                this.handleConceptChange(e.target);
            }
        });

        // Add enumeration answer
        container.addEventListener('click', (e) => {
            const addBtn = e.target.closest('.add-enum-answer');
            if (addBtn) {
                const questionElement = e.target.closest('.question-card');
                const questionId = questionElement ? questionElement.dataset.questionId : null;
                if (questionId) {
                    this.addEnumerationAnswer(questionId);
                }
            }
        });

        // Remove enumeration answer
        container.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.remove-enum-item');
            if (removeBtn) {
                const questionElement = e.target.closest('.question-card');
                const questionId = questionElement ? questionElement.dataset.questionId : null;
                if (questionId) {
                    const enumItem = removeBtn.closest('.enumeration-item');
                    const container = enumItem.parentElement;
                    
                    if (container.children.length > 1) {
                        enumItem.remove();
                        this.updateEnumerationNumbers(questionId);
                    }
                }
            }
        });

        // Add key concept for essays
        container.addEventListener('click', (e) => {
            const addBtn = e.target.closest('.add-concept');
            if (addBtn) {
                const questionElement = e.target.closest('.question-card');
                const questionId = questionElement ? questionElement.dataset.questionId : null;
                if (questionId) {
                    this.addKeyConceptToQuestion(questionId);
                }
            }
        });

        // Remove key concept
        container.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.remove-concept');
            if (removeBtn) {
                const conceptDiv = removeBtn.closest('.flex.items-center.space-x-2');
                const container = conceptDiv.parentElement;
                
                if (container.children.length > 1) {
                    conceptDiv.remove();
                }
            }
        });

        // Update rubric weights for essays
        container.addEventListener('input', (e) => {
            if (e.target.classList.contains('rubric-weight')) {
                const questionElement = e.target.closest('.question-card');
                const questionId = questionElement ? questionElement.dataset.questionId : null;
                if (questionId) {
                    this.updateRubricTotalForQuestion(questionId);
                }
                // Also call the original handler
                this.handleRubricWeightChange(e.target);
            }
        });

        // Handle essay length selection
        container.addEventListener('change', (e) => {
            if (e.target.classList.contains('essay-length')) {
                const questionElement = e.target.closest('.question-card');
                const wordCountInput = questionElement?.querySelector('.word-count');
                if (wordCountInput) {
                    if (e.target.value === 'custom') {
                        wordCountInput.classList.remove('hidden');
                    } else {
                        wordCountInput.classList.add('hidden');
                    }
                }
            }
        });
    }

    /**
     * Handle add questions
     */
    handleAddQuestions() {
        const type = this.view.getSelectedType();
        const quantity = this.view.getQuantity();
        
        // Validate input
        if (!type) {
            this.view.showError('Please select a question type');
            return;
        }
        
        if (!quantity || quantity < 1 || quantity > 50) {
            this.view.showError('Please enter a valid quantity (1-50)');
            return;
        }
        
        try {
            // Add questions
            for (let i = 0; i < quantity; i++) {
                this.addQuestion(type);
            }
            
            this.view.hideQuestionTypeMenu();
            
            // Show confirmation modal
            this.view.showAddQuestionConfirmation(quantity, this.view.templates.getTypeLabel(type));
            
            // Show success modal
            if (window.modernModal) {
                window.modernModal.success(
                    'Questions Added!',
                    `Successfully added ${quantity} ${this.view.templates.getTypeLabel(type)} question${quantity > 1 ? 's' : ''} to your exam.`,
                    {
                        autoClose: 2000,
                        confirmText: 'Continue'
                    }
                );
            }
        } catch (error) {
            console.error('Error adding questions:', error);
            this.view.showError('Failed to add questions: ' + error.message);
        }
    }

    /**
     * Add question to exam
     */
    addQuestion(type) {
        const question = new Question(type);
        this.exam.addQuestion(question);
        const questionElement = this.view.renderQuestion(question);
        
        console.log('✅ Question added, element:', questionElement);
        console.log('✅ Question type:', type);
        
        // Setup event listeners for the new question IMMEDIATELY
        if (type === 'multiple_choice') {
            console.log('🔧 Setting up multiple choice listeners...');
            console.log('🔧 Question element:', questionElement);
            
            // Call immediately AND with a delay to be safe
            this.setupOptionEventListeners(questionElement);
            
            setTimeout(() => {
                console.log('🔧 Setting up listeners again (delayed)...');
                this.setupOptionEventListeners(questionElement);
            }, 100);
        }
        
        this.updateUI();
    }

    /**
     * Handle delete question
     */
    async handleDeleteQuestion(questionId) {
        const question = this.exam.getQuestion(questionId);
        const questionText = question ? question.text.substring(0, 50) + '...' : 'this question';
        
        if (window.modernModal) {
            const confirmed = await window.modernModal.confirmDelete(questionText, 'question');
            if (confirmed) {
                this.confirmDeleteQuestion(questionId);
            }
        } else {
            // Fallback to old method
            this.questionToDelete = questionId;
            this.view.showDeleteModal(questionText);
        }
    }

    /**
     * Confirm delete question
     */
    confirmDeleteQuestion(questionId = null) {
        const targetId = questionId || this.questionToDelete;
        if (targetId) {
            // First, hide any existing modal
            if (this.view.hideDeleteModal) {
                this.view.hideDeleteModal();
            }
            
            // Then animate the question removal
            this.view.animateQuestionRemoval(targetId, () => {
                // After animation completes, remove from model
                this.exam.removeQuestion(targetId);
                this.view.removeQuestion(targetId);
                this.updateUI();
                
                // Show modern success modal
                if (window.modernModal) {
                    window.modernModal.success(
                        'Question Deleted',
                        'The question has been successfully removed from your exam.',
                        {
                            autoClose: 2000,
                            confirmText: 'Continue'
                        }
                    );
                } else {
                    // Fallback to old method
                    this.view.showDeleteConfirmation();
                }
            });
            
            this.questionToDelete = null;
        }
    }

    /**
     * Handle points change
     */
    handlePointsChange(input) {
        const questionId = input.dataset.questionId;
        const points = parseInt(input.value) || 1;
        const question = this.exam.getQuestion(questionId);
        
        if (question) {
            question.points = points;
            this.updateUI();
        }
    }

    /**
     * Handle text change
     */
    handleTextChange(textarea) {
        const questionId = textarea.dataset.questionId;
        const text = textarea.value;
        const question = this.exam.getQuestion(questionId);
        
        if (question) {
            question.text = text;
            
            // Update character count
            const charCount = textarea.closest('.relative')?.querySelector('.char-count');
            if (charCount) {
                charCount.textContent = text.length;
            }
        }
    }

    /**
     * Handle add option (multiple choice)
     */
    handleAddOption(questionId) {
        const question = this.exam.getQuestion(questionId);
        if (question && question.type === 'multiple_choice') {
            const newOption = { text: `Option ${String.fromCharCode(65 + question.options.length)}`, isCorrect: false };
            question.options.push(newOption);
            this.view.addOption(questionId, newOption, question.options.length - 1);
            
            // Re-setup event listeners for all options in this question
            const questionElement = this.container.querySelector(`[data-question-id="${questionId}"]`);
            if (questionElement) {
                setTimeout(() => {
                    this.setupOptionEventListeners(questionElement);
                }, 10);
            }
        }
    }

    /**
     * Handle remove option (multiple choice) - Fixed for template engine
     */
    handleRemoveOption(questionId, optionIndex) {
        const question = this.exam.getQuestion(questionId);
        if (question && question.type === 'multiple_choice' && question.options.length > 2) {
            // Remove from model
            question.options.splice(optionIndex, 1);
            
            // Re-render the entire question to update all indices and structure
            this.refreshQuestion(questionId);
            
            console.log(`✅ Removed option ${optionIndex} from question ${questionId}`);
        } else {
            if (window.modernModal) {
                window.modernModal.warning(
                    'Cannot Remove Option',
                    'Multiple choice questions must have at least 2 options.'
                );
            } else {
                this.view.showWarning('Multiple choice questions must have at least 2 options');
            }
        }
    }

    /**
     * Handle correct answer change (multiple choice)
     * Enhanced with old business logic approach
     */
    handleCorrectAnswerChange(radio) {
        const questionId = radio.dataset.questionId;
        const optionIndex = parseInt(radio.dataset.optionIndex);
        const question = this.exam.getQuestion(questionId);
        
        console.log('🔘 Radio changed:', { questionId, optionIndex, checked: radio.checked });
        
        if (question && question.type === 'multiple_choice') {
            // Update model
            question.options.forEach((opt, idx) => {
                opt.isCorrect = (idx === optionIndex);
            });
            
            console.log('✅ Model updated:', question.options.map((o, i) => ({ index: i, text: o.text, isCorrect: o.isCorrect })));
            
            // Use old business logic approach for immediate visual feedback
            const questionElement = this.container.querySelector(`[data-question-id="${questionId}"]`);
            if (questionElement) {
                // Try the old style first for immediate feedback
                this.updateCorrectAnswerLabelsOldStyle(questionElement);
                
                // Then apply modern styling as enhancement
                setTimeout(() => {
                    this.updateCorrectAnswerLabelsReliable(questionElement);
                }, 50);
                
                console.log('🎨 Visual state updated with old business logic + modern enhancements');
            }
        }
    }

    /**
     * Handle option text change - Fixed for template engine
     */
    handleOptionTextChange(input) {
        const questionElement = input.closest('.question-card');
        const optionItem = input.closest('.option-item');
        
        if (questionElement && optionItem) {
            const questionId = questionElement.dataset.questionId;
            const optionIndex = parseInt(optionItem.dataset.optionIndex);
            const text = input.value;
            const question = this.exam.getQuestion(questionId);
            
            if (question && question.type === 'multiple_choice' && question.options[optionIndex]) {
                question.options[optionIndex].text = text;
                console.log(`✅ Updated option ${optionIndex} text for question ${questionId}: "${text}"`);
            }
        }
    }

    /**
     * Handle true/false answer change
     */
    handleTrueFalseChange(select) {
        const questionId = select.dataset.questionId;
        const answer = select.value;
        const question = this.exam.getQuestion(questionId);
        
        console.log(`🔄 Handling True/False change for question ${questionId}: ${answer}`);
        console.log(`🔍 Available questions:`, this.exam.questions.map(q => ({ id: q.id, type: q.type })));
        
        if (question && question.type === 'true_false') {
            question.correctAnswer = answer;
            console.log(`✅ Updated question ${questionId} correctAnswer to: ${answer}`);
            
            // Update visual state immediately
            this.updateTrueFalseVisuals(questionId, answer);
            
            // Also trigger UI update to show changes
            this.updateUI();
        } else {
            console.warn(`⚠️ Could not find true/false question with ID: ${questionId}`);
            console.warn(`🔍 Question search result:`, question);
            
            // Try to find by string comparison (in case of type mismatch)
            const questionByString = this.exam.questions.find(q => String(q.id) === String(questionId));
            if (questionByString && questionByString.type === 'true_false') {
                console.log(`🔧 Found question by string comparison, updating...`);
                questionByString.correctAnswer = answer;
                this.updateTrueFalseVisuals(questionId, answer);
                this.updateUI();
            }
        }
    }

    /**
     * Update True/False visual state
     */
    updateTrueFalseVisuals(questionId, selectedValue) {
        const questionElement = this.container.querySelector(`[data-question-id="${questionId}"]`);
        if (!questionElement) return;

        const trueOption = questionElement.querySelector('input[value="true"]').closest('label');
        const falseOption = questionElement.querySelector('input[value="false"]').closest('label');
        
        // Reset both options to unselected state
        this.resetTrueFalseOption(trueOption, 'true');
        this.resetTrueFalseOption(falseOption, 'false');
        
        // Apply selected state to chosen option
        if (selectedValue === 'true') {
            this.applySelectedTrueFalseState(trueOption, 'true');
        } else if (selectedValue === 'false') {
            this.applySelectedTrueFalseState(falseOption, 'false');
        }
    }

    /**
     * Reset True/False option to unselected state
     */
    resetTrueFalseOption(optionElement, value) {
        const container = optionElement.querySelector('div[class*="flex items-center p-4"]');
        const radioVisual = optionElement.querySelector('div[class*="w-6 h-6"]');
        const iconContainer = optionElement.querySelector('div[class*="w-10 h-10"]');
        const titleText = optionElement.querySelector('div[class*="font-semibold"]');
        const descText = optionElement.querySelector('div[class*="text-xs"]');
        const selectedBadge = optionElement.querySelector('span[class*="inline-flex"]');
        const pulseIndicator = optionElement.querySelector('div[class*="animate-pulse"]');

        if (value === 'true') {
            // Reset TRUE option
            container.className = 'flex items-center p-4 bg-white border-2 border-gray-200 hover:border-green-300 hover:bg-green-50 rounded-xl transition-all duration-200 transform hover:scale-[1.01]';
            radioVisual.className = 'w-6 h-6 bg-white border-gray-300 group-hover:border-green-400 border-2 rounded-full flex items-center justify-center transition-all';
            radioVisual.innerHTML = '';
            iconContainer.className = 'w-10 h-10 bg-green-100 group-hover:bg-green-200 rounded-lg flex items-center justify-center mr-3 transition-colors';
            iconContainer.innerHTML = '<i class="fas fa-check text-green-600 text-lg"></i>';
            titleText.className = 'font-semibold text-gray-800 group-hover:text-green-800 transition-colors';
            descText.className = 'text-xs text-gray-500 transition-colors';
        } else {
            // Reset FALSE option
            container.className = 'flex items-center p-4 bg-white border-2 border-gray-200 hover:border-red-300 hover:bg-red-50 rounded-xl transition-all duration-200 transform hover:scale-[1.01]';
            radioVisual.className = 'w-6 h-6 bg-white border-gray-300 group-hover:border-red-400 border-2 rounded-full flex items-center justify-center transition-all';
            radioVisual.innerHTML = '';
            iconContainer.className = 'w-10 h-10 bg-red-100 group-hover:bg-red-200 rounded-lg flex items-center justify-center mr-3 transition-colors';
            iconContainer.innerHTML = '<i class="fas fa-times text-red-600 text-lg"></i>';
            titleText.className = 'font-semibold text-gray-800 group-hover:text-red-800 transition-colors';
            descText.className = 'text-xs text-gray-500 transition-colors';
        }

        // Remove selected badge and pulse indicator if they exist
        if (selectedBadge) selectedBadge.remove();
        if (pulseIndicator) pulseIndicator.remove();
    }

    /**
     * Apply selected state to True/False option
     */
    applySelectedTrueFalseState(optionElement, value) {
        const container = optionElement.querySelector('div[class*="flex items-center p-4"]');
        const radioVisual = optionElement.querySelector('div[class*="w-6 h-6"]');
        const radioContainer = radioVisual.parentElement;
        const iconContainer = optionElement.querySelector('div[class*="w-10 h-10"]');
        const titleText = optionElement.querySelector('div[class*="font-semibold"]');
        const descText = optionElement.querySelector('div[class*="text-xs"]');
        const contentContainer = optionElement.querySelector('div[class*="flex items-center flex-1"]');

        if (value === 'true') {
            // Apply TRUE selected state
            container.className = 'flex items-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 shadow-md rounded-xl transition-all duration-200 transform scale-[1.02]';
            radioVisual.className = 'w-6 h-6 bg-green-500 border-green-500 border-2 rounded-full flex items-center justify-center transition-all';
            radioVisual.innerHTML = '<i class="fas fa-check text-white text-xs"></i>';
            iconContainer.className = 'w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center mr-3 transition-colors';
            iconContainer.innerHTML = '<i class="fas fa-check text-white text-lg"></i>';
            titleText.className = 'font-semibold text-green-800 transition-colors';
            descText.className = 'text-xs text-green-600 transition-colors';
            
            // Add pulse indicator
            radioContainer.insertAdjacentHTML('beforeend', '<div class="absolute -top-1 -right-1 w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>');
            
            // Add selected badge
            contentContainer.insertAdjacentHTML('afterend', '<div class="ml-auto"><span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full"><i class="fas fa-star mr-1"></i>Selected</span></div>');
        } else {
            // Apply FALSE selected state
            container.className = 'flex items-center p-4 bg-gradient-to-r from-red-50 to-rose-50 border-2 border-red-300 shadow-md rounded-xl transition-all duration-200 transform scale-[1.02]';
            radioVisual.className = 'w-6 h-6 bg-red-500 border-red-500 border-2 rounded-full flex items-center justify-center transition-all';
            radioVisual.innerHTML = '<i class="fas fa-check text-white text-xs"></i>';
            iconContainer.className = 'w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center mr-3 transition-colors';
            iconContainer.innerHTML = '<i class="fas fa-times text-white text-lg"></i>';
            titleText.className = 'font-semibold text-red-800 transition-colors';
            descText.className = 'text-xs text-red-600 transition-colors';
            
            // Add pulse indicator
            radioContainer.insertAdjacentHTML('beforeend', '<div class="absolute -top-1 -right-1 w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>');
            
            // Add selected badge
            contentContainer.insertAdjacentHTML('afterend', '<div class="ml-auto"><span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full"><i class="fas fa-star mr-1"></i>Selected</span></div>');
        }
    }

    /**
     * Handle enumeration answers change
     */
    handleEnumerationChange(textarea) {
        const questionId = textarea.dataset.questionId;
        const answers = textarea.value.split('\n').filter(a => a.trim());
        const question = this.exam.getQuestion(questionId);
        
        if (question && question.type === 'enumeration') {
            question.correctAnswer = answers;
        }
    }

    /**
     * Handle rubric weight change
     */
    handleRubricWeightChange(input) {
        const questionId = input.dataset.questionId;
        const criterion = input.dataset.criterion;
        const value = parseInt(input.value) || 0;
        const question = this.exam.getQuestion(questionId);
        
        if (question && question.type === 'essay') {
            if (!question.metadata.rubric) {
                question.metadata.rubric = {};
            }
            question.metadata.rubric[criterion] = value;
            
            // Update display
            this.view.updateRubricWeight(questionId, criterion, value);
            
            // Update total
            const total = Object.values(question.metadata.rubric).reduce((a, b) => a + b, 0);
            this.view.updateRubricTotal(questionId, total);
        }
    }

    /**
     * Handle add concept
     */
    handleAddConcept(questionId) {
        const question = this.exam.getQuestion(questionId);
        if (question && question.type === 'essay') {
            if (!question.metadata.keyConcepts) {
                question.metadata.keyConcepts = [];
            }
            question.metadata.keyConcepts.push('');
            this.view.addKeyConcept(questionId, '', question.metadata.keyConcepts.length - 1);
        }
    }

    /**
     * Handle remove concept
     */
    handleRemoveConcept(questionId, conceptIndex) {
        const question = this.exam.getQuestion(questionId);
        if (question && question.type === 'essay' && question.metadata.keyConcepts) {
            if (question.metadata.keyConcepts.length > 1) {
                question.metadata.keyConcepts.splice(conceptIndex, 1);
                this.refreshQuestion(questionId);
            }
        }
    }

    /**
     * Handle concept text change
     */
    handleConceptChange(input) {
        const questionId = input.dataset.questionId;
        const conceptIndex = parseInt(input.dataset.conceptIndex);
        const text = input.value;
        const question = this.exam.getQuestion(questionId);
        
        if (question && question.type === 'essay' && question.metadata.keyConcepts) {
            question.metadata.keyConcepts[conceptIndex] = text;
        }
    }

    /**
     * Refresh question rendering
     */
    refreshQuestion(questionId) {
        const question = this.exam.getQuestion(questionId);
        if (question) {
            const oldElement = this.view.container.querySelector(`[data-question-id="${questionId}"]`);
            if (oldElement) {
                const newElement = this.view.renderQuestion(question);
                oldElement.replaceWith(newElement);
                
                // Re-setup event listeners for multiple choice questions
                if (question.type === 'multiple_choice') {
                    const questionElement = this.container.querySelector(`[data-question-id="${questionId}"]`);
                    if (questionElement) {
                        setTimeout(() => {
                            this.setupOptionEventListeners(questionElement);
                        }, 10);
                    }
                }
            }
        }
    }

    /**
     * Update UI (points, numbers, etc.)
     */
    updateUI() {
        this.view.updateQuestionNumbers();
        this.view.updateTotalPoints(this.exam.getTotalPoints());
    }

    /**
     * Save exam
     */
    async saveExam() {
        // CRITICAL FIX: Prevent double save
        if (this.isSaving) {
            console.log('⚠️ SAVE ALREADY IN PROGRESS - Ignoring duplicate request');
            return;
        }
        
        this.isSaving = true;
        
        // Declare savingModal in outer scope to avoid reference errors
        let savingModal = null;
        
        try {
            // Show loading state
            const saveBtn = document.getElementById('saveBtn');
            if (saveBtn) {
                saveBtn.disabled = true;
                const loadingText = this.isEditMode ? 'Updating...' : 'Saving...';
                saveBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>${loadingText}`;
            }
            
            // Get exam metadata from form
            this.updateExamMetadata();
            
            // Update all questions from DOM
            this.updateQuestionsFromDOM();
            
            // Additional validation for new exams - check assignment data
            if (!this.isEditMode) {
                if (!this.exam.subjectId) {
                    this.view.showValidationErrors(['Please select a subject before saving the exam.']);
                    this.resetSaveButton();
                    return;
                }
                
                if (!this.exam.yearLevel || !this.exam.section) {
                    this.view.showValidationErrors(['Subject assignment data is missing. Please select a valid subject.']);
                    this.resetSaveButton();
                    return;
                }
            }
            
            // Validate
            const validation = this.exam.validate();
            if (!validation.isValid) {
                this.view.showValidationErrors(validation.errors);
                this.resetSaveButton();
                return;
            }
            
            // Show warnings if any
            if (validation.warnings.length > 0) {
                const proceed = await this.view.confirmWithWarnings(validation.warnings);
                if (!proceed) {
                    this.resetSaveButton();
                    return;
                }
            }
            
            // Show modern saving modal using info modal
            try {
                if (window.modernModal) {
                    const savingTitle = this.isEditMode ? 'Updating Exam...' : 'Saving Exam...';
                    const savingMessage = this.isEditMode 
                        ? 'Please wait while we update your exam with the latest changes.'
                        : 'Please wait while we save your exam. This may take a few moments.';
                    
                    // Create a custom saving modal that we can control
                    savingModal = {
                        close: () => {
                            if (window.modernModal.currentModal) {
                                window.modernModal.close();
                            }
                        }
                    };
                    
                    window.modernModal.info(savingTitle, savingMessage);
                }
            } catch (error) {
                console.warn('ModernModal not available:', error.message);
            }
            
            console.log('=== FRONTEND CONTROLLER - SAVE EXAM ANALYSIS ===');
            console.log('🚀 FRONTEND CONTROLLER - Starting save process...');
            console.log('🔍 FRONTEND CONTROLLER - Edit mode:', this.isEditMode);
            console.log('🔍 FRONTEND CONTROLLER - Exam ID from constructor:', this.examId);
            console.log('🔍 FRONTEND CONTROLLER - Exam object ID:', this.exam.id);
            
            const examData = this.exam.toJSON();
            console.log('🔍 FRONTEND CONTROLLER - Exam data from toJSON():', examData);
            console.log('🔍 FRONTEND CONTROLLER - Assignment data in exam object:', {
                yearLevel: this.exam.yearLevel,
                section: this.exam.section,
                academicYear: this.exam.academicYear,
                semester: this.exam.semester
            });
            
            // Ensure exam ID is set for edit mode
            if (this.isEditMode && this.examId) {
                examData.id = this.examId;
                
                // CRITICAL: Double-check assignment data is preserved
                console.log('🔒 FINAL CHECK - Assignment data before save:', {
                    yearLevel: examData.yearLevel,
                    section: examData.section,
                    academicYear: examData.academicYear,
                    semester: examData.semester,
                    facultyId: examData.facultyId
                });
                
                // Ensure assignment data is not null/undefined
                if (!examData.yearLevel || !examData.section) {
                    console.error('❌ CRITICAL ERROR: Assignment data is missing!');
                    throw new Error('Assignment data is missing. Cannot update exam.');
                }
            }
            
            const result = await this.service.saveExam(examData);
            
            if (result.success) {
                // Close saving modal
                try {
                    if (savingModal && typeof savingModal.close === 'function') {
                        savingModal.close();
                    }
                } catch (error) {
                    console.warn('Error closing saving modal:', error.message);
                }
                
                // Show modern success modal with fallback to old business logic
                if (window.modernModal) {
                    const successTitle = this.isEditMode ? 'Exam Updated Successfully!' : 'Exam Saved Successfully!';
                    const successMessage = this.isEditMode 
                        ? `Your exam "${this.exam.title}" has been updated successfully. All changes have been saved.`
                        : `Your exam "${this.exam.title}" has been saved successfully and is now available in your exam list.`;
                    
                    window.modernModal.success(successTitle, successMessage);
                } else if (typeof showMessage === 'function') {
                    // Fallback to old business logic
                    const successMsg = this.isEditMode ? 'Exam updated successfully!' : 'Exam saved successfully!';
                    showMessage(successMsg, 'success');
                }
                
                // Update save button to show success
                if (saveBtn) {
                    const successText = this.isEditMode ? 'Updated!' : 'Saved!';
                    saveBtn.innerHTML = `<i class="fas fa-check mr-2"></i>${successText}`;
                    saveBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                    saveBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                }
            } else {
                throw new Error(result.message || 'Failed to save exam');
            }
        } catch (error) {
            console.error('Save error:', error);
            
            // Close saving modal
            try {
                if (savingModal && typeof savingModal.close === 'function') {
                    savingModal.close();
                }
            } catch (error) {
                console.warn('Error closing saving modal:', error.message);
            }
            
            // Show modern error modal
            if (window.modernModal) {
                window.modernModal.error(
                    'Failed to Save Exam',
                    'There was an error saving your exam: ' + error.message,
                    {
                        confirmText: 'Try Again',
                        onConfirm: () => this.saveExam()
                    }
                );
            }
            this.resetSaveButton();
        } finally {
            // CRITICAL: Reset saving flag
            this.isSaving = false;
        }
    }
    
    /**
     * Reset save button to original state
     */
    resetSaveButton() {
        const saveBtn = document.getElementById('saveBtn');
        if (saveBtn) {
            saveBtn.disabled = false;
            // Use correct text based on mode
            const buttonText = this.isEditMode ? 'Update Exam' : 'Save Exam';
            saveBtn.innerHTML = `<i class="fas fa-save mr-2"></i>${buttonText}`;
            saveBtn.classList.remove('bg-green-600');
        }
    }

    /**
     * Show professional save success modal
     */
    showSaveSuccessModal(examId) {
        // Remove any existing modal
        const existingModal = document.getElementById('saveSuccessModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Create modal backdrop
        const backdrop = document.createElement('div');
        backdrop.id = 'saveSuccessModal';
        backdrop.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
        
        // Create modal content
        backdrop.innerHTML = `
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 transform transition-all duration-300 scale-95 opacity-0">
                <div class="text-center">
                    <!-- Success Icon -->
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check-circle text-green-500 text-4xl"></i>
                    </div>
                    
                    <!-- Title -->
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">
                        ${this.isEditMode ? 'Exam Updated Successfully!' : 'Exam Saved Successfully!'}
                    </h3>
                    
                    <!-- Message -->
                    <p class="text-gray-600 mb-8 leading-relaxed">
                        ${this.isEditMode ? 'Your exam has been updated successfully. All changes have been saved.' : 'Your exam has been saved and is now available in your exam list. Students can take this exam once you activate it.'}
                    </p>
                    
                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button onclick="examBuilderController.continueSaveModal()" 
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-xl font-semibold transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>
                            Create Another
                        </button>
                        <button onclick="examBuilderController.goToDashboardFromModal()" 
                                class="flex-1 bg-gray-600 hover:bg-gray-700 text-white py-3 px-6 rounded-xl font-semibold transition-colors duration-200">
                            <i class="fas fa-list mr-2"></i>
                            View All Exams
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(backdrop);
        
        // Animate in
        requestAnimationFrame(() => {
            const modal = backdrop.querySelector('.bg-white');
            modal.classList.remove('scale-95', 'opacity-0');
            modal.classList.add('scale-100', 'opacity-100');
        });
        
        // Store exam ID for later use
        this.lastSavedExamId = examId;
    }
    
    /**
     * Continue creating another exam
     */
    continueSaveModal() {
        const modal = document.getElementById('saveSuccessModal');
        if (modal) {
            modal.remove();
        }
        
        // Reset the form for a new exam
        window.location.href = '/faculty/create-exam';
    }
    
    /**
     * Go to dashboard/exam list
     */
    goToDashboardFromModal() {
        const modal = document.getElementById('saveSuccessModal');
        if (modal) {
            modal.remove();
        }
        
        // Navigate to exam list
        window.location.href = '/faculty/exams';
    }

    /**
     * Update exam metadata from form
     * Following the business logic from the old exam builder
     */
    updateExamMetadata() {
        const titleInput = document.getElementById('examTitle');
        const descInput = document.getElementById('examDescription');
        const subjectInput = document.getElementById('subjectId');
        const examTypeInput = document.getElementById('examType');
        const timeLimitInput = document.getElementById('time_limit');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const isActiveInput = document.getElementById('is_active');
        const instructionsInput = document.getElementById('instructions');
        
        if (titleInput) this.exam.title = titleInput.value;
        if (descInput) this.exam.description = descInput.value;
        if (subjectInput) this.exam.subjectId = subjectInput.value;
        if (examTypeInput) this.exam.examType = examTypeInput.value;
        if (timeLimitInput) this.exam.timeLimit = parseInt(timeLimitInput.value) || 60;
        if (startDateInput) this.exam.startDate = startDateInput.value;
        if (endDateInput) this.exam.endDate = endDateInput.value;
        if (isActiveInput) this.exam.isActive = isActiveInput.checked;
        if (instructionsInput) this.exam.instructions = instructionsInput.value;
        
        // CRITICAL FIX: In edit mode, NEVER change assignment data from subject dropdown
        // This prevents exams from moving to different year levels
        if (!this.isEditMode && subjectInput && subjectInput.value) {
            // Only for NEW exams - get assignment data from selected subject
            const selectedOption = subjectInput.options[subjectInput.selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                this.exam.yearLevel = selectedOption.dataset.year;
                this.exam.section = selectedOption.dataset.section;
                this.exam.academicYear = selectedOption.dataset.academicYear;
                this.exam.semester = selectedOption.dataset.semester;
                
                console.log('📋 NEW EXAM: Assignment data extracted from subject selection:', {
                    yearLevel: this.exam.yearLevel,
                    section: this.exam.section,
                    academicYear: this.exam.academicYear,
                    semester: this.exam.semester
                });
            }
        } else if (!this.isEditMode) {
            // For new exams without subject selection, provide defaults or show error
            console.warn('⚠️ NEW EXAM: No subject selected - assignment data will be missing');
        }
        
        // For edit mode, assignment data is already set in constructor and should NEVER change
        if (this.isEditMode) {
            console.log('🔒 Edit mode: Assignment data preserved:', {
                yearLevel: this.exam.yearLevel,
                section: this.exam.section,
                academicYear: this.exam.academicYear,
                semester: this.exam.semester
            });
            
            // CRITICAL VALIDATION: Ensure assignment data is not lost
            if (!this.exam.yearLevel || !this.exam.section) {
                console.error('❌ CRITICAL: Assignment data was lost during metadata update!');
                console.error('🔍 Current exam state:', this.exam);
                
                // Restore from window.existingExamData if available
                if (window.existingExamData) {
                    console.log('🔧 Restoring assignment data from existingExamData...');
                    this.exam.yearLevel = window.existingExamData.year_level;
                    this.exam.section = window.existingExamData.section;
                    this.exam.academicYear = window.existingExamData.academic_year;
                    this.exam.semester = window.existingExamData.semester;
                    this.exam.facultyId = window.existingExamData.faculty_id;
                }
            }
        }
    }

    /**
     * Load exam from server
     */
    async loadExam(examId) {
        try {
            const data = await this.service.loadExam(examId);
            this.exam = Exam.fromJSON(data);
            this.renderExam();
        } catch (error) {
            this.view.showError('Failed to load exam: ' + error.message);
        }
    }

    /**
     * Load existing questions (from window.existingQuestions)
     */
    loadExistingQuestions() {
        if (!window.existingQuestions) return;
        
        console.log('🔄 Loading existing questions:', window.existingQuestions.length, 'questions');
        
        // Clear everything first to prevent duplicates
        this.exam.questions = [];
        this.view.clearAllQuestions();
        
        window.existingQuestions.forEach((questionData, index) => {
            console.log(`📝 Loading question ${index + 1}:`, questionData);
            const question = Question.fromJSON(this.service.parseQuestionData(questionData));
            console.log(`✅ Created question object:`, question);
            this.exam.addQuestion(question);
            const questionElement = this.view.renderQuestion(question);
            
            // Populate question-specific data after rendering
            setTimeout(() => {
                this.populateQuestionData(question);
                
                // Setup event listeners for multiple choice questions in edit mode
                if (question.type === 'multiple_choice') {
                    console.log('🔧 Setting up MC listeners for edit mode question:', question.id);
                    this.setupOptionEventListeners(questionElement);
                }
            }, 50); // Increased delay to ensure DOM is ready
        });
        
        console.log(`✅ Loaded ${this.exam.questions.length} questions total`);
        this.updateUI();
    }

    /**
     * Render entire exam
     */
    renderExam() {
        // Populate form fields with exam data
        this.populateFormFields();
        
        this.view.clearAllQuestions();
        
        this.exam.questions.forEach(question => {
            this.view.renderQuestion(question);
            // Populate question-specific data after rendering with a small delay
            setTimeout(() => {
                this.populateQuestionData(question);
            }, 10);
        });
        
        this.updateUI();
    }

    /**
     * Populate form fields with exam data (for edit mode)
     */
    populateFormFields() {
        const titleInput = document.getElementById('examTitle');
        const descInput = document.getElementById('examDescription');
        const subjectInput = document.getElementById('subjectId');
        const examTypeInput = document.getElementById('examType');
        const timeLimitInput = document.getElementById('time_limit');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const isActiveInput = document.getElementById('is_active');
        const instructionsInput = document.getElementById('instructions');
        
        if (titleInput && this.exam.title) titleInput.value = this.exam.title;
        if (descInput && this.exam.description) descInput.value = this.exam.description;
        if (subjectInput && this.exam.subjectId) subjectInput.value = this.exam.subjectId;
        if (examTypeInput && this.exam.examType) examTypeInput.value = this.exam.examType;
        if (timeLimitInput && this.exam.timeLimit) timeLimitInput.value = this.exam.timeLimit;
        if (startDateInput && this.exam.startDate) startDateInput.value = this.exam.startDate;
        if (endDateInput && this.exam.endDate) endDateInput.value = this.exam.endDate;
        if (isActiveInput && this.exam.isActive !== undefined) isActiveInput.checked = this.exam.isActive;
        if (instructionsInput && this.exam.instructions) instructionsInput.value = this.exam.instructions;
    }

    /**
     * Populate question-specific data after rendering (for edit mode)
     */
    populateQuestionData(question) {
        const questionElement = this.container.querySelector(`[data-question-id="${question.id}"]`);
        if (!questionElement) return;

        // Populate question text and points
        const textArea = questionElement.querySelector('.question-text');
        const pointsInput = questionElement.querySelector('.question-points');
        
        if (textArea && question.text) textArea.value = question.text;
        if (pointsInput && question.points) pointsInput.value = question.points;

        // Populate type-specific data
        switch (question.type) {
            case 'multiple_choice':
                this.populateMultipleChoiceData(questionElement, question);
                break;
            case 'true_false':
                this.populateTrueFalseData(questionElement, question);
                break;
            case 'enumeration':
                this.populateEnumerationData(questionElement, question);
                break;
            case 'essay':
                this.populateEssayData(questionElement, question);
                break;
        }
    }

    /**
     * Populate multiple choice question data
     */
    populateMultipleChoiceData(questionElement, question) {
        const optionsContainer = questionElement.querySelector('.options-container');
        if (!optionsContainer || !question.options) return;

        // Clear existing options
        optionsContainer.innerHTML = '';

        // Add each option using the template engine
        question.options.forEach((option, index) => {
            const optionHtml = this.view.templates.renderOption(question.id, option, index);
            optionsContainer.insertAdjacentHTML('beforeend', optionHtml);
        });

        // IMPORTANT: Set up event listeners for the options
        this.setupOptionEventListeners(questionElement);
        
        // Ensure correct visual state using simple method (doesn't re-render)
        this.updateCorrectAnswerLabelsSimple(questionElement);
    }

    /**
     * Populate true/false question data
     */
    populateTrueFalseData(questionElement, question) {
        if (question.correctAnswer !== null) {
            // Find the radio button that matches the correct answer
            const targetRadio = questionElement.querySelector(`.true-false-answer[value="${question.correctAnswer}"]`);
            if (targetRadio) {
                targetRadio.checked = true;
                console.log(`True/False question ${question.id}: Set ${question.correctAnswer} as checked`);
                
                // Update visual state
                this.updateTrueFalseVisuals(question.id, question.correctAnswer);
            } else {
                console.log(`True/False question ${question.id}: Could not find radio button for value '${question.correctAnswer}'`);
            }
        }
    }

    /**
     * Populate enumeration question data
     */
    populateEnumerationData(questionElement, question) {
        const textArea = questionElement.querySelector('.enumeration-answers');
        const expectedCountInput = questionElement.querySelector('.expected-count');
        
        if (textArea && question.correctAnswer) {
            const answers = Array.isArray(question.correctAnswer) 
                ? question.correctAnswer.join('\n')
                : question.correctAnswer;
            textArea.value = answers;
        }
        
        if (expectedCountInput && question.metadata?.expectedCount) {
            expectedCountInput.value = question.metadata.expectedCount;
        }
    }

    /**
     * Populate essay question data
     */
    populateEssayData(questionElement, question) {
        // Populate rubric weights
        if (question.metadata?.rubric) {
            Object.entries(question.metadata.rubric).forEach(([criterion, weight]) => {
                const weightInput = questionElement.querySelector(`[data-criterion="${criterion}"]`);
                if (weightInput) {
                    weightInput.value = weight;
                    // Update display
                    const display = questionElement.querySelector(`#${criterion}-weight-${question.id}`);
                    if (display) display.textContent = `${weight}%`;
                }
            });
        }

        // Populate key concepts
        if (question.metadata?.keyConcepts) {
            const conceptsContainer = questionElement.querySelector('.key-concepts-container');
            if (conceptsContainer) {
                conceptsContainer.innerHTML = '';
                question.metadata.keyConcepts.forEach((concept, index) => {
                    if (concept.trim()) {
                        this.view.addKeyConcept(question.id, concept, index);
                    }
                });
            }
        }
    }

    /**
     * Setup event listeners for option elements - Fixed for template engine structure
     */
    setupOptionEventListeners(questionElement) {
        console.log('🔧 Setting up option event listeners for question:', questionElement.dataset.questionId);
        
        const questionId = questionElement.dataset.questionId;
        
        // Remove any existing delegated listener to prevent duplicates
        if (questionElement._optionClickHandler) {
            questionElement.removeEventListener('click', questionElement._optionClickHandler);
        }
        
        // Create new delegated event handler that works with template engine structure
        questionElement._optionClickHandler = (e) => {
            // Find the closest option item
            const optionItem = e.target.closest('.option-item');
            if (!optionItem) return;
            
            // Don't trigger if clicking on text input or remove button
            if (e.target.classList.contains('option-text') || 
                e.target.closest('.remove-option') ||
                e.target.type === 'text') {
                return;
            }
            
            // Find the radio button (it might be hidden in template engine)
            const radio = optionItem.querySelector('.correct-answer');
            if (radio && !radio.checked) {
                // Get the option index
                const optionIndex = parseInt(optionItem.dataset.optionIndex);
                
                // Uncheck all other radios in this question (using correct name pattern)
                const radioName = radio.name;
                const allRadios = questionElement.querySelectorAll(`input[name="${radioName}"]`);
                allRadios.forEach(r => r.checked = false);
                
                // Check this radio
                radio.checked = true;
                
                // Update the model
                const question = this.exam.getQuestion(questionId);
                if (question && question.type === 'multiple_choice') {
                    question.options.forEach((opt, idx) => {
                        opt.isCorrect = (idx === optionIndex);
                    });
                    
                    console.log('✅ Model updated via delegation:', question.options.map((o, i) => ({ index: i, isCorrect: o.isCorrect })));
                }
                
                // Trigger visual update using template engine approach
                this.refreshQuestion(questionId);
                
                console.log(`✨ Option ${optionIndex} selected via delegation for question ${questionId}`);
            }
        };
        
        // Add the delegated event listener
        questionElement.addEventListener('click', questionElement._optionClickHandler);
        
        // Also setup direct radio button change listeners as backup
        const radios = questionElement.querySelectorAll('.correct-answer');
        radios.forEach(radio => {
            radio.addEventListener('change', (e) => {
                if (e.target.checked) {
                    const optionIndex = parseInt(e.target.dataset.optionIndex);
                    const question = this.exam.getQuestion(questionId);
                    
                    if (question && question.type === 'multiple_choice') {
                        question.options.forEach((opt, idx) => {
                            opt.isCorrect = (idx === optionIndex);
                        });
                        
                        // Re-render the question to show updated state
                        setTimeout(() => {
                            this.refreshQuestion(questionId);
                        }, 10);
                    }
                }
            });
        });
        
        console.log('✅ Event delegation setup completed for question:', questionId);
    }

    /**
     * Update correct answer labels - MODERN approach with smooth animations
     */
    updateCorrectAnswerLabelsModern(questionElement) {
        const radioButtons = questionElement.querySelectorAll('.correct-answer');
        console.log(`🎨 MODERN UPDATE: Processing ${radioButtons.length} options with animations`);
        
        radioButtons.forEach((radio, index) => {
            const optionItem = radio.closest('.option-item');
            if (!optionItem) return;
            
            const optionContainer = optionItem.querySelector('.flex.items-start.gap-3');
            const optionBadge = optionItem.querySelector('.w-8.h-8');
            const optionInput = optionItem.querySelector('.option-text');
            const correctIndicator = optionItem.querySelector('.text-xs.text-blue-700.font-medium');
            const radioContainer = radio.closest('.relative');
            
            // Add smooth transition classes
            if (optionContainer) {
                optionContainer.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            }
            if (optionBadge) {
                optionBadge.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            }
            
            if (radio.checked) {
                console.log(`✨ MODERN: Animating option ${index} to CORRECT state`);
                
                // Modern correct state with gradient and glow
                if (optionContainer) {
                    optionContainer.className = 'flex items-start gap-3 bg-gradient-to-r from-emerald-50 via-blue-50 to-indigo-50 border-2 border-emerald-400 rounded-xl p-4 transition-all duration-300 shadow-lg ring-4 ring-emerald-200 ring-opacity-50 transform hover:scale-[1.02]';
                    
                    // Add pulse animation
                    optionContainer.style.animation = 'pulse-correct 0.6s ease-out';
                    setTimeout(() => {
                        optionContainer.style.animation = '';
                    }, 600);
                }
                
                // Modern badge with gradient
                if (optionBadge) {
                    optionBadge.className = 'w-8 h-8 bg-gradient-to-br from-emerald-500 to-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg transform transition-all duration-300 hover:scale-110';
                    optionBadge.style.boxShadow = '0 4px 20px rgba(16, 185, 129, 0.4)';
                }
                
                // Modern input styling
                if (optionInput) {
                    optionInput.className = 'option-text w-full bg-transparent border-none focus:outline-none text-sm text-emerald-900 font-semibold placeholder-gray-400 py-2';
                }
                
                // Add modern correct indicator with icon
                if (!correctIndicator) {
                    const indicator = document.createElement('div');
                    indicator.className = 'mt-2 text-xs text-emerald-700 font-bold flex items-center animate-fade-in';
                    indicator.innerHTML = '<i class="fas fa-check-circle mr-2 text-emerald-600"></i><span class="bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">Correct Answer</span>';
                    optionInput.parentElement.appendChild(indicator);
                    
                    // Animate in
                    indicator.style.opacity = '0';
                    indicator.style.transform = 'translateY(10px)';
                    setTimeout(() => {
                        indicator.style.transition = 'all 0.3s ease-out';
                        indicator.style.opacity = '1';
                        indicator.style.transform = 'translateY(0)';
                    }, 100);
                }
                
                // Add checkmark with bounce animation
                if (radioContainer && !radioContainer.querySelector('.absolute')) {
                    const checkmark = document.createElement('div');
                    checkmark.className = 'absolute -top-2 -right-2 w-5 h-5 bg-gradient-to-br from-emerald-500 to-green-600 rounded-full flex items-center justify-center shadow-lg animate-bounce-in';
                    checkmark.innerHTML = '<i class="fas fa-check text-white text-xs"></i>';
                    radioContainer.appendChild(checkmark);
                    
                    // Bounce animation
                    checkmark.style.transform = 'scale(0)';
                    setTimeout(() => {
                        checkmark.style.transition = 'transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
                        checkmark.style.transform = 'scale(1)';
                    }, 200);
                }
                
            } else {
                console.log(`🔘 MODERN: Animating option ${index} to INCORRECT state`);
                
                // Modern incorrect state with subtle styling
                if (optionContainer) {
                    optionContainer.className = 'flex items-start gap-3 bg-white hover:bg-gray-50 border-2 border-gray-200 hover:border-blue-300 rounded-xl p-4 transition-all duration-300 shadow-sm hover:shadow-md transform hover:scale-[1.01]';
                    optionContainer.style.animation = '';
                }
                
                // Reset badge to neutral
                if (optionBadge) {
                    optionBadge.className = 'w-8 h-8 bg-gradient-to-br from-gray-500 to-gray-600 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-md transition-all duration-300';
                    optionBadge.style.boxShadow = '0 2px 8px rgba(107, 114, 128, 0.3)';
                }
                
                // Reset input styling
                if (optionInput) {
                    optionInput.className = 'option-text w-full bg-transparent border-none focus:outline-none text-sm text-gray-800 placeholder-gray-400 py-2';
                }
                
                // Remove correct indicator with fade out
                if (correctIndicator) {
                    correctIndicator.style.transition = 'all 0.2s ease-in';
                    correctIndicator.style.opacity = '0';
                    correctIndicator.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        correctIndicator.remove();
                    }, 200);
                }
                
                // Remove checkmark with scale out
                const checkmark = radioContainer?.querySelector('.absolute');
                if (checkmark) {
                    checkmark.style.transition = 'transform 0.2s ease-in';
                    checkmark.style.transform = 'scale(0)';
                    setTimeout(() => {
                        checkmark.remove();
                    }, 200);
                }
            }
        });
        
        console.log('✨ MODERN: Visual state update completed with smooth animations');
    }

    /**
     * Update correct answer labels - Reliable approach that works with actual template
     */
    updateCorrectAnswerLabelsReliable(questionElement) {
        const radioButtons = questionElement.querySelectorAll('.correct-answer');
        console.log(`🔧 RELIABLE UPDATE: Processing ${radioButtons.length} options`);
        
        radioButtons.forEach((radio, index) => {
            const optionItem = radio.closest('.option-item');
            if (!optionItem) {
                console.log(`❌ No option-item found for radio ${index}`);
                return;
            }
            
            // Get the main container and badge elements
            const optionContainer = optionItem.querySelector('div.flex.items-start.gap-3');
            const badgeContainer = optionContainer?.querySelector('div.relative');
            const badge = badgeContainer?.querySelector('div.w-8.h-8');
            const textInput = optionContainer?.querySelector('input.option-text');
            const textContainer = textInput?.parentElement;
            
            console.log(`🔍 Option ${index} elements:`, {
                hasContainer: !!optionContainer,
                hasBadge: !!badge,
                hasTextInput: !!textInput,
                isChecked: radio.checked
            });
            
            if (radio.checked) {
                console.log(`✅ Setting option ${index} as CORRECT`);
                
                // Update container classes for correct state
                if (optionContainer) {
                    optionContainer.className = 'flex items-start gap-3 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 shadow-md ring-2 ring-blue-200 ring-opacity-50 rounded-xl p-4 transition-all duration-300';
                }
                
                // Update badge for correct state
                if (badge) {
                    badge.className = 'w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-md transition-all duration-300';
                }
                
                // Update text input for correct state
                if (textInput) {
                    textInput.className = 'option-text w-full bg-transparent border-none focus:outline-none text-sm text-blue-900 font-medium placeholder-gray-400 py-1';
                }
                
                // Add correct indicator if not exists
                if (textContainer && !textContainer.querySelector('.text-blue-700')) {
                    const indicator = document.createElement('div');
                    indicator.className = 'mt-1 text-xs text-blue-700 font-medium flex items-center';
                    indicator.innerHTML = '<i class="fas fa-star mr-1"></i>Correct Answer';
                    textContainer.appendChild(indicator);
                }
                
                // Add checkmark if not exists
                if (badgeContainer && !badgeContainer.querySelector('.absolute')) {
                    const checkmark = document.createElement('div');
                    checkmark.className = 'absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full flex items-center justify-center';
                    checkmark.innerHTML = '<i class="fas fa-check text-white" style="font-size: 6px;"></i>';
                    badgeContainer.appendChild(checkmark);
                }
                
            } else {
                console.log(`🔘 Setting option ${index} as INCORRECT`);
                
                // Update container classes for incorrect state
                if (optionContainer) {
                    optionContainer.className = 'flex items-start gap-3 bg-white hover:bg-gray-50 border-2 border-gray-200 hover:border-blue-300 rounded-xl p-4 transition-all duration-300 hover:shadow-md transform hover:scale-[1.01]';
                }
                
                // Update badge for incorrect state
                if (badge) {
                    badge.className = 'w-8 h-8 bg-gradient-to-br from-gray-500 to-gray-600 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-md transition-all duration-300';
                }
                
                // Update text input for incorrect state
                if (textInput) {
                    textInput.className = 'option-text w-full bg-transparent border-none focus:outline-none text-sm text-gray-800 placeholder-gray-400 py-1';
                }
                
                // Remove correct indicator
                const indicator = textContainer?.querySelector('.text-blue-700');
                if (indicator) {
                    indicator.remove();
                }
                
                // Remove checkmark
                const checkmark = badgeContainer?.querySelector('.absolute');
                if (checkmark) {
                    checkmark.remove();
                }
            }
        });
        
        console.log('✅ RELIABLE: Visual state update completed');
    }
    
    /**
     * Update correct answer labels - Simple approach (fallback)
     */
    updateCorrectAnswerLabelsSimple(questionElement) {
        const radioButtons = questionElement.querySelectorAll('.correct-answer');
        console.log(`🎨 Updating visual state for ${radioButtons.length} options`);
        
        radioButtons.forEach((radio) => {
            const optionItem = radio.closest('.option-item');
            if (!optionItem) {
                console.log('❌ No option-item found for radio button');
                return;
            }
            
            const optionContainer = optionItem.querySelector('.flex.items-start.gap-3');
            const optionBadge = optionItem.querySelector('.w-8.h-8');
            const optionInput = optionItem.querySelector('.option-text');
            const correctIndicator = optionItem.querySelector('.text-xs.text-blue-700.font-medium');
            const radioContainer = radio.closest('.relative');
            
            if (radio.checked) {
                console.log(`✅ Marking option ${radio.dataset.optionIndex} as correct`);
                
                // Update container styling - modern blue theme
                if (optionContainer) {
                    optionContainer.className = 'flex items-start gap-3 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-3 transition-all duration-200 shadow-md ring-2 ring-blue-200 ring-opacity-50';
                }
                
                // Update badge to blue (correct)
                if (optionBadge) {
                    optionBadge.className = 'w-7 h-7 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-sm';
                }
                
                // Update input styling
                if (optionInput) {
                    optionInput.className = 'option-text w-full bg-transparent border-none focus:outline-none text-sm text-blue-900 font-medium placeholder-gray-400 py-1';
                }
                
                // Add/update correct indicator
                if (!correctIndicator) {
                    const indicator = document.createElement('div');
                    indicator.className = 'mt-1 text-xs text-blue-700 font-medium flex items-center';
                    indicator.innerHTML = '<i class="fas fa-star mr-1"></i>Correct Answer';
                    optionInput.parentElement.appendChild(indicator);
                }
                
                // Add checkmark to radio button
                if (radioContainer && !radioContainer.querySelector('.absolute')) {
                    const checkmark = document.createElement('div');
                    checkmark.className = 'absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full flex items-center justify-center';
                    checkmark.innerHTML = '<i class="fas fa-check text-white text-xs"></i>';
                    radioContainer.appendChild(checkmark);
                }
                
            } else {
                console.log(`⭕ Marking option ${radio.dataset.optionIndex} as incorrect`);
                
                // Reset container styling - default gray theme
                if (optionContainer) {
                    optionContainer.className = 'flex items-start gap-3 bg-white hover:bg-gray-50 border border-gray-200 hover:border-blue-300 rounded-lg p-3 transition-all duration-200 shadow-sm hover:shadow-md';
                }
                
                // Reset badge to gray (incorrect)
                if (optionBadge) {
                    optionBadge.className = 'w-7 h-7 bg-gray-600 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-sm';
                }
                
                // Reset input styling
                if (optionInput) {
                    optionInput.className = 'option-text w-full bg-transparent border-none focus:outline-none text-sm text-gray-800 placeholder-gray-400 py-1';
                }
                
                // Remove correct indicator
                if (correctIndicator) {
                    correctIndicator.remove();
                }
                
                // Remove checkmark from radio button
                const checkmark = radioContainer?.querySelector('.absolute');
                if (checkmark) {
                    checkmark.remove();
                }
            }
        });
        
        console.log('🎨 Simple visual state update completed');
    }

    /**
     * Update correct answer labels for multiple choice (full re-render)
     */
    updateCorrectAnswerLabels(questionElement) {
        const questionId = questionElement.dataset.questionId;
        const question = this.exam.getQuestion(questionId);
        if (!question || question.type !== 'multiple_choice') return;
        
        const optionsContainer = questionElement.querySelector('.options-container');
        if (!optionsContainer) return;
        
        // Get current option texts and which one is checked from DOM
        const optionItems = optionsContainer.querySelectorAll('.option-item');
        const options = [];
        
        optionItems.forEach((optionItem, index) => {
            const textInput = optionItem.querySelector('.option-text');
            const radio = optionItem.querySelector('.correct-answer');
            const optionData = {
                text: textInput ? textInput.value : '',
                isCorrect: radio ? radio.checked : false
            };
            options.push(optionData);
        });
        
        // Update the model
        question.options = options;
        
        // Re-render all options with updated state
        optionsContainer.innerHTML = '';
        options.forEach((option, index) => {
            const optionHtml = this.view.templates.renderOption(questionId, option, index);
            optionsContainer.insertAdjacentHTML('beforeend', optionHtml);
        });
        
        // Re-attach event listeners
        this.setupOptionEventListeners(questionElement);
    }

    /**
     * Update all questions from DOM data before saving
     */
    updateQuestionsFromDOM() {
        const questionElements = this.container.querySelectorAll('.question-card');
        
        // Clear current questions and rebuild from DOM
        this.exam.questions = [];
        
        questionElements.forEach((questionElement) => {
            const questionData = this.collectQuestionDataFromDOM(questionElement);
            if (questionData) {
                const question = Question.fromJSON(questionData);
                this.exam.questions.push(question);
            }
        });
    }

    /**
     * Collect question data from a single DOM element
     */
    collectQuestionDataFromDOM(questionElement) {
        const questionId = questionElement.dataset.questionId;
        const questionType = questionElement.dataset.questionType;
        
        if (!questionId || !questionType) return null;

        // Get basic question data
        const textArea = questionElement.querySelector('.question-text');
        const pointsInput = questionElement.querySelector('.question-points');
        
        const questionData = {
            id: questionId,
            type: questionType,
            text: textArea ? textArea.value.trim() : '',
            points: pointsInput ? parseInt(pointsInput.value) || 1 : 1,
            options: [],
            correctAnswer: null,
            metadata: {}
        };

        // Collect type-specific data
        switch (questionType) {
            case 'multiple_choice':
                questionData.options = this.collectMultipleChoiceOptions(questionElement);
                break;
            case 'true_false':
                questionData.correctAnswer = this.collectTrueFalseAnswer(questionElement);
                break;
            case 'enumeration':
                const enumData = this.collectEnumerationData(questionElement);
                questionData.correctAnswer = enumData.answers;
                questionData.metadata.expectedCount = enumData.expectedCount;
                break;
            case 'essay':
                questionData.metadata = this.collectEssayData(questionElement);
                break;
        }

        return questionData;
    }

    /**
     * Collect multiple choice options from DOM
     */
    collectMultipleChoiceOptions(questionElement) {
        const options = [];
        const optionElements = questionElement.querySelectorAll('.option-item');
        
        optionElements.forEach((optionElement, index) => {
            const textInput = optionElement.querySelector('.option-text');
            const radioInput = optionElement.querySelector('.correct-answer');
            
            let optionText = textInput ? textInput.value.trim() : '';
            const isCorrect = radioInput ? radioInput.checked : false;
            
            // Ensure option text is not empty - provide fallback
            if (!optionText) {
                optionText = `Option ${String.fromCharCode(65 + index)}`;
                if (textInput) {
                    textInput.value = optionText;
                }
            }
            
            options.push({
                text: optionText,
                isCorrect: isCorrect
            });
        });
        
        return options;
    }

    /**
     * Collect true/false answer from DOM
     */
    collectTrueFalseAnswer(questionElement) {
        // Look for the checked radio button with class 'true-false-answer'
        const checkedRadio = questionElement.querySelector('.true-false-answer:checked');
        if (checkedRadio) {
            console.log(`True/False question ${questionElement.dataset.questionId}: Found checked radio with value '${checkedRadio.value}'`);
            return checkedRadio.value;
        }
        
        // Fallback: look for any radio button and get the first one's value (shouldn't happen)
        const anyRadio = questionElement.querySelector('.true-false-answer');
        if (anyRadio) {
            console.log(`True/False question ${questionElement.dataset.questionId}: No checked radio found, defaulting to 'true'`);
            return 'true';
        }
        
        console.log(`True/False question ${questionElement.dataset.questionId}: No radio buttons found, defaulting to 'true'`);
        return 'true';
    }

    /**
     * Collect enumeration data from DOM
     */
    collectEnumerationData(questionElement) {
        const textArea = questionElement.querySelector('.enumeration-answers');
        const expectedCountInput = questionElement.querySelector('.expected-count');
        
        const answersText = textArea ? textArea.value.trim() : '';
        const answers = answersText ? answersText.split('\n').filter(a => a.trim()) : [];
        const expectedCount = expectedCountInput ? parseInt(expectedCountInput.value) || 3 : 3;
        
        return {
            answers: answers,
            expectedCount: expectedCount
        };
    }

    /**
     * Collect essay data from DOM
     */
    collectEssayData(questionElement) {
        const metadata = {};
        
        // Collect rubric weights
        const rubric = {};
        const weightInputs = questionElement.querySelectorAll('.rubric-weight');
        weightInputs.forEach(input => {
            const criterion = input.dataset.criterion;
            if (criterion) {
                rubric[criterion] = parseInt(input.value) || 0;
            }
        });
        metadata.rubric = rubric;
        
        // Collect key concepts
        const keyConcepts = [];
        const conceptInputs = questionElement.querySelectorAll('.key-concept');
        conceptInputs.forEach(input => {
            const concept = input.value.trim();
            if (concept) {
                keyConcepts.push(concept);
            }
        });
        metadata.keyConcepts = keyConcepts;
        
        return metadata;
    }

    /**
     * Start auto-save
     */
    startAutoSave() {
        // Auto-save every 2 minutes
        this.autoSaveInterval = setInterval(() => {
            this.autoSave();
        }, 120000);
    }

    /**
     * Auto-save exam
     */
    async autoSave() {
        const questionElements = this.container.querySelectorAll('.question-card');
        if (questionElements.length > 0) {
            try {
                this.updateExamMetadata();
                this.updateQuestionsFromDOM();
                await this.service.autoSave(this.exam.toJSON());
                console.log('Auto-saved at', new Date().toLocaleTimeString());
            } catch (error) {
                console.error('Auto-save failed:', error);
            }
        }
    }

    /**
     * Add option to multiple choice question (fixed to match template engine)
     */
    addOptionToQuestion(questionId) {
        const questionElement = this.container.querySelector(`[data-question-id="${questionId}"]`);
        if (!questionElement) return;

        const optionsContainer = questionElement.querySelector('.options-container');
        const optionIndex = optionsContainer.children.length;
        const letters = ['A','B','C','D','E','F','G','H'];
        const letter = letters[optionIndex] || String.fromCharCode(65 + optionIndex);
        
        // Update the model first
        const question = this.exam.getQuestion(questionId);
        if (question && question.type === 'multiple_choice') {
            const newOption = { text: '', isCorrect: false };
            question.options.push(newOption);
            
            // Use the template engine to render the new option
            const optionHtml = this.view.templates.renderOption(questionId, newOption, optionIndex);
            optionsContainer.insertAdjacentHTML('beforeend', optionHtml);
            
            // Re-setup event listeners for all options
            this.setupOptionEventListeners(questionElement);
            
            console.log(`✅ Added option ${letter} to question ${questionId}`);
        }
    }

    /**
     * Update correct answer labels with old business logic approach
     */
    updateCorrectAnswerLabelsOldStyle(questionElement) {
        const radioButtons = questionElement.querySelectorAll('.correct-answer');
        
        radioButtons.forEach((radio, index) => {
            const label = radio.parentElement.querySelector('span');
            const optionItem = radio.closest('.option-item');
            
            if (radio.checked) {
                if (label) {
                    label.textContent = 'CORRECT';
                    label.className = 'ml-2 text-xs font-medium text-green-600';
                }
                if (optionItem) {
                    optionItem.classList.remove('border-gray-200');
                    optionItem.classList.add('border-green-400', 'bg-green-50');
                }
            } else {
                if (label) {
                    label.textContent = 'OPTION';
                    label.className = 'ml-2 text-xs font-medium text-gray-400';
                }
                if (optionItem) {
                    optionItem.classList.remove('border-green-400', 'bg-green-50');
                    optionItem.classList.add('border-gray-200');
                }
            }
        });
    }

    /**
     * Add enumeration answer (from old business logic)
     */
    addEnumerationAnswer(questionId) {
        const questionElement = this.container.querySelector(`[data-question-id="${questionId}"]`);
        if (!questionElement) return;

        const container = questionElement.querySelector('.enumeration-answers-container');
        if (!container) return;

        const answerCount = container.children.length + 1;
        
        const enumDiv = document.createElement('div');
        enumDiv.className = 'enumeration-item bg-white border-2 border-orange-200 rounded-lg p-4 hover:border-orange-300 transition-all duration-200';
        enumDiv.innerHTML = `
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                    <span class="text-orange-600 font-bold text-sm">${answerCount}</span>
                </div>
                <input type="text" class="enum-answer flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="Enter expected answer..." data-question-id="${questionId}">
                <button class="remove-enum-item text-gray-400 hover:text-red-600 p-1 rounded" title="Remove answer" data-question-id="${questionId}">
                    <i class="fas fa-trash text-sm"></i>
                </button>
            </div>
        `;
        
        container.appendChild(enumDiv);
        
        // Add remove event listener
        enumDiv.querySelector('.remove-enum-item').addEventListener('click', (e) => {
            const container = enumDiv.parentElement;
            if (container.children.length > 1) {
                enumDiv.remove();
                this.updateEnumerationNumbers(questionId);
            }
        });
    }

    /**
     * Update enumeration numbers (from old business logic)
     */
    updateEnumerationNumbers(questionId) {
        const questionElement = this.container.querySelector(`[data-question-id="${questionId}"]`);
        if (!questionElement) return;

        const enumItems = questionElement.querySelectorAll('.enumeration-item');
        enumItems.forEach((item, index) => {
            const numberSpan = item.querySelector('.w-8 span');
            if (numberSpan) {
                numberSpan.textContent = index + 1;
            }
        });
    }

    /**
     * Add key concept for essay questions (from old business logic)
     */
    addKeyConceptToQuestion(questionId) {
        const questionElement = this.container.querySelector(`[data-question-id="${questionId}"]`);
        if (!questionElement) return;

        const container = questionElement.querySelector('.key-concepts-container');
        if (!container) return;
        
        const conceptDiv = document.createElement('div');
        conceptDiv.className = 'flex items-center space-x-2';
        conceptDiv.innerHTML = `
            <input type="text" class="key-concept flex-1 px-3 py-2 border border-purple-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm" 
                   placeholder="Enter important concept or term..." data-question-id="${questionId}">
            <button class="remove-concept text-gray-400 hover:text-red-600 p-2" title="Remove concept" data-question-id="${questionId}">
                <i class="fas fa-times text-sm"></i>
            </button>
        `;
        
        container.appendChild(conceptDiv);
        
        // Add remove event listener
        conceptDiv.querySelector('.remove-concept').addEventListener('click', () => {
            if (container.children.length > 1) {
                conceptDiv.remove();
            }
        });
        
        // Focus on the new input
        conceptDiv.querySelector('.key-concept').focus();
    }

    /**
     * Update rubric total for essay questions (from old business logic)
     */
    updateRubricTotalForQuestion(questionId) {
        const questionElement = this.container.querySelector(`[data-question-id="${questionId}"]`);
        if (!questionElement) return;
        
        const weights = questionElement.querySelectorAll('.rubric-weight');
        let total = 0;
        
        weights.forEach(weight => {
            const value = parseInt(weight.value);
            total += value;
            
            // Update the display
            const criterion = weight.dataset.criterion;
            const display = document.getElementById(`${criterion}-weight-${questionId}`);
            if (display) {
                display.textContent = `${value}%`;
            }
        });
        
        // Update total display
        const totalDisplay = document.getElementById(`rubric-total-${questionId}`);
        if (totalDisplay) {
            totalDisplay.textContent = total;
            
            // Color code based on total
            if (total === 100) {
                totalDisplay.className = 'text-green-600 font-bold';
            } else if (total > 100) {
                totalDisplay.className = 'text-red-600 font-bold';
            } else {
                totalDisplay.className = 'text-orange-600 font-bold';
            }
        }
    }

    /**
     * Validate questions using old business logic approach
     */
    validateQuestionsOldStyle(questions) {
        const errors = [];
        
        questions.forEach((question, index) => {
            const questionNum = index + 1;
            
            // Check if question text is provided
            if (!question.text || question.text.trim() === '') {
                errors.push(`Question ${questionNum}: Please enter question text`);
            }
            
            // Check if question text is just a single letter (common mistake)
            if (question.text && question.text.trim().length === 1) {
                errors.push(`Question ${questionNum}: Question text seems too short - "${question.text}"`);
            }
            
            // Validate multiple choice questions
            if (question.type === 'multiple_choice') {
                if (!question.options || question.options.length < 2) {
                    errors.push(`Question ${questionNum}: Multiple choice questions need at least 2 options`);
                } else {
                    // Check if at least one option is marked as correct
                    const hasCorrectAnswer = question.options.some(option => option.isCorrect);
                    if (!hasCorrectAnswer) {
                        errors.push(`Question ${questionNum}: Please select which option is correct`);
                    }
                    
                    // Check if all options have text
                    question.options.forEach((option, optIndex) => {
                        if (!option.text || option.text.trim() === '') {
                            errors.push(`Question ${questionNum}: Option ${optIndex + 1} is empty`);
                        }
                    });
                }
            }
            
            // Validate True/False questions
            if (question.type === 'true_false') {
                if (!question.correctAnswer) {
                    errors.push(`Question ${questionNum}: Please select True or False as the correct answer`);
                }
            }
            
            // Check points
            if (!question.points || question.points <= 0) {
                errors.push(`Question ${questionNum}: Points must be greater than 0`);
            }
        });
        
        return errors;
    }

    /**
     * Cleanup
     */
    destroy() {
        if (this.autoSaveInterval) {
            clearInterval(this.autoSaveInterval);
        }
    }
}

/**
 * Legacy showMessage function for backward compatibility with old business logic
 */
function showMessage(message, type) {
    // Check if there's already a modern modal system
    if (window.modernModal) {
        if (type === 'success') {
            window.modernModal.success('Success', message);
        } else if (type === 'error') {
            window.modernModal.error('Error', message);
        } else if (type === 'warning') {
            window.modernModal.warning('Warning', message);
        } else {
            window.modernModal.info('Info', message);
        }
        return;
    }
    
    // Fallback to creating simple message container
    const messageContainer = document.getElementById('messageContainer') || createMessageContainer();
    const messageDiv = document.createElement('div');
    
    const bgColor = type === 'success' ? 'bg-green-500' : 
                   type === 'error' ? 'bg-red-500' : 
                   type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';
    
    // Handle multi-line messages
    const formattedMessage = message.replace(/\n/g, '<br>');
    
    messageDiv.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg mb-4 max-w-md`;
    messageDiv.innerHTML = `
        <div class="flex items-start justify-between">
            <div class="flex-1 text-sm">${formattedMessage}</div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200 flex-shrink-0">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    messageContainer.appendChild(messageDiv);
    
    // Auto remove after timeout
    const timeout = type === 'error' ? 8000 : 5000;
    setTimeout(() => {
        if (messageDiv.parentElement) {
            messageDiv.remove();
        }
    }, timeout);
}

/**
 * Create message container if it doesn't exist
 */
function createMessageContainer() {
    let container = document.getElementById('messageContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'messageContainer';
        container.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(container);
    }
    return container;
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ExamBuilderController;
} else {
    window.ExamBuilderController = ExamBuilderController;
    window.showMessage = showMessage; // Make showMessage globally available
}
