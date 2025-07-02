<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

$db = new Database();
$conn = $db->getConnection();

$products = [];

try {
    $stmt = $conn->query("SELECT id, name, description, price, image_url FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 處理圖片 URL，使其在前端可以正確顯示
    foreach ($products as &$product) {
        if ($product['image_url']) {
            $product['image_url'] = 'uploads/' . $product['image_url'];
        } else {
            $product['image_url'] = 'assets/images/placeholder_figure.jpg'; // 預設圖片
        }
    }

} catch (PDOException $e) {
    // 在生產環境中，不應直接輸出錯誤訊息
    error_log("Error fetching products: " . $e->getMessage());
    echo json_encode(['error' => '無法獲取產品資料。']);
    exit();
}

echo json_encode($products);
?>