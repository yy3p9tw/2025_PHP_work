<?php
// D:\YU\church_website\backend\api\pages.php

header('Content-Type: application/json');
require_once '../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $slug = $_GET['slug'] ?? null;
        if ($slug) {
            // 取得單一靜態頁面內容
            $stmt = $conn->prepare("SELECT id, slug, title, content FROM pages WHERE slug = ?");
            $stmt->bind_param('s', $slug);
            $stmt->execute();
            $result = $stmt->get_result();
            $page = $result->fetch_assoc();
            if ($page) {
                echo json_encode($page);
            } else {
                http_response_code(404); // Not Found
                echo json_encode(['message' => '靜態頁面不存在。']);
            }
            $stmt->close();
        } else {
            // 取得所有靜態頁面列表 (通常後台使用，前端可能不需要)
            $result = $conn->query("SELECT id, slug, title FROM pages ORDER BY title ASC");
            $all_pages = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($all_pages);
        }
        break;

    // 靜態頁面的 POST, PUT, DELETE 操作通常由後台管理介面處理，API 這裡僅提供讀取功能
    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['message' => '不允許的請求方法。']);
        break;
}

$conn->close();
?>
