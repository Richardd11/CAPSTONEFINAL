<?php
/**
 * Database Connection and User Check Script
 * Run this to verify your database connection and user data
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;

try {
    echo "🔍 Checking database connection and user data...\n\n";
    
    $db = Database::getInstance()->getConnection();
    echo "✅ Database connection successful!\n";
    echo "Connected to database: pokenginang\n\n";
    
    // Check if users table exists
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Users table exists\n";
        
        // Check users in the table
        $stmt = $db->query("SELECT user_id, school_id, full_name, role FROM users ORDER BY role, user_id");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($users)) {
            echo "⚠️  No users found in database!\n";
            echo "You need to run the databse1.sql file to insert sample users.\n\n";
        } else {
            echo "👥 Users found in database:\n";
            foreach ($users as $user) {
                echo "  - ID: {$user['user_id']}, School ID: {$user['school_id']}, Name: {$user['full_name']}, Role: {$user['role']}\n";
            }
            echo "\n";
        }
        
        // Check faculty users specifically
        $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'faculty'");
        $facultyCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "👨‍🏫 Faculty users: {$facultyCount}\n";
        
    } else {
        echo "❌ Users table does not exist!\n";
        echo "You need to run the databse1.sql file to create tables and insert data.\n\n";
    }
    
    // Check if subject_assignments table exists
    $stmt = $db->query("SHOW TABLES LIKE 'subject_assignments'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Subject assignments table exists\n";
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM subject_assignments");
        $assignmentCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "📚 Subject assignments: {$assignmentCount}\n";
    } else {
        echo "❌ Subject assignments table does not exist!\n";
    }
    
    // Check if exams table exists (new structure)
    $stmt = $db->query("SHOW TABLES LIKE 'exams'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Exams table exists\n";
        
        // Check if it has the new structure
        $stmt = $db->query("SHOW COLUMNS FROM exams LIKE 'is_active'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Exams table has new structure (with is_active column)\n";
        } else {
            echo "⚠️  Exams table exists but has old structure\n";
            echo "You need to run the updated databse1.sql to update the table structure.\n";
        }
    } else {
        echo "❌ Exams table does not exist!\n";
    }
    
    echo "\n🔧 RECOMMENDATIONS:\n";
    
    if (empty($users ?? [])) {
        echo "1. Run the databse1.sql file in your 'pokenginang' database\n";
        echo "2. This will create all tables and insert sample users\n";
        echo "3. Default login credentials will be:\n";
        echo "   - Admin: ADMIN001 / password\n";
        echo "   - Faculty: FAC001 / password\n";
        echo "   - Faculty: FAC002 / password\n";
    } else {
        echo "1. Database looks good!\n";
        echo "2. Try logging in with existing credentials\n";
        echo "3. If still having issues, check session configuration\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "🔧 SOLUTIONS:\n";
    echo "1. Make sure MySQL/XAMPP is running\n";
    echo "2. Check if 'pokenginang' database exists in phpMyAdmin\n";
    echo "3. If database doesn't exist, create it and run databse1.sql\n";
    echo "4. Verify database credentials in Database.php\n";
}

echo "\n✨ Check complete!\n";
?>
