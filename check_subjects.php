<?php
require_once 'vendor/autoload.php';

use App\Config\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== CHECKING SUBJECTS ===\n";
    $stmt = $db->prepare("SELECT * FROM subjects LIMIT 5");
    $stmt->execute();
    $subjects = $stmt->fetchAll();
    
    if (empty($subjects)) {
        echo "No subjects found. Creating a test subject...\n";
        
        $stmt = $db->prepare("INSERT INTO subjects (subject_name, subject_code, year_level, semester) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Test Subject', 'TEST101', '1st Year', '1st Semester']);
        
        $subjectId = $db->lastInsertId();
        echo "Created test subject with ID: $subjectId\n";
    } else {
        echo "Found subjects:\n";
        foreach ($subjects as $subject) {
            echo "- ID: {$subject['subject_id']}, Name: {$subject['subject_name']}, Code: {$subject['subject_code']}\n";
        }
    }
    
    echo "\n=== CHECKING USERS ===\n";
    $stmt = $db->prepare("SELECT * FROM users WHERE role = 'faculty' LIMIT 3");
    $stmt->execute();
    $faculty = $stmt->fetchAll();
    
    if (empty($faculty)) {
        echo "No faculty found. Creating a test faculty...\n";
        
        $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['testfaculty', 'test@faculty.com', password_hash('password', PASSWORD_DEFAULT), 'Test Faculty', 'faculty']);
        
        $facultyId = $db->lastInsertId();
        echo "Created test faculty with ID: $facultyId\n";
    } else {
        echo "Found faculty:\n";
        foreach ($faculty as $fac) {
            echo "- ID: {$fac['user_id']}, Name: {$fac['full_name']}, Email: {$fac['email']}\n";
        }
    }
    
    $stmt = $db->prepare("SELECT * FROM users WHERE role = 'student' LIMIT 3");
    $stmt->execute();
    $students = $stmt->fetchAll();
    
    if (empty($students)) {
        echo "No students found. Creating test students...\n";
        
        for ($i = 1; $i <= 2; $i++) {
            $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, role, year_level, section) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute(["teststudent$i", "test$i@student.com", password_hash('password', PASSWORD_DEFAULT), "Test Student $i", 'student', '1st Year', 'A']);
            
            $studentId = $db->lastInsertId();
            echo "Created test student $i with ID: $studentId\n";
        }
    } else {
        echo "Found students:\n";
        foreach ($students as $student) {
            echo "- ID: {$student['user_id']}, Name: {$student['full_name']}, Email: {$student['email']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
