<?php
/**
 * Database Verification and Health Check Script
 * 
 * This script verifies the database connection, checks table integrity,
 * and provides a health report for the exam management system.
 * 
 * Usage: php verify_database.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;

class DatabaseVerifier
{
    private $pdo;
    private $results = [];
    
    public function __construct()
    {
        $this->results = [
            'connection' => false,
            'tables' => [],
            'views' => [],
            'procedures' => [],
            'data_integrity' => [],
            'performance' => [],
            'recommendations' => []
        ];
    }
    
    public function runFullCheck()
    {
        echo "🔍 Database Health Check for Exam Management System\n";
        echo "==================================================\n\n";
        
        $this->checkConnection();
        $this->checkTables();
        $this->checkViews();
        $this->checkProcedures();
        $this->checkDataIntegrity();
        $this->checkPerformance();
        $this->generateReport();
        
        return $this->results;
    }
    
    private function checkConnection()
    {
        echo "📡 Checking database connection...\n";
        
        try {
            $db = Database::getInstance();
            $this->pdo = $db->getConnection();
            
            // Test connection with a simple query
            $stmt = $this->pdo->query("SELECT 1");
            $result = $stmt->fetchColumn();
            
            if ($result === 1) {
                $this->results['connection'] = true;
                echo "✅ Database connection successful\n";
                
                // Get database info
                $stmt = $this->pdo->query("SELECT DATABASE() as db_name, VERSION() as version");
                $info = $stmt->fetch();
                echo "   Database: {$info['db_name']}\n";
                echo "   MySQL Version: {$info['version']}\n";
            }
            
        } catch (Exception $e) {
            $this->results['connection'] = false;
            echo "❌ Database connection failed: " . $e->getMessage() . "\n";
            return false;
        }
        
        echo "\n";
        return true;
    }
    
    private function checkTables()
    {
        echo "📋 Checking database tables...\n";
        
        $expectedTables = [
            'users' => 'User management and authentication',
            'subjects' => 'Course and subject information',
            'subject_assignments' => 'Faculty-subject assignments',
            'student_enrollments' => 'Student course enrollments',
            'exams' => 'Exam configurations and settings',
            'questions' => 'Exam questions and content',
            'question_options' => 'Multiple choice options',
            'exam_attempts' => 'Student exam attempts tracking',
            'student_answers' => 'Individual question responses',
            'system_settings' => 'Application configuration',
            'audit_logs' => 'System activity logging'
        ];
        
        foreach ($expectedTables as $table => $description) {
            try {
                $stmt = $this->pdo->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() > 0) {
                    // Table exists, check structure
                    $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM $table");
                    $count = $stmt->fetchColumn();
                    
                    $this->results['tables'][$table] = [
                        'exists' => true,
                        'record_count' => $count,
                        'description' => $description
                    ];
                    
                    echo "✅ $table ($count records) - $description\n";
                } else {
                    $this->results['tables'][$table] = [
                        'exists' => false,
                        'record_count' => 0,
                        'description' => $description
                    ];
                    echo "❌ $table - Missing table\n";
                }
            } catch (Exception $e) {
                echo "⚠️ $table - Error checking: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n";
    }
    
    private function checkViews()
    {
        echo "👁️ Checking database views...\n";
        
        $expectedViews = [
            'active_exams_view' => 'Active exams with faculty and subject info',
            'student_results_view' => 'Student exam results and grades',
            'faculty_exam_stats' => 'Faculty examination statistics'
        ];
        
        foreach ($expectedViews as $view => $description) {
            try {
                $stmt = $this->pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW' AND Tables_in_pokenginang = '$view'");
                if ($stmt->rowCount() > 0) {
                    // Test view by selecting from it
                    $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM $view");
                    $count = $stmt->fetchColumn();
                    
                    $this->results['views'][$view] = [
                        'exists' => true,
                        'record_count' => $count,
                        'description' => $description
                    ];
                    
                    echo "✅ $view ($count records) - $description\n";
                } else {
                    $this->results['views'][$view] = [
                        'exists' => false,
                        'record_count' => 0,
                        'description' => $description
                    ];
                    echo "❌ $view - Missing view\n";
                }
            } catch (Exception $e) {
                echo "⚠️ $view - Error checking: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n";
    }
    
    private function checkProcedures()
    {
        echo "⚙️ Checking stored procedures...\n";
        
        $expectedProcedures = [
            'GetExamStatistics' => 'Calculate comprehensive exam statistics',
            'GetStudentExamHistory' => 'Retrieve student exam history with grades'
        ];
        
        foreach ($expectedProcedures as $procedure => $description) {
            try {
                $stmt = $this->pdo->query("SHOW PROCEDURE STATUS WHERE Name = '$procedure'");
                if ($stmt->rowCount() > 0) {
                    $this->results['procedures'][$procedure] = [
                        'exists' => true,
                        'description' => $description
                    ];
                    echo "✅ $procedure - $description\n";
                } else {
                    $this->results['procedures'][$procedure] = [
                        'exists' => false,
                        'description' => $description
                    ];
                    echo "❌ $procedure - Missing procedure\n";
                }
            } catch (Exception $e) {
                echo "⚠️ $procedure - Error checking: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n";
    }
    
    private function checkDataIntegrity()
    {
        echo "🔍 Checking data integrity...\n";
        
        $checks = [
            'orphaned_questions' => [
                'query' => "SELECT COUNT(*) FROM questions q LEFT JOIN exams e ON q.exam_id = e.id WHERE e.id IS NULL",
                'description' => 'Questions without valid exams'
            ],
            'orphaned_options' => [
                'query' => "SELECT COUNT(*) FROM question_options qo LEFT JOIN questions q ON qo.question_id = q.id WHERE q.id IS NULL",
                'description' => 'Question options without valid questions'
            ],
            'orphaned_attempts' => [
                'query' => "SELECT COUNT(*) FROM exam_attempts ea LEFT JOIN exams e ON ea.exam_id = e.id WHERE e.id IS NULL",
                'description' => 'Exam attempts without valid exams'
            ],
            'orphaned_answers' => [
                'query' => "SELECT COUNT(*) FROM student_answers sa LEFT JOIN exam_attempts ea ON sa.attempt_id = ea.id WHERE ea.id IS NULL",
                'description' => 'Student answers without valid attempts'
            ],
            'users_without_role' => [
                'query' => "SELECT COUNT(*) FROM users WHERE role IS NULL OR role = ''",
                'description' => 'Users without assigned roles'
            ],
            'exams_without_questions' => [
                'query' => "SELECT COUNT(*) FROM exams e LEFT JOIN questions q ON e.id = q.exam_id WHERE q.id IS NULL",
                'description' => 'Exams without any questions'
            ]
        ];
        
        foreach ($checks as $check => $config) {
            try {
                $stmt = $this->pdo->query($config['query']);
                $count = $stmt->fetchColumn();
                
                $this->results['data_integrity'][$check] = [
                    'count' => $count,
                    'description' => $config['description']
                ];
                
                if ($count == 0) {
                    echo "✅ {$config['description']}: None found\n";
                } else {
                    echo "⚠️ {$config['description']}: $count found\n";
                    $this->results['recommendations'][] = "Fix $count {$config['description']}";
                }
            } catch (Exception $e) {
                echo "❌ Error checking {$config['description']}: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n";
    }
    
    private function checkPerformance()
    {
        echo "⚡ Checking database performance...\n";
        
        try {
            // Check table sizes
            $stmt = $this->pdo->query("
                SELECT 
                    table_name,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                    table_rows
                FROM information_schema.TABLES 
                WHERE table_schema = DATABASE()
                ORDER BY (data_length + index_length) DESC
            ");
            
            echo "📊 Table sizes:\n";
            while ($row = $stmt->fetch()) {
                echo "   {$row['table_name']}: {$row['size_mb']} MB ({$row['table_rows']} rows)\n";
                
                $this->results['performance'][$row['table_name']] = [
                    'size_mb' => $row['size_mb'],
                    'rows' => $row['table_rows']
                ];
            }
            
            // Check for missing indexes (basic check)
            $indexChecks = [
                "SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'exam_attempts' AND index_name = 'idx_student_id'" => 'exam_attempts.student_id index',
                "SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'questions' AND index_name = 'idx_exam_id'" => 'questions.exam_id index',
                "SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'student_answers' AND index_name = 'idx_attempt_id'" => 'student_answers.attempt_id index'
            ];
            
            echo "\n🔍 Index verification:\n";
            foreach ($indexChecks as $query => $description) {
                $stmt = $this->pdo->query($query);
                $exists = $stmt->fetchColumn() > 0;
                
                if ($exists) {
                    echo "✅ $description exists\n";
                } else {
                    echo "⚠️ $description missing\n";
                    $this->results['recommendations'][] = "Add missing index: $description";
                }
            }
            
        } catch (Exception $e) {
            echo "❌ Error checking performance: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function generateReport()
    {
        echo "📋 HEALTH CHECK SUMMARY\n";
        echo "======================\n\n";
        
        // Connection status
        if ($this->results['connection']) {
            echo "✅ Database Connection: OK\n";
        } else {
            echo "❌ Database Connection: FAILED\n";
            return;
        }
        
        // Table status
        $totalTables = count($this->results['tables']);
        $existingTables = count(array_filter($this->results['tables'], function($table) {
            return $table['exists'];
        }));
        echo "📋 Tables: $existingTables/$totalTables present\n";
        
        // View status
        $totalViews = count($this->results['views']);
        $existingViews = count(array_filter($this->results['views'], function($view) {
            return $view['exists'];
        }));
        echo "👁️ Views: $existingViews/$totalViews present\n";
        
        // Procedure status
        $totalProcedures = count($this->results['procedures']);
        $existingProcedures = count(array_filter($this->results['procedures'], function($proc) {
            return $proc['exists'];
        }));
        echo "⚙️ Procedures: $existingProcedures/$totalProcedures present\n";
        
        // Data integrity issues
        $integrityIssues = array_sum(array_column($this->results['data_integrity'], 'count'));
        if ($integrityIssues == 0) {
            echo "✅ Data Integrity: No issues found\n";
        } else {
            echo "⚠️ Data Integrity: $integrityIssues issues found\n";
        }
        
        // Recommendations
        if (!empty($this->results['recommendations'])) {
            echo "\n💡 RECOMMENDATIONS:\n";
            foreach ($this->results['recommendations'] as $recommendation) {
                echo "   • $recommendation\n";
            }
        } else {
            echo "\n✅ No recommendations - database is healthy!\n";
        }
        
        // Overall status
        $overallHealth = ($existingTables == $totalTables && 
                         $existingViews == $totalViews && 
                         $existingProcedures == $totalProcedures && 
                         $integrityIssues == 0);
        
        echo "\n";
        if ($overallHealth) {
            echo "🎉 OVERALL STATUS: HEALTHY ✅\n";
            echo "Your exam management system database is ready to use!\n";
        } else {
            echo "⚠️ OVERALL STATUS: NEEDS ATTENTION\n";
            echo "Please address the issues above before using the system.\n";
        }
    }
    
    public function getDetailedResults()
    {
        return $this->results;
    }
}

// Main execution
try {
    $verifier = new DatabaseVerifier();
    $results = $verifier->runFullCheck();
    
    // Save results to file for reference
    $reportFile = __DIR__ . '/database_health_report_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($reportFile, json_encode($results, JSON_PRETTY_PRINT));
    
    echo "\n📄 Detailed report saved to: $reportFile\n";
    
} catch (Exception $e) {
    echo "\n❌ Health check failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
