<?php
// 簡單的 API 測試
require_once 'includes/db.php';

echo "=== 簡單 API 測試 ===\n\n";

try {
    // 測試分類 API
    echo "1. 測試分類 API\n";
    $_GET = []; // 清空 GET 參數
    
    ob_start();
    include 'api/categories.php';
    $output = ob_get_clean();
    
    $data = json_decode($output, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "✓ 分類 API 成功，返回 " . count($data['categories']) . " 個分類\n";
        foreach ($data['categories'] as $cat) {
            echo "  - {$cat['name']} (ID: {$cat['id']})\n";
        }
    } else {
        echo "✗ 分類 API 失敗\n";
        echo "原始輸出: $output\n";
    }
    
    echo "\n2. 測試商品 API\n";
    $_GET = ['page' => 1, 'limit' => 10]; // 設定 GET 參數
    
    ob_start();
    include 'api/products.php';
    $output = ob_get_clean();
    
    $data = json_decode($output, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "✓ 商品 API 成功，返回 " . count($data['products']) . " 個商品\n";
        foreach ($data['products'] as $product) {
            echo "  - {$product['name']} (\${$product['price']})\n";
            if (!empty($product['categories'])) {
                echo "    分類: " . implode(', ', array_column($product['categories'], 'name')) . "\n";
            }
        }
    } else {
        echo "✗ 商品 API 失敗\n";
        echo "原始輸出: $output\n";
        if ($data && isset($data['error'])) {
            echo "錯誤訊息: {$data['error']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "測試錯誤: " . $e->getMessage() . "\n";
}
?>
