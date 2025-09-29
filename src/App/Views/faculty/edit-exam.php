<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Exam - <?= htmlspecialchars($exam->getTitle()) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-gradient text-white py-8 mb-8 relative">
        <div class="container mx-auto px-6 relative z-10">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/exams" 
                       class="mr-6 p-2 rounded-full hover:bg-white/20 transition-all duration-300 group">
                        <i class="fas fa-arrow-left text-xl group-hover:transform group-hover:-translate-x-1 transition-transform"></i>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold mb-2 tracking-tight">
                            <i class="fas fa-edit mr-3 text-white/90"></i>
                            Edit Exam
                        </h1>
                        <p class="text-white/80 text-lg">Modify your exam settings and questions</p>
                    </div>
                </div>
                <div class="flex space-x-4">
                    <button id="previewBtn" class="bg-white/10 backdrop-blur-sm text-white px-6 py-3 rounded-xl hover:bg-white/20 transition-all duration-300 border border-white/20">
                        <i class="fas fa-eye mr-2"></i>Preview
                    </button>
                    <button id="saveBtn" class="btn-primary text-white px-8 py-3 rounded-xl font-semibold">
                        <i class="fas fa-save mr-2"></i>Update Exam
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
                <p class="text-gray-600 mt-2 ml-14">Update the basic parameters for your exam</p>
            </div>
            <div class="p-8">
                <form id="examSettingsForm">
                    <input type="hidden" id="examId" value="<?= $exam->getId() ?>">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Basic Information -->
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Exam Title *</label>
                            <input type="text" id="examTitle" name="title" required 
                                   value="<?= htmlspecialchars($exam->getTitle()) ?>"
                                   class="input-modern w-full px-4 py-4 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                                   placeholder="Enter exam title (e.g., Midterm Exam - Chapter 1-5)">
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Description</label>
                            <textarea id="examDescription" name="description" rows="4"
                                      class="input-modern w-full px-4 py-4 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                      placeholder="Brief description of the exam content and objectives"><?= htmlspecialchars($exam->getDescription() ?? '') ?></textarea>
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
                                                <?= $assignment->getSubjectId() == $exam->getSubjectId() ? 'selected' : '' ?>
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
                                <option value="quiz" <?= $exam->getExamType() == 'quiz' ? 'selected' : '' ?>>📝 Quiz</option>
                                <option value="midterm" <?= $exam->getExamType() == 'midterm' ? 'selected' : '' ?>>📚 Midterm Exam</option>
                                <option value="final" <?= $exam->getExamType() == 'final' ? 'selected' : '' ?>>🎓 Final Exam</option>
                                <option value="assignment" <?= $exam->getExamType() == 'assignment' ? 'selected' : '' ?>>📋 Assignment</option>
                                <option value="project" <?= $exam->getExamType() == 'project' ? 'selected' : '' ?>>🚀 Project</option>
                            </select>
                        </div>

                        <!-- Exam Settings -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-clock mr-1"></i>
                                Time Limit (minutes)
                            </label>
                            <input type="number" name="time_limit" id="time_limit" min="1" max="480" 
                                   value="<?= $exam->getTimeLimit() ?>"
                                   class="input-modern w-full px-4 py-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-calendar mr-1"></i>
                                Start Date & Time
                            </label>
                            <input type="datetime-local" name="start_date" id="start_date"
                                   value="<?= $exam->getStartDate() ? date('Y-m-d\TH:i', strtotime($exam->getStartDate())) : '' ?>"
                                   class="input-modern w-full px-4 py-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-calendar-times mr-1"></i>
                                End Date & Time
                            </label>
                            <input type="datetime-local" name="end_date" id="end_date"
                                   value="<?= $exam->getEndDate() ? date('Y-m-d\TH:i', strtotime($exam->getEndDate())) : '' ?>"
                                   class="input-modern w-full px-4 py-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" <?= $exam->getIsActive() ? 'checked' : '' ?> class="mr-2">
                            <label for="is_active" class="text-sm text-gray-700">
                                <i class="fas fa-toggle-on mr-1"></i>
                                Active (Visible to Students)
                            </label>
                        </div>

                        <!-- Instructions -->
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Instructions for Students</label>
                            <textarea id="instructions" name="instructions" rows="4"
                                      class="input-modern w-full px-4 py-4 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                      placeholder="Provide clear instructions for students taking this exam..."><?= htmlspecialchars($exam->getInstructions() ?? '') ?></textarea>
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
                                Questions (<span id="questionCount" class="text-blue-600"><?= count($questions ?? []) ?></span>)
                            </h2>
                            <p class="text-gray-600 text-sm">Total Points: <span id="totalPoints" class="font-semibold text-green-600"><?= array_sum(array_map(function($q) { return $q->getPoints(); }, $questions ?? [])) ?></span></p>
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
                                    <button class="dropdown-item question-type-btn w-full text-left px-4 py-4 rounded-xl flex items-center font-medium" data-type="short_answer">
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
            <div class="p-8">
                <div id="questionsContainer" class="space-y-4">
                    <!-- Existing questions will be loaded here -->
                    <?php if (empty($questions)): ?>
                        <div id="noQuestionsMessage" class="text-center py-12">
                            <i class="fas fa-question-circle text-6xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Questions Yet</h3>
                            <p class="text-gray-500 mb-4">Start building your exam by adding questions</p>
                            <button class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors" onclick="document.getElementById('addQuestionBtn').click()">
                                <i class="fas fa-plus mr-2"></i>Add Your First Question
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- Load existing questions -->
                        <script>
                            // Pre-load existing questions
                            window.existingQuestions = <?= json_encode(array_map(function($q) {
                                $questionData = [
                                    'id' => $q->getId(),
                                    'question_text' => $q->getQuestionText(),
                                    'question_type' => $q->getQuestionType(),
                                    'points' => $q->getPoints(),
                                    'order_index' => $q->getOrderIndex(),
                                    'options' => []
                                ];
                                
                                if ($q->getQuestionType() === 'multiple_choice') {
                                    $options = $q->getOptions() ?? [];
                                    foreach ($options as $option) {
                                        $questionData['options'][] = [
                                            'option_text' => $option->getOptionText(),
                                            'is_correct' => $option->getIsCorrect()
                                        ];
                                    }
                                }
                                
                                return $questionData;
                            }, $questions)) ?>;
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="messageContainer" class="fixed top-4 right-4 z-50"></div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script src="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/js/exam-builder.js"></script>
    <script>
        // Override save function for edit mode
        function saveExam() {
            const examId = document.getElementById('examId').value;
            const examData = collectExamData();
            
            console.log('Updating exam data:', examData);
            
            if (!examData.title || !examData.subject_id) {
                showMessage('Please fill in exam title and select a subject', 'error');
                return;
            }

            // Show loading
            const saveBtn = document.getElementById('saveBtn');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
            saveBtn.disabled = true;

            // Send to server
            fetch(`<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/exam/${examId}/update`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(examData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Exam updated successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = `<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/exam/${examId}`;
                    }, 2000);
                } else {
                    showMessage(data.message || 'Failed to update exam', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred while updating the exam', 'error');
            })
            .finally(() => {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            });
        }

        // Existing questions are loaded by the main exam-builder.js file
    </script>
</body>
</html>
