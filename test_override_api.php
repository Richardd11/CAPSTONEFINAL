<?php
/**
 * Test script for Faculty Override API
 * Run this to verify the override functionality is working
 */

echo "🔧 Testing Faculty Override API...\n\n";

// Test data
$testData = [
    'attempt_id' => 20,  // Your Mathematics essay attempt
    'question_id' => 32, // The question ID from your essay
    'new_score' => 8.5,
    'reason' => 'Student demonstrated good understanding of mathematical concepts and provided clear examples.'
];

echo "📝 Test Override Data:\n";
echo "- Attempt ID: " . $testData['attempt_id'] . "\n";
echo "- Question ID: " . $testData['question_id'] . "\n";
echo "- New Score: " . $testData['new_score'] . "\n";
echo "- Reason: " . $testData['reason'] . "\n\n";

// Simulate the API call
$url = 'http://localhost:8000/public/faculty/api/override-score';
$jsonData = json_encode($testData);

echo "🔄 Sending POST request to: $url\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📊 Response Status: HTTP $httpCode\n";

if ($error) {
    echo "❌ cURL Error: $error\n";
} else {
    echo "📄 Response Body:\n";
    echo $response . "\n\n";
    
    $responseData = json_decode($response, true);
    
    if ($responseData) {
        if ($responseData['success']) {
            echo "🎉 SUCCESS! Override API is working!\n";
            echo "✅ Score override completed successfully\n";
            echo "📝 Message: " . ($responseData['message'] ?? 'No message') . "\n";
        } else {
            echo "⚠️  API responded but with error:\n";
            echo "❌ Message: " . ($responseData['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "❌ Invalid JSON response\n";
    }
}

echo "\n🔧 Troubleshooting:\n";
echo "1. Make sure your PHP server is running (php -S localhost:8000 -t public)\n";
echo "2. Check that you're logged in as faculty\n";
echo "3. Verify the attempt_id and question_id are correct\n";
echo "4. Check the browser console for JavaScript errors\n\n";

echo "💡 To test in browser:\n";
echo "1. Go to Faculty → Exam Results\n";
echo "2. Click on 'dfdfd' exam\n";
echo "3. Click 'View Details' on RR's result\n";
echo "4. Click 'Override Score' button\n";
echo "5. Enter new score and reason\n";
echo "6. Check browser console (F12) for debug messages\n";
?>
