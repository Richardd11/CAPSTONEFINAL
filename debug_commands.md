# 🔧 EXAM UPDATE DEBUGGING COMMANDS - WINDSURF TERMINAL

## 📋 Quick Copy-Paste Commands for Windsurf Terminal

### 1. 🚀 Start PHP Server
```bash
php -S localhost:8000 -t public
```

### 2. 📊 Check PHP Error Logs (Real-time)
```bash
Get-Content "C:\xampp\php\logs\php_error_log" -Wait -Tail 10
```

### 3. 📋 View Recent Error Logs (Last 20 lines)
```bash
Get-Content "C:\xampp\php\logs\php_error_log" -Tail 20
```

### 4. 🗑️ Clear Error Logs
```bash
Clear-Content "C:\xampp\php\logs\php_error_log"
```

### 5. 🔍 Search for Specific Errors
```bash
Select-String -Path "C:\xampp\php\logs\php_error_log" -Pattern "UPDATE EXAM" | Select-Object -Last 10
```

### 6. 📈 Monitor CREATE vs UPDATE Calls
```bash
Select-String -Path "C:\xampp\php\logs\php_error_log" -Pattern "(CREATE EXAM|UPDATE EXAM)" | Select-Object -Last 15
```

### 7. 🎯 Check Assignment Data Preservation
```bash
Select-String -Path "C:\xampp\php\logs\php_error_log" -Pattern "Preserving assignment data" | Select-Object -Last 5
```

### 8. 🔄 Real-time Monitoring (Run this while testing)
```bash
Get-Content "C:\xampp\php\logs\php_error_log" -Wait | Where-Object { $_ -match "(UPDATE EXAM|CREATE EXAM|Preserving)" }
```

### 9. 📊 Count Total Exams in Database (via PHP)
```bash
php -r "
$pdo = new PDO('mysql:host=localhost;dbname=exam_system', 'root', '');
$stmt = $pdo->query('SELECT COUNT(*) as count FROM exams');
$result = $stmt->fetch();
echo 'Total exams: ' . $result['count'] . PHP_EOL;
"
```

### 10. 🔍 Check Specific Exam Details
```bash
php -r "
$examId = 142; // Change this to the exam ID you want to check
$pdo = new PDO('mysql:host=localhost;dbname=exam_system', 'root', '');
$stmt = $pdo->prepare('SELECT id, title, year_level, section, created_at, updated_at FROM exams WHERE id = ?');
$stmt->execute([$examId]);
$exam = $stmt->fetch(PDO::FETCH_ASSOC);
if ($exam) {
    echo 'Exam Details:' . PHP_EOL;
    foreach ($exam as $key => $value) {
        echo '  ' . $key . ': ' . $value . PHP_EOL;
    }
} else {
    echo 'Exam not found' . PHP_EOL;
}
"
```

### 11. 📋 List All Exams with Year Levels
```bash
php -r "
$pdo = new PDO('mysql:host=localhost;dbname=exam_system', 'root', '');
$stmt = $pdo->query('SELECT id, title, year_level, section, created_at FROM exams ORDER BY created_at DESC LIMIT 10');
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo 'Recent Exams:' . PHP_EOL;
foreach ($exams as $exam) {
    echo sprintf('  ID: %d | %s | %s - Section %s | Created: %s', 
        $exam['id'], 
        substr($exam['title'], 0, 30), 
        $exam['year_level'], 
        $exam['section'], 
        $exam['created_at']
    ) . PHP_EOL;
}
"
```

### 12. 🎯 Test Update Endpoint Directly
```bash
$examId = 142  # Change this to your exam ID
$updateData = @{
    exam_id = $examId
    title = "UPDATED TEST - $(Get-Date -Format 'HH:mm:ss')"
    description = "Test update"
    subject_id = 1
    exam_type = "quiz"
    time_limit = 60
    is_active = 1
    year_level = "1st Year"
    section = "A"
    academic_year = "2024-2025"
    semester = "1st Semester"
    questions = @()
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/faculty/exam/$examId/update" -Method POST -Body $updateData -ContentType "application/json"
```

### 13. 🔄 Monitor File Changes (Optional)
```bash
Get-ChildItem -Path "src/App/Services/Exam/ExamService.php" | ForEach-Object { 
    Write-Host "Last modified: $($_.LastWriteTime)" 
}
```

### 14. 🧹 Clean Up Test Files
```bash
Remove-Item -Path "debug_*.html", "test_*.html" -ErrorAction SilentlyContinue
Write-Host "Cleaned up debug files"
```

---

## 🎯 DEBUGGING WORKFLOW

### Step 1: Start Monitoring
```bash
# Terminal 1: Start server
php -S localhost:8000 -t public

# Terminal 2: Monitor logs in real-time
Get-Content "C:\xampp\php\logs\php_error_log" -Wait -Tail 5
```

### Step 2: Test Update
```bash
# Open browser and go to: http://localhost:8000/faculty/exams
# Edit an exam and save it
```

### Step 3: Check Results
```bash
# Check if CREATE or UPDATE was called
Select-String -Path "C:\xampp\php\logs\php_error_log" -Pattern "(CREATE EXAM|UPDATE EXAM)" | Select-Object -Last 5

# Check assignment data preservation
Select-String -Path "C:\xampp\php\logs\php_error_log" -Pattern "Preserving assignment data" | Select-Object -Last 3

# Count exams before and after
php -r "
$pdo = new PDO('mysql:host=localhost;dbname=exam_system', 'root', '');
$stmt = $pdo->query('SELECT COUNT(*) as count FROM exams');
echo 'Total exams: ' . $stmt->fetch()['count'] . PHP_EOL;
"
```

---

## 🚨 QUICK TROUBLESHOOTING

### If No Logs Appear:
```bash
# Create log directory
New-Item -ItemType Directory -Force -Path "C:\xampp\php\logs"

# Check PHP error logging is enabled
php -r "echo 'Error logging: ' . ini_get('log_errors') . PHP_EOL;"
```

### If Database Connection Fails:
```bash
# Test database connection
php -r "
try {
    $pdo = new PDO('mysql:host=localhost;dbname=exam_system', 'root', '');
    echo 'Database connection: OK' . PHP_EOL;
} catch (Exception $e) {
    echo 'Database error: ' . $e->getMessage() . PHP_EOL;
}
"
```

### If Server Won't Start:
```bash
# Check if port 8000 is in use
netstat -an | findstr :8000

# Use different port if needed
php -S localhost:8001 -t public
```

---

## 📊 EXPECTED LOG OUTPUT (When Working)

```
[03-Oct-2025 23:43:00] UPDATE EXAM CONTROLLER - Received data: {"exam_id":"142",...}
[03-Oct-2025 23:43:00] UPDATE EXAM - Preserving assignment data: {"year_level":"1st Year","section":"A",...}
[03-Oct-2025 23:43:00] UPDATE EXAM SERVICE - DAO update result: SUCCESS
```

## ❌ PROBLEMATIC LOG OUTPUT (When Broken)

```
[03-Oct-2025 23:43:00] CREATE EXAM - Creating new exam with data: {...}
[03-Oct-2025 23:43:00] SAVE EXAM CONTROLLER - Creating new exam
```

---

**Copy paste lang ang mga commands sa Windsurf terminal boss! Real-time monitoring na ni!** 🚀
