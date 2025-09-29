@echo off
echo Applying iOS-style enhancements to exam results...

:: Backup current file
copy "src\App\Views\faculty\exam-results.php" "src\App\Views\faculty\exam-results-before-ios.php"

:: Add CSS link to the head section
powershell -Command "(Get-Content 'src\App\Views\faculty\exam-results.php') -replace '<link href=\"https://fonts.googleapis.com', '<link href=\"<?= dirname($_SERVER[''SCRIPT_NAME'']) ?>/ios-style-patch.css\" rel=\"stylesheet\">`n    <link href=\"https://fonts.googleapis.com' | Set-Content 'src\App\Views\faculty\exam-results.php'"

:: Add JavaScript before closing body tag
powershell -Command "(Get-Content 'src\App\Views\faculty\exam-results.php') -replace '</body>', '<script src=\"<?= dirname($_SERVER[''SCRIPT_NAME'']) ?>/subject-organization.js\"></script>`n</body>' | Set-Content 'src\App\Views\faculty\exam-results.php'"

echo.
echo ✅ iOS-style enhancements applied successfully!
echo.
echo Changes made:
echo   • Added iOS-style CSS with subject organization
echo   • Enhanced JavaScript for subject grouping
echo   • Collapsible subject sections
echo   • Modern card animations
echo.
echo 🔄 Please refresh your browser to see the changes.
echo.
echo Files created:
echo   • ios-style-patch.css (iOS styling)
echo   • subject-organization.js (Subject grouping)
echo   • exam-results-before-ios.php (backup)
echo.
pause
