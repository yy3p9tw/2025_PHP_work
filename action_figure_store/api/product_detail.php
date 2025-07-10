<?php
// 取得資料庫連線
require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(null);
    exit;
}

$id = intval($_GET['id']);

try {
    $sql = "SELECT id, name, description, price, image_url, created_at FROM products WHERE id = :id LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        // 處理圖片 URL
        if ($row['image_url']) {
            $row['image_url'] = 'uploads/' . $row['image_url'];
        } else {
            $row['image_url'] = 'assets/images/placeholder_figure.jpg';
        }
        // 補上前端需要但資料表沒有的欄位
        $row['brand'] = null;
        $row['stock'] = null;
        echo json_encode($row, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(null);
    }
} catch (Exception $e) {
    echo json_encode(null);
}
