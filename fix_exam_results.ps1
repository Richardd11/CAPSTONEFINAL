# PowerShell script to fix the exam results loading issue
$filePath = "src\App\Views\faculty\exam-results.php"
$scriptTag = '    <script src="<?= dirname($_SERVER[''SCRIPT_NAME'']) ?>/exam-results-fix.js"></script>'

# Read the file content
$content = Get-Content $filePath -Raw

# Find the position to insert the script tag (after the CSS link)
$insertAfter = '<link href="<?= dirname($_SERVER[''SCRIPT_NAME'']) ?>/assets/css/faculty-shared.css" rel="stylesheet">'
$insertPosition = $content.IndexOf($insertAfter)

if ($insertPosition -ne -1) {
    # Calculate position after the CSS link line
    $endOfLine = $content.IndexOf("`n", $insertPosition) + 1
    
    # Insert the script tag
    $newContent = $content.Substring(0, $endOfLine) + $scriptTag + "`n" + $content.Substring($endOfLine)
    
    # Write back to file
    $newContent | Set-Content $filePath -NoNewline
    
    Write-Host "✅ Successfully added enhanced JavaScript to exam-results.php" -ForegroundColor Green
    Write-Host "📄 Script tag added: $scriptTag" -ForegroundColor Yellow
    Write-Host "🔄 Please refresh your browser to see the fix" -ForegroundColor Cyan
} else {
    Write-Host "❌ Could not find the CSS link to insert after" -ForegroundColor Red
    Write-Host "📝 Please manually add this line after the CSS link:" -ForegroundColor Yellow
    Write-Host $scriptTag -ForegroundColor White
}

Write-Host "`n🔧 Fix applied! The enhanced JavaScript will:" -ForegroundColor Magenta
Write-Host "   • Add 15-second timeout protection" -ForegroundColor White
Write-Host "   • Show detailed error messages" -ForegroundColor White  
Write-Host "   • Provide retry functionality" -ForegroundColor White
Write-Host "   • Display debugging information" -ForegroundColor White
