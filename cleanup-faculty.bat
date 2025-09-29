@echo off
echo ========================================
echo Cleaning up Faculty folder...
echo ========================================
echo.

cd src\App\Views\faculty

echo Removing redundant backup files...
del exam-results-backup.php 2>nul
del exam-results-original-backup.php 2>nul
del exam-results-clean.php 2>nul

echo.
echo Backing up current exam-results.php...
copy exam-results.php exam-results.old.php

echo.
echo Applying final iOS-styled version...
copy exam-results-final.php exam-results.php

cd ..\..\..\..

echo.
echo Removing temporary files from root...
del exam-results-fix.js 2>nul
del ios-style-patch.css 2>nul
del subject-organization.js 2>nul
del debug_api.php 2>nul
del test_exam_api.php 2>nul
del fix_exam_results.ps1 2>nul
del add_fix.bat 2>nul
del apply-ios-enhancements.bat 2>nul
del EXAM_RESULTS_FIX_INSTRUCTIONS.md 2>nul
del QUICK_FIX.txt 2>nul
del MANUAL_IOS_INTEGRATION.md 2>nul

echo.
echo ========================================
echo ✅ Cleanup Complete!
echo ========================================
echo.
echo What was done:
echo   • Removed all backup files
echo   • Applied iOS-styled exam results
echo   • Cleaned temporary files
echo   • Organized codebase
echo.
echo The faculty folder is now clean and professional!
echo.
pause
