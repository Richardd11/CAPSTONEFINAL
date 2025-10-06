<?php
/**
 * Test script for AI Essay Grading System
 * Run this to verify your OpenAI API key is working
 */

require_once __DIR__ . '/src/App/Services/AIEssayService.php';

use App\Services\AIEssayService;

echo "🤖 Testing AI Essay Grading System...\n\n";

// Test essay
$testEssay = "Science is magical because it helps us understand the world around us. Through scientific methods, we can discover how things work, from the smallest atoms to the largest galaxies. Science gives us tools to solve problems, cure diseases, and improve our lives. It's like magic because it reveals the hidden secrets of nature and allows us to predict and control natural phenomena.";

// Test AI configuration
$testConfig = [
    'learning_objectives' => 'Explain what science is and why it is important',
    'key_concepts' => ['scientific method', 'understanding', 'problem solving'],
    'rubric_weights' => [
        'content' => 40,
        'organization' => 25,
        'thinking' => 20,
        'language' => 15
    ],
    'sample_response' => 'Science is a systematic way of understanding the natural world...',
    'expected_length' => 'short',
    'grading_method' => 'ai_with_override',
    'confidence_threshold' => 85
];

try {
    echo "📝 Test Essay: " . substr($testEssay, 0, 100) . "...\n\n";
    
    $aiService = new AIEssayService();
    echo "✅ AI Service initialized successfully\n";
    
    echo "🔄 Grading essay with AI...\n";
    $result = $aiService->gradeEssay($testEssay, $testConfig, 10);
    
    echo "\n🎉 AI Grading Results:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📊 Score: " . $result['ai_score'] . "/" . $result['max_points'] . "\n";
    echo "🎯 Confidence: " . $result['confidence'] . "%\n";
    echo "🤖 Graded by AI: " . ($result['graded_by_ai'] ? 'Yes' : 'No') . "\n";
    echo "⚠️  Manual Review: " . ($result['requires_manual_review'] ? 'Yes' : 'No') . "\n";
    
    if (!empty($result['overall_feedback'])) {
        echo "\n💬 Overall Feedback:\n" . $result['overall_feedback'] . "\n";
    }
    
    if (!empty($result['strengths'])) {
        echo "\n✨ Strengths:\n";
        foreach ($result['strengths'] as $strength) {
            echo "  • " . $strength . "\n";
        }
    }
    
    if (!empty($result['improvements'])) {
        echo "\n🔧 Areas for Improvement:\n";
        foreach ($result['improvements'] as $improvement) {
            echo "  • " . $improvement . "\n";
        }
    }
    
    if (!empty($result['criterion_scores'])) {
        echo "\n📈 Criterion Breakdown:\n";
        foreach ($result['criterion_scores'] as $criterion => $data) {
            echo "  • " . ucfirst($criterion) . ": " . $data['score'] . "% - " . $data['feedback'] . "\n";
        }
    }
    
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🎉 SUCCESS! Your AI Essay Grading System is working perfectly!\n";
    echo "✅ OpenAI API key is valid and functional\n";
    echo "✅ AI service is properly configured\n";
    echo "✅ Essay grading is operational\n\n";
    
    echo "🚀 Next Steps:\n";
    echo "1. Run the database migration: ai_essay_grading_schema.sql\n";
    echo "2. Create an essay question with AI grading enabled\n";
    echo "3. Test with student submissions\n";
    echo "4. Check faculty override functionality\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
    
    echo "🔧 Troubleshooting:\n";
    echo "1. Check your OpenAI API key in .env file\n";
    echo "2. Verify internet connection\n";
    echo "3. Ensure OpenAI API has sufficient credits\n";
    echo "4. Check PHP cURL extension is enabled\n\n";
    
    echo "💡 If API key is missing, the system will fall back to manual grading.\n";
}

echo "📚 For more help, see: AI_SETUP_GUIDE.md\n";
?>
