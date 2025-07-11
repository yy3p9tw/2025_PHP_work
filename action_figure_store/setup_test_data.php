<?php
require_once 'includes/db.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "=== 設定測試分類資料 ===\n";
    
    // 插入測試分類 (如果不存在)
    $categories = [
        ['name' => '動漫公仔', 'description' => '日本動漫角色精品公仔', 'parent_id' => null, 'sort_order' => 1],
        ['name' => '鋼彈模型', 'description' => '高品質鋼彈模型系列', 'parent_id' => null, 'sort_order' => 2],
        ['name' => '手辦模型', 'description' => '精緻手工製作模型', 'parent_id' => null, 'sort_order' => 3],
        ['name' => '盒玩系列', 'description' => '驚喜盒裝公仔', 'parent_id' => null, 'sort_order' => 4],
        
        // 動漫公仔子分類
        ['name' => '一番賞', 'description' => '一番賞限定公仔', 'parent_id' => 1, 'sort_order' => 1],
        ['name' => '景品公仔', 'description' => '夾娃娃機景品', 'parent_id' => 1, 'sort_order' => 2],
        ['name' => 'PVC模型', 'description' => 'PVC材質精品模型', 'parent_id' => 1, 'sort_order' => 3],
        
        // 鋼彈模型子分類
        ['name' => 'RG系列', 'description' => 'Real Grade 系列', 'parent_id' => 2, 'sort_order' => 1],
        ['name' => 'HG系列', 'description' => 'High Grade 系列', 'parent_id' => 2, 'sort_order' => 2],
        ['name' => 'MG系列', 'description' => 'Master Grade 系列', 'parent_id' => 2, 'sort_order' => 3],
    ];
    
    foreach ($categories as $category) {
        // 檢查是否已存在
        $check_stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
        $check_stmt->execute([$category['name']]);
        
        if (!$check_stmt->fetch()) {
            $stmt = $conn->prepare("INSERT INTO categories (name, description, parent_id, sort_order, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->execute([
                $category['name'],
                $category['description'],
                $category['parent_id'],
                $category['sort_order']
            ]);
            echo "新增分類: {$category['name']}\n";
        } else {
            // 更新為 active 狀態
            $update_stmt = $conn->prepare("UPDATE categories SET status = 'active' WHERE name = ?");
            $update_stmt->execute([$category['name']]);
            echo "更新分類狀態: {$category['name']}\n";
        }
    }
    
    echo "\n=== 設定測試商品資料 ===\n";
    
    // 插入測試商品 (如果不存在)
    $products = [
        ['name' => '海賊王 路飛 公仔', 'description' => '草帽海賊團船長蒙其·D·路飞精品公仔', 'price' => 1200.00, 'image' => 'luffy.jpg'],
        ['name' => '鬼滅之刃 炭治郎 模型', 'description' => '竈門炭治郎戰鬥姿態模型', 'price' => 980.00, 'image' => 'tanjiro.jpg'],
        ['name' => '新世紀福音戰士 初號機 RG', 'description' => 'RG系列 EVA初號機模型', 'price' => 1500.00, 'image' => 'eva01.jpg'],
        ['name' => '獵人 小傑 景品公仔', 'description' => '小傑·富力士夾娃娃機景品', 'price' => 650.00, 'image' => 'gon.jpg'],
        ['name' => '自由鋼彈 MG版', 'description' => 'MG 1/100 ZGMF-X10A Freedom 模型', 'price' => 2200.00, 'image' => 'freedom.jpg'],
        ['name' => '進擊的巨人 兵長 手辦', 'description' => '里維·阿卡曼精緻手辦模型', 'price' => 1800.00, 'image' => 'levi.jpg']
    ];
    
    foreach ($products as $product) {
        // 檢查是否已存在
        $check_stmt = $conn->prepare("SELECT id FROM products WHERE name = ?");
        $check_stmt->execute([$product['name']]);
        
        if (!$check_stmt->fetch()) {
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, image_url, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->execute([
                $product['name'],
                $product['description'],
                $product['price'],
                $product['image']
            ]);
            echo "新增商品: {$product['name']}\n";
        } else {
            echo "商品已存在: {$product['name']}\n";
        }
    }
    
    echo "\n=== 設定商品分類關聯 ===\n";
    
    // 設定商品分類關聯
    $product_categories = [
        ['product_name' => '海賊王 路飛 公仔', 'category_names' => ['動漫公仔', '景品公仔']],
        ['product_name' => '鬼滅之刃 炭治郎 模型', 'category_names' => ['動漫公仔', 'PVC模型']],
        ['product_name' => '新世紀福音戰士 初號機 RG', 'category_names' => ['鋼彈模型', 'RG系列']],
        ['product_name' => '獵人 小傑 景品公仔', 'category_names' => ['動漫公仔', '景品公仔']],
        ['product_name' => '自由鋼彈 MG版', 'category_names' => ['鋼彈模型', 'MG系列']],
        ['product_name' => '進擊的巨人 兵長 手辦', 'category_names' => ['動漫公仔', '手辦模型']],
    ];
    
    foreach ($product_categories as $pc) {
        // 獲取商品ID
        $product_stmt = $conn->prepare("SELECT id FROM products WHERE name = ?");
        $product_stmt->execute([$pc['product_name']]);
        $product = $product_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            $product_id = $product['id'];
            
            foreach ($pc['category_names'] as $category_name) {
                // 獲取分類ID
                $category_stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
                $category_stmt->execute([$category_name]);
                $category = $category_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($category) {
                    $category_id = $category['id'];
                    
                    // 檢查關聯是否已存在
                    $check_stmt = $conn->prepare("SELECT 1 FROM product_category WHERE product_id = ? AND category_id = ?");
                    $check_stmt->execute([$product_id, $category_id]);
                    
                    if (!$check_stmt->fetch()) {
                        $insert_stmt = $conn->prepare("INSERT INTO product_category (product_id, category_id) VALUES (?, ?)");
                        $insert_stmt->execute([$product_id, $category_id]);
                        echo "新增關聯: {$pc['product_name']} -> {$category_name}\n";
                    } else {
                        echo "關聯已存在: {$pc['product_name']} -> {$category_name}\n";
                    }
                }
            }
        }
    }
    
    echo "\n=== 顯示最終資料統計 ===\n";
    
    // 顯示分類統計
    $stmt = $conn->query("SELECT COUNT(*) as count FROM categories WHERE status = 'active'");
    $category_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "活躍分類數量: {$category_count}\n";
    
    // 顯示商品統計
    $stmt = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'active'");
    $product_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "活躍商品數量: {$product_count}\n";
    
    // 顯示關聯統計
    $stmt = $conn->query("SELECT COUNT(*) as count FROM product_category");
    $relation_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "商品分類關聯數量: {$relation_count}\n";
    
    echo "\n=== 測試資料設定完成 ===\n";
    
} catch (Exception $e) {
    echo "錯誤: " . $e->getMessage() . "\n";
    echo "錯誤位置: " . $e->getFile() . " 第 " . $e->getLine() . " 行\n";
}
?>
