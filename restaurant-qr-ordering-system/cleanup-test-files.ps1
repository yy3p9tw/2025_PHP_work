# 清理重複的測試檔案
# 執行前請確保 test-center.html 運作正常

Write-Host "🧹 開始清理重複的測試檔案..." -ForegroundColor Green

$filesToRemove = @(
    "test-suite.html",
    "test-suite-fixed.html", 
    "simple-test.html",
    "dependency-check.html",
    "errorhandler-test.html",
    "performance-monitor.html"
)

$currentPath = Get-Location
Write-Host "當前路徑: $currentPath" -ForegroundColor Yellow

foreach ($file in $filesToRemove) {
    $filePath = Join-Path $currentPath $file
    
    if (Test-Path $filePath) {
        Write-Host "🗑️  準備移除: $file" -ForegroundColor Red
        
        # 創建備份
        $backupPath = Join-Path $currentPath "backup"
        if (!(Test-Path $backupPath)) {
            New-Item -ItemType Directory -Path $backupPath | Out-Null
        }
        
        Copy-Item $filePath (Join-Path $backupPath $file)
        Write-Host "💾 已備份到: backup/$file" -ForegroundColor Cyan
        
        # 移除原檔案
        Remove-Item $filePath
        Write-Host "✅ 已移除: $file" -ForegroundColor Green
    } else {
        Write-Host "⚠️  檔案不存在: $file" -ForegroundColor Yellow
    }
}

Write-Host "`n🎉 清理完成！" -ForegroundColor Green
Write-Host "📋 保留的主要檔案:" -ForegroundColor Cyan
Write-Host "   - test-center.html (主要測試中心)" -ForegroundColor White
Write-Host "   - backup/ (備份資料夾)" -ForegroundColor White

Write-Host "`n💡 如需恢復檔案，請從 backup/ 資料夾複製回來" -ForegroundColor Yellow
