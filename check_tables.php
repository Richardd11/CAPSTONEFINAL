<?php
// Check table structure
require_once 'vendor/autoload.php';

use App\Config\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Checking exam_attempts table structure:\n";
    echo "=====================================\n";
    
    // Check if table exists
    $stmt = $db->prepare("SHOW TABLES LIKE 'exam_attempts'");
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Table 'exam_attempts' exists\n\n";
        
        // Show table structure
        $stmt = $db->prepare("DESCRIBE exam_attempts");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Columns in exam_attempts table:\n";
        foreach ($columns as $column) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
        }
    } else {
        echo "❌ Table 'exam_attempts' does not exist\n";
    }
    
    echo "\n";
    echo "Checking student_answers table structure:\n";
    echo "========================================\n";
    
    // Check student_answers table
    $stmt = $db->prepare("SHOW TABLES LIKE 'student_answers'");
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Table 'student_answers' exists\n\n";
        
        // Show table structure
        $stmt = $db->prepare("DESCRIBE student_answers");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Columns in student_answers table:\n";
        foreach ($columns as $column) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
        }
    } else {
        echo "❌ Table 'student_answers' does not exist\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
