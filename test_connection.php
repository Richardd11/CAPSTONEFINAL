<?php
/**
 * Simple Database Connection Test
 * 
 * Quick test to verify database connectivity and basic functionality
 * Usage: php test_connection.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;

echo "🔌 Testing Database Connection\n";
echo "=============================\n\n";

try {
    // Test basic connection
    echo "1. Testing database connection...\n";
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    echo "   ✅ Connection established\n\n";
    
    // Test database selection
    echo "2. Testing database selection...\n";
    $stmt = $pdo->query("SELECT DATABASE() as current_db, VERSION() as version");
    $info = $stmt->fetch();
    echo "   Database: {$info['current_db']}\n";
    echo "   MySQL Version: {$info['version']}\n\n";
    
    // Test table existence
    echo "3. Testing core tables...\n";
    $coreTables = ['users', 'subjects', 'exams', 'questions'];
    
    foreach ($coreTables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetchColumn();
            echo "   ✅ $table ($count records)\n";
        } else {
            echo "   ❌ $table (missing)\n";
        }
    }
    
    echo "\n4. Testing sample data...\n";
    
    // Test users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $adminCount = $stmt->fetchColumn();
    echo "   Admin users: $adminCount\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'faculty'");
    $facultyCount = $stmt->fetchColumn();
    echo "   Faculty users: $facultyCount\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'");
    $studentCount = $stmt->fetchColumn();
    echo "   Student users: $studentCount\n";
    
    // Test exams
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM exams WHERE is_active = 1");
    $activeExams = $stmt->fetchColumn();
    echo "   Active exams: $activeExams\n";
    
    echo "\n🎉 Connection test completed successfully!\n";
    echo "Your database is ready for the exam management system.\n";
    
} catch (Exception $e) {
    echo "\n❌ Connection test failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    echo "💡 Troubleshooting tips:\n";
    echo "1. Make sure MySQL server is running\n";
    echo "2. Check database credentials in src/App/Config/Database.php\n";
    echo "3. Ensure database 'pokenginang' exists\n";
    echo "4. Run setup_complete_database.php if tables are missing\n";
    
    exit(1);
}
?>
