<?php
header('Content-Type: application/json');
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

// 檢查管理員權限
session_start();
try {
    requireLogin();
    requireAdmin();
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => '需要管理員權限'
    ]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $id = (int)($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        throw new Exception('無效的分類 ID');
    }
    
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        throw new Exception('分類不存在');
    }
    
    // 轉換狀態值：1 = active, 0 = inactive
    $category['status'] = ($category['status'] == 1) ? 'active' : 'inactive';
    
    echo json_encode([
        'success' => true,
        'data' => $category
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
