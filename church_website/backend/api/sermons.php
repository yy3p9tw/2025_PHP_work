<?php
// D:\YU\church_website\backend\api\sermons.php

header('Content-Type: application/json');
require_once '../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
switch ($method) {
    case 'GET': {
        $id = $_GET['id'] ?? null;
        if ($id) {
            // 取得單一講道
            $stmt = $conn->prepare("SELECT id, title, date, speaker, content, audio_url, video_url FROM sermons WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $sermon = $result->fetch_assoc();
            echo json_encode($sermon);
            $stmt->close();
        } else {
            // 取得所有講道列表
            $result = $conn->query("SELECT id, title, date, speaker, content, audio_url, video_url FROM sermons ORDER BY date DESC");
            $sermons = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($sermons);
        }
        break;
    }
    case 'POST':
        // 新增講道 (通常由後台管理介面處理，這裡僅為 API 範例)
        $title = $input['title'] ?? '';
        $date = $input['date'] ?? '';
        $speaker = $input['speaker'] ?? '';
        $content = $input['content'] ?? '';
        $audio_url = $input['audio_url'] ?? '';
        $video_url = $input['video_url'] ?? '';

        if (empty($title) || empty($date) || empty($speaker)) {
            http_response_code(400); // Bad Request
            echo json_encode(['message' => '標題、日期和講員為必填欄位。']);
            break;
        }

        $stmt = $conn->prepare("INSERT INTO sermons (title, date, speaker, content, audio_url, video_url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssss', $title, $date, $speaker, $content, $audio_url, $video_url);
        if ($stmt->execute()) {
            http_response_code(201); // Created
            echo json_encode(['message' => '講道新增成功！', 'id' => $conn->insert_id]);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['message' => '新增失敗: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'PUT':
        // 更新講道 (通常由後台管理介面處理，這裡僅為 API 範例)
        $id = $_GET['id'] ?? null; // PUT 請求通常 ID 在 URL 中
        if (!$id) {
            http_response_code(400);
            echo json_encode(['message' => '缺少講道 ID。']);
            break;
        }

        $title = $input['title'] ?? '';
        $date = $input['date'] ?? '';
        $speaker = $input['speaker'] ?? '';
        $content = $input['content'] ?? '';
        $audio_url = $input['audio_url'] ?? '';
        $video_url = $input['video_url'] ?? '';

        if (empty($title) || empty($date) || empty($speaker)) {
            http_response_code(400);
            echo json_encode(['message' => '標題、日期和講員為必填欄位。']);
            break;
        }

        $stmt = $conn->prepare("UPDATE sermons SET title = ?, date = ?, speaker = ?, content = ?, audio_url = ?, video_url = ? WHERE id = ?");
        $stmt->bind_param('ssssssi', $title, $date, $speaker, $content, $audio_url, $video_url, $id);
        if ($stmt->execute()) {
            echo json_encode(['message' => '講道更新成功！']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => '更新失敗: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'DELETE':
        // 刪除講道 (通常由後台管理介面處理，這裡僅為 API 範例)
        $id = $_GET['id'] ?? null; // DELETE 請求通常 ID 在 URL 中
        if (!$id) {
            http_response_code(400);
            echo json_encode(['message' => '缺少講道 ID。']);
            break;
        }

        $stmt = $conn->prepare("DELETE FROM sermons WHERE id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            echo json_encode(['message' => '講道刪除成功！']);
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
