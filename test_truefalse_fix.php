<?php
/**
 * Quick test to verify true/false question fix
 */

require_once 'vendor/autoload.php';

use App\Services\Exam\ExamService;

echo "=== TESTING TRUE/FALSE FIX ===\n\n";

try {
    $examService = new ExamService();
    
    // Create a simple exam with just a true/false question
    $examData = [
        'title' => 'True/False Test',
        'description' => 'Testing true/false fix',
        'subject_id' => 7,
        'faculty_id' => 12,
        'year_level' => '1st Year',
        'section' => 'A',
        'academic_year' => '2024-2025',
        'semester' => '1st Semester',
        'time_limit' => 10,
        'total_points' => 10,
        'questions' => [
            [
                'question_text' => 'The sky is blue.',
                'question_type' => 'true_false',
                'points' => 10,
                'order_index' => 1,
                'correct_answer' => 'true'  // This should be the correct answer
            ]
        ]
    ];
    
    echo "1. Creating exam with true/false question (correct answer: 'true')...\n";
    $result = $examService->createExam($examData);
    
    if (!$result['success']) {
        echo "❌ Failed: " . $result['message'] . "\n";
        exit(1);
    }
    
    $examId = $result['data']['id'];
    echo "✅ Exam created: $examId\n\n";
    
    // Get the question to verify it was stored correctly
    $questions = $examService->getExamQuestions($examId);
    $question = $questions[0];
    
    echo "2. Verifying question storage...\n";
    echo "   Question: " . $question->getQuestionText() . "\n";
    echo "   Type: " . $question->getQuestionType() . "\n";
    echo "   Stored correct answer: " . ($question->getCorrectAnswer() ?? 'NULL') . "\n\n";
    
    // Test with correct answer
    echo "3. Testing with CORRECT answer ('true')...\n";
    $attemptId1 = $examService->createOrGetExamAttempt(11, $examId);
    $result1 = $examService->submitExamAnswers($attemptId1, [$question->getId() => 'true'], 11);
    
    echo "   Score: " . $result1['score'] . "% (should be 100%)\n";
    if ($result1['score'] == 100) {
        echo "   ✅ CORRECT answer validation: PASSED\n";
    } else {
        echo "   ❌ CORRECT answer validation: FAILED\n";
    }
    
    // Test with wrong answer
    echo "\n4. Testing with WRONG answer ('false')...\n";
    $attemptId2 = $examService->createOrGetExamAttempt(14, $examId);
    $result2 = $examService->submitExamAnswers($attemptId2, [$question->getId() => 'false'], 14);
    
    echo "   Score: " . $result2['score'] . "% (should be 0%)\n";
    if ($result2['score'] == 0) {
        echo "   ✅ WRONG answer validation: PASSED\n";
    } else {
        echo "   ❌ WRONG answer validation: FAILED\n";
    }
    
    echo "\n=== TEST SUMMARY ===\n";
    if ($result1['score'] == 100 && $result2['score'] == 0) {
        echo "🎉 ALL TESTS PASSED! True/False questions are working correctly.\n";
    } else {
        echo "❌ TESTS FAILED! There are still issues with true/false questions.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
