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
    
    $input = json_decode(file_get_contents('php://input'), true);
    $categories = $input['categories'] ?? [];
    
    if (empty($categories)) {
        throw new Exception('沒有提供分類排序資料');
    }
    
    // 開始事務
    $conn->beginTransaction();
    
    try {
        foreach ($categories as $category) {
            $id = (int)$category['id'];
            $sort_order = (int)$category['sort_order'];
            $parent_id = !empty($category['parent_id']) ? (int)$category['parent_id'] : null;
            
            $stmt = $conn->prepare("UPDATE categories SET sort_order = ?, parent_id = ? WHERE id = ?");
            $stmt->execute([$sort_order, $parent_id, $id]);
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => '分類排序更新成功'
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
