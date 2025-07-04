<?php
header('Content-Type: application/json');
session_start();

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

// 檢查是否登入
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => '未授權']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // 簡單測試查詢
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM stocks WHERE status = 'active'");
    $stmt->execute();
    $count = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'API 測試成功',
        'stock_count' => $count['count'],
        'session_info' => [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username']
        ],
        'request_info' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'action' => $_GET['action'] ?? 'none',
            'get_params' => $_GET
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
