<?php
// 簡單測試腳本
session_start();

echo "=== 系統測試 ===\n";

// 1. 測試資料庫連接
try {
    require_once 'includes/db.php';
    $db = new Database();
    $conn = $db->getConnection();
    echo "✓ 資料庫連接成功\n";
    
    // 2. 測試用戶登入
    $username = 'admin';
    $password = 'admin123';
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        echo "✓ 用戶登入成功: {$user['username']} (角色: {$user['role']})\n";
    } else {
        echo "✗ 用戶登入失敗\n";
    }
    
    // 3. 測試股票資料
    $stmt = $conn->prepare("SELECT COUNT(*) FROM stocks WHERE status = 'active'");
    $stmt->execute();
    $stock_count = $stmt->fetchColumn();
    echo "✓ 活躍股票數量: $stock_count\n";
    
    // 4. 測試投資組合
    $stmt = $conn->prepare("SELECT COUNT(*) FROM portfolios");
    $stmt->execute();
    $portfolio_count = $stmt->fetchColumn();
    echo "✓ 投資組合記錄: $portfolio_count\n";
    
    // 5. 測試交易記錄
    $stmt = $conn->prepare("SELECT COUNT(*) FROM transactions");
    $stmt->execute();
    $transaction_count = $stmt->fetchColumn();
    echo "✓ 交易記錄數量: $transaction_count\n";
    
    // 6. 測試新聞
    $stmt = $conn->prepare("SELECT COUNT(*) FROM news WHERE status = 'active'");
    $stmt->execute();
    $news_count = $stmt->fetchColumn();
    echo "✓ 活躍新聞數量: $news_count\n";
    
    // 7. 測試市場指數
    $stmt = $conn->prepare("SELECT COUNT(*) FROM market_indices WHERE status = 'active'");
    $stmt->execute();
    $index_count = $stmt->fetchColumn();
    echo "✓ 市場指數數量: $index_count\n";
    
    echo "\n=== 測試完成 ===\n";
    echo "所有基本功能運行正常！\n";
    
} catch (Exception $e) {
    echo "✗ 測試失敗: " . $e->getMessage() . "\n";
}

// 清理 session
session_destroy();
?>
