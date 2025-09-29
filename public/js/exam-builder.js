// Global variables
let questionCounter = 0;
let questionsData = [];

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeExamBuilder();
    
    // Load existing questions if in edit mode
    if (window.existingQuestions && window.existingQuestions.length > 0) {
        loadExistingQuestions();
    }
});

function initializeExamBuilder() {
    // Event listeners
    document.getElementById('addQuestionBtn').addEventListener('click', toggleQuestionTypeMenu);
    document.getElementById('saveBtn').addEventListener('click', saveExam);
    
    // Question type menu events
    document.querySelectorAll('.question-type-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const type = this.dataset.type;
            showQuantityPanel(type);
        });
    });

    // Quantity panel events
    document.getElementById('confirmQuantity').addEventListener('click', function() {
        const type = document.getElementById('selectedTypeName').dataset.type;
        const quantity = parseInt(document.getElementById('questionQuantity').value);
        addMultipleQuestions(type, quantity);
        hideQuestionTypeMenu();
    });

    document.getElementById('cancelQuantity').addEventListener('click', function() {
        hideQuantityPanel();
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#addQuestionBtn') && !e.target.closest('#questionTypeMenu')) {
            hideQuestionTypeMenu();
        }
    });

    // Subject selection handler
    document.getElementById('subjectId').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            // Auto-fill class information from data attributes
            console.log('Selected subject:', selectedOption.value);
        }
    });
}

function toggleQuestionTypeMenu() {
    const menu = document.getElementById('questionTypeMenu');
    menu.classList.toggle('hidden');
}

function hideQuestionTypeMenu() {
    document.getElementById('questionTypeMenu').classList.add('hidden');
    hideQuantityPanel();
}

function showQuantityPanel(type) {
    const typeInfo = {
        'multiple_choice': { icon: 'fas fa-list-ul', name: 'Multiple Choice', color: 'text-blue-600' },
        'true_false': { icon: 'fas fa-check-circle', name: 'True/False', color: 'text-green-600' },
        'enumeration': { icon: 'fas fa-list-ol', name: 'Enumeration', color: 'text-orange-600' },
        'essay': { icon: 'fas fa-file-alt', name: 'Essay', color: 'text-purple-600' }
    };

    const info = typeInfo[type];
    const iconElement = document.getElementById('selectedTypeIcon');
    const nameElement = document.getElementById('selectedTypeName');
    
    iconElement.className = `${info.icon} mr-2 ${info.color}`;
    nameElement.textContent = info.name;
    nameElement.dataset.type = type;
    
    // Reset quantity to 1
    document.getElementById('questionQuantity').value = 1;
    
    // Show quantity panel
    document.getElementById('quantityPanel').classList.remove('hidden');
}

function hideQuantityPanel() {
    document.getElementById('quantityPanel').classList.add('hidden');
}

function addMultipleQuestions(type, quantity) {
    for (let i = 0; i < quantity; i++) {
        addQuestion(type);
    }
    
    // Show success message
    showMessage(`Added ${quantity} ${getTypeLabel(type)} question${quantity > 1 ? 's' : ''}`, 'success');
}

function addQuestion(type) {
    questionCounter++;
    const questionId = `question_${questionCounter}`;
    
    let questionHtml = createQuestionHtml(type, questionId);

    // Hide no questions message
    document.getElementById('noQuestionsMessage').style.display = 'none';
    
    // Add question to container
    const questionsContainer = document.getElementById('questionsContainer');
    const questionDiv = document.createElement('div');
    questionDiv.className = 'question-card bg-white border border-gray-200 rounded-lg p-6 mb-4';
    questionDiv.dataset.questionId = questionId;
    questionDiv.dataset.questionType = type;
    questionDiv.innerHTML = questionHtml;
    
    questionsContainer.appendChild(questionDiv);
    
    // Add event listeners for this question
    addQuestionEventListeners(questionDiv);
    
    // Update counters
    updateQuestionNumbers();
    updateTotalPoints();
}

function createQuestionHtml(type, questionId) {
    const baseHeader = `
        <div class="flex justify-between items-start mb-4">
            <div class="flex items-center">
                <div class="drag-handle mr-3 text-gray-400 cursor-move">
                    <i class="fas fa-grip-vertical"></i>
                </div>
                <div>
                    <span class="question-number text-sm font-medium text-gray-600">Question 1</span>
                    <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">${getTypeLabel(type)}</span>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <input type="number" class="question-points w-16 px-2 py-1 border border-gray-300 rounded text-sm" value="${getDefaultPoints(type)}" min="0" max="100">
                <span class="text-sm text-gray-600">pts</span>
                <button class="delete-question text-gray-400 hover:text-red-600" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;

    const questionTextArea = `
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-3">
                <i class="fas fa-edit mr-2 text-indigo-600"></i>
                Question Text
                <span class="text-xs text-gray-500 ml-2">(Write a clear, specific question)</span>
            </label>
            <div class="relative">
                <textarea class="question-text w-full px-4 py-4 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gradient-to-br from-white to-gray-50 shadow-sm transition-all duration-200" 
                          placeholder="Type your question here... Be clear and specific to help students understand what you're asking." 
                          rows="3"></textarea>
                <div class="absolute bottom-2 right-2 text-xs text-gray-400">
                    <i class="fas fa-info-circle mr-1"></i>
                    <span class="char-count">0</span> characters
                </div>
            </div>
        </div>
    `;

    switch(type) {
        case 'multiple_choice':
            return baseHeader + questionTextArea + `
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <i class="fas fa-list-ul mr-2 text-blue-600"></i>
                        Answer Options
                        <span class="text-xs text-gray-500 ml-2">(Click the radio button to mark the correct answer)</span>
                    </label>
                    <div class="options-container space-y-3">
                        <div class="option-item bg-white border-2 border-gray-200 rounded-lg p-3 hover:border-blue-300 transition-all duration-200">
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center">
                                    <input type="radio" name="correct_answer_${questionId}" value="0" class="correct-answer text-green-600 focus:ring-green-500 w-4 h-4" checked>
                                    <span class="ml-2 text-xs font-medium text-green-600">CORRECT</span>
                                </div>
                                <input type="text" class="option-text flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter option A...">
                                <button class="remove-option text-gray-400 hover:text-red-600 p-1 rounded" title="Remove option">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                        <div class="option-item bg-white border-2 border-gray-200 rounded-lg p-3 hover:border-blue-300 transition-all duration-200">
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center">
                                    <input type="radio" name="correct_answer_${questionId}" value="1" class="correct-answer text-green-600 focus:ring-green-500 w-4 h-4">
                                    <span class="ml-2 text-xs font-medium text-gray-400">OPTION</span>
                                </div>
                                <input type="text" class="option-text flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter option B...">
                                <button class="remove-option text-gray-400 hover:text-red-600 p-1 rounded" title="Remove option">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        <button class="add-option bg-blue-50 text-blue-600 hover:bg-blue-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add Another Option
                        </button>
                        <p class="text-xs text-gray-500 italic">
                            <i class="fas fa-lightbulb mr-1"></i>
                            Select the radio button next to the correct answer
                        </p>
                    </div>
                </div>
            `;
        
        case 'true_false':
            return baseHeader + questionTextArea + `
                <div class="mb-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-4 border border-gray-200">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <i class="fas fa-check-circle mr-2 text-green-600"></i>
                        Select the Correct Answer
                    </label>
                    <div class="flex space-x-4">
                        <label class="flex items-center cursor-pointer bg-white border-2 border-gray-300 rounded-lg px-6 py-3 hover:border-green-500 hover:shadow-md transition-all duration-200 group">
                            <input type="radio" name="tf_answer_${questionId}" value="true" class="mr-3 correct-answer text-green-600 focus:ring-green-500" checked>
                            <span class="font-medium text-gray-700 group-hover:text-green-600">
                                <i class="fas fa-check text-green-600 mr-1"></i>
                                TRUE
                            </span>
                        </label>
                        <label class="flex items-center cursor-pointer bg-white border-2 border-gray-300 rounded-lg px-6 py-3 hover:border-red-500 hover:shadow-md transition-all duration-200 group">
                            <input type="radio" name="tf_answer_${questionId}" value="false" class="mr-3 correct-answer text-red-600 focus:ring-red-500">
                            <span class="font-medium text-gray-700 group-hover:text-red-600">
                                <i class="fas fa-times text-red-600 mr-1"></i>
                                FALSE
                            </span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-3 italic">
                        <i class="fas fa-info-circle mr-1"></i>
                        The selected option will be marked as the correct answer for this True/False question
                    </p>
                </div>
            `;
        
        case 'enumeration':
            return baseHeader + questionTextArea + `
                <div class="mb-4 bg-gradient-to-br from-orange-50 to-amber-50 rounded-xl p-6 border border-orange-200">
                    <label class="block text-sm font-semibold text-gray-700 mb-4">
                        <i class="fas fa-list-ol mr-2 text-orange-600"></i>
                        Expected Answers
                        <span class="text-xs text-gray-500 ml-2">(Students need to list these items)</span>
                    </label>
                    
                    <div class="enumeration-answers-container space-y-3">
                        <div class="enumeration-item bg-white border-2 border-orange-200 rounded-lg p-4 hover:border-orange-300 transition-all duration-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                    <span class="text-orange-600 font-bold text-sm">1</span>
                                </div>
                                <input type="text" class="enum-answer flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="Enter first expected answer...">
                                <button class="remove-enum-item text-gray-400 hover:text-red-600 p-1 rounded" title="Remove answer">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                        <div class="enumeration-item bg-white border-2 border-orange-200 rounded-lg p-4 hover:border-orange-300 transition-all duration-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                    <span class="text-orange-600 font-bold text-sm">2</span>
                                </div>
                                <input type="text" class="enum-answer flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="Enter second expected answer...">
                                <button class="remove-enum-item text-gray-400 hover:text-red-600 p-1 rounded" title="Remove answer">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 flex items-center justify-between">
                        <button class="add-enum-answer bg-orange-50 text-orange-600 hover:bg-orange-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors border border-orange-200">
                            <i class="fas fa-plus mr-2"></i>Add Another Answer
                        </button>
                        <div class="text-xs text-gray-500 italic">
                            <i class="fas fa-info-circle mr-1"></i>
                            Students can provide answers in any order
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 bg-orange-100 rounded-lg">
                        <h5 class="font-medium text-orange-800 mb-2">
                            <i class="fas fa-cog mr-1"></i>Scoring Options
                        </h5>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="enum_scoring_${questionId}" value="exact" class="mr-2 text-orange-600" checked>
                                <span class="text-sm text-orange-700">Exact match required (case-sensitive)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="enum_scoring_${questionId}" value="flexible" class="mr-2 text-orange-600">
                                <span class="text-sm text-orange-700">Flexible matching (case-insensitive, ignore spaces)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="enum_scoring_${questionId}" value="partial" class="mr-2 text-orange-600">
                                <span class="text-sm text-orange-700">Partial credit (points per correct item)</span>
                            </label>
                        </div>
                    </div>
                </div>
            `;
        
        case 'essay':
            return baseHeader + questionTextArea + `
                <div class="mb-4 bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-6 border border-purple-200">
                    <label class="block text-sm font-semibold text-gray-700 mb-4">
                        <i class="fas fa-file-alt mr-2 text-purple-600"></i>
                        Grading Guidelines
                        <span class="text-xs text-gray-500 ml-2">(Help students understand expectations)</span>
                    </label>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-purple-700 mb-2">Expected Response Length</label>
                            <div class="flex items-center space-x-4">
                                <select class="essay-length px-3 py-2 border border-purple-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                    <option value="short">Short (1-2 paragraphs)</option>
                                    <option value="medium" selected>Medium (3-5 paragraphs)</option>
                                    <option value="long">Long (5+ paragraphs)</option>
                                    <option value="custom">Custom word count</option>
                                </select>
                                <input type="number" class="word-count w-24 px-3 py-2 border border-purple-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500 hidden" placeholder="Words" min="50" max="5000">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-purple-700 mb-2">Grading Rubric</label>
                            <textarea class="grading-rubric w-full px-3 py-2 border border-purple-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500" 
                                      placeholder="Example:&#10;• Content & Understanding (40%)&#10;• Organization & Structure (30%)&#10;• Grammar & Style (20%)&#10;• Creativity & Insight (10%)" rows="4"></textarea>
                        </div>
                        
                        <div class="bg-purple-100 rounded-lg p-3">
                            <h5 class="font-medium text-purple-800 mb-2">
                                <i class="fas fa-lightbulb mr-1"></i>Grading Options
                            </h5>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="essay_grading_${questionId}" value="manual" class="mr-2 text-purple-600" checked>
                                    <span class="text-sm text-purple-700">Manual grading by instructor</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="essay_grading_${questionId}" value="peer" class="mr-2 text-purple-600">
                                    <span class="text-sm text-purple-700">Peer review + instructor final grade</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="essay_grading_${questionId}" value="rubric" class="mr-2 text-purple-600">
                                    <span class="text-sm text-purple-700">Detailed rubric scoring</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        
        default:
            return baseHeader + questionTextArea;
    }
}

function getTypeLabel(type) {
    const labels = {
        'multiple_choice': 'Multiple Choice',
        'true_false': 'True/False',
        'enumeration': 'Enumeration',
        'essay': 'Essay'
    };
    return labels[type] || type;
}

function getDefaultPoints(type) {
    const points = {
        'multiple_choice': 1,
        'true_false': 1,
        'enumeration': 5,
        'essay': 10
    };
    return points[type] || 1;
}

function addQuestionEventListeners(questionDiv) {
    // Delete question
    questionDiv.querySelector('.delete-question').addEventListener('click', function() {
        showDeleteQuestionModal(questionDiv);
    });

    // Points change
    questionDiv.querySelector('.question-points').addEventListener('input', updateTotalPoints);

    // Add option for multiple choice
    const addOptionBtn = questionDiv.querySelector('.add-option');
    if (addOptionBtn) {
        addOptionBtn.addEventListener('click', function() {
            addOptionToQuestion(questionDiv);
        });
    }

    // Remove option events
    questionDiv.querySelectorAll('.remove-option').forEach(btn => {
        btn.addEventListener('click', function() {
            const optionItem = btn.closest('.option-item');
            const optionsContainer = optionItem.parentElement;
            
            if (optionsContainer.children.length > 2) {
                optionItem.remove();
            }
        });
    });

    // Handle correct answer selection visual feedback
    questionDiv.querySelectorAll('.correct-answer').forEach(radio => {
        radio.addEventListener('change', function() {
            updateCorrectAnswerLabels(questionDiv);
        });
    });

    // Add character counter for question text
    const questionTextArea = questionDiv.querySelector('.question-text');
    const charCount = questionDiv.querySelector('.char-count');
    if (questionTextArea && charCount) {
        questionTextArea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }

    // Handle enumeration question events
    const addEnumBtn = questionDiv.querySelector('.add-enum-answer');
    if (addEnumBtn) {
        addEnumBtn.addEventListener('click', function() {
            addEnumerationAnswer(questionDiv);
        });
    }

    // Remove enumeration answer events
    questionDiv.querySelectorAll('.remove-enum-item').forEach(btn => {
        btn.addEventListener('click', function() {
            const enumItem = btn.closest('.enumeration-item');
            const container = enumItem.parentElement;
            
            if (container.children.length > 1) {
                enumItem.remove();
                updateEnumerationNumbers(questionDiv);
            }
        });
    });
}

function addOptionToQuestion(questionDiv) {
    const optionsContainer = questionDiv.querySelector('.options-container');
    const questionId = questionDiv.dataset.questionId;
    const optionIndex = optionsContainer.children.length;
    const optionLetter = String.fromCharCode(65 + optionIndex); // A, B, C, D...
    
    const optionDiv = document.createElement('div');
    optionDiv.className = 'option-item bg-white border-2 border-gray-200 rounded-lg p-3 hover:border-blue-300 transition-all duration-200';
    optionDiv.innerHTML = `
        <div class="flex items-center space-x-3">
            <div class="flex items-center">
                <input type="radio" name="correct_answer_${questionId}" value="${optionIndex}" class="correct-answer text-green-600 focus:ring-green-500 w-4 h-4">
                <span class="ml-2 text-xs font-medium text-gray-400">OPTION</span>
            </div>
            <input type="text" class="option-text flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter option ${optionLetter}...">
            <button class="remove-option text-gray-400 hover:text-red-600 p-1 rounded" title="Remove option">
                <i class="fas fa-trash text-sm"></i>
            </button>
        </div>
    `;
    
    optionsContainer.appendChild(optionDiv);
    
    // Add remove event listener
    optionDiv.querySelector('.remove-option').addEventListener('click', function() {
        const optionsContainer = optionDiv.parentElement;
        if (optionsContainer.children.length > 2) {
            optionDiv.remove();
        }
    });

    // Add correct answer change listener
    optionDiv.querySelector('.correct-answer').addEventListener('change', function() {
        updateCorrectAnswerLabels(questionDiv);
    });
}

function updateCorrectAnswerLabels(questionDiv) {
    const radioButtons = questionDiv.querySelectorAll('.correct-answer');
    
    radioButtons.forEach((radio, index) => {
        const label = radio.parentElement.querySelector('span');
        const optionItem = radio.closest('.option-item');
        
        if (radio.checked) {
            label.textContent = 'CORRECT';
            label.className = 'ml-2 text-xs font-medium text-green-600';
            optionItem.classList.remove('border-gray-200');
            optionItem.classList.add('border-green-400', 'bg-green-50');
        } else {
            label.textContent = 'OPTION';
            label.className = 'ml-2 text-xs font-medium text-gray-400';
            optionItem.classList.remove('border-green-400', 'bg-green-50');
            optionItem.classList.add('border-gray-200');
        }
    });
}

function addEnumerationAnswer(questionDiv) {
    const container = questionDiv.querySelector('.enumeration-answers-container');
    const answerCount = container.children.length + 1;
    
    const enumDiv = document.createElement('div');
    enumDiv.className = 'enumeration-item bg-white border-2 border-orange-200 rounded-lg p-4 hover:border-orange-300 transition-all duration-200';
    enumDiv.innerHTML = `
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                <span class="text-orange-600 font-bold text-sm">${answerCount}</span>
            </div>
            <input type="text" class="enum-answer flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="Enter expected answer...">
            <button class="remove-enum-item text-gray-400 hover:text-red-600 p-1 rounded" title="Remove answer">
                <i class="fas fa-trash text-sm"></i>
            </button>
        </div>
    `;
    
    container.appendChild(enumDiv);
    
    // Add remove event listener
    enumDiv.querySelector('.remove-enum-item').addEventListener('click', function() {
        const container = enumDiv.parentElement;
        if (container.children.length > 1) {
            enumDiv.remove();
            updateEnumerationNumbers(questionDiv);
        }
    });
}

function updateEnumerationNumbers(questionDiv) {
    const enumItems = questionDiv.querySelectorAll('.enumeration-item');
    enumItems.forEach((item, index) => {
        const numberSpan = item.querySelector('.w-8 span');
        if (numberSpan) {
            numberSpan.textContent = index + 1;
        }
    });
}

function updateQuestionNumbers() {
    const questions = document.querySelectorAll('.question-card');
    questions.forEach((question, index) => {
        const numberSpan = question.querySelector('.question-number');
        if (numberSpan) {
            numberSpan.textContent = `Question ${index + 1}`;
        }
    });
    
    document.getElementById('questionCount').textContent = questions.length;
}

function updateTotalPoints() {
    const pointInputs = document.querySelectorAll('.question-points');
    let total = 0;
    
    pointInputs.forEach(input => {
        total += parseInt(input.value) || 0;
    });
    
    document.getElementById('totalPoints').textContent = total;
}

function collectExamData() {
    const examData = {
        title: document.getElementById('examTitle').value,
        description: document.getElementById('examDescription').value,
        subject_id: document.getElementById('subjectId').value,
        exam_type: document.getElementById('examType').value,
        time_limit: document.getElementById('time_limit').value,
        start_date: document.getElementById('start_date').value,
        end_date: document.getElementById('end_date').value,
        is_active: document.getElementById('is_active').checked,
        instructions: document.getElementById('instructions').value,
        questions: []
    };

    // Get selected subject data
    const subjectSelect = document.getElementById('subjectId');
    const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
    if (selectedOption.value) {
        examData.year_level = selectedOption.dataset.year;
        examData.section = selectedOption.dataset.section;
        examData.academic_year = selectedOption.dataset.academicYear;
        examData.semester = selectedOption.dataset.semester;
    }

    // Collect questions data
    const questions = document.querySelectorAll('.question-card');
    questions.forEach((question, index) => {
        const questionData = {
            order_index: index,
            question_type: question.dataset.questionType,
            question_text: question.querySelector('.question-text').value,
            points: parseInt(question.querySelector('.question-points').value) || 0,
            options: []
        };

        // Collect options for multiple choice
        if (question.dataset.questionType === 'multiple_choice') {
            const options = question.querySelectorAll('.option-item');
            const correctAnswer = question.querySelector('input[type="radio"]:checked');
            
            options.forEach((option, optionIndex) => {
                const optionText = option.querySelector('.option-text').value;
                const isCorrect = correctAnswer && correctAnswer.value == optionIndex;
                
                questionData.options.push({
                    option_text: optionText,
                    is_correct: isCorrect,
                    order_index: optionIndex
                });
            });
        }

        // Handle True/False questions - create options array like multiple choice
        if (question.dataset.questionType === 'true_false') {
            const correctAnswer = question.querySelector('input[type="radio"]:checked');
            questionData.correct_answer = correctAnswer ? correctAnswer.value : 'true';
            
            // Also add options array for consistency (though backend will handle this)
            questionData.options = [
                {
                    option_text: 'true',
                    is_correct: questionData.correct_answer === 'true',
                    order_index: 0
                },
                {
                    option_text: 'false',
                    is_correct: questionData.correct_answer === 'false',
                    order_index: 1
                }
            ];
        }

        // Handle Enumeration questions (stored as short_answer in backend)
        if (question.dataset.questionType === 'enumeration') {
            // Change question type to short_answer for backend compatibility
            questionData.question_type = 'short_answer';
            
            const enumAnswers = question.querySelectorAll('.enum-answer');
            const scoringMethod = question.querySelector('input[name*="enum_scoring"]:checked');
            
            // Store enumeration answers as a JSON string in correct_answer field
            const enumerationData = {
                answers: [],
                scoring_method: scoringMethod ? scoringMethod.value : 'exact'
            };
            
            enumAnswers.forEach((input, index) => {
                if (input.value.trim()) {
                    enumerationData.answers.push({
                        answer_text: input.value.trim(),
                        order_index: index
                    });
                }
            });
            
            // Store as JSON string in correct_answer field
            questionData.correct_answer = JSON.stringify(enumerationData);
        }

        // Handle Essay questions
        if (question.dataset.questionType === 'essay') {
            const rubric = question.querySelector('.grading-rubric');
            const lengthSelect = question.querySelector('.essay-length');
            const wordCount = question.querySelector('.word-count');
            const gradingMethod = question.querySelector('input[name*="essay_grading"]:checked');
            
            questionData.grading_rubric = rubric ? rubric.value : '';
            questionData.expected_length = lengthSelect ? lengthSelect.value : 'medium';
            questionData.word_count = wordCount && !wordCount.classList.contains('hidden') ? wordCount.value : null;
            questionData.grading_method = gradingMethod ? gradingMethod.value : 'manual';
        }

        examData.questions.push(questionData);
    });

    return examData;
}

function saveExam() {
    // Get exam data
    const examData = collectExamData();
    
    console.log('Exam data to save:', examData); // Debug log
    
    if (!examData.title || !examData.subject_id) {
        showMessage('Please fill in exam title and select a subject', 'error');
        return;
    }
    
    if (examData.questions.length === 0) {
        showMessage('Please add at least one question', 'error');
        return;
    }

    // Validate questions
    const validationErrors = validateQuestions(examData.questions);
    if (validationErrors.length > 0) {
        showMessage('Please fix the following issues:\n• ' + validationErrors.join('\n• '), 'error');
        return;
    }

    // Show loading
    const saveBtn = document.getElementById('saveBtn');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    saveBtn.disabled = true;

    // Send to server
    fetch(window.location.pathname.replace('/create-exam', '/save-exam'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(examData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Exam saved successfully!', 'success');
            setTimeout(() => {
                window.location.href = window.location.pathname.replace('/create-exam', '/dashboard');
            }, 3000);
        } else {
            showMessage(data.message || 'Failed to save exam', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred while saving the exam', 'error');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

function validateQuestions(questions) {
    const errors = [];
    
    questions.forEach((question, index) => {
        const questionNum = index + 1;
        
        // Check if question text is provided
        if (!question.question_text || question.question_text.trim() === '') {
            errors.push(`Question ${questionNum}: Please enter question text`);
        }
        
        // Check if question text is just a single letter (common mistake)
        if (question.question_text && question.question_text.trim().length === 1) {
            errors.push(`Question ${questionNum}: Question text seems too short - "${question.question_text}"`);
        }
        
        // Validate multiple choice questions
        if (question.question_type === 'multiple_choice') {
            if (!question.options || question.options.length < 2) {
                errors.push(`Question ${questionNum}: Multiple choice questions need at least 2 options`);
            } else {
                // Check if at least one option is marked as correct
                const hasCorrectAnswer = question.options.some(option => option.is_correct);
                if (!hasCorrectAnswer) {
                    errors.push(`Question ${questionNum}: Please select which option is correct`);
                }
                
                // Check if all options have text
                question.options.forEach((option, optIndex) => {
                    if (!option.option_text || option.option_text.trim() === '') {
                        errors.push(`Question ${questionNum}: Option ${optIndex + 1} is empty`);
                    }
                });
            }
        }
        
        // Validate True/False questions
        if (question.question_type === 'true_false') {
            if (!question.correct_answer) {
                errors.push(`Question ${questionNum}: Please select True or False as the correct answer`);
            }
        }
        
        // Validate Enumeration questions (stored as short_answer with JSON data)
        if (question.question_type === 'short_answer' && question.correct_answer) {
            try {
                const enumerationData = JSON.parse(question.correct_answer);
                if (enumerationData.answers) {
                    // This is an enumeration question
                    if (!enumerationData.answers || enumerationData.answers.length === 0) {
                        errors.push(`Question ${questionNum}: Please provide at least one expected answer`);
                    } else {
                        // Check if all enumeration answers have text
                        enumerationData.answers.forEach((answer, answerIndex) => {
                            if (!answer.answer_text || answer.answer_text.trim() === '') {
                                errors.push(`Question ${questionNum}: Answer ${answerIndex + 1} is empty`);
                            }
                        });
                    }
                }
            } catch (e) {
                // Not JSON data, might be a regular short answer question
                // No additional validation needed
            }
        }
        
        // Check points
        if (!question.points || question.points <= 0) {
            errors.push(`Question ${questionNum}: Points must be greater than 0`);
        }
    });
    
    return errors;
}

function showMessage(message, type) {
    const messageContainer = document.getElementById('messageContainer');
    const messageDiv = document.createElement('div');
    
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    
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
    
    // Auto remove after 8 seconds for error messages (longer to read)
    const timeout = type === 'error' ? 8000 : 5000;
    setTimeout(() => {
        if (messageDiv.parentElement) {
            messageDiv.remove();
        }
    }, timeout);
}

function loadExistingQuestions() {
    console.log('Loading existing questions:', window.existingQuestions);
    
    // Hide no questions message
    const noQuestionsMsg = document.getElementById('noQuestionsMessage');
    if (noQuestionsMsg) {
        noQuestionsMsg.style.display = 'none';
    }
    
    window.existingQuestions.forEach(function(questionData, index) {
        questionCounter++;
        const questionId = `question_${questionCounter}`;
        
        // Create question HTML
        let questionHtml = createQuestionHtml(questionData.question_type, questionId);
        
        // Create question div
        const questionDiv = document.createElement('div');
        questionDiv.className = 'question-card bg-white border border-gray-200 rounded-xl p-6 mb-6';
        questionDiv.dataset.questionId = questionId;
        questionDiv.dataset.questionType = questionData.question_type;
        questionDiv.innerHTML = questionHtml;
        
        // Add to container
        document.getElementById('questionsContainer').appendChild(questionDiv);
        
        // Fill in the existing data
        const questionTextInput = questionDiv.querySelector('.question-text');
        const questionPointsInput = questionDiv.querySelector('.question-points');
        
        if (questionTextInput) questionTextInput.value = questionData.question_text || '';
        if (questionPointsInput) questionPointsInput.value = questionData.points || 1;
        
        // Handle options for multiple choice questions
        if (questionData.question_type === 'multiple_choice' && questionData.options && questionData.options.length > 0) {
            const optionsContainer = questionDiv.querySelector('.options-container');
            if (optionsContainer) {
                optionsContainer.innerHTML = ''; // Clear default options
                
                questionData.options.forEach(function(option, optionIndex) {
                    const optionDiv = document.createElement('div');
                    optionDiv.className = 'option-item flex items-center space-x-3 mb-3';
                    optionDiv.innerHTML = `
                        <input type="radio" name="correct_answer_${questionId}" value="${optionIndex}" 
                               class="correct-answer" ${option.is_correct ? 'checked' : ''}>
                        <input type="text" class="option-text flex-1 px-3 py-2 border border-gray-300 rounded-lg" 
                               placeholder="Option ${optionIndex + 1}" value="${option.option_text || ''}">
                        <button type="button" class="remove-option text-gray-400 hover:text-red-600 p-2" title="Remove option">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    optionsContainer.appendChild(optionDiv);
                    
                    // Add remove option event listener
                    optionDiv.querySelector('.remove-option').addEventListener('click', function() {
                        if (optionsContainer.children.length > 2) {
                            optionDiv.remove();
                            updateOptionNumbers(optionsContainer, questionId);
                        }
                    });
                });
            }
        }
        
        // Handle true/false questions
        if (questionData.question_type === 'true_false') {
            // Set correct answer if available
            if (questionData.correct_answer !== undefined) {
                const correctAnswerSelect = questionDiv.querySelector('.true-false-answer');
                if (correctAnswerSelect) {
                    correctAnswerSelect.value = questionData.correct_answer;
                }
            }
        }
        
        // Handle enumeration questions
        if (questionData.question_type === 'enumeration') {
            if (questionData.correct_answer) {
                const answersTextarea = questionDiv.querySelector('.enumeration-answers');
                if (answersTextarea) {
                    answersTextarea.value = questionData.correct_answer;
                }
            }
            if (questionData.expected_count) {
                const expectedCountInput = questionDiv.querySelector('.expected-count');
                if (expectedCountInput) {
                    expectedCountInput.value = questionData.expected_count;
                }
            }
        }
        
        // Add event listeners to the question
        addQuestionEventListeners(questionDiv);
    });
    
    // Update counters
    updateQuestionNumbers();
    updateTotalPoints();
    
    console.log('Loaded', window.existingQuestions.length, 'existing questions');
}

// Modal functions for question deletion
let questionToDelete = null;

function showDeleteQuestionModal(questionDiv) {
    questionToDelete = questionDiv;
    
    // Get question text to show in modal
    const questionTextElement = questionDiv.querySelector('.question-text');
    const questionText = questionTextElement ? questionTextElement.value.trim() : '';
    const questionNumber = questionDiv.querySelector('.question-number')?.textContent || 'this question';
    
    // Update modal content
    const questionToDeleteText = document.getElementById('questionToDeleteText');
    if (questionText && questionText.length > 0) {
        // Truncate long question text
        const displayText = questionText.length > 100 ? questionText.substring(0, 100) + '...' : questionText;
        questionToDeleteText.textContent = `"${displayText}"`;
    } else {
        questionToDeleteText.textContent = questionNumber;
    }
    
    // Show modal with animation
    const modal = document.getElementById('deleteQuestionModal');
    const modalContent = document.getElementById('deleteModalContent');
    
    modal.classList.remove('hidden');
    
    // Trigger animation
    setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
    
    // Add event listeners if not already added
    if (!modal.hasEventListener) {
        modal.hasEventListener = true;
        
        // Cancel button
        document.getElementById('cancelDeleteBtn').addEventListener('click', hideDeleteQuestionModal);
        
        // Confirm button
        document.getElementById('confirmDeleteBtn').addEventListener('click', confirmDeleteQuestion);
        
        // Click outside to close
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideDeleteQuestionModal();
            }
        });
        
        // Escape key to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                hideDeleteQuestionModal();
            }
        });
    }
}

function hideDeleteQuestionModal() {
    const modal = document.getElementById('deleteQuestionModal');
    const modalContent = document.getElementById('deleteModalContent');
    
    // Animate out
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    
    // Hide modal after animation
    setTimeout(() => {
        modal.classList.add('hidden');
        questionToDelete = null;
    }, 300);
}

function confirmDeleteQuestion() {
    if (questionToDelete) {
        // Remove the question
        questionToDelete.remove();
        
        // Update counters and UI
        updateQuestionNumbers();
        updateTotalPoints();
        
        // Show no questions message if no questions left
        const questionsContainer = document.getElementById('questionsContainer');
        if (questionsContainer.querySelectorAll('.question-card').length === 0) {
            document.getElementById('noQuestionsMessage').style.display = 'block';
        }
        
        // Show success message
        showMessage('Question deleted successfully', 'success');
        
        // Hide modal
        hideDeleteQuestionModal();
    }
}
