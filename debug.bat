@echo off
echo 🔧 EXAM UPDATE DEBUGGING TOOL
echo.

if "%1"=="count" goto count
if "%1"=="logs" goto logs
if "%1"=="clear" goto clear
if "%1"=="monitor" goto monitor
if "%1"=="test" goto test
goto help

:help
echo Available commands:
echo   debug.bat count    - Count total exams
echo   debug.bat logs     - Show recent logs
echo   debug.bat clear    - Clear logs
echo   debug.bat monitor  - Monitor logs (Ctrl+C to stop)
echo   debug.bat test     - Test update
goto end

:count
echo 📊 Counting exams...
php -r "try { $pdo = new PDO('mysql:host=localhost;dbname=exam_system', 'root', ''); $stmt = $pdo->query('SELECT COUNT(*) as count FROM exams'); $result = $stmt->fetch(); echo 'Total exams: ' . $result['count'] . PHP_EOL; $stmt2 = $pdo->query('SELECT id, title, year_level FROM exams ORDER BY created_at DESC LIMIT 5'); echo 'Recent exams:' . PHP_EOL; while ($exam = $stmt2->fetch()) { echo '  ID: ' . $exam['id'] . ' - ' . substr($exam['title'], 0, 30) . ' (' . $exam['year_level'] . ')' . PHP_EOL; } } catch (Exception $e) { echo 'Error: ' . $e->getMessage() . PHP_EOL; }"
goto end

:logs
echo 📋 Recent logs:
powershell -Command "if (Test-Path 'C:\xampp\php\logs\php_error_log') { Get-Content 'C:\xampp\php\logs\php_error_log' -Tail 10 } else { 'No logs found' }"
goto end

:clear
echo 🗑️ Clearing logs...
powershell -Command "if (Test-Path 'C:\xampp\php\logs\php_error_log') { Clear-Content 'C:\xampp\php\logs\php_error_log'; 'Logs cleared' } else { 'No log file found' }"
goto end

:monitor
echo 👁️ Monitoring logs (Press Ctrl+C to stop)...
powershell -Command "Get-Content 'C:\xampp\php\logs\php_error_log' -Wait -Tail 5"
goto end

:test
echo 🎯 Testing update...
curl -X POST -H "Content-Type: application/json" -d "{\"exam_id\":\"142\",\"title\":\"TEST UPDATE\",\"year_level\":\"1st Year\",\"section\":\"A\"}" http://localhost:8000/faculty/exam/142/update
goto end

:end
