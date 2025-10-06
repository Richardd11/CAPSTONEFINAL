<?php
/**
 * Apply Database Fix for correct_answer column
 * Run this script to add the correct_answer column to the questions table
 */

require_once 'vendor/autoload.php';

use App\Config\Database;

try {
    echo "========================================\n";
    echo "Database Fix: Adding correct_answer column\n";
    echo "========================================\n\n";

    $db = Database::getInstance()->getConnection();
    
    // Check if column already exists
    echo "1. Checking if correct_answer column exists...\n";
    $checkStmt = $db->prepare("
        SELECT COUNT(*) AS column_exists 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'questions' 
        AND COLUMN_NAME = 'correct_answer'
    ");
    $checkStmt->execute();
    $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['column_exists'] > 0) {
        echo "✅ Column 'correct_answer' already exists in questions table.\n";
    } else {
        echo "⚠️ Column 'correct_answer' does not exist. Adding it now...\n";
        
        // Add the column
        $alterStmt = $db->prepare("
            ALTER TABLE `questions` 
            ADD COLUMN `correct_answer` TEXT NULL 
            COMMENT 'Stores correct answer for true/false, short_answer, and other non-multiple-choice questions'
            AFTER `explanation`
        ");
        $alterStmt->execute();
        echo "✅ Column 'correct_answer' has been added successfully.\n";
    }
    
    // Check if index exists
    echo "\n2. Checking for performance index...\n";
    $indexCheckStmt = $db->prepare("
        SELECT COUNT(*) AS index_exists
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'questions'
        AND INDEX_NAME = 'idx_questions_correct_answer'
    ");
    $indexCheckStmt->execute();
    $indexResult = $indexCheckStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($indexResult['index_exists'] > 0) {
        echo "✅ Index 'idx_questions_correct_answer' already exists.\n";
    } else {
        echo "⚠️ Index does not exist. Creating it now...\n";
        $indexStmt = $db->prepare("
            CREATE INDEX `idx_questions_correct_answer` ON `questions` (`correct_answer`(100))
        ");
        $indexStmt->execute();
        echo "✅ Index 'idx_questions_correct_answer' has been created.\n";
    }
    
    // Update existing true/false questions
    echo "\n3. Updating existing true/false questions...\n";
    $updateStmt = $db->prepare("
        UPDATE `questions` 
        SET `correct_answer` = 'true' 
        WHERE `question_type` = 'true_false' 
        AND (`correct_answer` IS NULL OR `correct_answer` = '')
    ");
    $updateStmt->execute();
    $rowsUpdated = $updateStmt->rowCount();
    echo "✅ Updated $rowsUpdated true/false questions with default correct answer.\n";
    
    // Verify the column structure
    echo "\n4. Verifying column structure...\n";
    $describeStmt = $db->prepare("DESCRIBE questions");
    $describeStmt->execute();
    $columns = $describeStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $found = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'correct_answer') {
            echo "✅ Column 'correct_answer' details:\n";
            echo "   - Type: " . $column['Type'] . "\n";
            echo "   - Null: " . $column['Null'] . "\n";
            echo "   - Default: " . ($column['Default'] ?? 'NULL') . "\n";
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        throw new Exception("Column 'correct_answer' was not found after creation!");
    }
    
    echo "\n========================================\n";
    echo "✅ DATABASE FIX COMPLETED SUCCESSFULLY!\n";
    echo "========================================\n\n";
    echo "The 'correct_answer' column has been added to the questions table.\n";
    echo "You can now save exams with true/false questions without errors.\n";
    
} catch (PDOException $e) {
    echo "\n❌ Database Error: " . $e->getMessage() . "\n";
    echo "Please check your database connection and permissions.\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
