<?php
require_once 'includes/db.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "=== 檢查分類表結構 ===\n";
    $stmt = $conn->query('DESCRIBE categories');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . ' - Default: ' . ($row['Default'] ?? 'NULL') . "\n";
    }
    
    echo "\n=== 檢查商品表結構 ===\n";
    $stmt = $conn->query('DESCRIBE products');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . ' - Default: ' . ($row['Default'] ?? 'NULL') . "\n";
    }
    
    echo "\n=== 將測試資料設為 active ===\n";
    
    // 直接將需要的分類和商品設為 1 (active)
    $categories_to_activate = [1, 34, 35, 36, 37, 38, 39, 40, 41, 42]; // 對應新分類
    foreach ($categories_to_activate as $cat_id) {
        $stmt = $conn->prepare('UPDATE categories SET status = 1 WHERE id = ?');
        $stmt->execute([$cat_id]);
        echo "啟用分類 ID: $cat_id\n";
    }
    
    $products_to_activate = [10, 11, 12, 13, 14, 15]; // 對應新商品
    foreach ($products_to_activate as $prod_id) {
        $stmt = $conn->prepare('UPDATE products SET status = 1 WHERE id = ?');
        $stmt->execute([$prod_id]);
        echo "啟用商品 ID: $prod_id\n";
    }
    
} catch (Exception $e) {
    echo "錯誤: " . $e->getMessage() . "\n";
}
?>
