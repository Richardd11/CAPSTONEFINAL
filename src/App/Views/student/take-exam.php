<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Exam - <?= htmlspecialchars($exam['title'] ?? 'Exam') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@100;200;300;400;500;600;700;800;900&family=SF+Pro+Text:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ios-blue: #007AFF;
            --ios-blue-light: #5AC8FA;
            --ios-blue-dark: #0051D5;
            --ios-gray: #F2F2F7;
            --ios-gray-dark: #8E8E93;
        }

        body {
            font-family: 'SF Pro Text', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #F0F4FF 0%, #E8F2FF 50%, #F0F8FF 100%);
            min-height: 100vh;
        }

        .sf-pro-display {
            font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .ios-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 122, 255, 0.08), 0 2px 16px rgba(0, 0, 0, 0.04);
        }

        .question-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 16px;
            transition: all 0.3s ease;
            border-left: 4px solid var(--ios-blue);
        }

        .question-card.answered {
            border-left-color: #34C759;
            background: rgba(52, 199, 89, 0.05);
        }

        .timer-card {
            background: linear-gradient(135deg, #FF3B30 0%, #FF6B6B 100%);
            color: white;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(255, 59, 48, 0.3);
        }

        .timer-warning {
            background: linear-gradient(135deg, #FF9500 0%, #FF9F0A 100%);
        }

        .ios-button {
            background: linear-gradient(135deg, var(--ios-blue) 0%, var(--ios-blue-light) 100%);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(0, 122, 255, 0.3);
        }

        .ios-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 122, 255, 0.4);
        }

        .ios-button-secondary {
            background: rgba(142, 142, 147, 0.1);
            color: var(--ios-gray-dark);
            border: 1px solid rgba(142, 142, 147, 0.2);
        }

        .ios-button-success {
            background: linear-gradient(135deg, #34C759 0%, #30D158 100%);
            box-shadow: 0 4px 16px rgba(52, 199, 89, 0.3);
        }

        .option-item {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(142, 142, 147, 0.2);
            border-radius: 12px;
            padding: 16px;
            margin: 8px 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .option-item:hover {
            border-color: var(--ios-blue-light);
            background: rgba(0, 122, 255, 0.05);
        }

        .option-item.selected {
            border-color: var(--ios-blue);
            background: rgba(0, 122, 255, 0.1);
            box-shadow: 0 4px 16px rgba(0, 122, 255, 0.2);
        }

        .progress-bar {
            background: rgba(142, 142, 147, 0.2);
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
        }

        .progress-fill {
            background: linear-gradient(90deg, var(--ios-blue) 0%, var(--ios-blue-light) 100%);
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        .navigation-dots {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin: 20px 0;
        }

        .nav-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(142, 142, 147, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .nav-dot.current {
            background: var(--ios-blue);
            transform: scale(1.2);
        }

        .nav-dot.answered {
            background: #34C759;
        }

        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Sticky Header with Timer and Progress -->
    <div class="sticky-header py-4">
        <div class="container mx-auto px-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="sf-pro-display text-xl font-bold text-gray-800">
                        <?= htmlspecialchars($exam['title'] ?? 'Exam') ?>
                    </h1>
                    <p class="text-gray-600"><?= htmlspecialchars($exam['subject_name'] ?? 'Subject') ?></p>
                </div>
                
                <div class="flex items-center space-x-6">
                    <!-- Progress -->
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-1">Progress</p>
                        <div class="progress-bar w-32">
                            <div class="progress-fill" id="progressBar" style="width: 0%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            <span id="currentQuestion">1</span> of <span id="totalQuestions"><?= count($questions) ?></span>
                        </p>
                    </div>
                    
                    <!-- Timer -->
                    <div class="timer-card" id="timerCard">
                        <div class="sf-pro-display text-2xl font-bold" id="timerDisplay">
                            <?= gmdate('H:i:s', $timeRemaining) ?>
                        </div>
                        <p class="text-sm opacity-80">Time Remaining</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-8 py-8 max-w-4xl">
        <form id="examForm" method="POST" action="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/student/exam/submit">
            <input type="hidden" name="attempt_id" value="<?= $attemptId ?>">
            
            <!-- Questions -->
            <div id="questionsContainer">
                <?php if (empty($questions)): ?>
                    <div class="question-card p-8 mb-8 text-center">
                        <i class="fas fa-exclamation-triangle text-6xl text-yellow-500 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Questions Available</h3>
                        <p class="text-gray-500">This exam doesn't have any questions yet.</p>
                        <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/student/dashboard" 
                           class="ios-button text-white px-6 py-3 rounded-xl font-semibold mt-4 inline-block">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Dashboard
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($questions as $index => $question): ?>
                    <div class="question-card p-8 mb-8 question-slide" 
                         data-question-id="<?= $question->getId() ?>"
                         data-question-index="<?= $index ?>"
                         style="<?= $index === 0 ? '' : 'display: none;' ?>">
                        
                        <div class="flex justify-between items-start mb-6">
                            <div class="flex-1">
                                <div class="flex items-center mb-4">
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold mr-3">
                                        Question <?= $index + 1 ?>
                                    </span>
                                    <span class="text-gray-500 text-sm">
                                        <?= $question->getPoints() ?> point<?= $question->getPoints() !== 1 ? 's' : '' ?>
                                    </span>
                                </div>
                                
                                <h3 class="sf-pro-display text-xl font-semibold text-gray-800 mb-6 leading-relaxed">
                                    <?= nl2br(htmlspecialchars($question->getQuestionText())) ?>
                                </h3>
                            </div>
                        </div>

                        <!-- Answer Options -->
                        <div class="answer-section">
                            <?php if ($question->getQuestionType() === 'multiple_choice'): ?>
                                <?php $options = $question->getOptions(); ?>
                                <?php if (!empty($options)): ?>
                                    <div class="space-y-3">
                                        <?php foreach ($options as $optionIndex => $option): ?>
                                            <label class="option-item block cursor-pointer">
                                                <div class="flex items-center">
                                                    <input type="radio" 
                                                           name="answers[<?= $question->getId() ?>]" 
                                                           value="<?= $optionIndex ?>"
                                                           class="hidden option-radio"
                                                           onchange="selectOption(this)">
                                                    <div class="flex items-center">
                                                        <div class="w-6 h-6 border-2 border-gray-300 rounded-full mr-4 flex items-center justify-center radio-circle">
                                                            <div class="w-3 h-3 bg-blue-500 rounded-full hidden radio-dot"></div>
                                                        </div>
                                                        <span class="text-gray-800 font-medium">
                                                            <?= htmlspecialchars($option['option_text']) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                            <?php elseif ($question->getQuestionType() === 'true_false'): ?>
                                <div class="space-y-3">
                                    <label class="option-item block cursor-pointer">
                                        <div class="flex items-center">
                                            <input type="radio" 
                                                   name="answers[<?= $question->getId() ?>]" 
                                                   value="true"
                                                   class="hidden option-radio"
                                                   onchange="selectOption(this)">
                                            <div class="flex items-center">
                                                <div class="w-6 h-6 border-2 border-gray-300 rounded-full mr-4 flex items-center justify-center radio-circle">
                                                    <div class="w-3 h-3 bg-blue-500 rounded-full hidden radio-dot"></div>
                                                </div>
                                                <span class="text-gray-800 font-medium">True</span>
                                            </div>
                                        </div>
                                    </label>
                                    <label class="option-item block cursor-pointer">
                                        <div class="flex items-center">
                                            <input type="radio" 
                                                   name="answers[<?= $question->getId() ?>]" 
                                                   value="false"
                                                   class="hidden option-radio"
                                                   onchange="selectOption(this)">
                                            <div class="flex items-center">
                                                <div class="w-6 h-6 border-2 border-gray-300 rounded-full mr-4 flex items-center justify-center radio-circle">
                                                    <div class="w-3 h-3 bg-blue-500 rounded-full hidden radio-dot"></div>
                                                </div>
                                                <span class="text-gray-800 font-medium">False</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                
                            <?php elseif ($question->getQuestionType() === 'short_answer'): ?>
                                <textarea name="answers[<?= $question->getId() ?>]" 
                                          rows="4" 
                                          class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none resize-none"
                                          placeholder="Type your answer here..."
                                          onchange="saveAnswer(<?= $question->getId() ?>, this.value)"></textarea>
                                          
                            <?php elseif ($question->getQuestionType() === 'essay'): ?>
                                <textarea name="answers[<?= $question->getId() ?>]" 
                                          rows="8" 
                                          class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none resize-none"
                                          placeholder="Write your essay here..."
                                          onchange="saveAnswer(<?= $question->getId() ?>, this.value)"></textarea>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Navigation -->
            <div class="ios-card p-6 mt-8">
                <div class="flex justify-between items-center">
                    <button type="button" 
                            id="prevBtn" 
                            class="ios-button-secondary px-6 py-3 rounded-xl font-semibold"
                            onclick="previousQuestion()"
                            style="display: none;">
                        <i class="fas fa-chevron-left mr-2"></i>
                        Previous
                    </button>
                    
                    <div class="navigation-dots" id="navigationDots">
                        <?php for ($i = 0; $i < count($questions); $i++): ?>
                            <div class="nav-dot <?= $i === 0 ? 'current' : '' ?>" 
                                 onclick="goToQuestion(<?= $i ?>)"></div>
                        <?php endfor; ?>
                    </div>
                    
                    <button type="button" 
                            id="nextBtn" 
                            class="ios-button text-white px-6 py-3 rounded-xl font-semibold"
                            onclick="nextQuestion()">
                        Next
                        <i class="fas fa-chevron-right ml-2"></i>
                    </button>
                    
                    <button type="button" 
                            id="submitBtn" 
                            class="ios-button-success text-white px-8 py-3 rounded-xl font-semibold transition-all duration-200"
                            style="display: none;"
                            onclick="submitExam()">
                        <i class="fas fa-check mr-2"></i>
                        <span id="submitText">Submit Exam</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        let currentQuestionIndex = 0;
        let totalQuestions = <?= count($questions) ?>;
        let timeRemaining = <?= $timeRemaining ?>;
        let answers = {};
        
        // Timer functionality
        function updateTimer() {
            if (timeRemaining <= 0) {
                alert('Time is up! Your exam will be submitted automatically.');
                document.getElementById('examForm').submit();
                return;
            }
            
            const hours = Math.floor(timeRemaining / 3600);
            const minutes = Math.floor((timeRemaining % 3600) / 60);
            const seconds = timeRemaining % 60;
            
            document.getElementById('timerDisplay').textContent = 
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            // Change timer color when time is running low
            const timerCard = document.getElementById('timerCard');
            if (timeRemaining <= 300) { // 5 minutes
                timerCard.classList.add('timer-warning');
            }
            
            timeRemaining--;
        }
        
        setInterval(updateTimer, 1000);
        
        // Navigation functions
        function showQuestion(index) {
            document.querySelectorAll('.question-slide').forEach((slide, i) => {
                slide.style.display = i === index ? 'block' : 'none';
            });
            
            currentQuestionIndex = index;
            updateNavigation();
            updateProgress();
        }
        
        function nextQuestion() {
            if (currentQuestionIndex < totalQuestions - 1) {
                showQuestion(currentQuestionIndex + 1);
            }
        }
        
        function previousQuestion() {
            if (currentQuestionIndex > 0) {
                showQuestion(currentQuestionIndex - 1);
            }
        }
        
        function goToQuestion(index) {
            showQuestion(index);
        }
        
        function updateNavigation() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');
            
            prevBtn.style.display = currentQuestionIndex === 0 ? 'none' : 'block';
            
            if (currentQuestionIndex === totalQuestions - 1) {
                nextBtn.style.display = 'none';
                submitBtn.style.display = 'block';
            } else {
                nextBtn.style.display = 'block';
                submitBtn.style.display = 'none';
            }
            
            // Update navigation dots
            document.querySelectorAll('.nav-dot').forEach((dot, index) => {
                dot.classList.remove('current');
                if (index === currentQuestionIndex) {
                    dot.classList.add('current');
                }
            });
            
            document.getElementById('currentQuestion').textContent = currentQuestionIndex + 1;
        }
        
        function updateProgress() {
            const progress = ((currentQuestionIndex + 1) / totalQuestions) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
        }
        
        function selectOption(radio) {
            const questionCard = radio.closest('.question-card');
            const questionId = questionCard.dataset.questionId;
            
            // Update visual selection
            questionCard.querySelectorAll('.option-item').forEach(item => {
                item.classList.remove('selected');
            });
            radio.closest('.option-item').classList.add('selected');
            
            // Update radio button visual
            questionCard.querySelectorAll('.radio-circle').forEach(circle => {
                circle.classList.remove('border-blue-500');
                circle.classList.add('border-gray-300');
                circle.querySelector('.radio-dot').classList.add('hidden');
            });
            
            const selectedCircle = radio.closest('.option-item').querySelector('.radio-circle');
            selectedCircle.classList.remove('border-gray-300');
            selectedCircle.classList.add('border-blue-500');
            selectedCircle.querySelector('.radio-dot').classList.remove('hidden');
            
            // Mark question as answered
            questionCard.classList.add('answered');
            const navDot = document.querySelectorAll('.nav-dot')[currentQuestionIndex];
            navDot.classList.add('answered');
            
            // Save answer
            answers[questionId] = radio.value;
            saveAnswer(questionId, radio.value);
        }
        
        function saveAnswer(questionId, answer) {
            answers[questionId] = answer;
            
            // Auto-save to server
            fetch('<?= dirname($_SERVER['SCRIPT_NAME']) ?>/student/exam/save-answer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `attempt_id=<?= $attemptId ?>&question_id=${questionId}&answer=${encodeURIComponent(answer)}`
            });
        }
        
        async function submitExam() {
            const answeredCount = Object.keys(answers).length;
            const unansweredCount = totalQuestions - answeredCount;
            
            // Create custom modal instead of browser confirm
            let confirmMessage = 'Are you sure you want to submit your exam? This action cannot be undone.';
            if (unansweredCount > 0) {
                confirmMessage = `You have ${unansweredCount} unanswered question(s). Are you sure you want to submit?`;
            }
            
            const confirmed = await showCustomConfirm(confirmMessage);
            if (!confirmed) {
                return;
            }
            
            // Disable page leave warning during submission
            window.removeEventListener('beforeunload', beforeUnloadHandler);
            
            // Disable submit button and show loading
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const originalText = submitText.innerHTML;
            
            submitBtn.disabled = true;
            submitText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...';
            submitBtn.style.opacity = '0.7';
            
            // Prepare form data
            const formData = new FormData(document.getElementById('examForm'));
            
            // Submit via AJAX
            fetch('<?= dirname($_SERVER['SCRIPT_NAME']) ?>/student/exam/submit', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(data => {
                // Show success message
                showToast('Exam submitted successfully!', 'success');
                
                // Redirect after showing toast
                setTimeout(() => {
                    window.location.href = '<?= dirname($_SERVER['SCRIPT_NAME']) ?>/student-success';
                }, 1500);
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error submitting exam. Please try again.', 'error');
                
                // Re-enable button
                submitBtn.disabled = false;
                submitText.innerHTML = originalText;
                submitBtn.style.opacity = '1';
            });
        }

        // Add toast notification function
        function showToast(message, type = 'success') {
            // Remove existing toast if any
            const existingToast = document.getElementById('toast');
            if (existingToast) {
                existingToast.remove();
            }

            // Create toast element
            const toast = document.createElement('div');
            toast.id = 'toast';
            toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-medium transform transition-all duration-300 translate-x-full ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            }`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(toast);

            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);

            // Auto remove after 3 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            }, 3000);
        }

        // Custom confirmation modal
        function showCustomConfirm(message) {
            return new Promise((resolve) => {
                // Create modal backdrop
                const backdrop = document.createElement('div');
                backdrop.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                
                // Create modal
                const modal = document.createElement('div');
                modal.className = 'bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-8';
                modal.innerHTML = `
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 rounded-full bg-orange-100 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-exclamation-triangle text-orange-500 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-2">Confirm Submission</h3>
                        <p class="text-slate-600">${message}</p>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button id="cancelBtn" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-3 px-4 rounded-lg font-medium transition-colors">
                            Cancel
                        </button>
                        <button id="confirmBtn" class="flex-1 bg-orange-500 hover:bg-orange-600 text-white py-3 px-4 rounded-lg font-medium transition-colors">
                            Submit Exam
                        </button>
                    </div>
                `;
                
                backdrop.appendChild(modal);
                document.body.appendChild(backdrop);
                
                // Handle button clicks
                document.getElementById('cancelBtn').onclick = () => {
                    document.body.removeChild(backdrop);
                    resolve(false);
                };
                
                document.getElementById('confirmBtn').onclick = () => {
                    document.body.removeChild(backdrop);
                    resolve(true);
                };
            });
        }

        // Store beforeunload handler reference
        const beforeUnloadHandler = function(e) {
            e.preventDefault();
            e.returnValue = 'Are you sure you want to leave? Your progress will be lost.';
        };
        
        // Prevent accidental page refresh
        window.addEventListener('beforeunload', beforeUnloadHandler);
        
        // Initialize
        updateNavigation();
        updateProgress();
    </script>
</body>
</html>
