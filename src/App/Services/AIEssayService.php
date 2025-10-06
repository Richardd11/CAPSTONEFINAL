<?php

namespace App\Services;

/**
 * AI Essay Grading Service
 * Handles AI-powered essay evaluation with rubric-based scoring
 */
class AIEssayService
{
    private $apiKey;
    private $apiEndpoint;
    
    public function __construct()
    {
        // Load environment variables from .env file
        $this->loadEnvironmentVariables();
        
        // Get API key from environment or fallback
        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY') ?? null;
        $this->apiEndpoint = 'https://api.openai.com/v1/chat/completions';
        
        // For development, you can also hardcode the key temporarily
        if (!$this->apiKey) {
            $this->apiKey = 'your_openai_api_key_here'; // Replace with your actual API key
        }
    }
    
    /**
     * Grade an essay using AI based on the provided configuration
     * 
     * @param string $essayText The student's essay response
     * @param array $aiConfig AI configuration from the question
     * @param int $maxPoints Maximum points for the question
     * @return array Grading result with score, breakdown, and confidence
     */
    public function gradeEssay($essayText, $aiConfig, $maxPoints)
    {
        try {
            // If no API key is configured, return manual grading required
            if (!$this->apiKey) {
                return $this->getManualGradingResult($maxPoints);
            }
            
            // Prepare the grading prompt
            $prompt = $this->buildGradingPrompt($essayText, $aiConfig, $maxPoints);
            
            // Call OpenAI API
            $response = $this->callOpenAI($prompt);
            
            // Parse and validate the response
            $gradingResult = $this->parseGradingResponse($response, $maxPoints);
            
            // Check confidence threshold
            if ($aiConfig['confidence_threshold'] > 0 && 
                $gradingResult['confidence'] < $aiConfig['confidence_threshold']) {
                $gradingResult['requires_manual_review'] = true;
                $gradingResult['review_reason'] = 'AI confidence below threshold';
            }
            
            return $gradingResult;
            
        } catch (\Exception $e) {
            error_log("AI Essay Grading Error: " . $e->getMessage());
            return $this->getErrorResult($maxPoints, $e->getMessage());
        }
    }
    
    /**
     * Build the AI grading prompt based on configuration
     */
    private function buildGradingPrompt($essayText, $aiConfig, $maxPoints)
    {
        $prompt = "You are an expert essay grader. Please evaluate the following student essay based on the provided criteria.\n\n";
        
        // Add learning objectives
        if (!empty($aiConfig['learning_objectives'])) {
            $prompt .= "LEARNING OBJECTIVES:\n" . $aiConfig['learning_objectives'] . "\n\n";
        }
        
        // Add rubric weights
        $prompt .= "GRADING RUBRIC (Total: {$maxPoints} points):\n";
        foreach ($aiConfig['rubric_weights'] as $criterion => $weight) {
            $points = round(($weight / 100) * $maxPoints, 1);
            $prompt .= "- " . ucfirst(str_replace('_', ' ', $criterion)) . ": {$weight}% ({$points} points)\n";
        }
        $prompt .= "\n";
        
        // Add key concepts to look for
        if (!empty($aiConfig['key_concepts'])) {
            $prompt .= "KEY CONCEPTS TO EVALUATE:\n";
            foreach ($aiConfig['key_concepts'] as $concept) {
                $prompt .= "- {$concept}\n";
            }
            $prompt .= "\n";
        }
        
        // Add sample response if provided
        if (!empty($aiConfig['sample_response'])) {
            $prompt .= "SAMPLE EXCELLENT RESPONSE:\n" . $aiConfig['sample_response'] . "\n\n";
        }
        
        // Add the student's essay
        $prompt .= "STUDENT ESSAY TO GRADE:\n" . $essayText . "\n\n";
        
        // Add grading instructions
        $prompt .= "GRADING INSTRUCTIONS:\n";
        $prompt .= "1. Evaluate the essay against each rubric criterion\n";
        $prompt .= "2. Provide a score for each criterion (0-100%)\n";
        $prompt .= "3. Calculate the total score out of {$maxPoints} points\n";
        $prompt .= "4. Provide specific feedback for each criterion\n";
        $prompt .= "5. Give an overall confidence level (0-100%)\n";
        $prompt .= "6. Suggest areas for improvement\n\n";
        
        $prompt .= "Please respond in the following JSON format:\n";
        $prompt .= "{\n";
        $prompt .= '  "total_score": 0.0,' . "\n";
        $prompt .= '  "total_points": ' . $maxPoints . ',' . "\n";
        $prompt .= '  "confidence": 0,' . "\n";
        $prompt .= '  "criterion_scores": {' . "\n";
        foreach ($aiConfig['rubric_weights'] as $criterion => $weight) {
            $prompt .= '    "' . $criterion . '": {"score": 0, "feedback": "..."},' . "\n";
        }
        $prompt = rtrim($prompt, ",\n") . "\n";
        $prompt .= '  },' . "\n";
        $prompt .= '  "overall_feedback": "...",' . "\n";
        $prompt .= '  "strengths": ["..."],' . "\n";
        $prompt .= '  "improvements": ["..."]' . "\n";
        $prompt .= "}\n";
        
        return $prompt;
    }
    
    /**
     * Call OpenAI API
     */
    private function callOpenAI($prompt)
    {
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 2000,
            'temperature' => 0.3
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiEndpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new \Exception("OpenAI API error: HTTP {$httpCode}");
        }
        
        $decoded = json_decode($response, true);
        if (!$decoded || !isset($decoded['choices'][0]['message']['content'])) {
            throw new \Exception("Invalid OpenAI API response");
        }
        
        return $decoded['choices'][0]['message']['content'];
    }
    
    /**
     * Parse the AI grading response
     */
    private function parseGradingResponse($response, $maxPoints)
    {
        // Try to extract JSON from the response
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');
        
        if ($jsonStart === false || $jsonEnd === false) {
            throw new \Exception("No valid JSON found in AI response");
        }
        
        $jsonStr = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
        $result = json_decode($jsonStr, true);
        
        if (!$result) {
            throw new \Exception("Failed to parse AI response JSON");
        }
        
        // Validate and sanitize the result
        $gradingResult = [
            'ai_score' => floatval($result['total_score'] ?? 0),
            'max_points' => $maxPoints,
            'confidence' => intval($result['confidence'] ?? 0),
            'criterion_scores' => $result['criterion_scores'] ?? [],
            'overall_feedback' => $result['overall_feedback'] ?? '',
            'strengths' => $result['strengths'] ?? [],
            'improvements' => $result['improvements'] ?? [],
            'graded_by_ai' => true,
            'graded_at' => date('Y-m-d H:i:s'),
            'requires_manual_review' => false,
            'review_reason' => null
        ];
        
        // Ensure score is within valid range
        $gradingResult['ai_score'] = max(0, min($maxPoints, $gradingResult['ai_score']));
        
        return $gradingResult;
    }
    
    /**
     * Return result when manual grading is required
     */
    private function getManualGradingResult($maxPoints)
    {
        return [
            'ai_score' => 0,
            'max_points' => $maxPoints,
            'confidence' => 0,
            'criterion_scores' => [],
            'overall_feedback' => 'AI grading not available. Manual grading required.',
            'strengths' => [],
            'improvements' => [],
            'graded_by_ai' => false,
            'graded_at' => date('Y-m-d H:i:s'),
            'requires_manual_review' => true,
            'review_reason' => 'AI service not configured'
        ];
    }
    
    /**
     * Return error result
     */
    private function getErrorResult($maxPoints, $errorMessage)
    {
        return [
            'ai_score' => 0,
            'max_points' => $maxPoints,
            'confidence' => 0,
            'criterion_scores' => [],
            'overall_feedback' => 'Error during AI grading: ' . $errorMessage,
            'strengths' => [],
            'improvements' => [],
            'graded_by_ai' => false,
            'graded_at' => date('Y-m-d H:i:s'),
            'requires_manual_review' => true,
            'review_reason' => 'AI grading error'
        ];
    }
    
    /**
     * Override AI score with faculty score
     */
    public function overrideScore($attemptId, $questionId, $newScore, $reason, $facultyId)
    {
        // This would update the database with the faculty override
        // Implementation depends on your database structure
        
        return [
            'success' => true,
            'message' => 'Score overridden successfully',
            'original_ai_score' => null, // Would fetch from DB
            'new_score' => $newScore,
            'override_reason' => $reason,
            'overridden_by' => $facultyId,
            'overridden_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Load environment variables from .env file
     */
    private function loadEnvironmentVariables()
    {
        $envFile = __DIR__ . '/../../../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '#') === 0) continue; // Skip comments
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    if (!array_key_exists($key, $_ENV)) {
                        $_ENV[$key] = $value;
                        putenv("$key=$value");
                    }
                }
            }
        }
    }
}
