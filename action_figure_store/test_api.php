<?php
// API 測試腳本
require_once 'includes/db.php';

echo "=== API 測試 ===\n\n";

try {
    // 測試分類 API
    echo "1. 測試分類 API\n";
    ob_start();
    include 'api/categories.php';
    $categories_output = ob_get_clean();
    
    $categories_data = json_decode($categories_output, true);
    if ($categories_data && $categories_data['success']) {
        echo "✓ 分類 API 正常，共 " . count($categories_data['categories']) . " 個分類\n";
        foreach ($categories_data['categories'] as $cat) {
            echo "  - {$cat['name']} (ID: {$cat['id']})\n";
            if (!empty($cat['children'])) {
                foreach ($cat['children'] as $child) {
                    echo "    └─ {$child['name']} (ID: {$child['id']})\n";
                }
            }
        }
    } else {
        echo "✗ 分類 API 失敗\n";
        echo "回應: $categories_output\n";
    }
    
    echo "\n2. 測試商品 API\n";
    $_GET = ['page' => 1, 'limit' => 6]; // 模擬 GET 參數
    ob_start();
    include 'api/products.php';
    $products_output = ob_get_clean();
    
    $products_data = json_decode($products_output, true);
    if ($products_data && $products_data['success']) {
        echo "✓ 商品 API 正常，共 " . count($products_data['products']) . " 個商品\n";
        foreach ($products_data['products'] as $product) {
            echo "  - {$product['name']} ($" . $product['price'] . ")\n";
            if (!empty($product['categories'])) {
                echo "    分類: " . implode(', ', array_column($product['categories'], 'name')) . "\n";
            }
        }
    } else {
        echo "✗ 商品 API 失敗\n";
        echo "回應: $products_output\n";
    }
    
    echo "\n3. 測試特定分類商品\n";
    $_GET = ['category_id' => 1, 'page' => 1, 'limit' => 10]; // 動漫公仔分類
    ob_start();
    include 'api/products.php';
    $category_products_output = ob_get_clean();
    
    $category_products_data = json_decode($category_products_output, true);
    if ($category_products_data && $category_products_data['success']) {
        echo "✓ 分類篩選正常，動漫公仔分類共 " . count($category_products_data['products']) . " 個商品\n";
        foreach ($category_products_data['products'] as $product) {
            echo "  - {$product['name']}\n";
        }
    } else {
        echo "✗ 分類篩選失敗\n";
        echo "回應: $category_products_output\n";
    }
    
} catch (Exception $e) {
    echo "測試錯誤: " . $e->getMessage() . "\n";
}

echo "\n=== 測試完成 ===\n";
?>
