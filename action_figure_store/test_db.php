<?php
require_once 'includes/db.php';

echo "=== 測試資料庫連接和資料 ===\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "✓ 資料庫連接成功\n\n";
    
    // 測試分類資料
    echo "1. 分類資料:\n";
    $stmt = $conn->query("SELECT id, name, parent_id, status FROM categories ORDER BY parent_id, sort_order");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $indent = $row['parent_id'] ? '  └─ ' : '';
        echo "  {$indent}{$row['name']} (ID:{$row['id']}, 狀態:{$row['status']})\n";
    }
    
    echo "\n2. 商品資料:\n";
    $stmt = $conn->query("SELECT id, name, price, status FROM products ORDER BY created_at DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['name']} - \${$row['price']} (ID:{$row['id']}, 狀態:{$row['status']})\n";
    }
    
    echo "\n3. 商品分類關聯:\n";
    $stmt = $conn->query("
        SELECT p.name as product_name, c.name as category_name 
        FROM product_category pc 
        JOIN products p ON pc.product_id = p.id 
        JOIN categories c ON pc.category_id = c.id 
        ORDER BY p.name, c.name
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['product_name']} → {$row['category_name']}\n";
    }
    
} catch (Exception $e) {
    echo "錯誤: " . $e->getMessage() . "\n";
}
?>
