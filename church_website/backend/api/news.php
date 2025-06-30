<?php
// D:\YU\church_website\backend\api\news.php

header('Content-Type: application/json');
require_once '../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        $id = $_GET['id'] ?? null;
        if ($id) {
            // 取得單一新聞
            $stmt = $conn->prepare("SELECT id, title, content, published_at FROM news WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $news_item = $result->fetch_assoc();
            echo json_encode($news_item);
            $stmt->close();
        } else {
            // 取得所有新聞列表
            $result = $conn->query("SELECT id, title, content, published_at FROM news ORDER BY published_at DESC");
            $all_news = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($all_news);
        }
        break;

    case 'POST':
        // 新增新聞 (通常由後台管理介面處理，這裡僅為 API 範例)
        $title = $input['title'] ?? '';
        $content = $input['content'] ?? '';
        $published_at = $input['published_at'] ?? '';

        if (empty($title) || empty($content) || empty($published_at)) {
            http_response_code(400); // Bad Request
            echo json_encode(['message' => '標題、內容和發佈時間為必填欄位。']);
            break;
        }

        $stmt = $conn->prepare("INSERT INTO news (title, content, published_at) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $title, $content, $published_at);
        if ($stmt->execute()) {
            http_response_code(201); // Created
            echo json_encode(['message' => '新聞新增成功！', 'id' => $conn->insert_id]);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['message' => '新增失敗: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'PUT':
        // 更新新聞 (通常由後台管理介面處理，這裡僅為 API 範例)
        $id = $_GET['id'] ?? null; // PUT 請求通常 ID 在 URL 中
        if (!$id) {
            http_response_code(400);
            echo json_encode(['message' => '缺少新聞 ID。']);
            break;
        }

        $title = $input['title'] ?? '';
        $content = $input['content'] ?? '';
        $published_at = $input['published_at'] ?? '';

        if (empty($title) || empty($content) || empty($published_at)) {
            http_response_code(400);
            echo json_encode(['message' => '標題、內容和發佈時間為必填欄位。']);
            break;
        }

        $stmt = $conn->prepare("UPDATE news SET title = ?, content = ?, published_at = ? WHERE id = ?");
        $stmt->bind_param('sssi', $title, $content, $published_at, $id);
        if ($stmt->execute()) {
            echo json_encode(['message' => '新聞更新成功！']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => '更新失敗: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'DELETE':
        // 刪除新聞 (通常由後台管理介面處理，這裡僅為 API 範例)
        $id = $_GET['id'] ?? null; // DELETE 請求通常 ID 在 URL 中
        if (!$id) {
            http_response_code(400);
            echo json_encode(['message' => '缺少新聞 ID。']);
            break;
        }

        $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            echo json_encode(['message' => '新聞刪除成功！']);
        }
        else {
            http_response_code(500);
            echo json_encode(['message' => '刪除失敗: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['message' => '不允許的請求方法。']);
        break;
}

$conn->close();
?>