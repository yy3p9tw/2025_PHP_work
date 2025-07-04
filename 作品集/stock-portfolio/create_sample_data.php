<?php
// 建立示例資料腳本
require_once 'includes/db.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "=== 建立示例資料 ===\n";
    
    // 1. 為管理員用戶建立示例投資組合
    $admin_user_id = 1;
    
    // 清除現有投資組合
    $stmt = $conn->prepare("DELETE FROM portfolios WHERE user_id = ?");
    $stmt->execute([$admin_user_id]);
    
    $stmt = $conn->prepare("DELETE FROM transactions WHERE user_id = ?");
    $stmt->execute([$admin_user_id]);
    
    echo "✓ 清除現有資料\n";
    
    // 2. 建立示例交易記錄
    $sample_transactions = [
        ['2330', 'buy', 100, 450.00],
        ['2317', 'buy', 200, 95.00],
        ['2454', 'buy', 50, 650.00],
        ['0050', 'buy', 80, 120.00],
        ['AAPL', 'buy', 10, 145.00],
        ['2330', 'sell', 20, 480.00],
        ['2317', 'buy', 100, 98.00],
    ];
    
    $conn->beginTransaction();
    
    foreach ($sample_transactions as $transaction) {
        [$stock_code, $type, $quantity, $price] = $transaction;
        $total_amount = $quantity * $price;
        
        // 新增交易記錄
        $stmt = $conn->prepare("
            INSERT INTO transactions (user_id, stock_code, type, quantity, price, total_amount, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW() - INTERVAL FLOOR(RAND() * 30) DAY)
        ");
        $stmt->execute([$admin_user_id, $stock_code, $type, $quantity, $price, $total_amount]);
        
        // 更新投資組合
        $stmt = $conn->prepare("SELECT * FROM portfolios WHERE user_id = ? AND stock_code = ?");
        $stmt->execute([$admin_user_id, $stock_code]);
        $portfolio = $stmt->fetch();
        
        if ($portfolio) {
            $current_quantity = $portfolio['quantity'];
            $current_avg_price = $portfolio['avg_price'];
            
            if ($type == 'buy') {
                $new_quantity = $current_quantity + $quantity;
                $new_avg_price = (($current_quantity * $current_avg_price) + ($quantity * $price)) / $new_quantity;
            } else {
                $new_quantity = $current_quantity - $quantity;
                $new_avg_price = $current_avg_price;
            }
            
            if ($new_quantity > 0) {
                $stmt = $conn->prepare("
                    UPDATE portfolios 
                    SET quantity = ?, avg_price = ?, updated_at = NOW() 
                    WHERE user_id = ? AND stock_code = ?
                ");
                $stmt->execute([$new_quantity, $new_avg_price, $admin_user_id, $stock_code]);
            } else {
                $stmt = $conn->prepare("DELETE FROM portfolios WHERE user_id = ? AND stock_code = ?");
                $stmt->execute([$admin_user_id, $stock_code]);
            }
        } else {
            if ($type == 'buy') {
                $stmt = $conn->prepare("
                    INSERT INTO portfolios (user_id, stock_code, quantity, avg_price)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$admin_user_id, $stock_code, $quantity, $price]);
            }
        }
    }
    
    $conn->commit();
    echo "✓ 建立示例交易記錄和投資組合\n";
    
    // 3. 建立示例關注清單
    $watchlist_stocks = ['GOOGL', 'MSFT', 'TSLA'];
    
    foreach ($watchlist_stocks as $stock_code) {
        $stmt = $conn->prepare("
            INSERT IGNORE INTO watchlist (user_id, stock_code)
            VALUES (?, ?)
        ");
        $stmt->execute([$admin_user_id, $stock_code]);
    }
    
    echo "✓ 建立示例關注清單\n";
    
    // 4. 顯示結果
    $stmt = $conn->prepare("SELECT COUNT(*) FROM portfolios WHERE user_id = ?");
    $stmt->execute([$admin_user_id]);
    $portfolio_count = $stmt->fetchColumn();
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ?");
    $stmt->execute([$admin_user_id]);
    $transaction_count = $stmt->fetchColumn();
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM watchlist WHERE user_id = ?");
    $stmt->execute([$admin_user_id]);
    $watchlist_count = $stmt->fetchColumn();
    
    echo "\n=== 示例資料建立完成 ===\n";
    echo "投資組合記錄: $portfolio_count\n";
    echo "交易記錄: $transaction_count\n";
    echo "關注清單: $watchlist_count\n";
    
    echo "\n現在可以使用 admin/admin123 登入系統測試所有功能！\n";
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo "✗ 建立示例資料失敗: " . $e->getMessage() . "\n";
}
?>
