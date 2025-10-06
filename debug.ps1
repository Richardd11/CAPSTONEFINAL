# 🔧 EXAM UPDATE DEBUGGING SCRIPT
# Usage: .\debug.ps1 [command]

param(
    [string]$Command = "help"
)

$logFile = "C:\xampp\php\logs\php_error_log"

function Show-Help {
    Write-Host "🔧 EXAM UPDATE DEBUGGING COMMANDS" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Usage: .\debug.ps1 [command]" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Available commands:" -ForegroundColor Green
    Write-Host "  server     - Start PHP development server" -ForegroundColor White
    Write-Host "  logs       - Show recent error logs" -ForegroundColor White
    Write-Host "  monitor    - Monitor logs in real-time" -ForegroundColor White
    Write-Host "  clear      - Clear error logs" -ForegroundColor White
    Write-Host "  count      - Count total exams in database" -ForegroundColor White
    Write-Host "  list       - List recent exams" -ForegroundColor White
    Write-Host "  updates    - Show recent UPDATE/CREATE calls" -ForegroundColor White
    Write-Host "  test       - Test update endpoint directly" -ForegroundColor White
    Write-Host "  check      - Check specific exam details" -ForegroundColor White
    Write-Host ""
    Write-Host "Examples:" -ForegroundColor Yellow
    Write-Host "  .\debug.ps1 server" -ForegroundColor Gray
    Write-Host "  .\debug.ps1 monitor" -ForegroundColor Gray
    Write-Host "  .\debug.ps1 count" -ForegroundColor Gray
}

function Start-Server {
    Write-Host "🚀 Starting PHP development server..." -ForegroundColor Green
    Write-Host "Server will be available at: http://localhost:8000" -ForegroundColor Yellow
    Write-Host "Press Ctrl+C to stop" -ForegroundColor Gray
    php -S localhost:8000 -t public
}

function Show-Logs {
    Write-Host "📋 Recent Error Logs (Last 20 lines):" -ForegroundColor Green
    if (Test-Path $logFile) {
        Get-Content $logFile -Tail 20 | ForEach-Object {
            if ($_ -match "ERROR|FAILED") {
                Write-Host $_ -ForegroundColor Red
            } elseif ($_ -match "SUCCESS|UPDATE EXAM") {
                Write-Host $_ -ForegroundColor Green
            } elseif ($_ -match "CREATE EXAM") {
                Write-Host $_ -ForegroundColor Yellow
            } else {
                Write-Host $_ -ForegroundColor White
            }
        }
    } else {
        Write-Host "❌ Log file not found: $logFile" -ForegroundColor Red
    }
}

function Monitor-Logs {
    Write-Host "👁️ Monitoring logs in real-time..." -ForegroundColor Green
    Write-Host "Press Ctrl+C to stop" -ForegroundColor Gray
    if (Test-Path $logFile) {
        Get-Content $logFile -Wait -Tail 5 | ForEach-Object {
            $timestamp = Get-Date -Format "HH:mm:ss"
            if ($_ -match "UPDATE EXAM|CREATE EXAM|Preserving") {
                if ($_ -match "CREATE EXAM") {
                    Write-Host "[$timestamp] $_" -ForegroundColor Yellow
                } elseif ($_ -match "UPDATE EXAM") {
                    Write-Host "[$timestamp] $_" -ForegroundColor Green
                } else {
                    Write-Host "[$timestamp] $_" -ForegroundColor Cyan
                }
            }
        }
    } else {
        Write-Host "❌ Log file not found: $logFile" -ForegroundColor Red
    }
}

function Clear-Logs {
    Write-Host "🗑️ Clearing error logs..." -ForegroundColor Yellow
    if (Test-Path $logFile) {
        Clear-Content $logFile
        Write-Host "✅ Logs cleared" -ForegroundColor Green
    } else {
        Write-Host "❌ Log file not found: $logFile" -ForegroundColor Red
    }
}

function Count-Exams {
    Write-Host "📊 Counting exams in database..." -ForegroundColor Green
    $phpCode = @"
try {
    `$pdo = new PDO('mysql:host=localhost;dbname=exam_system', 'root', '');
    `$stmt = `$pdo->query('SELECT COUNT(*) as count FROM exams');
    `$result = `$stmt->fetch();
    echo 'Total exams: ' . `$result['count'] . PHP_EOL;
} catch (Exception `$e) {
    echo 'Database error: ' . `$e->getMessage() . PHP_EOL;
}
"@
    php -r $phpCode
}

function List-Exams {
    Write-Host "📋 Recent exams:" -ForegroundColor Green
    $phpCode = @"
try {
    `$pdo = new PDO('mysql:host=localhost;dbname=exam_system', 'root', '');
    `$stmt = `$pdo->query('SELECT id, title, year_level, section, created_at FROM exams ORDER BY created_at DESC LIMIT 10');
    `$exams = `$stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach (`$exams as `$exam) {
        echo sprintf('  ID: %d | %s | %s - Section %s | Created: %s', 
            `$exam['id'], 
            substr(`$exam['title'], 0, 30), 
            `$exam['year_level'], 
            `$exam['section'], 
            `$exam['created_at']
        ) . PHP_EOL;
    }
} catch (Exception `$e) {
    echo 'Database error: ' . `$e->getMessage() . PHP_EOL;
}
"@
    php -r $phpCode
}

function Show-Updates {
    Write-Host "🔄 Recent UPDATE/CREATE calls:" -ForegroundColor Green
    if (Test-Path $logFile) {
        Select-String -Path $logFile -Pattern "(CREATE EXAM|UPDATE EXAM)" | Select-Object -Last 10 | ForEach-Object {
            if ($_.Line -match "CREATE EXAM") {
                Write-Host $_.Line -ForegroundColor Yellow
            } else {
                Write-Host $_.Line -ForegroundColor Green
            }
        }
    } else {
        Write-Host "❌ Log file not found: $logFile" -ForegroundColor Red
    }
}

function Test-Update {
    Write-Host "🎯 Testing update endpoint..." -ForegroundColor Green
    
    # Get first exam ID
    $phpCode = @"
try {
    `$pdo = new PDO('mysql:host=localhost;dbname=exam_system', 'root', '');
    `$stmt = `$pdo->query('SELECT id FROM exams LIMIT 1');
    `$result = `$stmt->fetch();
    echo `$result['id'];
} catch (Exception `$e) {
    echo 'ERROR';
}
"@
    
    $examId = php -r $phpCode
    
    if ($examId -eq "ERROR") {
        Write-Host "❌ Could not get exam ID from database" -ForegroundColor Red
        return
    }
    
    Write-Host "📋 Testing with exam ID: $examId" -ForegroundColor Yellow
    
    $updateData = @{
        exam_id = $examId
        title = "TEST UPDATE - $(Get-Date -Format 'HH:mm:ss')"
        description = "Test update from PowerShell"
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
    
    try {
        $response = Invoke-RestMethod -Uri "http://localhost:8000/faculty/exam/$examId/update" -Method POST -Body $updateData -ContentType "application/json"
        Write-Host "✅ Update successful:" -ForegroundColor Green
        Write-Host ($response | ConvertTo-Json) -ForegroundColor White
    } catch {
        Write-Host "❌ Update failed: $($_.Exception.Message)" -ForegroundColor Red
    }
}

function Check-Exam {
    $examId = Read-Host "Enter exam ID to check"
    Write-Host "🔍 Checking exam ID: $examId" -ForegroundColor Green
    
    $phpCode = @"
try {
    `$pdo = new PDO('mysql:host=localhost;dbname=exam_system', 'root', '');
    `$stmt = `$pdo->prepare('SELECT * FROM exams WHERE id = ?');
    `$stmt->execute(['$examId']);
    `$exam = `$stmt->fetch(PDO::FETCH_ASSOC);
    if (`$exam) {
        echo 'Exam Details:' . PHP_EOL;
        foreach (`$exam as `$key => `$value) {
            echo '  ' . `$key . ': ' . `$value . PHP_EOL;
        }
    } else {
        echo 'Exam not found' . PHP_EOL;
    }
} catch (Exception `$e) {
    echo 'Database error: ' . `$e->getMessage() . PHP_EOL;
}
"@
    php -r $phpCode
}

# Main command dispatcher
switch ($Command.ToLower()) {
    "help" { Show-Help }
    "server" { Start-Server }
    "logs" { Show-Logs }
    "monitor" { Monitor-Logs }
    "clear" { Clear-Logs }
    "count" { Count-Exams }
    "list" { List-Exams }
    "updates" { Show-Updates }
    "test" { Test-Update }
    "check" { Check-Exam }
    default { 
        Write-Host "❌ Unknown command: $Command" -ForegroundColor Red
        Write-Host ""
        Show-Help 
    }
}
