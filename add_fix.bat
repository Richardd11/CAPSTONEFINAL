@echo off
echo Adding enhanced JavaScript to exam-results.php...

:: Create a temporary file with the script tag
echo     ^<script src="^<?= dirname($_SERVER['SCRIPT_NAME']) ?^>/exam-results-fix.js"^>^</script^> > temp_script.txt

:: Find the line number of the CSS link
findstr /n "faculty-shared.css" "src\App\Views\faculty\exam-results.php" > temp_line.txt

:: Read the content and add the script tag after the CSS link
powershell -Command "$content = Get-Content 'src\App\Views\faculty\exam-results.php'; $lineNum = (Select-String -Path 'src\App\Views\faculty\exam-results.php' -Pattern 'faculty-shared.css').LineNumber; $newContent = $content[0..($lineNum-1)] + '    <script src=\"<?= dirname($_SERVER[''SCRIPT_NAME'']) ?>/exam-results-fix.js\"></script>' + $content[$lineNum..($content.Length-1)]; $newContent | Set-Content 'src\App\Views\faculty\exam-results.php'"

:: Clean up
del temp_script.txt 2>nul
del temp_line.txt 2>nul

echo.
echo ✅ Enhanced JavaScript added successfully!
echo 🔄 Please refresh your browser to see the fix.
echo.
echo The enhanced script will:
echo   • Add timeout protection
echo   • Show detailed error messages  
echo   • Provide retry functionality
echo   • Display debugging information
pause
