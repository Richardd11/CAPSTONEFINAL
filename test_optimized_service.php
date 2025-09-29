<?php
// Test the optimized service directly
require_once 'vendor/autoload.php';

use App\Services\Exam\ExamServiceOptimized;

$service = new ExamServiceOptimized();
$result = $service->getAccurateStudentExamDetails(17);

echo "=== OPTIMIZED SERVICE TEST RESULT ===\n\n";

if ($result) {
    echo "SUCCESS! Result:\n";
    echo "Student: " . $result['student_name'] . "\n";
    echo "Score: " . $result['score'] . "%\n";
    echo "Correct Answers: " . $result['correct_answers'] . "/" . $result['total_questions'] . "\n";
    echo "Original Score: " . $result['original_score'] . "%\n\n";
    
    echo "Questions:\n";
    foreach ($result['questions'] as $i => $q) {
        echo ($i + 1) . ". " . $q['question_text'] . "\n";
        echo "   Student: '" . $q['student_answer'] . "'\n";
        echo "   Correct: '" . $q['correct_answer'] . "'\n";
        echo "   Status: " . ($q['is_correct'] ? 'CORRECT ✅' : 'WRONG ❌') . "\n\n";
    }
} else {
    echo "FAILED: No result returned\n";
}
?>
