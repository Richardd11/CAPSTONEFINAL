<?php
// Database setup script
require_once 'vendor/autoload.php';

use App\Config\Database;

try {
    echo "Setting up database tables...\n";
    
    $db = Database::getInstance()->getConnection();
    
    // Create exam_attempts table
    $sql1 = "
    CREATE TABLE IF NOT EXISTS exam_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        exam_id INT NOT NULL,
        status ENUM('in_progress', 'completed', 'abandoned') DEFAULT 'in_progress',
        score DECIMAL(5,2) NULL,
        started_at TIMESTAMP NULL,
        completed_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_student_exam (student_id, exam_id),
        INDEX idx_status (status),
        FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
    )";
    
    $db->exec($sql1);
    echo "✅ Created exam_attempts table\n";
    
    // Create student_answers table
    $sql2 = "
    CREATE TABLE IF NOT EXISTS student_answers (
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
    
    $db->exec($sql2);
    echo "✅ Created student_answers table\n";
    
    echo "\n🎉 Database setup completed successfully!\n";
    echo "You can now use the exam completion tracking features.\n";
    
} catch (Exception $e) {
    echo "❌ Error setting up database: " . $e->getMessage() . "\n";
    echo "\nPlease check your database configuration and try again.\n";
}
?>
