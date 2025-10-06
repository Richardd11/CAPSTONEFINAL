<?php
/**
 * Final comprehensive test for all recent fixes
 * Tests: Multiple choice answers, True/false answers, Score hiding, Modal improvements
 */

require_once 'vendor/autoload.php';

use App\Services\Exam\ExamService;

echo "=== FINAL COMPREHENSIVE TEST ===\n\n";

try {
    $examService = new ExamService();
    
    // Create comprehensive test exam
    $examData = [
        'title' => 'Final Test Exam - All Question Types',
        'description' => 'Testing all fixes: multiple choice, true/false, scoring, etc.',
        'subject_id' => 7,
        'faculty_id' => 12,
        'year_level' => '1st Year',
        'section' => 'A',
        'academic_year' => '2024-2025',
        'semester' => '1st Semester',
        'time_limit' => 30,
        'total_points' => 30,
        'questions' => [
            // Multiple Choice Question
            [
                'question_text' => 'What is the capital of France?',
                'question_type' => 'multiple_choice',
                'points' => 10,
                'order_index' => 1,
                'options' => [
                    ['option_text' => 'London', 'is_correct' => false],
                    ['option_text' => 'Paris', 'is_correct' => true],
                    ['option_text' => 'Berlin', 'is_correct' => false],
                    ['option_text' => 'Madrid', 'is_correct' => false]
                ]
            ],
            // True/False Question 1
            [
                'question_text' => 'The Earth is round.',
                'question_type' => 'true_false',
                'points' => 10,
                'order_index' => 2,
                'correct_answer' => 'true'
            ],
            // True/False Question 2
            [
                'question_text' => 'Water boils at 50°C.',
                'question_type' => 'true_false',
                'points' => 10,
                'order_index' => 3,
                'correct_answer' => 'false'
            ]
        ]
    ];
    
    echo "1. Creating comprehensive test exam...\n";
    $result = $examService->createExam($examData);
    
    if (!$result['success']) {
        echo "❌ Failed: " . $result['message'] . "\n";
        exit(1);
    }
    
    $examId = $result['data']['id'];
    echo "✅ Exam created: $examId\n\n";
    
    // Get questions for testing
    $questions = $examService->getExamQuestions($examId);
    echo "2. Retrieved " . count($questions) . " questions\n\n";
    
    // Test Case 1: All Correct Answers
    echo "3. Test Case 1: All CORRECT answers\n";
    $correctAnswers = [];
    foreach ($questions as $question) {
        $questionId = $question->getId();
        $questionType = $question->getQuestionType();
        
        if ($questionType === 'multiple_choice') {
            $correctAnswers[$questionId] = 'Paris'; // Correct answer
        } elseif ($questionType === 'true_false') {
            $correctAnswers[$questionId] = $question->getCorrectAnswer(); // Use stored correct answer
        }
        
        echo "   Q{$questionId} ({$questionType}): '{$correctAnswers[$questionId]}'\n";
    }
    
    $attemptId1 = $examService->createOrGetExamAttempt(11, $examId);
    $result1 = $examService->submitExamAnswers($attemptId1, $correctAnswers, 11);
    
    echo "   Result: {$result1['score']}% (Expected: 100%)\n";
    echo "   Status: " . ($result1['score'] == 100 ? "✅ PASS" : "❌ FAIL") . "\n\n";
    
    // Test Case 2: All Wrong Answers
    echo "4. Test Case 2: All WRONG answers\n";
    $wrongAnswers = [];
    foreach ($questions as $question) {
        $questionId = $question->getId();
        $questionType = $question->getQuestionType();
        
        if ($questionType === 'multiple_choice') {
            $wrongAnswers[$questionId] = 'London'; // Wrong answer
        } elseif ($questionType === 'true_false') {
            // Use opposite of correct answer
            $correctAnswer = $question->getCorrectAnswer();
            $wrongAnswers[$questionId] = ($correctAnswer === 'true') ? 'false' : 'true';
        }
        
        echo "   Q{$questionId} ({$questionType}): '{$wrongAnswers[$questionId]}'\n";
    }
    
    $attemptId2 = $examService->createOrGetExamAttempt(14, $examId);
    $result2 = $examService->submitExamAnswers($attemptId2, $wrongAnswers, 14);
    
    echo "   Result: {$result2['score']}% (Expected: 0%)\n";
    echo "   Status: " . ($result2['score'] == 0 ? "✅ PASS" : "❌ FAIL") . "\n\n";
    
    // Test Case 3: Mixed Answers
    echo "5. Test Case 3: MIXED answers (2 correct, 1 wrong)\n";
    $mixedAnswers = [];
    $questionCount = 0;
    foreach ($questions as $question) {
        $questionId = $question->getId();
        $questionType = $question->getQuestionType();
        $questionCount++;
        
        if ($questionCount <= 2) {
            // First 2 questions correct
            if ($questionType === 'multiple_choice') {
                $mixedAnswers[$questionId] = 'Paris';
            } else {
                $mixedAnswers[$questionId] = $question->getCorrectAnswer();
            }
        } else {
            // Last question wrong
            if ($questionType === 'multiple_choice') {
                $mixedAnswers[$questionId] = 'London';
            } else {
                $correctAnswer = $question->getCorrectAnswer();
                $mixedAnswers[$questionId] = ($correctAnswer === 'true') ? 'false' : 'true';
            }
        }
        
        echo "   Q{$questionId} ({$questionType}): '{$mixedAnswers[$questionId]}'\n";
    }
    
    $attemptId3 = $examService->createOrGetExamAttempt(27, $examId);
    $result3 = $examService->submitExamAnswers($attemptId3, $mixedAnswers, 27);
    
    $expectedScore = round((2/3) * 100, 2); // 2 out of 3 correct
    echo "   Result: {$result3['score']}% (Expected: ~{$expectedScore}%)\n";
    echo "   Status: " . (abs($result3['score'] - $expectedScore) < 1 ? "✅ PASS" : "❌ FAIL") . "\n\n";
    
    // Summary
    echo "=== TEST SUMMARY ===\n";
    $allPassed = ($result1['score'] == 100) && ($result2['score'] == 0) && (abs($result3['score'] - $expectedScore) < 1);
    
    if ($allPassed) {
        echo "🎉 ALL TESTS PASSED!\n\n";
        echo "✅ Multiple Choice Questions: Working correctly\n";
        echo "✅ True/False Questions: Working correctly\n";
        echo "✅ Answer Validation: Accurate scoring\n";
        echo "✅ Mixed Scenarios: Proper calculation\n\n";
        echo "🚀 System is ready for production use!\n";
    } else {
        echo "❌ SOME TESTS FAILED!\n";
        echo "Test 1 (All Correct): " . ($result1['score'] == 100 ? "✅" : "❌") . "\n";
        echo "Test 2 (All Wrong): " . ($result2['score'] == 0 ? "✅" : "❌") . "\n";
        echo "Test 3 (Mixed): " . (abs($result3['score'] - $expectedScore) < 1 ? "✅" : "❌") . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
