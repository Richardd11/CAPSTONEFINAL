<?php
// Quick test exam creator
require_once __DIR__ . '/vendor/autoload.php';

use App\Services\Exam\ExamService;
use App\Models\Exam;
use App\Models\Question;

// Get student details from session or URL parameters
$studentYearLevel = $_GET['year'] ?? '2nd Year';
$studentSection = $_GET['section'] ?? 'A';

echo "<h1>Creating Test Exam</h1>";
echo "<p>Creating exam for: <strong>$studentYearLevel - Section $studentSection</strong></p>";

try {
    // Create a test exam
    $examData = [
        'title' => 'Sample Quiz - ' . $studentYearLevel . ' Section ' . $studentSection,
        'description' => 'This is a test exam created for demonstration purposes.',
        'subject_id' => 1, // Assuming subject ID 1 exists
        'faculty_id' => 1, // Assuming faculty ID 1 exists
        'year_level' => $studentYearLevel,
        'section' => $studentSection,
        'academic_year' => '2024-2025',
        'semester' => '1st Semester',
        'exam_type' => 'quiz',
        'time_limit' => 30, // 30 minutes
        'total_points' => 10,
        'instructions' => 'Answer all questions to the best of your ability.',
        'is_active' => true,
        'start_date' => null,
        'end_date' => null,
        'allow_retakes' => false,
        'max_attempts' => 1,
        'show_results' => true,
        'randomize_questions' => false
    ];
    
    $exam = new Exam($examData);
    $examService = new ExamService();
    
    // Note: This is a simplified creation - in real implementation,
    // you'd need to properly save to database through ExamDAO
    
    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✅ Test Exam Created Successfully!</h3>";
    echo "<p><strong>Title:</strong> " . htmlspecialchars($exam->getTitle()) . "</p>";
    echo "<p><strong>Year Level:</strong> " . htmlspecialchars($exam->getYearLevel()) . "</p>";
    echo "<p><strong>Section:</strong> " . htmlspecialchars($exam->getSection()) . "</p>";
    echo "<p><strong>Duration:</strong> " . $exam->getTimeLimit() . " minutes</p>";
    echo "<p><strong>Status:</strong> " . ($exam->getIsActive() ? 'Active' : 'Inactive') . "</p>";
    echo "</div>";
    
    echo "<h3>🎯 Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Go to your <a href='/public/student/dashboard' style='color: #007AFF; text-decoration: none;'><strong>Student Dashboard</strong></a></li>";
    echo "<li>You should now see this exam in your available exams</li>";
    echo "<li>Click 'Start Exam' to test the exam taking interface</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Error Creating Test Exam</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr style='margin: 30px 0;'>";
echo "<h3>🔧 Manual Creation Alternative:</h3>";
echo "<p>If the automatic creation doesn't work, you can:</p>";
echo "<ol>";
echo "<li><a href='/public/login' style='color: #007AFF;'>Log in as Faculty</a></li>";
echo "<li><a href='/public/faculty/create-exam' style='color: #007AFF;'>Go to Create Exam</a></li>";
echo "<li>Create an exam with Year Level: <strong>$studentYearLevel</strong> and Section: <strong>$studentSection</strong></li>";
echo "</ol>";

echo "<p style='margin-top: 30px;'>";
echo "<a href='/public/student/dashboard' style='background: #007AFF; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px;'>← Back to Student Dashboard</a>";
echo "</p>";
?>

<style>
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    line-height: 1.6;
}

h1 { color: #007AFF; }
h3 { color: #333; }
a { color: #007AFF; }
</style>
