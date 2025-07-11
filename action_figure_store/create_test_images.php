<?php
// 生成假圖片腳本
require_once 'includes/db.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "=== 生成假圖片並更新資料庫 ===\n";
    
    // 創建一個簡單的 SVG 圖片生成函數
    function createProductImage($name, $color = '#4f46e5') {
        $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg">
  <rect width="100%" height="100%" fill="' . $color . '"/>
  <rect x="50" y="50" width="300" height="300" fill="white" opacity="0.9"/>
  <text x="200" y="180" font-family="Arial, sans-serif" font-size="24" fill="' . $color . '" text-anchor="middle" font-weight="bold">公仔天堂</text>
  <text x="200" y="220" font-family="Arial, sans-serif" font-size="18" fill="' . $color . '" text-anchor="middle">' . htmlspecialchars($name) . '</text>
  <circle cx="200" cy="270" r="30" fill="' . $color . '" opacity="0.3"/>
  <rect x="170" y="240" width="60" height="60" fill="none" stroke="' . $color . '" stroke-width="2"/>
</svg>';
        return $svg;
    }
    
    // 顏色陣列
    $colors = ['#4f46e5', '#059669', '#dc2626', '#d97706', '#7c3aed', '#0891b2'];
    
    // 獲取所有商品
    $stmt = $conn->query("SELECT id, name FROM products WHERE status = 1");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $index => $product) {
        $color = $colors[$index % count($colors)];
        $filename = 'product_' . $product['id'] . '.svg';
        $filepath = 'uploads/' . $filename;
        
        // 生成 SVG 圖片
        $svg = createProductImage($product['name'], $color);
        file_put_contents($filepath, $svg);
        
        // 更新資料庫
        $updateStmt = $conn->prepare("UPDATE products SET image_url = ? WHERE id = ?");
        $updateStmt->execute([$filename, $product['id']]);
        
        echo "生成圖片: {$filepath} - {$product['name']}\n";
    }
    
    echo "\n=== 圖片生成完成 ===\n";
    echo "總共生成 " . count($products) . " 張商品圖片\n";
    
} catch (Exception $e) {
    echo "錯誤: " . $e->getMessage() . "\n";
}
?>
