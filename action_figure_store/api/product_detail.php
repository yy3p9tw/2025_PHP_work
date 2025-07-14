<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $product_id = $_GET['id'] ?? null;
    
    if (!$product_id || !is_numeric($product_id)) {
        jsonResponse(false, null, '無效的商品ID');
    }
    
    $product_id = (int)$product_id;
    
    $product_query = "
        SELECT 
            p.id, p.name, p.description, p.price, p.image_url, p.created_at,
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
        jsonResponse(false, null, '商品不存在或已下架');
    }
    
    $product = formatProductData($product);

    $related_products = [];
    if (!empty($product['categories'])) {
        $category_ids = array_column($product['categories'], 'id');
        $placeholders = str_repeat('?,', count($category_ids) - 1) . '?';
        $related_query = "SELECT DISTINCT p.id, p.name, p.price, p.image_url FROM products p INNER JOIN product_category pc ON p.id = pc.product_id WHERE pc.category_id IN ($placeholders) AND p.id != ? ORDER BY p.created_at DESC LIMIT 4";
        
        $related_params = array_merge($category_ids, [$product_id]);
        $related_stmt = $conn->prepare($related_query);
        $related_stmt->execute($related_params);
        $related_products_raw = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($related_products_raw as $related_raw) {
            $related_products[] = formatProductData($related_raw);
        }
    }
    
    jsonResponse(true, ['product' => $product, 'related_products' => $related_products]);
    
} catch (Exception $e) {
    jsonResponse(false, null, $e->getMessage());
}
?>
