<?php
require_once 'includes/db.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // 檢查商品數量
    $stmt = $conn->query('SELECT COUNT(*) as count FROM products');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "商品數量: " . $result['count'] . "\n";
    
    // 顯示前3個商品
    $stmt = $conn->query('SELECT id, name, price FROM products LIMIT 3');
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($products as $product) {
        echo "ID: {$product['id']}, 名稱: {$product['name']}, 價格: {$product['price']}\n";
    }
    
    // 檢查購物車表
    $stmt = $conn->query('SELECT COUNT(*) as count FROM cart_items');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "購物車項目數量: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "錯誤: " . $e->getMessage() . "\n";
}
?>
