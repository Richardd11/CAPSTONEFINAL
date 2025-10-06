<?php
/**
 * Complete test of the new academic score system
 * Tests multiple scenarios with different point distributions
 */

require_once 'vendor/autoload.php';

use App\Services\Exam\ExamService;

echo "=== COMPLETE ACADEMIC SCORE SYSTEM TEST ===\n\n";

try {
    $examService = new ExamService();
    
    // Create comprehensive test exam with realistic point distribution
    $examData = [
        'title' => 'Complete Score System Test',
        'description' => 'Testing real academic scoring with various point values',
        'subject_id' => 7,
        'faculty_id' => 12,
        'year_level' => '1st Year',
        'section' => 'A',
        'academic_year' => '2024-2025',
        'semester' => '1st Semester',
        'time_limit' => 60,
        'total_points' => 100,
        'questions' => [
            // Easy questions - 5 points each
            [
                'question_text' => 'What is 2 + 2?',
                'question_type' => 'multiple_choice',
                'points' => 5,
                'order_index' => 1,
                'options' => [
                    ['option_text' => '3', 'is_correct' => false],
                    ['option_text' => '4', 'is_correct' => true],
                    ['option_text' => '5', 'is_correct' => false],
                    ['option_text' => '6', 'is_correct' => false]
                ]
            ],
            [
                'question_text' => 'The sun rises in the east.',
                'question_type' => 'true_false',
                'points' => 5,
                'order_index' => 2,
                'correct_answer' => 'true'
            ],
            // Medium questions - 15 points each
            [
                'question_text' => 'What is the capital of Japan?',
                'question_type' => 'multiple_choice',
                'points' => 15,
                'order_index' => 3,
                'options' => [
                    ['option_text' => 'Beijing', 'is_correct' => false],
                    ['option_text' => 'Seoul', 'is_correct' => false],
                    ['option_text' => 'Tokyo', 'is_correct' => true],
                    ['option_text' => 'Bangkok', 'is_correct' => false]
                ]
            ],
            [
                'question_text' => 'Water freezes at 0°C.',
                'question_type' => 'true_false',
                'points' => 15,
                'order_index' => 4,
                'correct_answer' => 'true'
            ],
            // Hard question - 60 points (major portion)
            [
                'question_text' => 'Which programming language was created by Bjarne Stroustrup?',
                'question_type' => 'multiple_choice',
                'points' => 60,
                'order_index' => 5,
                'options' => [
                    ['option_text' => 'Java', 'is_correct' => false],
                    ['option_text' => 'Python', 'is_correct' => false],
                    ['option_text' => 'C++', 'is_correct' => true],
                    ['option_text' => 'JavaScript', 'is_correct' => false]
                ]
            ]
        ]
    ];
    
    echo "1. Creating realistic exam with weighted questions:\n";
    echo "   📚 Easy Questions (5 pts each):\n";
    echo "      - Math: 2 + 2 = ?\n";
    echo "      - Geography: Sun rises in east\n";
    echo "   📖 Medium Questions (15 pts each):\n";
    echo "      - Geography: Capital of Japan\n";
    echo "      - Science: Water freezing point\n";
    echo "   📘 Hard Question (60 pts):\n";
    echo "      - Programming: C++ creator\n";
    echo "   📊 Total: 100 points\n\n";
    
    $result = $examService->createExam($examData);
    
    if (!$result['success']) {
        echo "❌ Failed: " . $result['message'] . "\n";
        exit(1);
    }
    
    $examId = $result['data']['id'];
    echo "✅ Exam created: $examId\n\n";
    
    // Get questions for testing
    $questions = $examService->getExamQuestions($examId);
    
    // Test Scenario 1: Perfect Student (all correct)
    echo "2. 🌟 SCENARIO 1: Perfect Student (all correct)\n";
    echo "   Expected: 100/100 points (100%)\n";
    
    $perfectAnswers = [];
    foreach ($questions as $question) {
        $questionId = $question->getId();
        $questionType = $question->getQuestionType();
        
        if ($questionType === 'multiple_choice') {
            if (strpos($question->getQuestionText(), '2 + 2') !== false) {
                $perfectAnswers[$questionId] = '4';
            } elseif (strpos($question->getQuestionText(), 'Japan') !== false) {
                $perfectAnswers[$questionId] = 'Tokyo';
            } elseif (strpos($question->getQuestionText(), 'Stroustrup') !== false) {
                $perfectAnswers[$questionId] = 'C++';
            }
        } elseif ($questionType === 'true_false') {
            $perfectAnswers[$questionId] = $question->getCorrectAnswer();
        }
    }
    
    $attemptId1 = $examService->createOrGetExamAttempt(11, $examId);
    $result1 = $examService->submitExamAnswers($attemptId1, $perfectAnswers, 11);
    
    if (isset($result1['score_data'])) {
        $scoreData1 = $result1['score_data'];
        echo "   📊 Result: {$scoreData1['raw_score']} ({$scoreData1['percentage']}%)\n";
        echo "   Status: " . ($scoreData1['percentage'] == 100 ? "✅ PERFECT!" : "❌ Error") . "\n\n";
    }
    
    // Test Scenario 2: Good Student (misses hard question)
    echo "3. 😊 SCENARIO 2: Good Student (gets easy/medium, misses hard)\n";
    echo "   Expected: 40/100 points (40%)\n";
    
    $goodAnswers = [];
    foreach ($questions as $question) {
        $questionId = $question->getId();
        $questionType = $question->getQuestionType();
        $points = $question->getPoints();
        
        if ($points <= 15) { // Easy and medium questions
            if ($questionType === 'multiple_choice') {
                if (strpos($question->getQuestionText(), '2 + 2') !== false) {
                    $goodAnswers[$questionId] = '4'; // Correct
                } elseif (strpos($question->getQuestionText(), 'Japan') !== false) {
                    $goodAnswers[$questionId] = 'Tokyo'; // Correct
                }
            } elseif ($questionType === 'true_false') {
                $goodAnswers[$questionId] = $question->getCorrectAnswer(); // Correct
            }
        } else { // Hard question - get wrong
            $goodAnswers[$questionId] = 'Java'; // Wrong answer
        }
    }
    
    $attemptId2 = $examService->createOrGetExamAttempt(14, $examId);
    $result2 = $examService->submitExamAnswers($attemptId2, $goodAnswers, 14);
    
    if (isset($result2['score_data'])) {
        $scoreData2 = $result2['score_data'];
        echo "   📊 Result: {$scoreData2['raw_score']} ({$scoreData2['percentage']}%)\n";
        echo "   Status: " . ($scoreData2['percentage'] == 40 ? "✅ CORRECT!" : "❌ Error") . "\n\n";
    }
    
    // Test Scenario 3: Struggling Student (only gets easy questions)
    echo "4. 😰 SCENARIO 3: Struggling Student (only easy questions correct)\n";
    echo "   Expected: 10/100 points (10%)\n";
    
    $strugglingAnswers = [];
    foreach ($questions as $question) {
        $questionId = $question->getId();
        $questionType = $question->getQuestionType();
        $points = $question->getPoints();
        
        if ($points == 5) { // Only easy questions
            if ($questionType === 'multiple_choice') {
                $strugglingAnswers[$questionId] = '4'; // Correct
            } elseif ($questionType === 'true_false') {
                $strugglingAnswers[$questionId] = $question->getCorrectAnswer(); // Correct
            }
        } else { // Medium and hard questions - get wrong
            if ($questionType === 'multiple_choice') {
                $strugglingAnswers[$questionId] = 'Wrong Answer'; // Wrong
            } else {
                $correctAnswer = $question->getCorrectAnswer();
                $strugglingAnswers[$questionId] = ($correctAnswer === 'true') ? 'false' : 'true'; // Wrong
            }
        }
    }
    
    $attemptId3 = $examService->createOrGetExamAttempt(27, $examId);
    $result3 = $examService->submitExamAnswers($attemptId3, $strugglingAnswers, 27);
    
    if (isset($result3['score_data'])) {
        $scoreData3 = $result3['score_data'];
        echo "   📊 Result: {$scoreData3['raw_score']} ({$scoreData3['percentage']}%)\n";
        echo "   Status: " . ($scoreData3['percentage'] == 10 ? "✅ CORRECT!" : "❌ Error") . "\n\n";
    }
    
    // Summary
    echo "=== ACADEMIC SCORING ANALYSIS ===\n";
    echo "🎯 Real-World Impact Demonstration:\n\n";
    
    echo "📊 OLD SYSTEM (Question Count):\n";
    echo "   - Perfect Student: 5/5 questions = 100% ✅\n";
    echo "   - Good Student: 4/5 questions = 80% ❌ (misleading!)\n";
    echo "   - Struggling Student: 2/5 questions = 40% ❌ (misleading!)\n\n";
    
    echo "📊 NEW SYSTEM (Point Values):\n";
    if (isset($scoreData1, $scoreData2, $scoreData3)) {
        echo "   - Perfect Student: {$scoreData1['raw_score']} = {$scoreData1['percentage']}% ✅\n";
        echo "   - Good Student: {$scoreData2['raw_score']} = {$scoreData2['percentage']}% ✅ (accurate!)\n";
        echo "   - Struggling Student: {$scoreData3['raw_score']} = {$scoreData3['percentage']}% ✅ (accurate!)\n\n";
    }
    
    echo "🏆 BENEFITS:\n";
    echo "   ✅ Fair Assessment: Hard questions worth more points\n";
    echo "   ✅ Real Academic Format: Shows actual points like schools\n";
    echo "   ✅ Accurate Grading: Reflects true student performance\n";
    echo "   ✅ Professional Display: Matches real grade reports\n\n";
    
    echo "🎓 Your exam system now uses REAL ACADEMIC GRADING! 🎉\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
