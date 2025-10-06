# PowerShell script to start the server with proper routing
Write-Host "Starting PHP Development Server with proper routing..." -ForegroundColor Green
Write-Host ""
Write-Host "Server will be available at: http://localhost:8000" -ForegroundColor Yellow
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host ""

# Change to the exam-main directory (project root)
Set-Location $PSScriptRoot
php -S localhost:8000 -t public public/simple_router.php
