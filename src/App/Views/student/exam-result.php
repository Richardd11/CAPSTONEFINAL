<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results - <?= htmlspecialchars($exam['title'] ?? 'Unknown Exam') ?></title>
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

        .header-gradient {
            background: linear-gradient(135deg, var(--ios-blue) 0%, var(--ios-blue-light) 50%, var(--ios-blue-dark) 100%);
        }

        .score-circle {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, #34C759 0deg, #34C759 var(--score-angle), #E5E5EA var(--score-angle), #E5E5EA 360deg);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .score-circle::before {
            content: '';
            width: 160px;
            height: 160px;
            background: white;
            border-radius: 50%;
            position: absolute;
        }

        .score-text {
            position: relative;
            z-index: 10;
            text-align: center;
        }

        .result-excellent {
            --score-color: #34C759;
            --score-bg: rgba(52, 199, 89, 0.1);
        }

        .result-good {
            --score-color: #007AFF;
            --score-bg: rgba(0, 122, 255, 0.1);
        }

        .result-fair {
            --score-color: #FF9500;
            --score-bg: rgba(255, 149, 0, 0.1);
        }

        .result-poor {
            --score-color: #FF3B30;
            --score-bg: rgba(255, 59, 48, 0.1);
        }

        .stats-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 48px rgba(0, 122, 255, 0.12);
        }

        .icon-container {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .blue-gradient {
            background: linear-gradient(135deg, var(--ios-blue) 0%, var(--ios-blue-light) 100%);
        }

        .green-gradient {
            background: linear-gradient(135deg, #34C759 0%, #30D158 100%);
        }

        .orange-gradient {
            background: linear-gradient(135deg, #FF9500 0%, #FF9F0A 100%);
        }

        .red-gradient {
            background: linear-gradient(135deg, #FF3B30 0%, #FF6B6B 100%);
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

        .answer-correct {
            background: rgba(52, 199, 89, 0.1);
            border-left: 4px solid #34C759;
        }

        .answer-incorrect {
            background: rgba(255, 59, 48, 0.1);
            border-left: 4px solid #FF3B30;
        }

        .answer-partial {
            background: rgba(255, 149, 0, 0.1);
            border-left: 4px solid #FF9500;
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .animate-delay-100 { animation-delay: 0.1s; }
        .animate-delay-200 { animation-delay: 0.2s; }
        .animate-delay-300 { animation-delay: 0.3s; }
        .animate-delay-400 { animation-delay: 0.4s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-gradient text-white py-12 mb-8 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/20 to-purple-600/20"></div>
        <div class="container mx-auto px-8 relative z-10">
            <div class="flex justify-between items-center">
                <div class="animate-fade-in-up">
                    <div class="flex items-center mb-4">
                        <div class="icon-container blue-gradient mr-4">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <h1 class="sf-pro-display text-4xl font-bold mb-2 tracking-tight">
                                Exam Results
                            </h1>
                            <p class="text-xl opacity-90 font-medium">
                                <?= htmlspecialchars($exam['title'] ?? 'Unknown Exam') ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="animate-fade-in-up animate-delay-200">
                    <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/student/dashboard" 
                       class="bg-white/10 backdrop-blur-sm border-2 border-white/30 text-white px-8 py-3 rounded-2xl hover:bg-white hover:text-blue-600 transition-all duration-300 font-semibold">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-8 max-w-6xl">
        <!-- Score Overview -->
        <div class="ios-card p-8 mb-12 animate-fade-in-up">
            <div class="flex flex-col lg:flex-row items-center justify-between">
                <div class="text-center lg:text-left mb-8 lg:mb-0">
                    <h2 class="sf-pro-display text-3xl font-bold text-gray-800 mb-4">
                        Your Score
                    </h2>
                    <div class="space-y-2">
                        <p class="text-gray-600">
                            <i class="fas fa-calendar mr-2 text-blue-500"></i>
                            Completed: <?= isset($attempt['completed_at']) && $attempt['completed_at'] ? date('F d, Y \a\t g:i A', strtotime($attempt['completed_at'])) : 'Recently' ?>
                        </p>
                        <p class="text-gray-600">
                            <i class="fas fa-clock mr-2 text-green-500"></i>
                            Duration: <?= $attempt['duration'] ?? 'N/A' ?> minutes
                        </p>
                        <p class="text-gray-600">
                            <i class="fas fa-list-ol mr-2 text-purple-500"></i>
                            Questions: <?= $exam['total_questions'] ?? 0 ?>
                        </p>
                    </div>
                </div>
                
                <div class="text-center">
                    <?php 
                    $score = $attempt['score'];
                    $scoreAngle = ($score / 100) * 360;
                    $resultClass = $score >= 90 ? 'result-excellent' : 
                                  ($score >= 80 ? 'result-good' : 
                                  ($score >= 70 ? 'result-fair' : 'result-poor'));
                    ?>
                    <div class="score-circle <?= $resultClass ?>" style="--score-angle: <?= $scoreAngle ?>deg;">
                        <div class="score-text">
                            <div class="sf-pro-display text-4xl font-bold" style="color: var(--score-color);">
                                <?= $score ?>%
                            </div>
                            <p class="text-gray-600 font-semibold">
                                <?php
                                if ($score >= 90) echo 'Excellent!';
                                elseif ($score >= 80) echo 'Good Job!';
                                elseif ($score >= 70) echo 'Fair';
                                else echo 'Needs Improvement';
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            <div class="stats-card p-6 animate-fade-in-up">
                <div class="flex items-center">
                    <div class="icon-container green-gradient mr-4">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Correct</p>
                        <p class="sf-pro-display text-2xl font-bold text-green-600">
                            <?= $results['correct_answers'] ?? 0 ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="stats-card p-6 animate-fade-in-up animate-delay-100">
                <div class="flex items-center">
                    <div class="icon-container red-gradient mr-4">
                        <i class="fas fa-times"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Incorrect</p>
                        <p class="sf-pro-display text-2xl font-bold text-red-600">
                            <?= $results['incorrect_answers'] ?? 0 ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="stats-card p-6 animate-fade-in-up animate-delay-200">
                <div class="flex items-center">
                    <div class="icon-container orange-gradient mr-4">
                        <i class="fas fa-question"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Unanswered</p>
                        <p class="sf-pro-display text-2xl font-bold text-orange-600">
                            <?= $results['unanswered'] ?? 0 ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="stats-card p-6 animate-fade-in-up animate-delay-300">
                <div class="flex items-center">
                    <div class="icon-container blue-gradient mr-4">
                        <i class="fas fa-star"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Points Earned</p>
                        <p class="sf-pro-display text-2xl font-bold text-blue-600">
                            <?= $results['points_earned'] ?? 0 ?>/<?= $exam['total_points'] ?? 0 ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Results -->
        <?php if (!empty($results['questions'])): ?>
        <div class="ios-card p-8 animate-fade-in-up animate-delay-400">
            <div class="flex items-center mb-8">
                <div class="icon-container blue-gradient mr-4">
                    <i class="fas fa-list-alt"></i>
                </div>
                <div>
                    <h2 class="sf-pro-display text-2xl font-bold text-gray-800">
                        Question-by-Question Review
                    </h2>
                    <p class="text-gray-500 font-medium">Detailed breakdown of your answers</p>
                </div>
            </div>

            <div class="space-y-6">
                <?php foreach ($results['questions'] as $index => $questionResult): ?>
                    <div class="p-6 rounded-xl <?= $questionResult['is_correct'] ? 'answer-correct' : 'answer-incorrect' ?>">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center mb-3">
                                    <span class="bg-white px-3 py-1 rounded-full text-sm font-semibold mr-3">
                                        Question <?= $index + 1 ?>
                                    </span>
                                    <span class="text-sm text-gray-600">
                                        <?= $questionResult['points'] ?> point<?= $questionResult['points'] !== 1 ? 's' : '' ?>
                                    </span>
                                </div>
                                
                                <h3 class="sf-pro-display text-lg font-semibold text-gray-800 mb-4">
                                    <?= nl2br(htmlspecialchars($questionResult['question_text'])) ?>
                                </h3>
                            </div>
                            
                            <div class="text-center ml-4">
                                <?php if ($questionResult['is_correct']): ?>
                                    <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-white">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-green-600 mt-2">Correct</p>
                                <?php else: ?>
                                    <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center text-white">
                                        <i class="fas fa-times"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-red-600 mt-2">Incorrect</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-semibold text-gray-700 mb-2">Your Answer:</h4>
                                <div class="bg-white p-3 rounded-lg">
                                    <p class="text-gray-800">
                                        <?= htmlspecialchars($questionResult['student_answer'] ?? 'No answer provided') ?>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if (!$questionResult['is_correct']): ?>
                            <div>
                                <h4 class="font-semibold text-gray-700 mb-2">Correct Answer:</h4>
                                <div class="bg-green-50 p-3 rounded-lg border border-green-200">
                                    <p class="text-green-800">
                                        <?= htmlspecialchars($questionResult['correct_answer']) ?>
                                    </p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($questionResult['explanation'])): ?>
                        <div class="mt-4">
                            <h4 class="font-semibold text-gray-700 mb-2">Explanation:</h4>
                            <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                                <p class="text-blue-800">
                                    <?= nl2br(htmlspecialchars($questionResult['explanation'])) ?>
                                </p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="text-center py-8">
            <div class="space-x-4">
                <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/student/dashboard" 
                   class="ios-button text-white px-8 py-3 rounded-xl font-semibold inline-block">
                    <i class="fas fa-home mr-2"></i>
                    Back to Dashboard
                </a>
                
                <button onclick="window.print()" 
                        class="bg-gray-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-gray-700 transition-colors">
                    <i class="fas fa-print mr-2"></i>
                    Print Results
                </button>
            </div>
        </div>
    </div>

    <script>
        // Add print styles
        const printStyles = `
            @media print {
                body { background: white !important; }
                .header-gradient { background: #007AFF !important; }
                .ios-card { box-shadow: none !important; border: 1px solid #ddd !important; }
                .animate-fade-in-up { animation: none !important; }
            }
        `;
        
        const styleSheet = document.createElement("style");
        styleSheet.innerText = printStyles;
        document.head.appendChild(styleSheet);
    </script>
</body>
</html>
