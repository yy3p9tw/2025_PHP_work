<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

$db = new Database();
$conn = $db->getConnection();

// 獲取分頁參數
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 9; // 每頁顯示9個產品
$offset = ($page - 1) * $limit;

$products = [];
$total_products = 0;

try {
    // 獲取總產品數量
    $total_stmt = $conn->query("SELECT COUNT(*) FROM products");
    $total_products = $total_stmt->fetchColumn();

    // 獲取分頁產品資料
    $stmt = $conn->prepare("SELECT id, name, description, price, image_url FROM products ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 處理圖片 URL，使其在前端可以正確顯示
    foreach ($products as &$product) {
        if ($product['image_url']) {
            $product['image_url'] = 'uploads/' . $product['image_url'];
        } else {
            $product['image_url'] = 'assets/images/placeholder_figure.jpg'; // 預設圖片
        }
    }

    echo json_encode([
        'products' => $products,
        'total_products' => $total_products,
        'page' => $page,
        'limit' => $limit
    ]);

} catch (PDOException $e) {
    // 在生產環境中，不應直接輸出錯誤訊息
    error_log("Error fetching products: " . $e->getMessage());
    echo json_encode(['error' => '無法獲取產品資料。']);
    exit();
}
?>