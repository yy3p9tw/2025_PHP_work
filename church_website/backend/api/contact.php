<?php
// D:\YU\church_website\backend\api\contact.php

header('Content-Type: application/json');

// 允許跨域請求 (僅限開發環境，生產環境應限制特定來源)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 處理 OPTIONS 請求 (CORS 預檢)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    $subject = $input['subject'] ?? '';
    $message = $input['message'] ?? '';

    // 簡單驗證
    if (empty($name) || empty($email) || empty($message)) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => '姓名、Email 和訊息為必填欄位。']);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Email 格式不正確。']);
        exit();
    }

    // --- 在這裡可以加入將聯絡訊息存入資料庫的邏輯，或發送 Email ---
    // 例如：
    // require_once '../includes/db.php';
    // $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    // $stmt->bind_param('ssss', $name, $email, $subject, $message);
    // if ($stmt->execute()) {
    //     // 成功
    // } else {
    //     // 失敗
    // }
    // $conn->close();

    http_response_code(200); // OK
    echo json_encode(['success' => true, 'message' => '您的訊息已成功送出，我們會盡快回覆您。']);

} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => '不允許的請求方法。']);
}
?>
