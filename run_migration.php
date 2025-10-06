<?php
/**
 * Database Migration Runner
 * Run this to create the AI grading tables
 */

echo "🗄️  Running AI Essay Grading Database Migration...\n\n";

try {
    // Database connection
    $host = '127.0.0.1';
    $dbname = 'pokenginang';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database: $dbname\n\n";
    
    // Read migration file
    $migrationFile = __DIR__ . '/database/migrations/ai_essay_grading_schema.sql';
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    echo "📄 Read migration file: " . basename($migrationFile) . "\n\n";
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );
    
    echo "🔄 Executing " . count($statements) . " SQL statements...\n\n";
    
    $successCount = 0;
    foreach ($statements as $statement) {
        if (empty(trim($statement))) continue;
        
        try {
            $pdo->exec($statement);
            $successCount++;
            
            // Show what we're creating
            if (preg_match('/CREATE TABLE.*?`([^`]+)`/i', $statement, $matches)) {
                echo "✅ Created table: " . $matches[1] . "\n";
            } elseif (preg_match('/ALTER TABLE.*?`([^`]+)`/i', $statement, $matches)) {
                echo "✅ Modified table: " . $matches[1] . "\n";
            } elseif (preg_match('/CREATE INDEX.*?`([^`]+)`/i', $statement, $matches)) {
                echo "✅ Created index: " . $matches[1] . "\n";
            } elseif (preg_match('/CREATE.*?VIEW.*?`([^`]+)`/i', $statement, $matches)) {
                echo "✅ Created view: " . $matches[1] . "\n";
            } elseif (preg_match('/CREATE.*?TRIGGER.*?`([^`]+)`/i', $statement, $matches)) {
                echo "✅ Created trigger: " . $matches[1] . "\n";
            } else {
                echo "✅ Executed SQL statement\n";
            }
        } catch (PDOException $e) {
            // Check if it's just a "already exists" error
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "⚠️  Already exists (skipping)\n";
            } else {
                echo "❌ Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n🎉 Migration completed successfully!\n";
    echo "📊 Executed $successCount statements\n\n";
    
    // Verify tables were created
    echo "🔍 Verifying tables...\n";
    $tables = ['faculty_score_overrides', 'ai_grading_results'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table exists: $table\n";
        } else {
            echo "❌ Table missing: $table\n";
        }
    }
    
    // Check if columns were added
    echo "\n🔍 Verifying columns...\n";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM questions LIKE 'ai_config'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Column exists: questions.ai_config\n";
    } else {
        echo "❌ Column missing: questions.ai_config\n";
    }
    
    $stmt = $pdo->query("SHOW COLUMNS FROM student_answers LIKE 'score'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Column exists: student_answers.score\n";
    } else {
        echo "❌ Column missing: student_answers.score\n";
    }
    
    echo "\n🚀 Your AI Essay Grading System is now ready!\n";
    echo "📝 Next steps:\n";
    echo "1. Create an essay question with AI grading enabled\n";
    echo "2. Test with student submissions\n";
    echo "3. Check the override functionality\n\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "\n🔧 Troubleshooting:\n";
    echo "1. Check database connection settings\n";
    echo "2. Ensure MySQL is running\n";
    echo "3. Verify database 'pokenginang' exists\n";
    echo "4. Check user permissions\n\n";
}
?>
