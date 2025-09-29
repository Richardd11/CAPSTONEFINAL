<?php
/**
 * Test script to check exam saving functionality
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;
use App\Services\Exam\ExamService;

try {
    echo "🔍 Testing Exam Save Functionality...\n\n";
    
    // Test database connection
    $db = Database::getInstance()->getConnection();
    echo "✅ Database connection successful\n";
    
    // Check if exam tables exist
    $tables = ['exams', 'questions', 'question_options'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' exists\n";
        } else {
            echo "❌ Table '$table' does not exist\n";
        }
    }
    
    // Test exam service
    $examService = new ExamService();
    echo "✅ ExamService instantiated successfully\n";
    
    // Test data
    $testExamData = [
        'title' => 'Test Exam',
        'description' => 'Test Description',
        'subject_id' => 1,
        'faculty_id' => 313, // Assuming this faculty exists
        'exam_type' => 'quiz',
        'time_limit' => 60,
        'year_level' => '1st Year',
        'section' => 'A',
        'academic_year' => '2024-2025',
        'semester' => '1st Semester',
        'is_active' => true,
        'questions' => [
            [
                'question_text' => 'What is 2+2?',
                'question_type' => 'multiple_choice',
                'points' => 1,
                'order_index' => 0,
                'options' => [
                    ['option_text' => '3', 'is_correct' => false],
                    ['option_text' => '4', 'is_correct' => true],
                    ['option_text' => '5', 'is_correct' => false]
                ]
            ]
        ]
    ];
    
    echo "\n🧪 Testing exam creation...\n";
    $result = $examService->createExam($testExamData);
    
    if ($result['success']) {
        echo "✅ Exam created successfully!\n";
        echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "❌ Exam creation failed!\n";
        echo "Error: " . $result['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n✨ Test complete!\n";
?>
