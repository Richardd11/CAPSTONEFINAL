<?php
// Fix missing columns in exam_attempts table
require_once 'vendor/autoload.php';

use App\Config\Database;

try {
    echo "Fixing exam_attempts table columns...\n";
    
    $db = Database::getInstance()->getConnection();
    
    // Check current table structure
    $stmt = $db->prepare("DESCRIBE exam_attempts");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Current columns: " . implode(', ', $columns) . "\n\n";
    
    // Add missing columns
    $columnsToAdd = [
        'started_at' => 'TIMESTAMP NULL',
        'completed_at' => 'TIMESTAMP NULL',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ];
    
    foreach ($columnsToAdd as $columnName => $columnDefinition) {
        if (!in_array($columnName, $columns)) {
            echo "Adding column: $columnName\n";
            $sql = "ALTER TABLE exam_attempts ADD COLUMN $columnName $columnDefinition";
            $db->exec($sql);
            echo "✅ Added $columnName\n";
        } else {
            echo "✅ Column $columnName already exists\n";
        }
    }
    
    // Check if student_answers table exists, if not create it
    $stmt = $db->prepare("SHOW TABLES LIKE 'student_answers'");
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        echo "\nCreating student_answers table...\n";
        $sql = "
        CREATE TABLE student_answers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            attempt_id INT NOT NULL,
            question_id INT NOT NULL,
            answer TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_attempt_question (attempt_id, question_id),
            FOREIGN KEY (attempt_id) REFERENCES exam_attempts(id) ON DELETE CASCADE,
            FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
        )";
        $db->exec($sql);
        echo "✅ Created student_answers table\n";
    } else {
        echo "✅ student_answers table already exists\n";
    }
    
    echo "\n🎉 Table structure fixed successfully!\n";
    echo "Exam completion tracking should now work properly.\n";
    
} catch (Exception $e) {
    echo "❌ Error fixing table structure: " . $e->getMessage() . "\n";
}
?>
