@echo off
echo Starting PHP Development Server with proper routing...
echo.
echo Server will be available at: http://localhost:8000
echo Press Ctrl+C to stop the server
echo.
cd /d "%~dp0"
php -S localhost:8000 -t public public/router.php
pause
