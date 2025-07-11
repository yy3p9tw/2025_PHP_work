<?php
require_once 'includes/db.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "=== 分類產品管理工具 ===\n";
    
    // 顯示有產品的分類
    echo "\n📊 有產品的分類列表：\n";
    $stmt = $conn->query("
        SELECT c.id, c.name, COUNT(pc.product_id) as product_count,
               GROUP_CONCAT(p.name SEPARATOR ', ') as product_names
        FROM categories c
        LEFT JOIN product_category pc ON c.id = pc.category_id
        LEFT JOIN products p ON pc.product_id = p.id
        GROUP BY c.id, c.name
        HAVING product_count > 0
        ORDER BY product_count DESC
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("分類「%s」(ID:%d) - %d個產品\n", 
            $row['name'], 
            $row['id'], 
            $row['product_count']
        );
        echo "  產品：" . $row['product_names'] . "\n\n";
    }
    
    // 提供操作選項
    echo "🔧 可用操作：\n";
    echo "1. 將特定分類的產品移到其他分類\n";
    echo "2. 清除特定分類的所有產品關聯\n";
    echo "3. 查看分類詳細資訊\n";
    
    echo "\n輸入操作 (1-3) 或 q 退出：";
    $choice = trim(fgets(STDIN));
    
    switch ($choice) {
        case '1':
            echo "請輸入來源分類 ID：";
            $source_id = (int)trim(fgets(STDIN));
            
            echo "請輸入目標分類 ID：";
            $target_id = (int)trim(fgets(STDIN));
            
            // 移動產品
            $stmt = $conn->prepare("UPDATE product_category SET category_id = ? WHERE category_id = ?");
            $result = $stmt->execute([$target_id, $source_id]);
            
            if ($result) {
                echo "✅ 成功將分類 {$source_id} 的產品移至分類 {$target_id}\n";
            } else {
                echo "❌ 移動失敗\n";
            }
            break;
            
        case '2':
            echo "請輸入要清除的分類 ID：";
            $category_id = (int)trim(fgets(STDIN));
            
            $stmt = $conn->prepare("DELETE FROM product_category WHERE category_id = ?");
            $result = $stmt->execute([$category_id]);
            
            if ($result) {
                echo "✅ 成功清除分類 {$category_id} 的所有產品關聯\n";
            } else {
                echo "❌ 清除失敗\n";
            }
            break;
            
        case '3':
            echo "請輸入分類 ID：";
            $category_id = (int)trim(fgets(STDIN));
            
            $stmt = $conn->prepare("
                SELECT c.*, COUNT(pc.product_id) as product_count
                FROM categories c
                LEFT JOIN product_category pc ON c.id = pc.category_id
                WHERE c.id = ?
                GROUP BY c.id
            ");
            $stmt->execute([$category_id]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($category) {
                echo "分類資訊：\n";
                echo "ID: {$category['id']}\n";
                echo "名稱: {$category['name']}\n";
                echo "描述: {$category['description']}\n";
                echo "狀態: " . ($category['status'] ? '啟用' : '停用') . "\n";
                echo "產品數量: {$category['product_count']}\n";
            } else {
                echo "❌ 分類不存在\n";
            }
            break;
            
        case 'q':
            echo "退出\n";
            exit;
            
        default:
            echo "❌ 無效選擇\n";
    }

} catch (Exception $e) {
    echo "❌ 錯誤: " . $e->getMessage() . "\n";
}
?>
