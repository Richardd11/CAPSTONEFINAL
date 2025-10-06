/**
 * ExamBuilderView - Handles all DOM manipulation and rendering
 * Pure View layer - NO business logic, only presentation
 */
class ExamBuilderView {
    constructor(containerId = 'questionsContainer') {
        this.container = document.getElementById(containerId);
        this.templates = new TemplateEngine();
        this.noQuestionsMessage = document.getElementById('noQuestionsMessage');
        this.totalPointsDisplay = document.getElementById('totalPoints');
        
        this.initializeDeleteModal();
    }

    /**
     * Initialize delete confirmation modal
     */
    initializeDeleteModal() {
        if (!document.getElementById('deleteQuestionModal')) {
            document.body.insertAdjacentHTML('beforeend', this.templates.renderDeleteModal());
        }
    }

    /**
     * Render a question and add to DOM
     */
    renderQuestion(question) {
        const html = this.templates.renderQuestion(question);
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        const questionElement = wrapper.firstElementChild;
        
        this.container.appendChild(questionElement);
        this.hideNoQuestionsMessage();
        
        return questionElement;
    }

    /**
     * Animate question removal with smooth effects
     */
    animateQuestionRemoval(questionId, callback) {
        const element = this.container.querySelector(`[data-question-id="${questionId}"]`);
        if (!element) {
            if (callback) callback();
            return;
        }
        
        // Add animation classes
        element.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
        element.style.transformOrigin = 'center';
        
        // Stage 1: Shake animation
        element.style.animation = 'shake 0.3s ease-in-out';
        
        setTimeout(() => {
            // Stage 2: Fade out and slide up
            element.style.opacity = '0';
            element.style.transform = 'translateX(-50px) scale(0.8)';
            element.style.maxHeight = element.offsetHeight + 'px';
            
            setTimeout(() => {
                // Stage 3: Collapse height
                element.style.maxHeight = '0';
                element.style.marginBottom = '0';
                element.style.paddingTop = '0';
                element.style.paddingBottom = '0';
                
                setTimeout(() => {
                    // Stage 4: Remove from DOM
                    element.remove();
                    
                    // Show no questions message if container is empty
                    if (this.container.children.length === 0) {
                        this.showNoQuestionsMessage();
                    }
                    
                    if (callback) callback();
                }, 300);
            }, 200);
        }, 300);
    }

    /**
     * Remove question from DOM (without animation)
     */
    removeQuestion(questionId) {
        const element = this.container.querySelector(`[data-question-id="${questionId}"]`);
        if (element) {
            element.remove();
        }
        
        // Show no questions message if container is empty
        if (this.container.children.length === 0) {
            this.showNoQuestionsMessage();
        }
    }

    /**
     * Update question numbers
     */
    updateQuestionNumbers() {
        const questions = this.container.querySelectorAll('.question-card');
        questions.forEach((card, index) => {
            const numberSpan = card.querySelector('.question-number');
            if (numberSpan) {
                numberSpan.textContent = `Question ${index + 1}`;
            }
        });
        
        // Update question count display
        const questionCountDisplay = document.getElementById('questionCount');
        if (questionCountDisplay) {
            questionCountDisplay.textContent = questions.length;
        }
    }

    /**
     * Update total points display
     */
    updateTotalPoints(totalPoints) {
        if (this.totalPointsDisplay) {
            this.totalPointsDisplay.textContent = totalPoints;
        }
    }

    /**
     * Show/hide no questions message
     */
    showNoQuestionsMessage() {
        if (this.noQuestionsMessage) {
            this.noQuestionsMessage.style.display = 'block';
        }
    }

    hideNoQuestionsMessage() {
        if (this.noQuestionsMessage) {
            this.noQuestionsMessage.style.display = 'none';
        }
    }

    /**
     * Show delete confirmation modal
     */
    showDeleteModal(questionText) {
        const modal = document.getElementById('deleteQuestionModal');
        const modalContent = document.getElementById('deleteModalContent');
        const textElement = document.getElementById('questionToDeleteText');
        
        if (textElement) {
            const displayText = questionText && questionText.length > 100 
                ? questionText.substring(0, 100) + '...' 
                : questionText || 'this question';
            textElement.textContent = displayText;
        }
        
        // Show modal
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        
        // Animate in with smooth transition
        requestAnimationFrame(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
            modalContent.style.transform = 'scale(1)';
            modalContent.style.opacity = '1';
        });
    }

    /**
     * Hide delete confirmation modal
     */
    hideDeleteModal() {
        const modal = document.getElementById('deleteQuestionModal');
        const modalContent = document.getElementById('deleteModalContent');
        
        // Animate out
        modalContent.style.transform = 'scale(0.95)';
        modalContent.style.opacity = '0';
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }, 300);
    }

    /**
     * Show question type menu
     */
    showQuestionTypeMenu() {
        const menu = document.getElementById('questionTypeMenu');
        if (menu) {
            menu.classList.remove('hidden');
        }
    }

    /**
     * Hide question type menu
     */
    hideQuestionTypeMenu() {
        const menu = document.getElementById('questionTypeMenu');
        if (menu) {
            menu.classList.add('hidden');
        }
        this.hideQuantityPanel();
    }

    /**
     * Show quantity panel
     */
    showQuantityPanel(type) {
        const typeInfo = {
            'multiple_choice': { icon: 'fas fa-list-ul', name: 'Multiple Choice', color: 'text-blue-600' },
            'true_false': { icon: 'fas fa-check-circle', name: 'True/False', color: 'text-green-600' },
            'enumeration': { icon: 'fas fa-list-ol', name: 'Enumeration', color: 'text-orange-600' },
            'essay': { icon: 'fas fa-file-alt', name: 'Essay', color: 'text-purple-600' }
        };

        const info = typeInfo[type];
        const iconElement = document.getElementById('selectedTypeIcon');
        const nameElement = document.getElementById('selectedTypeName');
        
        if (iconElement && nameElement) {
            iconElement.className = `${info.icon} mr-2 ${info.color}`;
            nameElement.textContent = info.name;
            nameElement.dataset.type = type;
        }
        
        // Reset quantity to 1
        const quantityInput = document.getElementById('questionQuantity');
        if (quantityInput) {
            quantityInput.value = 1;
        }
        
        // Show panel
        const panel = document.getElementById('quantityPanel');
        if (panel) {
            panel.classList.remove('hidden');
        }
    }

    /**
     * Hide quantity panel
     */
    hideQuantityPanel() {
        const panel = document.getElementById('quantityPanel');
        if (panel) {
            panel.classList.add('hidden');
        }
    }

    /**
     * Get quantity from input
     */
    getQuantity() {
        const input = document.getElementById('questionQuantity');
        return input ? parseInt(input.value) || 1 : 1;
    }

    /**
     * Get selected type from quantity panel
     */
    getSelectedType() {
        const nameElement = document.getElementById('selectedTypeName');
        return nameElement ? nameElement.dataset.type : null;
    }

    /**
     * Add option to multiple choice question
     */
    addOption(questionId, option, index) {
        const container = this.container.querySelector(`[data-question-id="${questionId}"] .options-container`);
        if (container) {
            const html = this.templates.renderOption(questionId, option, index);
            container.insertAdjacentHTML('beforeend', html);
        }
    }

    /**
     * Remove option from multiple choice question
     */
    removeOption(questionId, optionIndex) {
        const container = this.container.querySelector(`[data-question-id="${questionId}"] .options-container`);
        if (container) {
            const options = container.querySelectorAll('.option-item');
            if (options[optionIndex]) {
                options[optionIndex].remove();
            }
        }
    }

    /**
     * Update rubric total display
     */
    updateRubricTotal(questionId, total) {
        const display = document.getElementById(`rubric-total-${questionId}`);
        if (display) {
            display.textContent = `${total}%`;
            display.className = `rubric-total text-lg font-bold ${this.templates.getRubricTotalClass(total)}`;
        }
    }

    /**
     * Update rubric weight display
     */
    updateRubricWeight(questionId, criterion, value) {
        const display = document.getElementById(`${criterion}-weight-${questionId}`);
        if (display) {
            display.textContent = `${value}%`;
        }
    }

    /**
     * Add key concept input
     */
    addKeyConcept(questionId, concept = '', index) {
        const container = this.container.querySelector(`[data-question-id="${questionId}"] .key-concepts-container`);
        if (container) {
            const html = `
                <div class="flex items-center space-x-2">
                    <input type="text" 
                           class="key-concept flex-1 px-3 py-2 border border-purple-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm" 
                           placeholder="Enter important concept or term..."
                           value="${concept}"
                           data-question-id="${questionId}"
                           data-concept-index="${index}">
                    <button class="remove-concept text-gray-400 hover:text-red-600 p-2" 
                            title="Remove concept"
                            data-question-id="${questionId}"
                            data-concept-index="${index}">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }
    }

    /**
     * Show delete confirmation modal
     */
    showDeleteConfirmation() {
        // Create modal if it doesn't exist
        let modal = document.getElementById('deleteConfirmModal');
        
        if (!modal) {
            const modalHTML = `
                <div id="deleteConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
                    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="deleteConfirmContent">
                        <div class="text-center">
                            <!-- Success Icon -->
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                                <i class="fas fa-trash-alt text-red-600 text-3xl"></i>
                            </div>
                            
                            <!-- Title -->
                            <h3 class="text-2xl font-bold text-gray-900 mb-4">Question Deleted!</h3>
                            
                            <!-- Message -->
                            <p class="text-gray-600 mb-6">
                                The question has been successfully removed from your exam.
                            </p>
                            
                            <div class="bg-green-50 rounded-lg p-4 mb-6">
                                <p class="text-sm text-green-800">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Your exam has been updated automatically.
                                </p>
                            </div>
                            
                            <!-- Button -->
                            <button id="closeDeleteConfirmBtn" class="w-full px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-xl hover:from-gray-600 hover:to-gray-700 transition-all duration-200 font-semibold shadow-lg">
                                <i class="fas fa-check mr-2"></i>Okay
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            modal = document.getElementById('deleteConfirmModal');
        }
        
        // Show modal
        const modalContent = document.getElementById('deleteConfirmContent');
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        
        requestAnimationFrame(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
            modalContent.style.transform = 'scale(1)';
            modalContent.style.opacity = '1';
        });
        
        // Handle close button
        const closeBtn = document.getElementById('closeDeleteConfirmBtn');
        closeBtn.onclick = () => {
            modalContent.style.transform = 'scale(0.95)';
            modalContent.style.opacity = '0';
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }, 300);
        };
        
        // Auto-close after 2 seconds
        setTimeout(() => {
            if (!modal.classList.contains('hidden')) {
                closeBtn.click();
            }
        }, 2000);
    }

    /**
     * Show add question confirmation modal
     */
    showAddQuestionConfirmation(quantity, questionType) {
        // Create modal if it doesn't exist
        let modal = document.getElementById('addQuestionConfirmModal');
        
        if (!modal) {
            const modalHTML = `
                <div id="addQuestionConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
                    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="addQuestionConfirmContent">
                        <div class="text-center">
                            <!-- Success Icon -->
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
                                <i class="fas fa-check-circle text-green-600 text-3xl"></i>
                            </div>
                            
                            <!-- Title -->
                            <h3 class="text-2xl font-bold text-gray-900 mb-4">Questions Added!</h3>
                            
                            <!-- Message -->
                            <p class="text-gray-600 mb-6" id="confirmationMessage">
                                Successfully added <span class="font-bold text-green-600" id="questionCount"></span> 
                                <span id="questionTypeName"></span> question<span id="pluralS"></span> to your exam.
                            </p>
                            
                            <div class="bg-blue-50 rounded-lg p-4 mb-6">
                                <p class="text-sm text-blue-800">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Don't forget to fill in the question details and set the correct answers!
                                </p>
                            </div>
                            
                            <!-- Button -->
                            <button id="closeConfirmBtn" class="w-full px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 transition-all duration-200 font-semibold shadow-lg">
                                <i class="fas fa-thumbs-up mr-2"></i>Got it!
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            modal = document.getElementById('addQuestionConfirmModal');
        }
        
        // Update content
        document.getElementById('questionCount').textContent = quantity;
        document.getElementById('questionTypeName').textContent = questionType;
        document.getElementById('pluralS').textContent = quantity > 1 ? 's' : '';
        
        // Show modal
        const modalContent = document.getElementById('addQuestionConfirmContent');
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        
        requestAnimationFrame(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
            modalContent.style.transform = 'scale(1)';
            modalContent.style.opacity = '1';
        });
        
        // Handle close button
        const closeBtn = document.getElementById('closeConfirmBtn');
        closeBtn.onclick = () => {
            modalContent.style.transform = 'scale(0.95)';
            modalContent.style.opacity = '0';
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }, 300);
        };
        
        // Auto-close after 3 seconds
        setTimeout(() => {
            if (!modal.classList.contains('hidden')) {
                closeBtn.click();
            }
        }, 3000);
    }

    /**
     * Show success message
     */
    showSuccess(message) {
        this.showMessage(message, 'success');
    }

    /**
     * Show error message
     */
    showError(message) {
        this.showMessage(message, 'error');
    }

    /**
     * Show warning message
     */
    showWarning(message) {
        this.showMessage(message, 'warning');
    }

    /**
     * Show message using modern modal service
     */
    showMessage(message, type = 'info') {
        if (window.modernModal) {
            switch(type) {
                case 'success':
                    window.modernModal.success('Success', message);
                    break;
                case 'error':
                    window.modernModal.error('Error', message);
                    break;
                case 'warning':
                    window.modernModal.warning('Warning', message);
                    break;
                default:
                    window.modernModal.info('Information', message);
            }
        } else {
            // Fallback to alert
            alert(message);
        }
    }

    /**
     * Show validation errors
     */
    showValidationErrors(errors) {
        if (errors.length === 0) return;
        
        const errorList = errors.map(err => `• ${err}`).join('\n');
        this.showError(`Validation failed:\n${errorList}`);
    }

    /**
     * Show confirmation dialog with warnings
     */
    async confirmWithWarnings(warnings) {
        // Show warnings as modern modal
        if (window.modernModal && warnings.length > 0) {
            const warningText = warnings.join('\n\n');
            const confirm = await window.modernModal.confirm(
                'Validation Warnings',
                `The following issues were found:\n\n${warningText}\n\nDo you want to continue anyway?`,
                {
                    confirmText: 'Continue',
                    cancelText: 'Fix Issues',
                    icon: 'fas fa-exclamation-triangle'
                }
            );
            return confirm;
        }
        // Auto-continue if no warnings
        return true;
    }

    /**
     * Get all question data from DOM
{{ ... }}
    getQuestionDataFromDOM(questionId) {
        const card = this.container.querySelector(`[data-question-id="${questionId}"]`);
        if (!card) return null;

        const type = card.dataset.questionType;
        const text = card.querySelector('.question-text')?.value || '';
        const points = parseInt(card.querySelector('.question-points')?.value) || 1;

        const data = {
            id: questionId,
            type: type,
            text: text,
            points: points
        };

        // Get type-specific data
        switch (type) {
            case 'multiple_choice':
                data.options = this.getMultipleChoiceOptions(card);
                break;
            case 'true_false':
                // Find the checked radio button for true/false questions
                const checkedRadio = card.querySelector('.true-false-answer:checked');
                data.correctAnswer = checkedRadio ? checkedRadio.value : 'true';
                break;
            case 'enumeration':
                const answers = card.querySelector('.enumeration-answers')?.value || '';
                data.correctAnswer = answers.split('\n').filter(a => a.trim());
                data.expectedCount = parseInt(card.querySelector('.expected-count')?.value) || 3;
                break;
            case 'essay':
                data.rubric = this.getEssayRubric(card);
                data.keyConcepts = this.getKeyConcepts(card);
                break;
        }

        return data;
    }

    /**
     * Get multiple choice options from DOM
     */
    getMultipleChoiceOptions(card) {
        const options = [];
        const optionElements = card.querySelectorAll('.option-item');
        
        optionElements.forEach((elem, index) => {
            const text = elem.querySelector('.option-text')?.value || '';
            const isCorrect = elem.querySelector('.correct-answer')?.checked || false;
            options.push({ text, isCorrect });
        });
        
        return options;
    }

    /**
     * Get essay rubric from DOM
     */
    getEssayRubric(card) {
        const rubric = {};
        const weights = card.querySelectorAll('.rubric-weight');
        
        weights.forEach(weight => {
            const criterion = weight.dataset.criterion;
            rubric[criterion] = parseInt(weight.value) || 0;
        });
        
        return rubric;
    }

    /**
     * Get key concepts from DOM
     */
    getKeyConcepts(card) {
        const concepts = [];
        const inputs = card.querySelectorAll('.key-concept');
        
        inputs.forEach(input => {
            const value = input.value.trim();
            if (value) {
                concepts.push(value);
            }
        });
        
        return concepts;
    }

    /**
     * Clear all questions
     */
    clearAllQuestions() {
        this.container.innerHTML = '';
        this.showNoQuestionsMessage();
    }
}

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ExamBuilderView;
} else {
    window.ExamBuilderView = ExamBuilderView;
}
