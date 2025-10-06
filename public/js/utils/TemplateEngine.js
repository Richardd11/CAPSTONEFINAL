/**
 * TemplateEngine - Generates HTML templates for questions
 */
class TemplateEngine {
    constructor() {
        this.questionNumber = 1;
    }

    renderQuestion(question) {
        const templates = {
            'multiple_choice': this.renderMultipleChoice.bind(this),
            'true_false': this.renderTrueFalse.bind(this),
            'enumeration': this.renderEnumeration.bind(this),
            'essay': this.renderEssay.bind(this)
        };
        const renderFunc = templates[question.type];
        if (!renderFunc) {
            console.error(`Unknown question type: ${question.type}`);
            return '';
        }
        return renderFunc(question);
    }

    renderMultipleChoice(question) {
        const optionsHTML = question.options.map((option, index) => 
            this.renderOption(question.id, option, index)
        ).join('');
        return `
            <div class="question-card bg-white rounded-xl shadow-lg p-6 mb-6 border-l-4 border-blue-500" 
                 data-question-id="${question.id}" 
                 data-question-type="multiple_choice">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="question-number text-lg font-bold text-gray-800">
                        <i class="fas fa-list-ul text-blue-600 mr-2"></i>
                        Question ${this.questionNumber++}
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Points:</span>
                        <input type="number" 
                               class="question-points w-16 px-2 py-1 border border-gray-300 rounded-md text-center" 
                               value="${question.points}" 
                               min="1" 
                               data-question-id="${question.id}">
                        <button class="delete-question text-gray-400 hover:text-red-600" 
                                data-question-id="${question.id}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Question Text</label>
                    <textarea class="question-text w-full px-4 py-3 border border-gray-300 rounded-lg resize-y min-h-[100px]" 
                              data-question-id="${question.id}">${question.text}</textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Answer Options</label>
                    <div class="options-container space-y-3">
                        ${optionsHTML}
                    </div>
                    <button class="add-option mt-3 text-blue-600 text-sm font-medium" 
                            data-question-id="${question.id}">
                        <i class="fas fa-plus mr-1"></i>Add Option
                    </button>
                </div>
            </div>
        `;
    }

    renderOption(questionId, option, index) {
        const letters = ['A','B','C','D','E','F','G','H'];
        const letter = letters[index] || String.fromCharCode(65 + index);
        const isCorrect = option.isCorrect || false;
        
        return `
            <div class="option-item group cursor-pointer" 
                 data-question-id="${questionId}" 
                 data-option-index="${index}">
                <div class="flex items-start gap-3 ${isCorrect ? 'bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 shadow-md ring-2 ring-blue-200 ring-opacity-50' : 'bg-white hover:bg-gray-50 border-2 border-gray-200 hover:border-blue-300'} rounded-xl p-4 transition-all duration-300 ${isCorrect ? '' : 'hover:shadow-md'} transform ${isCorrect ? '' : 'hover:scale-[1.01]'}">
                    <div class="relative">
                        <div class="w-8 h-8 ${isCorrect ? 'bg-blue-600' : 'bg-gradient-to-br from-gray-500 to-gray-600'} text-white rounded-full flex items-center justify-center text-sm font-bold shadow-md transition-all duration-300">
                            ${letter}
                        </div>
                        ${isCorrect ? '<div class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full flex items-center justify-center"><i class="fas fa-check text-white" style="font-size: 6px;"></i></div>' : ''}
                        <input type="radio" 
                               name="correct_${questionId}"
                               class="correct-answer opacity-0 absolute inset-0" 
                               ${isCorrect ? 'checked' : ''} 
                               data-question-id="${questionId}" 
                               data-option-index="${index}">
                    </div>
                    <div class="flex-1">
                        <input type="text" 
                               class="option-text w-full bg-transparent border-none focus:outline-none text-sm ${isCorrect ? 'text-blue-900 font-medium' : 'text-gray-800'} placeholder-gray-400 py-1" 
                               placeholder="Enter option text..."
                               value="${this.escapeHtml(option.text)}" 
                               data-question-id="${questionId}" 
                               data-option-index="${index}">
                        ${isCorrect ? '<div class="mt-1 text-xs text-blue-700 font-medium flex items-center"><i class="fas fa-star mr-1"></i>Correct Answer</div>' : ''}
                    </div>
                    ${index > 3 ? `
                        <button class="remove-option text-gray-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition-colors" 
                                data-question-id="${questionId}" 
                                data-option-index="${index}">
                            <i class="fas fa-times"></i>
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    }

    renderTrueFalse(question) {
        const isTrue = question.correctAnswer === 'true';
        const isFalse = question.correctAnswer === 'false';
        
        return `
            <div class="question-card bg-white rounded-xl shadow-lg p-6 mb-6 border-l-4 border-green-500" 
                 data-question-id="${question.id}" 
                 data-question-type="true_false">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="question-number text-lg font-bold text-gray-800">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        Question ${this.questionNumber++}
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Points:</span>
                        <input type="number" 
                               class="question-points w-16 px-2 py-1 border border-gray-300 rounded-md text-center" 
                               value="${question.points}" 
                               min="1" 
                               data-question-id="${question.id}">
                        <button class="delete-question text-gray-400 hover:text-red-600" 
                                data-question-id="${question.id}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Question Text</label>
                    <textarea class="question-text w-full px-4 py-3 border border-gray-300 rounded-lg resize-y min-h-[100px]" 
                              data-question-id="${question.id}">${question.text}</textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Select Correct Answer</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="group cursor-pointer relative">
                            <input type="radio" 
                                   name="truefalse_${question.id}" 
                                   class="true-false-answer hidden" 
                                   value="true" 
                                   ${isTrue ? 'checked' : ''} 
                                   data-question-id="${question.id}">
                            <div class="flex items-center p-4 ${isTrue ? 'bg-gradient-to-r from-green-50 via-emerald-50 to-teal-50 border-2 border-green-500 shadow-lg ring-4 ring-green-200 ring-opacity-50' : 'bg-white border-2 border-gray-200 hover:border-green-300 hover:bg-green-50'} rounded-xl transition-all duration-200 transform ${isTrue ? 'scale-[1.02]' : 'hover:scale-[1.01]'}">
                                <div class="relative mr-4">
                                    <div class="w-6 h-6 ${isTrue ? 'bg-green-500 border-green-500' : 'bg-white border-gray-300 group-hover:border-green-400'} border-2 rounded-full flex items-center justify-center transition-all">
                                        ${isTrue ? '<div class="w-3 h-3 bg-white rounded-full"></div>' : ''}
                                    </div>
                                </div>
                                <div class="w-10 h-10 ${isTrue ? 'bg-green-600' : 'bg-green-100 group-hover:bg-green-200'} rounded-lg flex items-center justify-center mr-3 transition-colors">
                                    <i class="fas fa-check ${isTrue ? 'text-white' : 'text-green-600'} text-lg"></i>
                                </div>
                                <div class="flex items-center flex-1">
                                    <div>
                                        <div class="font-semibold ${isTrue ? 'text-green-800' : 'text-gray-800 group-hover:text-green-800'} transition-colors">
                                            True
                                        </div>
                                        <div class="text-xs ${isTrue ? 'text-green-600' : 'text-gray-500'} transition-colors">
                                            Statement is correct
                                        </div>
                                    </div>
                                </div>
                                ${isTrue ? '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-600 text-white shadow-sm ml-2"><i class="fas fa-check-circle mr-1"></i>Correct Answer</span>' : ''}
                            </div>
                            ${isTrue ? '<div class="absolute top-2 right-2 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center animate-pulse shadow-lg"><i class="fas fa-check text-white text-xs"></i></div>' : ''}
                        </label>
                        
                        <label class="group cursor-pointer relative">
                            <input type="radio" 
                                   name="truefalse_${question.id}" 
                                   class="true-false-answer hidden" 
                                   value="false" 
                                   ${isFalse ? 'checked' : ''} 
                                   data-question-id="${question.id}">
                            <div class="flex items-center p-4 ${isFalse ? 'bg-gradient-to-r from-red-50 via-rose-50 to-pink-50 border-2 border-red-500 shadow-lg ring-4 ring-red-200 ring-opacity-50' : 'bg-white border-2 border-gray-200 hover:border-red-300 hover:bg-red-50'} rounded-xl transition-all duration-200 transform ${isFalse ? 'scale-[1.02]' : 'hover:scale-[1.01]'}">
                                <div class="relative mr-4">
                                    <div class="w-6 h-6 ${isFalse ? 'bg-red-500 border-red-500' : 'bg-white border-gray-300 group-hover:border-red-400'} border-2 rounded-full flex items-center justify-center transition-all">
                                        ${isFalse ? '<div class="w-3 h-3 bg-white rounded-full"></div>' : ''}
                                    </div>
                                </div>
                                <div class="w-10 h-10 ${isFalse ? 'bg-red-600' : 'bg-red-100 group-hover:bg-red-200'} rounded-lg flex items-center justify-center mr-3 transition-colors">
                                    <i class="fas fa-times ${isFalse ? 'text-white' : 'text-red-600'} text-lg"></i>
                                </div>
                                <div class="flex items-center flex-1">
                                    <div>
                                        <div class="font-semibold ${isFalse ? 'text-red-800' : 'text-gray-800 group-hover:text-red-800'} transition-colors">
                                            False
                                        </div>
                                        <div class="text-xs ${isFalse ? 'text-red-600' : 'text-gray-500'} transition-colors">
                                            Statement is incorrect
                                        </div>
                                    </div>
                                </div>
                                ${isFalse ? '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-600 text-white shadow-sm ml-2"><i class="fas fa-check-circle mr-1"></i>Correct Answer</span>' : ''}
                            </div>
                            ${isFalse ? '<div class="absolute top-2 right-2 w-6 h-6 bg-red-500 rounded-full flex items-center justify-center animate-pulse shadow-lg"><i class="fas fa-check text-white text-xs"></i></div>' : ''}
                        </label>
                    </div>
                </div>
            </div>
        `;
    }

    renderEnumeration(question) {
        const answers = Array.isArray(question.correctAnswer) ? question.correctAnswer.join('\n') : '';
        const expectedCount = question.metadata?.expectedCount || 3;
        return `
            <div class="question-card bg-white rounded-xl shadow-lg p-6 mb-6 border-l-4 border-orange-500" 
                 data-question-id="${question.id}" 
                 data-question-type="enumeration">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="question-number text-lg font-bold text-gray-800">
                        <i class="fas fa-list-ol text-orange-600 mr-2"></i>
                        Question ${this.questionNumber++}
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Points:</span>
                        <input type="number" 
                               class="question-points w-16 px-2 py-1 border border-gray-300 rounded-md text-center" 
                               value="${question.points}" 
                               min="1" 
                               data-question-id="${question.id}">
                        <button class="delete-question text-gray-400 hover:text-red-600" 
                                data-question-id="${question.id}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Question Text</label>
                    <textarea class="question-text w-full px-4 py-3 border border-gray-300 rounded-lg resize-y min-h-[100px]" 
                              data-question-id="${question.id}">${question.text}</textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Expected Answers</label>
                    <input type="number" 
                           class="expected-count w-24 px-3 py-2 border border-gray-300 rounded-md" 
                           value="${expectedCount}" 
                           min="1" 
                           data-question-id="${question.id}">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Correct Answers (one per line)</label>
                    <textarea class="enumeration-answers w-full px-4 py-3 border border-orange-300 rounded-lg resize-y min-h-[120px]" 
                              data-question-id="${question.id}">${answers}</textarea>
                </div>
            </div>
        `;
    }

    renderEssay(question) {
        const rubric = question.metadata?.rubric || {content:40,organization:30,grammar:20,creativity:10};
        const keyConcepts = question.metadata?.keyConcepts || [''];
        const total = Object.values(rubric).reduce((sum,val) => sum + val, 0);
        
        const rubricHTML = Object.entries(rubric).map(([criterion,weight]) => `
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium capitalize">${criterion}</label>
                <div class="flex items-center space-x-2">
                    <input type="range" 
                           class="rubric-weight w-32" 
                           min="0" 
                           max="100" 
                           value="${weight}" 
                           data-question-id="${question.id}" 
                           data-criterion="${criterion}">
                    <span id="${criterion}-weight-${question.id}" class="text-sm font-semibold text-purple-600 w-12">
                        ${weight}%
                    </span>
                </div>
            </div>
        `).join('');
        
        const conceptsHTML = keyConcepts.map((concept,idx) => `
            <div class="flex items-center space-x-2">
                <input type="text" 
                       class="key-concept flex-1 px-3 py-2 border rounded-md" 
                       value="${this.escapeHtml(concept)}" 
                       data-question-id="${question.id}" 
                       data-concept-index="${idx}">
                <button class="remove-concept text-gray-400 hover:text-red-600" 
                        data-question-id="${question.id}" 
                        data-concept-index="${idx}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');
        
        return `
            <div class="question-card bg-white rounded-xl shadow-lg p-6 mb-6 border-l-4 border-purple-500" 
                 data-question-id="${question.id}" 
                 data-question-type="essay">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="question-number text-lg font-bold text-gray-800">
                        <i class="fas fa-file-alt text-purple-600 mr-2"></i>
                        Question ${this.questionNumber++}
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Points:</span>
                        <input type="number" 
                               class="question-points w-16 px-2 py-1 border border-gray-300 rounded-md text-center" 
                               value="${question.points}" 
                               min="1" 
                               data-question-id="${question.id}">
                        <button class="delete-question text-gray-400 hover:text-red-600" 
                                data-question-id="${question.id}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Question Text</label>
                    <textarea class="question-text w-full px-4 py-3 border border-gray-300 rounded-lg resize-y min-h-[100px]" 
                              data-question-id="${question.id}">${question.text}</textarea>
                </div>

                <div class="mb-4 bg-purple-50 rounded-lg p-4">
                    <div class="flex justify-between mb-3">
                        <label class="text-sm font-medium">Grading Rubric</label>
                        <span id="rubric-total-${question.id}" class="text-lg font-bold ${this.getRubricTotalClass(total)}">
                            ${total}%
                        </span>
                    </div>
                    <div class="space-y-3">
                        ${rubricHTML}
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Key Concepts</label>
                    <div class="key-concepts-container space-y-2">
                        ${conceptsHTML}
                    </div>
                    <button class="add-concept mt-2 text-purple-600 text-sm" 
                            data-question-id="${question.id}">
                        <i class="fas fa-plus mr-1"></i>Add Concept
                    </button>
                </div>
            </div>
        `;
    }

    renderDeleteModal() {
        return `<div id="deleteQuestionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4"><div id="deleteModalContent" class="bg-white rounded-2xl p-8 max-w-md w-full transform scale-95 opacity-0"><div class="text-center"><div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6"><i class="fas fa-exclamation-triangle text-red-600 text-3xl"></i></div><h3 class="text-2xl font-bold text-gray-900 mb-4">Delete Question?</h3><p class="text-gray-600 mb-2">Are you sure you want to delete:</p><p id="questionToDeleteText" class="text-sm text-gray-800 font-medium mb-6 p-3 bg-gray-50 rounded-lg italic"></p><p class="text-sm text-red-600 mb-6">This action cannot be undone.</p><div class="flex space-x-3"><button id="cancelDeleteBtn" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 font-semibold">Cancel</button><button id="confirmDeleteBtn" class="flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 font-semibold shadow-lg"><i class="fas fa-trash mr-2"></i>Delete</button></div></div></div></div>`;
    }

    getRubricTotalClass(total) {
        if (total === 100) return 'text-green-600';
        if (total > 100) return 'text-red-600';
        return 'text-orange-600';
    }

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    resetQuestionNumber() {
        this.questionNumber = 1;
    }

    getTypeLabel(type) {
        const labels = {
            'multiple_choice': 'Multiple Choice',
            'true_false': 'True/False',
            'enumeration': 'Enumeration',
            'essay': 'Essay'
        };
        return labels[type] || type;
    }
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = TemplateEngine;
} else {
    window.TemplateEngine = TemplateEngine;
}
