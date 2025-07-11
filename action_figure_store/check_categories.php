<?php
require_once 'includes/db.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // 檢查分類表是否存在並有數據
    $stmt = $conn->query("SELECT COUNT(*) as count FROM categories WHERE status = 'active'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "目前活躍分類數量: " . $result['count'] . "\n";
    
    if ($result['count'] == 0) {
        echo "沒有分類數據，正在插入範例數據...\n";
        
        // 插入主分類
        $mainCategories = [
            ['動漫公仔', '各種動漫角色公仔', null, 1],
            ['遊戲公仔', '各種遊戲角色公仔', null, 2],
            ['電影公仔', '各種電影角色公仔', null, 3],
            ['原創公仔', '原創設計公仔', null, 4]
        ];
        
        foreach ($mainCategories as $category) {
            $stmt = $conn->prepare("INSERT INTO categories (name, description, parent_id, sort_order, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->execute($category);
            echo "插入主分類: " . $category[0] . "\n";
        }
        
        // 插入子分類
        $subCategories = [
            ['火影忍者', '火影忍者系列公仔', 1, 1],
            ['鬼滅之刃', '鬼滅之刃系列公仔', 1, 2],
            ['海賊王', '海賊王系列公仔', 1, 3],
            ['進擊的巨人', '進擊的巨人系列公仔', 1, 4],
            ['英雄聯盟', '英雄聯盟角色公仔', 2, 1],
            ['原神', '原神角色公仔', 2, 2],
            ['最終幻想', '最終幻想系列公仔', 2, 3],
            ['漫威', '漫威電影公仔', 3, 1],
            ['DC', 'DC電影公仔', 3, 2]
        ];
        
        foreach ($subCategories as $category) {
            $stmt = $conn->prepare("INSERT INTO categories (name, description, parent_id, sort_order, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->execute($category);
            echo "插入子分類: " . $category[0] . "\n";
        }
        
        echo "分類數據插入完成！\n";
    } else {
        echo "分類數據已存在\n";
        
        // 顯示現有分類
        $stmt = $conn->query("SELECT id, name, parent_id, status FROM categories ORDER BY parent_id, sort_order");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $prefix = $row['parent_id'] ? '  └─ ' : '';
            echo $prefix . $row['name'] . " (ID: " . $row['id'] . ", 狀態: " . $row['status'] . ")\n";
        }
    }
    
} catch (Exception $e) {
    echo "錯誤: " . $e->getMessage() . "\n";
}
?>
