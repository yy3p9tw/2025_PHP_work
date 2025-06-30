<?php
// D:\YU\church_website\backend\api\events.php

header('Content-Type: application/json');
require_once '../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        $id = $_GET['id'] ?? null;
        if ($id) {
            // 取得單一活動
            $stmt = $conn->prepare("SELECT id, title, date, location, description FROM events WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $event = $result->fetch_assoc();
            echo json_encode($event);
            $stmt->close();
        } else {
            // 取得所有活動列表
            $result = $conn->query("SELECT id, title, date, location, description FROM events ORDER BY date DESC");
            $events = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($events);
        }
        break;

    case 'POST':
        // 新增活動 (通常由後台管理介面處理，這裡僅為 API 範例)
        $title = $input['title'] ?? '';
        $date = $input['date'] ?? '';
        $location = $input['location'] ?? '';
        $description = $input['description'] ?? '';

        if (empty($title) || empty($date) || empty($location)) {
            http_response_code(400); // Bad Request
            echo json_encode(['message' => '標題、日期和地點為必填欄位。']);
            break;
        }

        $stmt = $conn->prepare("INSERT INTO events (title, date, location, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $title, $date, $location, $description);
        if ($stmt->execute()) {
            http_response_code(201); // Created
            echo json_encode(['message' => '活動新增成功！', 'id' => $conn->insert_id]);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['message' => '新增失敗: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'PUT':
        // 更新活動 (通常由後台管理介面處理，這裡僅為 API 範例)
        $id = $_GET['id'] ?? null; // PUT 請求通常 ID 在 URL 中
        if (!$id) {
            http_response_code(400);
            echo json_encode(['message' => '缺少活動 ID。']);
            break;
        }

        $title = $input['title'] ?? '';
        $date = $input['date'] ?? '';
        $location = $input['location'] ?? '';
        $description = $input['description'] ?? '';

        if (empty($title) || empty($date) || empty($location)) {
            http_response_code(400);
            echo json_encode(['message' => '標題、日期和地點為必填欄位。']);
            break;
        }

        $stmt = $conn->prepare("UPDATE events SET title = ?, date = ?, location = ?, description = ? WHERE id = ?");
        $stmt->bind_param('ssssi', $title, $date, $location, $description, $id);
        if ($stmt->execute()) {
            echo json_encode(['message' => '活動更新成功！']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => '更新失敗: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'DELETE':
        // 刪除活動 (通常由後台管理介面處理，這裡僅為 API 範例)
        $id = $_GET['id'] ?? null; // DELETE 請求通常 ID 在 URL 中
        if (!$id) {
            http_response_code(400);
            echo json_encode(['message' => '缺少活動 ID。']);
            break;
        }

        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            echo json_encode(['message' => '活動刪除成功！']);
        } else {
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
