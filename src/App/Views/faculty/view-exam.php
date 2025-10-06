<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($exam->getTitle()) ?> - Exam Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .header-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .question-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.3s ease;
        }
        
        .question-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-gradient text-white py-8 mb-8">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <a href="/faculty/exams" 
                       class="mr-6 p-2 rounded-full hover:bg-white/20 transition-all duration-300 group">
                        <i class="fas fa-arrow-left text-xl group-hover:transform group-hover:-translate-x-1 transition-transform"></i>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold mb-2 tracking-tight">
                            <?= htmlspecialchars($exam->getTitle()) ?>
                        </h1>
                        <p class="text-white/80 text-lg">Exam Details & Questions</p>
                    </div>
                </div>
                <div class="flex space-x-4">
                    <a href="/faculty/exam/<?= $exam->getId() ?>/edit" 
                       class="bg-white/10 backdrop-blur-sm text-white px-6 py-3 rounded-xl hover:bg-white/20 transition-all duration-300 border border-white/20">
                        <i class="fas fa-edit mr-2"></i>Edit Exam
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-6 max-w-5xl">
        <!-- Exam Information -->
        <div class="glass-card rounded-2xl mb-8 p-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Exam Information</h2>
                    
                    <?php if ($exam->getDescription()): ?>
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                            <p class="text-gray-600"><?= htmlspecialchars($exam->getDescription()) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Exam Type</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <?= ucfirst(htmlspecialchars($exam->getExamType())) ?>
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $exam->getIsActive() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                <?= $exam->getIsActive() ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Settings</h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <i class="fas fa-clock text-blue-500 mr-3"></i>
                            <span class="text-gray-700">Time Limit: <strong><?= $exam->getTimeLimit() ?> minutes</strong></span>
                        </div>
                        
                        <?php if ($exam->getStartDate()): ?>
                            <div class="flex items-center">
                                <i class="fas fa-calendar-plus text-green-500 mr-3"></i>
                                <span class="text-gray-700">Start: <strong><?= date('M j, Y g:i A', strtotime($exam->getStartDate())) ?></strong></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($exam->getEndDate()): ?>
                            <div class="flex items-center">
                                <i class="fas fa-calendar-times text-red-500 mr-3"></i>
                                <span class="text-gray-700">End: <strong><?= date('M j, Y g:i A', strtotime($exam->getEndDate())) ?></strong></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex items-center">
                            <i class="fas fa-question-circle text-purple-500 mr-3"></i>
                            <span class="text-gray-700">Questions: <strong><?= count($questions ?? []) ?></strong></span>
                        </div>
                        
                        <div class="flex items-center">
                            <i class="fas fa-star text-yellow-500 mr-3"></i>
                            <span class="text-gray-700">Total Points: <strong><?= array_sum(array_map(function($q) { return $q->getPoints(); }, $questions ?? [])) ?></strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions -->
        <div class="glass-card rounded-2xl p-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Questions (<?= count($questions ?? []) ?>)</h2>
            </div>
            
            <?php if (empty($questions)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-question-circle text-6xl text-gray-400 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Questions Added</h3>
                    <p class="text-gray-500 mb-4">This exam doesn't have any questions yet.</p>
                    <a href="/faculty/exam/<?= $exam->getId() ?>/edit" 
                       class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Questions
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="question-card rounded-xl p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                        <span class="text-blue-600 font-bold text-sm"><?= $index + 1 ?></span>
                                    </div>
                                    <div>
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            <?= ucfirst(str_replace('_', ' ', $question->getQuestionType())) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm text-gray-500">Points</span>
                                    <div class="text-lg font-bold text-blue-600"><?= $question->getPoints() ?></div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <p class="text-gray-800 font-medium"><?= htmlspecialchars($question->getQuestionText()) ?></p>
                            </div>
                            
                            <?php if ($question->getQuestionType() === 'multiple_choice'): ?>
                                <?php 
                                $options = [];
                                try {
                                    $options = $question->getOptions() ?? [];
                                } catch (Exception $e) {
                                    $options = [];
                                }
                                ?>
                                <?php if (!empty($options)): ?>
                                    <div class="space-y-2">
                                        <?php foreach ($options as $optionIndex => $option): ?>
                                            <div class="flex items-center p-3 rounded-lg <?= $option->getIsCorrect() ? 'bg-green-50 border border-green-200' : 'bg-gray-50' ?>">
                                                <div class="w-6 h-6 rounded-full border-2 <?= $option->getIsCorrect() ? 'border-green-500 bg-green-500' : 'border-gray-300' ?> flex items-center justify-center mr-3">
                                                    <?php if ($option->getIsCorrect()): ?>
                                                        <i class="fas fa-check text-white text-xs"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="text-gray-700"><?= htmlspecialchars($option->getOptionText()) ?></span>
                                                <?php if ($option->getIsCorrect()): ?>
                                                    <span class="ml-auto text-green-600 text-sm font-medium">Correct Answer</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php elseif ($question->getQuestionType() === 'true_false'): ?>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-check-circle text-blue-600"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-700">Correct Answer</div>
                                                <div class="text-lg font-bold text-blue-800">
                                                    <?= ucfirst(htmlspecialchars($question->getCorrectAnswer() ?? 'true')) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full">
                                            True/False
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($question->getQuestionType() === 'essay'): ?>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                    <div class="text-sm text-yellow-800">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        This is an essay question that requires manual grading.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
