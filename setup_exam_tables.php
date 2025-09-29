<?php
/**
 * Database Setup Script for Exam System
 * Run this script once to create the exam-related tables
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;

try {
    echo "Setting up exam system tables...\n";
    
    $db = Database::getInstance()->getConnection();
    
    // Read the SQL schema file
    $sqlFile = __DIR__ . '/database_exam_schema.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL schema file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split the SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (trim($statement)) {
            echo "Executing: " . substr($statement, 0, 50) . "...\n";
            $db->exec($statement);
        }
    }
    
    echo "\n✅ Exam system tables created successfully!\n";
    echo "You can now use the exam creation functionality.\n\n";
    
    // Verify tables were created
    $tables = ['exams', 'questions', 'question_options', 'exam_attempts', 'student_answers'];
    echo "Verifying tables:\n";
    
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ $table table exists\n";
        } else {
            echo "✗ $table table missing\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n❌ Error setting up exam tables: " . $e->getMessage() . "\n";
    echo "Please check your database connection and try again.\n";
    exit(1);
}

echo "\n🎉 Setup complete! You can now create exams from the faculty dashboard.\n";
?>
