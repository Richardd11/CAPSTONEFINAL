<?php
// Test database connection
try {
    $host = '127.0.0.1';
    $database = 'pokenginang';
    $username = 'root';
    $password = '';
    $charset = 'utf8mb4';
    
    $dsn = "mysql:host={$host};dbname={$database};charset={$charset}";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    echo "Attempting to connect to database...\n";
    echo "Host: {$host}\n";
    echo "Database: {$database}\n";
    echo "Username: {$username}\n\n";
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    echo "✅ Database connection successful!\n\n";
    
    // Test if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Users table exists!\n";
        
        // Check if there are any users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "📊 Total users in database: " . $result['count'] . "\n";
        
        // Show sample users
        $stmt = $pdo->query("SELECT school_id, full_name, role FROM users LIMIT 5");
        $users = $stmt->fetchAll();
        
        if (!empty($users)) {
            echo "\n👥 Sample users:\n";
            foreach ($users as $user) {
                echo "  - {$user['school_id']} ({$user['full_name']}) - {$user['role']}\n";
            }
        }
    } else {
        echo "❌ Users table does not exist!\n";
        echo "📋 Available tables:\n";
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll();
        foreach ($tables as $table) {
            echo "  - " . array_values($table)[0] . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    echo "🔧 Troubleshooting steps:\n";
    echo "1. Make sure MySQL/XAMPP is running\n";
    echo "2. Check if database 'pokenginang' exists\n";
    echo "3. Verify MySQL credentials (root/no password)\n";
    echo "4. Check if port 3306 is available\n";
}
?>
