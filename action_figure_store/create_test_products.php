<?php
require_once 'includes/db.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "=== 創建測試產品並關聯分類 ===\n";
    
    // 先檢查是否已有產品
    $stmt = $conn->query("SELECT COUNT(*) FROM products");
    $product_count = $stmt->fetchColumn();
    
    if ($product_count > 0) {
        echo "已有 {$product_count} 個產品，跳過創建測試產品\n";
    } else {
        // 創建一些測試產品
        $test_products = [
            ['name' => '鳴人 PVC 公仔', 'description' => '火影忍者主角鳴人的精美 PVC 公仔', 'price' => 1200.00, 'category_id' => 11],
            ['name' => '佐助 手辦模型', 'description' => '宇智波佐助的高品質手辦', 'price' => 1500.00, 'category_id' => 11],
            ['name' => '炭治郎 DX 版公仔', 'description' => '鬼滅之刃主角炭治郎豪華版公仔', 'price' => 1800.00, 'category_id' => 12],
            ['name' => '禰豆子 Q版公仔', 'description' => '可愛的禰豆子 Q 版造型', 'price' => 980.00, 'category_id' => 12],
            ['name' => '義勇 水柱公仔', 'description' => '水柱富岡義勇戰鬥姿態公仔', 'price' => 1650.00, 'category_id' => 12],
            ['name' => '魯夫 橡膠公仔', 'description' => '海賊王主角蒙奇·D·魯夫', 'price' => 1400.00, 'category_id' => 13],
            ['name' => '索隆 三刀流公仔', 'description' => '羅羅諾亞·索隆三刀流姿態', 'price' => 1600.00, 'category_id' => 13],
        ];
        
        foreach ($test_products as $product) {
            // 新增產品
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, status) VALUES (?, ?, ?, 1)");
            $stmt->execute([$product['name'], $product['description'], $product['price']]);
            $product_id = $conn->lastInsertId();
            
            // 關聯分類
            $stmt = $conn->prepare("INSERT INTO product_category (product_id, category_id) VALUES (?, ?)");
            $stmt->execute([$product_id, $product['category_id']]);
            
            echo "✅ 創建產品：{$product['name']} (分類 ID: {$product['category_id']})\n";
        }
    }
    
    // 顯示各分類的產品數量統計
    echo "\n=== 分類產品數量統計 ===\n";
    $stmt = $conn->query("
        SELECT c.id, c.name, COUNT(pc.product_id) as product_count
        FROM categories c
        LEFT JOIN product_category pc ON c.id = pc.category_id
        GROUP BY c.id, c.name
        ORDER BY c.id
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("分類 %-15s (ID: %2d): %2d 個產品\n", 
            $row['name'], 
            $row['id'], 
            $row['product_count']
        );
    }
    
    echo "\n✅ 測試數據準備完成！\n";
    echo "現在可以前往分類管理頁面查看產品數量顯示效果。\n";
    
} catch (Exception $e) {
    echo "❌ 錯誤: " . $e->getMessage() . "\n";
}
?>
