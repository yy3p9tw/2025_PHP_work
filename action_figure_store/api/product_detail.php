<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // 獲取產品ID
    $product_id = $_GET['id'] ?? null;
    
    if (!$product_id || !is_numeric($product_id)) {
        throw new Exception('無效的產品ID');
    }
    
    $product_id = (int)$product_id;
    
    // 查詢產品詳細資料
    $product_query = "
        SELECT 
            p.id,
            p.name,
            p.description,
            p.price,
            p.image_url,
            p.created_at,
            GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as category_names,
            GROUP_CONCAT(DISTINCT c.id SEPARATOR ',') as category_ids
        FROM products p
        LEFT JOIN product_category pc ON p.id = pc.product_id
        LEFT JOIN categories c ON pc.category_id = c.id AND c.status = 1
        WHERE p.id = ?
        GROUP BY p.id
    ";
    
    $product_stmt = $conn->prepare($product_query);
    $product_stmt->execute([$product_id]);
    $product = $product_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        throw new Exception('產品不存在或已下架');
    }
    
    // 處理產品資料
    $product['id'] = (int)$product['id'];
    $product['price'] = (float)$product['price'];
    
    // 處理圖片URL
    if ($product['image_url']) {
        $product['image_url'] = 'uploads/' . $product['image_url'];
    } else {
        $product['image_url'] = 'assets/images/no-image.png';
    }
    
    // 處理分類
    $product['categories'] = [];
    if ($product['category_names']) {
        $names = explode(', ', $product['category_names']);
        $ids = explode(',', $product['category_ids']);
        for ($i = 0; $i < count($names); $i++) {
            if (isset($ids[$i]) && $ids[$i]) {
                $product['categories'][] = [
                    'id' => (int)$ids[$i],
                    'name' => trim($names[$i])
                ];
            }
        }
    }
    
    // 移除臨時欄位
    unset($product['category_names'], $product['category_ids']);
    
    // 格式化日期
    $product['created_at'] = date('Y-m-d H:i:s', strtotime($product['created_at']));
    
    // 獲取相關產品（同分類的其他產品）
    $related_products = [];
    if (!empty($product['categories'])) {
        $category_ids = array_column($product['categories'], 'id');
        $placeholders = str_repeat('?,', count($category_ids) - 1) . '?';
        
        $related_query = "
            SELECT DISTINCT
                p.id,
                p.name,
                p.price,
                p.image_url
            FROM products p
            INNER JOIN product_category pc ON p.id = pc.product_id
            WHERE pc.category_id IN ($placeholders)
            AND p.id != ?
            ORDER BY p.created_at DESC
            LIMIT 4
        ";
        
        $related_params = array_merge($category_ids, [$product_id]);
        $related_stmt = $conn->prepare($related_query);
        $related_stmt->execute($related_params);
        $related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 處理相關產品資料
        foreach ($related_products as &$related) {
            $related['id'] = (int)$related['id'];
            $related['price'] = (float)$related['price'];
            
            if ($related['image_url']) {
                $related['image_url'] = 'uploads/' . $related['image_url'];
            } else {
                $related['image_url'] = 'assets/images/no-image.png';
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'product' => $product,
        'related_products' => $related_products
    ]);
    
} catch (Exception $e) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
