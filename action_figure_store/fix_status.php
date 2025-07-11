<?php
require_once 'includes/db.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "=== 統一狀態格式 ===\n";
    
    // 將所有狀態為 1 的分類改為 'active'
    $stmt = $conn->prepare('UPDATE categories SET status = ? WHERE status = ?');
    $stmt->execute(['active', '1']);
    echo '更新了 ' . $stmt->rowCount() . ' 個分類狀態為 active' . "\n";
    
    // 將所有狀態為 0 的分類改為 'inactive'
    $stmt = $conn->prepare('UPDATE categories SET status = ? WHERE status = ?');
    $stmt->execute(['inactive', '0']);
    echo '更新了 ' . $stmt->rowCount() . ' 個分類狀態為 inactive' . "\n";
    
    // 同樣處理商品
    $stmt = $conn->prepare('UPDATE products SET status = ? WHERE status = ?');
    $stmt->execute(['active', '1']);
    echo '更新了 ' . $stmt->rowCount() . ' 個商品狀態為 active' . "\n";
    
    $stmt = $conn->prepare('UPDATE products SET status = ? WHERE status = ?');
    $stmt->execute(['inactive', '0']);
    echo '更新了 ' . $stmt->rowCount() . ' 個商品狀態為 inactive' . "\n";
    
    // 啟用新建立的測試資料
    $new_categories = ['動漫公仔', '鋼彈模型', '手辦模型', '盒玩系列', '一番賞', '景品公仔', 'PVC模型', 'RG系列', 'HG系列', 'MG系列'];
    foreach ($new_categories as $cat_name) {
        $stmt = $conn->prepare('UPDATE categories SET status = ? WHERE name = ?');
        $stmt->execute(['active', $cat_name]);
    }
    echo "啟用新建立的分類\n";
    
    $new_products = [
        '海賊王 路飛 公仔', '鬼滅之刃 炭治郎 模型', '新世紀福音戰士 初號機 RG', 
        '獵人 小傑 景品公仔', '自由鋼彈 MG版', '進擊的巨人 兵長 手辦'
    ];
    foreach ($new_products as $prod_name) {
        $stmt = $conn->prepare('UPDATE products SET status = ? WHERE name = ?');
        $stmt->execute(['active', $prod_name]);
    }
    echo "啟用新建立的商品\n";
    
    echo "\n=== 狀態統一完成 ===\n";
    
} catch (Exception $e) {
    echo "錯誤: " . $e->getMessage() . "\n";
}
?>
