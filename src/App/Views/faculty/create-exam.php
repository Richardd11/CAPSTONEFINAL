<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Exam - Faculty Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/modern-animations.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        html { font-size: 16px; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 0.95rem;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .question-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        
        .question-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
            border-color: rgba(59, 130, 246, 0.3);
        }
        
        .drag-handle {
            cursor: grab;
            transition: all 0.2s ease;
        }
        
        .drag-handle:hover {
            color: #3b82f6;
            transform: scale(1.1);
        }
        
        .drag-handle:active {
            cursor: grabbing;
        }
        
        .sortable-ghost {
            opacity: 0.4;
            transform: scale(0.98);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover:before {
            left: 100%;
        }
        
        .input-modern {
            transition: all 0.3s ease;
            border: 2px solid #e2e8f0;
            background: rgba(255, 255, 255, 0.9);
        }
        
        .input-modern:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: rgba(255, 255, 255, 1);
        }
        
        .header-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        
        .header-gradient:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .floating-label {
            position: relative;
        }
        
        .floating-label input:focus + label,
        .floating-label input:not(:placeholder-shown) + label {
            transform: translateY(-1.5rem) scale(0.85);
            color: #667eea;
        }
        
        .floating-label label {
            position: absolute;
            left: 0.75rem;
            top: 0.75rem;
            transition: all 0.3s ease;
            pointer-events: none;
            color: #64748b;
        }
        
        .type-badge {
            background: linear-gradient(135deg, var(--badge-from), var(--badge-to));
            color: white;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .type-badge.multiple-choice {
            --badge-from: #3b82f6;
            --badge-to: #1d4ed8;
        }
        
        .type-badge.true-false {
            --badge-from: #10b981;
            --badge-to: #047857;
        }
        
        .type-badge.short_answer {
            --badge-from: #f59e0b;
            --badge-to: #d97706;
        }
        
        .type-badge.essay {
            --badge-from: #8b5cf6;
            --badge-to: #7c3aed;
        }
        
        .question-number {
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            border: 2px solid #cbd5e1;
            color: #475569;
            font-weight: 700;
            min-width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-slide-up {
            animation: slideInUp 0.5s ease-out;
        }
        
        @keyframes bounce-slow {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }
        
        .animate-bounce-slow {
            animation: bounce-slow 2s ease-in-out infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
            }
        }
        
        .option-item.border-emerald-400 {
            animation: pulse-glow 2s ease-in-out infinite;
        }
        
        .dropdown-menu {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .dropdown-item {
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: translateX(4px);
        }
        
        /* Shake animation for delete */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <div class="header-gradient text-white py-8 mb-8 relative">
        <div class="container mx-auto px-6 relative z-10">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <a href="/faculty/dashboard" 
                       class="mr-6 p-2 rounded-full hover:bg-white/20 transition-all duration-300 group">
                        <i class="fas fa-arrow-left text-xl group-hover:transform group-hover:-translate-x-1 transition-transform"></i>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold mb-2 tracking-tight">
                            <i class="fas fa-graduation-cap mr-3 text-white/90"></i>
                            Create New Exam
                        </h1>
                        <p class="text-white/80 text-lg">Design your exam with modern, intuitive interface</p>
                    </div>
                </div>
                <div class="flex space-x-4">
                    <button id="previewBtn" class="bg-white/10 backdrop-blur-sm text-white px-6 py-3 rounded-xl hover:bg-white/20 transition-all duration-300 border border-white/20">
                        <i class="fas fa-eye mr-2"></i>Preview
                    </button>
                    <button id="saveBtn" class="btn-primary text-white px-8 py-3 rounded-xl font-semibold">
                        <i class="fas fa-save mr-2"></i>Save Exam
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-6 max-w-5xl">
        <!-- Exam Settings Form -->
        <div id="examSettingsCard" class="glass-card rounded-2xl mb-8 animate-slide-up">
            <div class="p-8 border-b border-gray-200/50">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-cog text-white"></i>
                    </div>
                    Exam Configuration
                </h2>
                <p class="text-gray-600 mt-2 ml-14">Set up the basic parameters for your exam</p>
            </div>
            <div class="p-8">
                <form id="examSettingsForm">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Basic Information -->
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Exam Title *</label>
                            <input type="text" id="examTitle" name="title" required 
                                   class="input-modern w-full px-4 py-4 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                                   placeholder="Enter exam title (e.g., Midterm Exam - Chapter 1-5)">
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Description</label>
                            <textarea id="examDescription" name="description" rows="4"
                                      class="input-modern w-full px-4 py-4 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                      placeholder="Brief description of the exam content and objectives"></textarea>
                        </div>

                        <!-- Subject and Class Information -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Subject *</label>
                            <select id="subjectId" name="subject_id" required 
                                    class="input-modern w-full px-4 py-4 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base">
                                <option value="">Select Subject</option>
                                <?php if (!empty($assignments)): ?>
                                    <?php foreach ($assignments as $assignment): ?>
                                        <option value="<?= $assignment->getSubjectId() ?>" 
                                                data-year="<?= htmlspecialchars($assignment->getYearLevel()) ?>"
                                                data-section="<?= htmlspecialchars($assignment->getSection()) ?>"
                                                data-academic-year="<?= htmlspecialchars($assignment->getAcademicYear()) ?>"
                                                data-semester="<?= htmlspecialchars($assignment->getSemester()) ?>">
                                            <?= htmlspecialchars($assignment->toArray()['subject_code'] ?? '') ?> - 
                                            <?= htmlspecialchars($assignment->toArray()['subject_name'] ?? '') ?>
                                            (<?= htmlspecialchars($assignment->getYearLevel()) ?> - Section <?= htmlspecialchars($assignment->getSection()) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Exam Type *</label>
                            <select id="examType" name="exam_type" required 
                                    class="input-modern w-full px-4 py-4 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base">
                                <option value="quiz">📝 Quiz</option>
                                <option value="midterm">📚 Midterm Exam</option>
                                <option value="final">🎓 Final Exam</option>
                                <option value="assignment">📋 Assignment</option>
                                <option value="project">🚀 Project</option>
                            </select>
                        </div>

                        <!-- Exam Settings -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-clock mr-1"></i>
                                Time Limit (minutes)
                            </label>
                            <input type="number" name="time_limit" id="time_limit" min="1" max="480" value="60"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar mr-1"></i>
                                Start Date & Time
                            </label>
                            <input type="datetime-local" name="start_date" id="start_date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-times mr-1"></i>
                                End Date & Time
                            </label>
                            <input type="datetime-local" name="end_date" id="end_date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" checked class="mr-2">
                            <label for="is_active" class="text-sm text-gray-700">
                                <i class="fas fa-toggle-on mr-1"></i>
                                Active (Visible to Students)
                            </label>
                        </div>

                        <!-- Instructions -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Instructions for Students</label>
                            <textarea id="instructions" name="instructions" rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Provide clear instructions for students taking this exam..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Questions Section -->
        <div class="glass-card rounded-2xl mb-8 animate-slide-up">
            <div class="p-8 border-b border-gray-200/50">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-teal-600 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-question-circle text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">
                                Questions (<span id="questionCount" class="text-blue-600">0</span>)
                            </h2>
                            <p class="text-gray-600 text-sm">Total Points: <span id="totalPoints" class="font-semibold text-green-600">0</span></p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <div class="relative">
                            <button id="addQuestionBtn" class="btn-primary text-white px-6 py-3 rounded-xl font-semibold flex items-center shadow-lg">
                                <i class="fas fa-plus mr-2"></i>Add Question
                            </button>
                            <div id="questionTypeMenu" class="dropdown-menu hidden absolute right-0 mt-3 w-80 rounded-2xl z-20">
                                <div class="p-3">
                                    <button class="dropdown-item question-type-btn w-full text-left px-4 py-4 rounded-xl flex items-center font-medium" data-type="multiple_choice">
                                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                                            <i class="fas fa-list-ul text-blue-600"></i>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-800">Multiple Choice</div>
                                            <div class="text-sm text-gray-500">Single correct answer from options</div>
                                        </div>
                                    </button>
                                    <button class="dropdown-item question-type-btn w-full text-left px-4 py-4 rounded-xl flex items-center font-medium" data-type="true_false">
                                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center mr-4">
                                            <i class="fas fa-check-circle text-green-600"></i>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-800">True/False</div>
                                            <div class="text-sm text-gray-500">Binary choice question</div>
                                        </div>
                                    </button>
                                    <button class="dropdown-item question-type-btn w-full text-left px-4 py-4 rounded-xl flex items-center font-medium" data-type="enumeration">
                                        <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center mr-4">
                                            <i class="fas fa-list-ol text-orange-600"></i>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-800">Enumeration</div>
                                            <div class="text-sm text-gray-500">List multiple items or answers</div>
                                        </div>
                                    </button>
                                    <button class="dropdown-item question-type-btn w-full text-left px-4 py-4 rounded-xl flex items-center font-medium" data-type="essay">
                                        <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
                                            <i class="fas fa-file-alt text-purple-600"></i>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-800">Essay</div>
                                            <div class="text-sm text-gray-500">Long-form written response</div>
                                        </div>
                                    </button>
                                </div>
                                
                                <!-- Quantity Selection Panel -->
                                <div id="quantityPanel" class="hidden border-t border-gray-200 p-4">
                                    <div class="mb-3">
                                        <div class="flex items-center mb-2">
                                            <i id="selectedTypeIcon" class="fas fa-list-ul mr-2 text-blue-600"></i>
                                            <span id="selectedTypeName" class="font-medium">Multiple Choice</span>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-3">How many questions do you want to add?</p>
                                    </div>
                                    
                                    <div class="flex items-center space-x-3">
                                        <input type="number" id="questionQuantity" min="1" max="50" value="1" 
                                               class="w-20 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <span class="text-sm text-gray-600">questions</span>
                                    </div>
                                    
                                    <div class="flex justify-end space-x-2 mt-4">
                                        <button id="cancelQuantity" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800">
                                            Cancel
                                        </button>
                                        <button id="confirmQuantity" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                                            Add Questions
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div id="questionsContainer" class="space-y-4">
                    <!-- Questions will be dynamically added here -->
                    <div id="noQuestionsMessage" class="text-center py-12">
                        <i class="fas fa-question-circle text-6xl text-gray-400 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Questions Yet</h3>
                        <p class="text-gray-500 mb-4">Start building your exam by adding questions</p>
                        <button class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors" onclick="document.getElementById('addQuestionBtn').click()">
                            <i class="fas fa-plus mr-2"></i>Add Your First Question
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Modal Container (handled by ModernModalService) -->

    <!-- Delete Question Modal -->
    <div id="deleteQuestionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="deleteModalContent">
            <div class="text-center">
                <!-- Icon -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                
                <!-- Title -->
                <h3 class="text-xl font-bold text-gray-900 mb-4">Delete Question</h3>
                
                <!-- Message -->
                <p class="text-gray-600 mb-2">Are you sure you want to delete this question?</p>
                <p class="text-sm text-gray-500 mb-6">
                    <span id="questionToDeleteText" class="font-medium">Question content will be shown here</span>
                </p>
                <p class="text-xs text-red-500 mb-6">This action cannot be undone.</p>
                
                <!-- Buttons -->
                <div class="flex space-x-3 justify-center">
                    <button id="cancelDeleteBtn" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors font-medium">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button id="confirmDeleteBtn" class="px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium">
                        <i class="fas fa-trash mr-2"></i>Delete Question
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    
    <!-- MVC Structure - Load in correct order -->
    <script src="/js/core/ApiClient.js"></script>
    <script src="/js/models/Question.js"></script>
    <script src="/js/models/Exam.js"></script>
    <script src="/js/utils/TemplateEngine.js"></script>
    <script src="/js/utils/ModernModalService.js"></script>
    <script src="/js/views/ExamBuilderView.js"></script>
    <script src="/js/services/ExamBuilderService.js"></script>
    <script src="/js/controllers/ExamBuilderController.js"></script>
    <script src="/js/exam-builder-mvc.js"></script>
</body>
</html>
