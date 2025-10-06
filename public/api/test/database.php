<?php
/**
 * Database Test Endpoint
 * Tests database connectivity and verifies correct_answer column exists
 */

require_once '../../../vendor/autoload.php';

use App\Config\Database;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    // Test database connection
    $db = Database::getInstance()->getConnection();
    
    if (!$db) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    // Check if correct_answer column exists
    $stmt = $db->prepare("
        SELECT COUNT(*) AS column_exists 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'questions' 
        AND COLUMN_NAME = 'correct_answer'
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['column_exists'] > 0) {
        // Column exists - test is successful
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Database connection successful',
            'correct_answer_column' => true,
            'details' => 'The correct_answer column exists in questions table'
        ]);
    } else {
        // Column doesn't exist
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database structure incomplete',
            'correct_answer_column' => false,
            'details' => 'The correct_answer column is missing from questions table'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database test failed',
        'error' => $e->getMessage()
    ]);
}
