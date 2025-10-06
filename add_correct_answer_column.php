<?php
require_once 'vendor/autoload.php';

use App\Config\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== ADDING CORRECT_ANSWER COLUMN ===\n";
    
    // Check if column already exists
    $stmt = $db->prepare("SHOW COLUMNS FROM questions LIKE 'correct_answer'");
    $stmt->execute();
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "✅ correct_answer column already exists\n";
    } else {
        echo "Adding correct_answer column to questions table...\n";
        
        $alterSQL = "ALTER TABLE questions ADD COLUMN correct_answer VARCHAR(255) NULL AFTER explanation";
        $db->exec($alterSQL);
        
        echo "✅ correct_answer column added successfully\n";
    }
    
    // Show the updated table structure
    echo "\n=== QUESTIONS TABLE STRUCTURE ===\n";
    $stmt = $db->prepare("DESCRIBE questions");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']} ({$column['Null']}, {$column['Key']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
