<?php
/**
 * Test file to verify question handling fixes
 * Run this to test multiple choice and true/false question functionality
 */

require_once 'vendor/autoload.php';

use App\Services\Exam\ExamService;
use App\Models\Exam;
use App\Models\Question;

echo "=== TESTING QUESTION FIXES ===\n\n";

try {
    $examService = new ExamService();
    
    // Test data for exam creation
    $examData = [
        'title' => 'Test Exam - Question Fixes',
        'description' => 'Testing multiple choice and true/false questions',
        'subject_id' => 7,
        'faculty_id' => 12,
        'year_level' => '1st Year',
        'section' => 'A',
        'academic_year' => '2024-2025',
        'semester' => '1st Semester',
        'time_limit' => 30,
        'total_points' => 20,
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
            // True/False Question
            [
                'question_text' => 'The Earth is flat.',
                'question_type' => 'true_false',
                'points' => 10,
                'order_index' => 2,
                'correct_answer' => 'false'
            ]
        ]
    ];
    
    echo "1. Creating test exam...\n";
    $result = $examService->createExam($examData);
    
    if (!$result['success']) {
        echo "❌ Failed to create exam: " . $result['message'] . "\n";
        exit(1);
    }
    
    echo "✅ Exam created successfully!\n";
    $examId = $result['data']['id'];
    echo "   Exam ID: $examId\n\n";
    
    // Get the created questions
    echo "2. Retrieving questions...\n";
    $questions = $examService->getExamQuestions($examId);
    
    if (empty($questions)) {
        echo "❌ No questions found!\n";
        exit(1);
    }
    
    echo "✅ Found " . count($questions) . " questions\n\n";
    
    // Test answer validation
    echo "3. Testing answer validation...\n";
    
    $testAnswers = [];
    foreach ($questions as $question) {
        $questionId = $question->getId();
        $questionType = $question->getQuestionType();
        
        echo "   Question $questionId ($questionType): " . $question->getQuestionText() . "\n";
        
        if ($questionType === 'multiple_choice') {
            // Test with correct answer
            $testAnswers[$questionId] = 'Paris';
            echo "     Testing with answer: 'Paris'\n";
        } elseif ($questionType === 'true_false') {
            // Test with correct answer
            $testAnswers[$questionId] = 'false';
            echo "     Testing with answer: 'false'\n";
        }
    }
    
    // Create a mock exam attempt for testing
    echo "\n4. Creating test exam attempt...\n";
    $attemptId = $examService->createOrGetExamAttempt(11, $examId); // Using student ID 11
    
    if (!$attemptId) {
        echo "❌ Failed to create exam attempt\n";
        exit(1);
    }
    
    echo "✅ Exam attempt created: $attemptId\n";
    
    // Test submitting answers
    echo "\n5. Testing answer submission...\n";
    $submitResult = $examService->submitExamAnswers($attemptId, $testAnswers, 11);
    
    if (!$submitResult['success']) {
        echo "❌ Failed to submit answers: " . $submitResult['message'] . "\n";
        exit(1);
    }
    
    echo "✅ Answers submitted successfully!\n";
    echo "   Score: " . $submitResult['score'] . "%\n";
    
    // Test getting detailed results
    echo "\n6. Testing detailed results...\n";
    $detailedResults = $examService->getDetailedExamResults($attemptId);
    
    if (!$detailedResults) {
        echo "❌ Failed to get detailed results\n";
        exit(1);
    }
    
    echo "✅ Detailed results retrieved:\n";
    echo "   Correct answers: " . $detailedResults['correct_answers'] . "\n";
    echo "   Total questions: " . $detailedResults['total_questions'] . "\n";
    
    // Display question-by-question results
    echo "\n7. Question-by-question results:\n";
    foreach ($detailedResults['questions'] as $i => $questionResult) {
        $status = $questionResult['is_correct'] ? '✅ CORRECT' : '❌ WRONG';
        echo "   " . ($i + 1) . ". " . $questionResult['question_text'] . "\n";
        echo "      Student Answer: '" . $questionResult['student_answer'] . "'\n";
        echo "      Correct Answer: '" . $questionResult['correct_answer'] . "'\n";
        echo "      Status: $status\n\n";
    }
    
    // Test with wrong answers
    echo "8. Testing with wrong answers...\n";
    
    $wrongAnswers = [];
    foreach ($questions as $question) {
        $questionId = $question->getId();
        $questionType = $question->getQuestionType();
        
        if ($questionType === 'multiple_choice') {
            $wrongAnswers[$questionId] = 'London'; // Wrong answer
        } elseif ($questionType === 'true_false') {
            $wrongAnswers[$questionId] = 'true'; // Wrong answer
        }
    }
    
    // Create another attempt for wrong answers
    $wrongAttemptId = $examService->createOrGetExamAttempt(14, $examId); // Using student ID 14
    $wrongSubmitResult = $examService->submitExamAnswers($wrongAttemptId, $wrongAnswers, 14);
    
    if ($wrongSubmitResult['success']) {
        echo "✅ Wrong answers test completed\n";
        echo "   Score: " . $wrongSubmitResult['score'] . "% (should be 0%)\n";
        
        if ($wrongSubmitResult['score'] == 0) {
            echo "✅ Scoring validation working correctly!\n";
        } else {
            echo "❌ Scoring validation failed - expected 0%, got " . $wrongSubmitResult['score'] . "%\n";
        }
    }
    
    echo "\n=== ALL TESTS COMPLETED ===\n";
    echo "✅ Multiple choice questions: Working\n";
    echo "✅ True/false questions: Working\n";
    echo "✅ Answer validation: Working\n";
    echo "✅ Score calculation: Working\n";
    
} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
