<?php
/**
 * Test script to verify student enrollment system
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;
use App\Services\Student\StudentService;
use App\Services\Assignment\AssignmentService;

try {
    echo "🔍 Testing Student System...\n\n";
    
    // Test database connection
    $db = Database::getInstance()->getConnection();
    echo "✅ Database connection successful\n";
    
    // Test services
    $studentService = new StudentService();
    $assignmentService = new AssignmentService();
    echo "✅ Services instantiated successfully\n";
    
    // Test getting students for a faculty (assuming faculty ID 2 exists)
    $facultyId = 2;
    echo "\n📊 Testing student retrieval for faculty ID: $facultyId\n";
    
    $students = $studentService->getStudentsForFaculty($facultyId);
    echo "✅ Found " . count($students) . " students for faculty\n";
    
    if (!empty($students)) {
        echo "\n👥 Student List:\n";
        foreach ($students as $student) {
            echo "  - " . $student->getFullName() . " (" . $student->getSchoolId() . ") - " . 
                 $student->getYearLevel() . " Year, Section " . $student->getSection() . "\n";
        }
    }
    
    // Test student statistics
    $stats = $studentService->getStudentStats($facultyId);
    echo "\n📈 Student Statistics:\n";
    echo "  Total Students: " . $stats['total_students'] . "\n";
    echo "  By Year Level: " . json_encode($stats['by_year_level']) . "\n";
    echo "  By Section: " . json_encode($stats['by_section']) . "\n";
    
    // Test assignments for faculty
    echo "\n📚 Faculty Assignments:\n";
    $assignments = $assignmentService->getAssignmentsByFilters(['faculty_id' => $facultyId]);
    foreach ($assignments as $assignment) {
        echo "  - Subject: " . ($assignment->toArray()['subject_code'] ?? 'N/A') . 
             " | Year: " . $assignment->getYearLevel() . 
             " | Section: " . $assignment->getSection() . "\n";
        
        // Get students for this specific assignment
        $assignmentStudents = $studentService->getStudentsForAssignment($assignment->getId());
        echo "    Students in this assignment: " . count($assignmentStudents) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n✨ Test complete!\n";
?>
