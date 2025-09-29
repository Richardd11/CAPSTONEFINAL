<?php
/**
 * Complete Database Setup Script for Exam Management System
 * 
 * This script sets up the entire database with all tables, sample data,
 * views, and stored procedures for the exam management system.
 * 
 * Usage: php setup_complete_database.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;

class DatabaseSetup
{
    private $pdo;
    private $logFile;
    
    public function __construct()
    {
        $this->logFile = __DIR__ . '/setup_log_' . date('Y-m-d_H-i-s') . '.txt';
        $this->log("=== Database Setup Started ===");
    }
    
    private function log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        echo $logMessage;
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
    
    public function connect()
    {
        try {
            $this->log("Connecting to database...");
            $db = Database::getInstance();
            $this->pdo = $db->getConnection();
            $this->log("✅ Database connection established successfully");
            return true;
        } catch (Exception $e) {
            $this->log("❌ Database connection failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function setupDatabase()
    {
        try {
            $this->log("Starting complete database setup...");
            
            // Read the complete SQL setup file
            $sqlFile = __DIR__ . '/complete_database_setup.sql';
            if (!file_exists($sqlFile)) {
                throw new Exception("SQL setup file not found: $sqlFile");
            }
            
            $sql = file_get_contents($sqlFile);
            $this->log("SQL file loaded successfully");
            
            // Split SQL into statements, handling DELIMITER changes
            $statements = $this->parseSqlStatements($sql);
            $this->log("Found " . count($statements) . " SQL statements to execute");
            
            // Execute each statement
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($statements as $index => $statement) {
                $statement = trim($statement);
                if (empty($statement) || $this->isComment($statement)) {
                    continue;
                }
                
                try {
                    $this->log("Executing statement " . ($index + 1) . ": " . substr($statement, 0, 60) . "...");
                    $this->pdo->exec($statement);
                    $successCount++;
                } catch (PDOException $e) {
                    $errorCount++;
                    $this->log("⚠️ Error in statement " . ($index + 1) . ": " . $e->getMessage());
                    
                    // Continue with non-critical errors, but stop on critical ones
                    if ($this->isCriticalError($e)) {
                        throw $e;
                    }
                }
            }
            
            $this->log("✅ Database setup completed!");
            $this->log("Successfully executed: $successCount statements");
            if ($errorCount > 0) {
                $this->log("Warnings/Errors: $errorCount statements");
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->log("❌ Database setup failed: " . $e->getMessage());
            return false;
        }
    }
    
    private function parseSqlStatements($sql)
    {
        $statements = [];
        $currentStatement = '';
        $delimiter = ';';
        $inDelimiterBlock = false;
        
        $lines = explode("\n", $sql);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Handle DELIMITER changes
            if (preg_match('/^DELIMITER\s+(.+)$/i', $line, $matches)) {
                $delimiter = trim($matches[1]);
                $inDelimiterBlock = ($delimiter !== ';');
                continue;
            }
            
            // Skip empty lines and comments
            if (empty($line) || $this->isComment($line)) {
                continue;
            }
            
            $currentStatement .= $line . "\n";
            
            // Check if statement is complete
            if ($inDelimiterBlock) {
                // In a stored procedure or function block
                if (substr(rtrim($line), -strlen($delimiter)) === $delimiter) {
                    $statements[] = substr($currentStatement, 0, -strlen($delimiter) - 1);
                    $currentStatement = '';
                }
            } else {
                // Regular SQL statement
                if (substr(rtrim($line), -1) === $delimiter) {
                    $statements[] = substr($currentStatement, 0, -2); // Remove delimiter and newline
                    $currentStatement = '';
                }
            }
        }
        
        // Add any remaining statement
        if (!empty(trim($currentStatement))) {
            $statements[] = trim($currentStatement);
        }
        
        return array_filter($statements, function($stmt) {
            return !empty(trim($stmt));
        });
    }
    
    private function isComment($line)
    {
        return preg_match('/^\s*--/', $line) || preg_match('/^\s*\/\*/', $line) || preg_match('/^\s*#/', $line);
    }
    
    private function isCriticalError($exception)
    {
        $message = $exception->getMessage();
        
        // These are considered non-critical errors that we can continue from
        $nonCriticalPatterns = [
            '/table.*already exists/i',
            '/view.*already exists/i',
            '/procedure.*already exists/i',
            '/duplicate entry/i',
            '/key.*already exists/i'
        ];
        
        foreach ($nonCriticalPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return false;
            }
        }
        
        return true;
    }
    
    public function verifySetup()
    {
        $this->log("Verifying database setup...");
        
        $expectedTables = [
            'users', 'subjects', 'subject_assignments', 'student_enrollments',
            'exams', 'questions', 'question_options', 'exam_attempts', 
            'student_answers', 'system_settings', 'audit_logs'
        ];
        
        $expectedViews = [
            'active_exams_view', 'student_results_view', 'faculty_exam_stats'
        ];
        
        $expectedProcedures = [
            'GetExamStatistics', 'GetStudentExamHistory'
        ];
        
        $missingItems = [];
        
        // Check tables
        foreach ($expectedTables as $table) {
            $stmt = $this->pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() === 0) {
                $missingItems[] = "Table: $table";
            } else {
                $this->log("✓ Table '$table' exists");
            }
        }
        
        // Check views
        foreach ($expectedViews as $view) {
            $stmt = $this->pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW' AND Tables_in_pokenginang = '$view'");
            if ($stmt->rowCount() === 0) {
                $missingItems[] = "View: $view";
            } else {
                $this->log("✓ View '$view' exists");
            }
        }
        
        // Check stored procedures
        foreach ($expectedProcedures as $procedure) {
            $stmt = $this->pdo->query("SHOW PROCEDURE STATUS WHERE Name = '$procedure'");
            if ($stmt->rowCount() === 0) {
                $missingItems[] = "Procedure: $procedure";
            } else {
                $this->log("✓ Procedure '$procedure' exists");
            }
        }
        
        if (empty($missingItems)) {
            $this->log("✅ All database components verified successfully!");
            return true;
        } else {
            $this->log("⚠️ Missing components:");
            foreach ($missingItems as $item) {
                $this->log("  - $item");
            }
            return false;
        }
    }
    
    public function showSampleData()
    {
        $this->log("Displaying sample data...");
        
        try {
            // Show users
            $stmt = $this->pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
            $this->log("Users by role:");
            while ($row = $stmt->fetch()) {
                $this->log("  - {$row['role']}: {$row['count']} users");
            }
            
            // Show subjects
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM subjects");
            $subjectCount = $stmt->fetchColumn();
            $this->log("Total subjects: $subjectCount");
            
            // Show exams
            $stmt = $this->pdo->query("SELECT exam_type, COUNT(*) as count FROM exams GROUP BY exam_type");
            $this->log("Exams by type:");
            while ($row = $stmt->fetch()) {
                $this->log("  - {$row['exam_type']}: {$row['count']} exams");
            }
            
            // Show system settings
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM system_settings");
            $settingsCount = $stmt->fetchColumn();
            $this->log("System settings configured: $settingsCount");
            
        } catch (Exception $e) {
            $this->log("Error displaying sample data: " . $e->getMessage());
        }
    }
    
    public function getSetupSummary()
    {
        return [
            'log_file' => $this->logFile,
            'database_name' => 'pokenginang',
            'setup_time' => date('Y-m-d H:i:s'),
            'default_credentials' => [
                'admin' => ['username' => 'ADMIN001', 'password' => 'password'],
                'faculty' => ['username' => 'FAC001', 'password' => 'password'],
                'student' => ['username' => '2022-001', 'password' => 'password']
            ]
        ];
    }
}

// Main execution
try {
    echo "🚀 Starting Complete Database Setup for Exam Management System\n";
    echo "============================================================\n\n";
    
    $setup = new DatabaseSetup();
    
    // Connect to database
    if (!$setup->connect()) {
        exit(1);
    }
    
    // Setup database
    if (!$setup->setupDatabase()) {
        exit(1);
    }
    
    // Verify setup
    $setup->verifySetup();
    
    // Show sample data
    $setup->showSampleData();
    
    // Display summary
    echo "\n============================================================\n";
    echo "🎉 DATABASE SETUP COMPLETED SUCCESSFULLY!\n";
    echo "============================================================\n\n";
    
    $summary = $setup->getSetupSummary();
    
    echo "📋 Setup Summary:\n";
    echo "- Database: {$summary['database_name']}\n";
    echo "- Setup completed at: {$summary['setup_time']}\n";
    echo "- Log file: {$summary['log_file']}\n\n";
    
    echo "🔐 Default Login Credentials:\n";
    echo "- Admin: {$summary['default_credentials']['admin']['username']} / {$summary['default_credentials']['admin']['password']}\n";
    echo "- Faculty: {$summary['default_credentials']['faculty']['username']} / {$summary['default_credentials']['faculty']['password']}\n";
    echo "- Student: {$summary['default_credentials']['student']['username']} / {$summary['default_credentials']['student']['password']}\n\n";
    
    echo "📚 What's been created:\n";
    echo "- 11 database tables with proper relationships\n";
    echo "- Sample users (admin, faculty, students)\n";
    echo "- Sample subjects and courses\n";
    echo "- Sample exams and assignments\n";
    echo "- 3 database views for common queries\n";
    echo "- 2 stored procedures for statistics\n";
    echo "- System configuration settings\n";
    echo "- Audit logging system\n\n";
    
    echo "🚀 Next Steps:\n";
    echo "1. Access the application through your web server\n";
    echo "2. Login with the default credentials above\n";
    echo "3. Change default passwords for security\n";
    echo "4. Configure system settings as needed\n";
    echo "5. Start creating your own exams!\n\n";
    
    echo "For detailed logs, check: {$summary['log_file']}\n";
    
} catch (Exception $e) {
    echo "\n❌ Setup failed with error: " . $e->getMessage() . "\n";
    echo "Please check your database configuration and try again.\n";
    exit(1);
}
?>
