<?php
/**
 * Test the new score format (points earned / total points)
 */

require_once 'vendor/autoload.php';

use App\Services\Exam\ExamService;

echo "=== TESTING NEW SCORE FORMAT ===\n\n";

try {
    $examService = new ExamService();
    
    // Create test exam with different point values
    $examData = [
        'title' => 'Score Format Test',
        'description' => 'Testing new score format display',
        'subject_id' => 7,
        'faculty_id' => 12,
        'year_level' => '1st Year',
        'section' => 'A',
        'academic_year' => '2024-2025',
        'semester' => '1st Semester',
        'time_limit' => 30,
        'total_points' => 100, // Will be calculated from questions
        'questions' => [
            // Multiple Choice - 30 points
            [
                'question_text' => 'What is the capital of France?',
                'question_type' => 'multiple_choice',
                'points' => 30,
                'order_index' => 1,
                'options' => [
                    ['option_text' => 'London', 'is_correct' => false],
                    ['option_text' => 'Paris', 'is_correct' => true],
                    ['option_text' => 'Berlin', 'is_correct' => false],
                    ['option_text' => 'Madrid', 'is_correct' => false]
                ]
            ],
            // True/False - 20 points
            [
                'question_text' => 'The Earth is round.',
                'question_type' => 'true_false',
                'points' => 20,
                'order_index' => 2,
                'correct_answer' => 'true'
            ],
            // True/False - 50 points
            [
                'question_text' => 'Water boils at 50°C.',
                'question_type' => 'true_false',
                'points' => 50,
                'order_index' => 3,
                'correct_answer' => 'false'
            ]
        ]
    ];
    
    echo "1. Creating test exam with weighted questions...\n";
    echo "   - Multiple Choice: 30 points\n";
    echo "   - True/False #1: 20 points\n";
    echo "   - True/False #2: 50 points\n";
    echo "   - Total: 100 points\n\n";
    
    $result = $examService->createExam($examData);
    
    if (!$result['success']) {
        echo "❌ Failed: " . $result['message'] . "\n";
        exit(1);
    }
    
    $examId = $result['data']['id'];
    echo "✅ Exam created: $examId\n\n";
    
    // Get questions for testing
    $questions = $examService->getExamQuestions($examId);
    
    // Test Scenario 1: Get 2 out of 3 questions correct (30 + 20 = 50 out of 100 points)
    echo "2. Test Scenario: 2 out of 3 questions correct\n";
    echo "   Expected: 50/100 points (50%)\n";
    
    $answers = [];
    foreach ($questions as $question) {
        $questionId = $question->getId();
        $questionType = $question->getQuestionType();
        $points = $question->getPoints();
        
        if ($questionType === 'multiple_choice') {
            $answers[$questionId] = 'Paris'; // CORRECT (30 points)
            echo "   Q{$questionId} (MC, {$points}pts): 'Paris' ✅\n";
        } elseif ($questionType === 'true_false') {
            if ($points == 20) {
                $answers[$questionId] = 'true'; // CORRECT (20 points)
                echo "   Q{$questionId} (TF, {$points}pts): 'true' ✅\n";
            } else {
                $answers[$questionId] = 'true'; // WRONG (0 points, should be false)
                echo "   Q{$questionId} (TF, {$points}pts): 'true' ❌ (should be false)\n";
            }
        }
    }
    
    $attemptId = $examService->createOrGetExamAttempt(11, $examId);
    $result = $examService->submitExamAnswers($attemptId, $answers, 11);
    
    echo "\n   Results:\n";
    if (isset($result['score_data'])) {
        $scoreData = $result['score_data'];
        echo "   📊 New Format:\n";
        echo "      Raw Score: " . $scoreData['raw_score'] . "\n";
        echo "      Points Earned: " . $scoreData['points_earned'] . "\n";
        echo "      Total Points: " . $scoreData['total_points'] . "\n";
        echo "      Percentage: " . $scoreData['percentage'] . "%\n";
        
        // Verify the calculation
        $expectedPoints = 50; // 30 + 20 = 50 points
        $expectedPercentage = 50; // 50/100 = 50%
        
        if ($scoreData['points_earned'] == 2 && $scoreData['total_points'] == 3) {
            echo "   ⚠️  NOTE: System is counting questions (2/3) instead of points (50/100)\n";
            echo "   🔧 This needs to be fixed to use actual point values\n";
        } elseif ($scoreData['points_earned'] == $expectedPoints) {
            echo "   ✅ PERFECT: Point calculation is correct!\n";
        } else {
            echo "   ❌ ISSUE: Expected {$expectedPoints} points, got " . $scoreData['points_earned'] . "\n";
        }
    } else {
        echo "   📊 Legacy Format: " . $result['score'] . "%\n";
        echo "   ⚠️  New score format not implemented yet\n";
    }
    
    echo "\n=== ANALYSIS ===\n";
    echo "Current Implementation:\n";
    echo "- ✅ Returns detailed score data structure\n";
    echo "- ⚠️  May be counting questions instead of points\n";
    echo "- 🔧 Need to verify point-based calculation\n\n";
    
    echo "Real-world Example:\n";
    echo "- Student gets 2 easy questions (10 pts each) + misses 1 hard question (80 pts)\n";
    echo "- Should show: 20/100 (20%) not 2/3 (67%)\n";
    echo "- This reflects actual academic grading practices\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
