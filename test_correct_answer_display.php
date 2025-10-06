<?php
/**
 * Test to verify correct_answer is being loaded and displayed properly
 */

require_once 'vendor/autoload.php';

use App\Services\Exam\ExamService;

echo "=== TESTING CORRECT ANSWER DISPLAY ===\n\n";

try {
    $examService = new ExamService();
    
    // Create a simple exam with true/false question
    $examData = [
        'title' => 'Test Correct Answer Display',
        'description' => 'Testing if correct_answer is properly loaded',
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
                'correct_answer' => 'true'
            ]
        ]
    ];
    
    echo "1. Creating test exam...\n";
    $result = $examService->createExam($examData);
    
    if (!$result['success']) {
        echo "❌ Failed: " . $result['message'] . "\n";
        exit(1);
    }
    
    $examId = $result['data']['id'];
    echo "✅ Exam created: $examId\n\n";
    
    // Get the questions to check if correct_answer is loaded
    echo "2. Retrieving questions...\n";
    $questions = $examService->getExamQuestions($examId);
    
    if (empty($questions)) {
        echo "❌ No questions found!\n";
        exit(1);
    }
    
    $question = $questions[0];
    echo "✅ Question retrieved\n";
    echo "   Question Text: " . $question->getQuestionText() . "\n";
    echo "   Question Type: " . $question->getQuestionType() . "\n";
    echo "   Correct Answer: " . ($question->getCorrectAnswer() ?? 'NULL') . "\n\n";
    
    // Test the display
    if ($question->getCorrectAnswer() !== null) {
        echo "✅ SUCCESS: correct_answer field is properly loaded!\n";
        echo "   Value: '" . $question->getCorrectAnswer() . "'\n";
        echo "   Display: " . ucfirst($question->getCorrectAnswer()) . "\n";
    } else {
        echo "❌ ISSUE: correct_answer field is NULL or not loaded\n";
        
        // Let's check the database directly
        echo "\n3. Checking database directly...\n";
        $pdo = \App\Config\Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT id, question_text, question_type, correct_answer FROM questions WHERE exam_id = ?");
        $stmt->execute([$examId]);
        $dbResult = $stmt->fetch();
        
        if ($dbResult) {
            echo "   Database record:\n";
            echo "   - ID: " . $dbResult['id'] . "\n";
            echo "   - Text: " . $dbResult['question_text'] . "\n";
            echo "   - Type: " . $dbResult['question_type'] . "\n";
            echo "   - Correct Answer: " . ($dbResult['correct_answer'] ?? 'NULL') . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
