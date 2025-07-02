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


    // 寄送 Email
    $to = 'yourchurch@email.com'; // 請改成實際收件者
    $subject_mail = $subject ? ("[聯絡表單] $subject") : "[聯絡表單] 無主旨";
    $body = "姓名: $name\nEmail: $email\n主旨: $subject\n訊息:\n$message";
    $headers = "From: $email\r\nReply-To: $email\r\nContent-Type: text/plain; charset=utf-8";

    $mailResult = mail($to, $subject_mail, $body, $headers);

    if ($mailResult) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => '您的訊息已成功送出，我們會盡快回覆您。']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '訊息寄送失敗，請稍後再試。']);
    }

} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => '不允許的請求方法。']);
}
?>
