<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

$db = new Database();
$conn = $db->getConnection();

$slides = [];

try {
    $stmt = $conn->query("SELECT id, title, description, image_url FROM carousel_slides ORDER BY slide_order ASC, created_at DESC");
    $slides = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 處理圖片 URL，使其在前端可以正確顯示
    foreach ($slides as &$slide) {
        if ($slide['image_url']) {
            $slide['image_url'] = 'uploads/' . $slide['image_url'];
        } else {
            // 如果沒有圖片，可以使用預設佔位符或跳過
            $slide['image_url'] = 'assets/images/placeholder_banner1.jpg'; // 預設圖片
        }
    }

} catch (PDOException $e) {
    error_log("Error fetching carousel slides: " . $e->getMessage());
    echo json_encode(['error' => '無法獲取輪播資料。']);
    exit();
}

echo json_encode($slides);
?>